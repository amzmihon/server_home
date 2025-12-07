<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    /**
     * Display a listing of the payments.
     */
    public function index()
    {
        $payments = Payment::with('order', 'user')
            ->latest()
            ->paginate(20);

        $stats = [
            'total_revenue' => Payment::byStatus(Payment::STATUS_CAPTURED)->sum('amount'),
            'pending_payments' => Payment::byStatus(Payment::STATUS_PENDING)->count(),
            'failed_payments' => Payment::byStatus(Payment::STATUS_FAILED)->count(),
            'refunded' => Payment::byStatus(Payment::STATUS_REFUNDED)->sum('amount'),
        ];

        return view('admin.payments.index', compact('payments', 'stats'));
    }

    /**
     * Show the form for creating a manual payment.
     */
    public function create()
    {
        $orders = Order::where('status', Order::STATUS_PENDING)
            ->with('user', 'package')
            ->get();

        $gateways = [
            Payment::GATEWAY_STRIPE => 'Stripe',
            Payment::GATEWAY_BKASH => 'bKash',
            Payment::GATEWAY_NAGAD => 'Nagad',
            Payment::GATEWAY_BANK_TRANSFER => 'Bank Transfer',
        ];

        return view('admin.payments.create', compact('orders', 'gateways'));
    }

    /**
     * Store a manually created payment record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'gateway' => 'required|in:stripe,bkash,nagad,bank_transfer',
            'transaction_id' => 'nullable|string|unique:payments',
            'reference_id' => 'nullable|string',
            'status' => 'required|in:pending,authorized,captured,refunded,failed',
            'notes' => 'nullable|string',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'gateway' => $validated['gateway'],
            'transaction_id' => $validated['transaction_id'],
            'reference_id' => $validated['reference_id'],
            'amount' => $order->total_amount,
            'currency' => 'BDT',
            'status' => $validated['status'],
            'attempted_at' => now(),
            'completed_at' => in_array($validated['status'], [Payment::STATUS_CAPTURED, Payment::STATUS_AUTHORIZED]) ? now() : null,
        ]);

        // Update order status
        if ($validated['status'] === Payment::STATUS_CAPTURED) {
            $order->update(['status' => Order::STATUS_COMPLETED]);
        }

        AuditLog::log('CREATE', 'Payment', $payment->id, [
            'order_id' => $order->id,
            'amount' => $payment->amount,
            'gateway' => $payment->gateway,
        ]);

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $payment->load('order', 'user');

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Process a refund
     */
    public function refund(Payment $payment)
    {
        if (!$payment->isSuccessful()) {
            return back()->with('error', 'Only successful payments can be refunded.');
        }

        // Call payment gateway API for refund
        // This is a placeholder - implement actual refund logic based on gateway

        $payment->update([
            'status' => Payment::STATUS_REFUNDED,
        ]);

        $payment->order->update(['status' => Order::STATUS_CANCELLED]);

        AuditLog::log('REFUND', 'Payment', $payment->id, ['previous_status' => 'captured']);

        return back()->with('success', 'Payment refunded successfully.');
    }

    /**
     * Dashboard statistics
     */
    public function dashboard()
    {
        $stats = [
            'today_revenue' => Payment::where('status', Payment::STATUS_CAPTURED)
                ->whereDate('created_at', today())
                ->sum('amount'),
            'month_revenue' => Payment::where('status', Payment::STATUS_CAPTURED)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'pending_payments' => Payment::where('status', Payment::STATUS_PENDING)->count(),
            'total_transactions' => Payment::count(),
        ];

        $recentPayments = Payment::with('order', 'user')
            ->latest()
            ->limit(10)
            ->get();

        $gatewayStats = Payment::selectRaw('gateway, COUNT(*) as count, SUM(amount) as total')
            ->where('status', Payment::STATUS_CAPTURED)
            ->groupBy('gateway')
            ->get();

        return view('admin.payments.dashboard', compact('stats', 'recentPayments', 'gatewayStats'));
    }
}
