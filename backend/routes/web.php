<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CustomizationLimitController;

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Package management
    Route::resource('packages', PackageController::class);
    Route::post('packages/bulk-action', [PackageController::class, 'bulkAction']);

    // Feature management
    Route::resource('features', FeatureController::class);

    // Order management
    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('orders/{order}/generate-invoice', [OrderController::class, 'generateInvoice']);

    // Payment management
    Route::get('payments', [PaymentController::class, 'index'])->name('admin.payments.index');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('admin.payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('admin.payments.store');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('admin.payments.show');
    Route::post('payments/{payment}/refund', [PaymentController::class, 'refund'])->name('admin.payments.refund');
    Route::get('payments-dashboard', [PaymentController::class, 'dashboard'])->name('admin.payments.dashboard');

    // Customization limits
    Route::get('users/{user}/customization-limits', [CustomizationLimitController::class, 'index']);
    Route::post('users/{user}/customization-limits', [CustomizationLimitController::class, 'store']);
    Route::delete('users/{user}/customization-limits/{limit}', [CustomizationLimitController::class, 'destroy']);
    Route::post('customization-limits/bulk-set', [CustomizationLimitController::class, 'bulkSet']);
});
