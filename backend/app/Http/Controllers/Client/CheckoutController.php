<?php

namespace App\Http\Controllers\Client;

use App\Models\Order;
use App\Models\Payment;
use App\Models\CustomizationLimit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CheckoutController extends Controller
{
    /**
     * Process checkout and create order
     */
    public function process(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'customization' => 'nullable|array',
            'customization.*.feature_id' => 'exists:features,id',
            'customization.*.value' => 'required',
            'billing_cycle' => 'required|in:monthly,annually,biennial',
            'custom_fields' => 'nullable|array',
        ]);

        // Verify customization limits
        if (!empty($validated['customization'])) {
            foreach ($validated['customization'] as $customization) {
                $limit = CustomizationLimit::where('user_id', $user->id)
                    ->where('feature_id', $customization['feature_id'])
                    ->first();

                if ($limit && !$limit->canAddMore($customization['value'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Customization limit exceeded.',
                    ], 422);
                }
            }
        }

        $package = \App\Models\Package::findOrFail($validated['package_id']);

        // Calculate pricing
        $featureValues = [];
        if (!empty($validated['customization'])) {
            foreach ($validated['customization'] as $customization) {
                $featureValues[$customization['feature_id']] = $customization['value'];
            }
        }

        $subtotal = $package->calculatePrice($featureValues);
        $taxAmount = $subtotal * 0.15; // 15% tax
        $totalAmount = $subtotal + $taxAmount;

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'order_number' => Order::generateOrderNumber(),
            'customization_data' => $featureValues,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'billing_cycle' => $validated['billing_cycle'],
            'status' => Order::STATUS_PENDING,
            'custom_fields' => $validated['custom_fields'] ?? [],
        ]);

        // Update customization limits
        if (!empty($validated['customization'])) {
            foreach ($validated['customization'] as $customization) {
                CustomizationLimit::where('user_id', $user->id)
                    ->where('feature_id', $customization['feature_id'])
                    ->increment('current_value', $customization['value']);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'payment_url' => route('client.payment.process', ['order' => $order]),
            ],
        ]);
    }

    /**
     * Calculate total price
     */
    public function calculatePrice(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'customization' => 'nullable|array',
            'customization.*.feature_id' => 'exists:features,id',
            'customization.*.value' => 'required|numeric',
        ]);

        $package = \App\Models\Package::findOrFail($validated['package_id']);

        $featureValues = [];
        if (!empty($validated['customization'])) {
            foreach ($validated['customization'] as $customization) {
                $featureValues[$customization['feature_id']] = $customization['value'];
            }
        }

        $subtotal = $package->calculatePrice($featureValues);
        $taxAmount = $subtotal * 0.15;
        $totalAmount = $subtotal + $taxAmount;

        return response()->json([
            'success' => true,
            'data' => [
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ],
        ]);
    }
}
