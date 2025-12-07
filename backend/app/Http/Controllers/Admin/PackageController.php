<?php

namespace App\Http\Controllers\Admin;

use App\Models\Package;
use App\Models\Category;
use App\Models\Feature;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PackageController extends Controller
{
    /**
     * Display a listing of the packages.
     */
    public function index()
    {
        $packages = Package::with('category', 'features')
            ->latest()
            ->paginate(15);

        return view('admin.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        $categories = Category::active()->get();
        $features = Feature::where('is_active', true)->get()->groupBy('category_id');

        return view('admin.packages.create', compact('categories', 'features'));
    }

    /**
     * Store a newly created package in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:packages|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,annually,biennial',
            'setup_fee' => 'nullable|numeric|min:0',
            'is_popular' => 'boolean',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'order' => 'nullable|integer',
            'features' => 'nullable|array',
            'features.*.feature_id' => 'exists:features,id',
            'features.*.value' => 'nullable|string',
            'features.*.price_modifier' => 'nullable|numeric',
            'features.*.is_default' => 'boolean',
        ]);

        $package = Package::create($validated);

        // Attach features
        if (!empty($validated['features'])) {
            foreach ($validated['features'] as $feature) {
                $package->features()->attach($feature['feature_id'], [
                    'value' => $feature['value'] ?? null,
                    'price_modifier' => $feature['price_modifier'] ?? 0,
                    'is_default' => $feature['is_default'] ?? false,
                ]);
            }
        }

        AuditLog::log('CREATE', 'Package', $package->id, [
            'name' => $package->name,
            'base_price' => $package->base_price,
        ]);

        return redirect()
            ->route('admin.packages.show', $package)
            ->with('success', 'Package created successfully.');
    }

    /**
     * Display the specified package.
     */
    public function show(Package $package)
    {
        $package->load('category', 'features');

        return view('admin.packages.show', compact('package'));
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(Package $package)
    {
        $categories = Category::active()->get();
        $allFeatures = Feature::where('is_active', true)->get()->groupBy('category_id');
        $packageFeatures = $package->features()->get();

        return view('admin.packages.edit', compact('package', 'categories', 'allFeatures', 'packageFeatures'));
    }

    /**
     * Update the specified package in database.
     */
    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:packages,slug,' . $package->id . '|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,annually,biennial',
            'setup_fee' => 'nullable|numeric|min:0',
            'is_popular' => 'boolean',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'order' => 'nullable|integer',
            'features' => 'nullable|array',
            'features.*.feature_id' => 'exists:features,id',
            'features.*.value' => 'nullable|string',
            'features.*.price_modifier' => 'nullable|numeric',
            'features.*.is_default' => 'boolean',
        ]);

        $changes = $package->getChanges();
        $package->update($validated);

        // Sync features
        $featureSync = [];
        if (!empty($validated['features'])) {
            foreach ($validated['features'] as $feature) {
                $featureSync[$feature['feature_id']] = [
                    'value' => $feature['value'] ?? null,
                    'price_modifier' => $feature['price_modifier'] ?? 0,
                    'is_default' => $feature['is_default'] ?? false,
                ];
            }
        }
        $package->features()->sync($featureSync);

        AuditLog::log('UPDATE', 'Package', $package->id, $changes);

        return redirect()
            ->route('admin.packages.show', $package)
            ->with('success', 'Package updated successfully.');
    }

    /**
     * Remove the specified package from database.
     */
    public function destroy(Package $package)
    {
        $packageName = $package->name;
        $package->delete();

        AuditLog::log('DELETE', 'Package', $package->id, ['name' => $packageName]);

        return redirect()
            ->route('admin.packages.index')
            ->with('success', 'Package deleted successfully.');
    }

    /**
     * Bulk action endpoint
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete,duplicate',
            'ids' => 'required|array',
            'ids.*' => 'exists:packages,id',
        ]);

        $packages = Package::whereIn('id', $validated['ids'])->get();

        match ($validated['action']) {
            'activate' => $packages->each(fn ($p) => $p->update(['is_active' => true])),
            'deactivate' => $packages->each(fn ($p) => $p->update(['is_active' => false])),
            'delete' => $packages->each(fn ($p) => $p->delete()),
            default => null,
        };

        return response()->json([
            'success' => true,
            'message' => 'Action completed successfully.',
            'count' => $packages->count(),
        ]);
    }
}
