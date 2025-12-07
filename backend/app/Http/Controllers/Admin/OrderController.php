<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index()
    {
        $orders = Order::with('user', 'package', 'payment')
            ->latest()
            ->paginate(20);

        $stats = [
            'total_orders' => Order::count(),
            'completed' => Order::where('status', Order::STATUS_COMPLETED)->count(),
            'pending' => Order::where('status', Order::STATUS_PENDING)->count(),
            'failed' => Order::where('status', Order::STATUS_FAILED)->count(),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load('user', 'package', 'payment', 'invoices');

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Generate invoice for an order
     */
    public function generateInvoice(Order $order)
    {
        $existingInvoice = $order->invoices()->where('status', '!=', Invoice::STATUS_CANCELLED)->first();

        if ($existingInvoice) {
            return redirect()->route('admin.invoices.show', $existingInvoice);
        }

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoice_date' => today(),
            'due_date' => today()->addDays(30),
            'subtotal' => $order->subtotal,
            'tax_amount' => $order->tax_amount,
            'total_amount' => $order->total_amount,
            'status' => Invoice::STATUS_ISSUED,
            'items' => [
                [
                    'description' => $order->package->name,
                    'quantity' => 1,
                    'unit_price' => $order->subtotal,
                    'total' => $order->subtotal,
                ]
            ],
        ]);

        AuditLog::log('CREATE', 'Invoice', $invoice->id, ['order_id' => $order->id]);

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice generated successfully.');
    }

    /**
     * Cancel an order
     */
    public function cancel(Order $order)
    {
        if ($order->status === Order::STATUS_COMPLETED) {
            return back()->with('error', 'Cannot cancel a completed order.');
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);

        AuditLog::log('UPDATE', 'Order', $order->id, ['status' => Order::STATUS_CANCELLED]);

        return back()->with('success', 'Order cancelled successfully.');
    }
}
