<?php

namespace App\Http\Controllers\Client;

use App\Models\Order;
use App\Models\Payment;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    /**
     * Show payment page
     */
    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('client.payment.show', compact('order'));
    }

    /**
     * Process payment with Stripe
     */
    public function processStripe(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_method' => 'required|string',
        ]);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($order->total_amount * 100), // Amount in cents
                'currency' => 'bdt',
                'payment_method' => $validated['payment_method'],
                'confirm' => true,
                'return_url' => route('client.payment.confirmation'),
            ]);

            if ($paymentIntent->status === 'succeeded') {
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'gateway' => Payment::GATEWAY_STRIPE,
                    'transaction_id' => $paymentIntent->id,
                    'amount' => $order->total_amount,
                    'currency' => 'BDT',
                    'status' => Payment::STATUS_CAPTURED,
                    'completed_at' => now(),
                    'response_data' => $paymentIntent->toArray(),
                ]);

                $order->update(['status' => Order::STATUS_COMPLETED]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful!',
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment processing. Status: ' . $paymentIntent->status,
            ], 402);

        } catch (\Exception $e) {
            Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'gateway' => Payment::GATEWAY_STRIPE,
                'amount' => $order->total_amount,
                'currency' => 'BDT',
                'status' => Payment::STATUS_FAILED,
                'failed_reason' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Process payment with bKash
     */
    public function processBkash(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            // Initialize bKash payment
            // This is a placeholder - implement actual bKash API integration

            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'gateway' => Payment::GATEWAY_BKASH,
                'amount' => $order->total_amount,
                'currency' => 'BDT',
                'status' => Payment::STATUS_PENDING,
                'attempted_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'redirect_url' => 'https://bkash-payment-url.example.com',
                'payment_id' => $payment->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'bKash initialization failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Payment confirmation webhook
     */
    public function confirmation(Request $request)
    {
        $orderId = $request->query('order_id');
        $order = Order::findOrFail($orderId);

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $payment = $order->payment;

        return view('client.payment.confirmation', compact('order', 'payment'));
    }

    /**
     * Webhook for gateway callbacks
     */
    public function webhook(Request $request, string $gateway)
    {
        match (strtolower($gateway)) {
            'stripe' => $this->handleStripeWebhook($request),
            'bkash' => $this->handleBkashWebhook($request),
            default => abort(404),
        };

        return response()->json(['success' => true]);
    }

    private function handleStripeWebhook(Request $request)
    {
        // Implement Stripe webhook handling
    }

    private function handleBkashWebhook(Request $request)
    {
        // Implement bKash webhook handling
    }
}
