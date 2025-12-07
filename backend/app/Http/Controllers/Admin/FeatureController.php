<?php

namespace App\Http\Controllers\Admin;

use App\Models\Feature;
use App\Models\Category;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FeatureController extends Controller
{
    /**
     * Display a listing of the features.
     */
    public function index()
    {
        $features = Feature::with('category')
            ->latest()
            ->paginate(15);

        return view('admin.features.index', compact('features'));
    }

    /**
     * Show the form for creating a new feature.
     */
    public function create()
    {
        $categories = Category::active()->get();
        $types = Feature::validTypes();

        return view('admin.features.create', compact('categories', 'types'));
    }

    /**
     * Store a newly created feature in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:features|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', Feature::validTypes()),
            'options' => 'nullable|json', // For dropdown options
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
            'default_value' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
            'is_customizable' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        // Parse options if provided
        if ($request->has('options') && $request->options) {
            $validated['options'] = is_string($request->options) 
                ? json_decode($request->options, true)
                : $request->options;
        }

        $feature = Feature::create($validated);

        AuditLog::log('CREATE', 'Feature', $feature->id, [
            'name' => $feature->name,
            'type' => $feature->type,
        ]);

        return redirect()
            ->route('admin.features.show', $feature)
            ->with('success', 'Feature created successfully.');
    }

    /**
     * Display the specified feature.
     */
    public function show(Feature $feature)
    {
        $feature->load('category', 'packages');

        return view('admin.features.show', compact('feature'));
    }

    /**
     * Show the form for editing the specified feature.
     */
    public function edit(Feature $feature)
    {
        $categories = Category::active()->get();
        $types = Feature::validTypes();

        return view('admin.features.edit', compact('feature', 'categories', 'types'));
    }

    /**
     * Update the specified feature in database.
     */
    public function update(Request $request, Feature $feature)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:features,slug,' . $feature->id . '|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', Feature::validTypes()),
            'options' => 'nullable|json',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
            'default_value' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
            'is_customizable' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        if ($request->has('options') && $request->options) {
            $validated['options'] = is_string($request->options) 
                ? json_decode($request->options, true)
                : $request->options;
        }

        $changes = $feature->getChanges();
        $feature->update($validated);

        AuditLog::log('UPDATE', 'Feature', $feature->id, $changes);

        return redirect()
            ->route('admin.features.show', $feature)
            ->with('success', 'Feature updated successfully.');
    }

    /**
     * Remove the specified feature from database.
     */
    public function destroy(Feature $feature)
    {
        $featureName = $feature->name;
        $feature->delete();

        AuditLog::log('DELETE', 'Feature', $feature->id, ['name' => $featureName]);

        return redirect()
            ->route('admin.features.index')
            ->with('success', 'Feature deleted successfully.');
    }
}
