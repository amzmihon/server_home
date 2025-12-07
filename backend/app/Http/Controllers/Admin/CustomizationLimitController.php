<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\CustomizationLimit;
use App\Models\Feature;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomizationLimitController extends Controller
{
    /**
     * Display user customization limits
     */
    public function index(User $user)
    {
        $limits = CustomizationLimit::where('user_id', $user->id)
            ->with('feature')
            ->get();

        $availableFeatures = Feature::where('is_customizable', true)->get();

        return view('admin.customization-limits.index', compact('user', 'limits', 'availableFeatures'));
    }

    /**
     * Set or update customization limit for a user
     */
    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'feature_id' => 'required|exists:features,id',
            'max_value' => 'required|numeric|min:0',
            'is_enforced' => 'boolean',
        ]);

        $limit = CustomizationLimit::updateOrCreate(
            [
                'user_id' => $user->id,
                'feature_id' => $validated['feature_id'],
            ],
            [
                'max_value' => $validated['max_value'],
                'is_enforced' => $validated['is_enforced'] ?? true,
            ]
        );

        AuditLog::log('UPDATE', 'CustomizationLimit', $limit->id, [
            'user_id' => $user->id,
            'feature_id' => $validated['feature_id'],
            'max_value' => $validated['max_value'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customization limit updated successfully.',
        ]);
    }

    /**
     * Delete a customization limit
     */
    public function destroy(User $user, CustomizationLimit $limit)
    {
        if ($limit->user_id !== $user->id) {
            abort(403);
        }

        $limit->delete();

        AuditLog::log('DELETE', 'CustomizationLimit', $limit->id, ['user_id' => $user->id]);

        return back()->with('success', 'Customization limit removed.');
    }

    /**
     * Bulk set limits for multiple users
     */
    public function bulkSet(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'limits' => 'required|array',
            'limits.*.feature_id' => 'exists:features,id',
            'limits.*.max_value' => 'numeric|min:0',
        ]);

        foreach ($validated['user_ids'] as $userId) {
            foreach ($validated['limits'] as $limit) {
                CustomizationLimit::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'feature_id' => $limit['feature_id'],
                    ],
                    [
                        'max_value' => $limit['max_value'],
                        'is_enforced' => true,
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Limits updated for ' . count($validated['user_ids']) . ' users.',
        ]);
    }
}
