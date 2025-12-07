<?php

namespace App\Http\Controllers\Client;

use App\Models\Package;
use App\Models\Order;
use App\Models\CustomizationLimit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PackageController extends Controller
{
    /**
     * Get all active packages with features
     */
    public function list()
    {
        $packages = Package::active()
            ->with('features', 'category')
            ->get()
            ->groupBy('category.name');

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }

    /**
     * Get a specific package with all details
     */
    public function show(Package $package)
    {
        $package->load('features', 'category');

        return response()->json([
            'success' => true,
            'data' => $package,
        ]);
    }

    /**
     * Create a customized order
     */
    public function customize(Request $request, Package $package)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'customization' => 'nullable|array',
            'customization.*.feature_id' => 'exists:features,id',
            'customization.*.value' => 'required',
            'billing_cycle' => 'required|in:monthly,annually,biennial',
        ]);

        // Check customization limits
        if (!empty($validated['customization'])) {
            foreach ($validated['customization'] as $customization) {
                $limit = CustomizationLimit::where('user_id', $user->id)
                    ->where('feature_id', $customization['feature_id'])
                    ->first();

                if ($limit && !$limit->canAddMore($customization['value'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Customization limit exceeded for one or more features.',
                        'remaining' => $limit->getRemainingCapacity(),
                    ], 422);
                }
            }
        }

        // Calculate price
        $featureValues = [];
        if (!empty($validated['customization'])) {
            foreach ($validated['customization'] as $customization) {
                $featureValues[$customization['feature_id']] = $customization['value'];
            }
        }

        $totalPrice = $package->calculatePrice($featureValues);

        return response()->json([
            'success' => true,
            'data' => [
                'package_id' => $package->id,
                'package_name' => $package->name,
                'base_price' => $package->base_price,
                'setup_fee' => $package->setup_fee,
                'total_price' => $totalPrice,
                'billing_cycle' => $validated['billing_cycle'],
                'customization' => $featureValues,
            ],
        ]);
    }

    /**
     * Validate customization before checkout
     */
    public function validateCustomization(Request $request, Package $package)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'customization' => 'nullable|array',
            'customization.*.feature_id' => 'exists:features,id',
            'customization.*.value' => 'required',
        ]);

        $errors = [];
        $warnings = [];

        if (!empty($validated['customization'])) {
            foreach ($validated['customization'] as $idx => $customization) {
                $limit = CustomizationLimit::where('user_id', $user->id)
                    ->where('feature_id', $customization['feature_id'])
                    ->first();

                if ($limit && $limit->is_enforced) {
                    if (!$limit->canAddMore($customization['value'])) {
                        $errors[$idx] = "Limit exceeded. Remaining: {$limit->getRemainingCapacity()}";
                    }
                }
            }
        }

        return response()->json([
            'success' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ]);
    }
}
