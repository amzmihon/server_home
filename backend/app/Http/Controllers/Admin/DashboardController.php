<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Invoice;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        // Revenue statistics
        $todayRevenue = Payment::where('status', Payment::STATUS_CAPTURED)
            ->whereDate('created_at', today())
            ->sum('amount');

        $monthRevenue = Payment::where('status', Payment::STATUS_CAPTURED)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $totalRevenue = Payment::where('status', Payment::STATUS_CAPTURED)->sum('amount');

        // Order statistics
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', Order::STATUS_PENDING)->count();
        $completedOrders = Order::where('status', Order::STATUS_COMPLETED)->count();

        // Payment statistics
        $pendingPayments = Payment::where('status', Payment::STATUS_PENDING)->count();
        $failedPayments = Payment::where('status', Payment::STATUS_FAILED)->count();

        // Recent activity
        $recentOrders = Order::with('user', 'package')
            ->latest()
            ->limit(10)
            ->get();

        $recentPayments = Payment::with('order', 'user')
            ->latest()
            ->limit(10)
            ->get();

        $recentAuditLogs = AuditLog::with('user')
            ->latest()
            ->limit(20)
            ->get();

        // Revenue chart data (last 7 days)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = Payment::where('status', Payment::STATUS_CAPTURED)
                ->whereDate('created_at', $date)
                ->sum('amount');
            
            $chartData[] = [
                'date' => $date->format('M d'),
                'revenue' => $revenue,
            ];
        }

        return view('admin.dashboard.index', compact(
            'todayRevenue',
            'monthRevenue',
            'totalRevenue',
            'totalOrders',
            'pendingOrders',
            'completedOrders',
            'pendingPayments',
            'failedPayments',
            'recentOrders',
            'recentPayments',
            'recentAuditLogs',
            'chartData'
        ));
    }
}
