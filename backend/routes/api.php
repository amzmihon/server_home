<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\PackageController as ClientPackageController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\PaymentController as ClientPaymentController;

Route::middleware('auth:sanctum')->group(function () {
    // Package endpoints
    Route::get('/packages', [ClientPackageController::class, 'list']);
    Route::get('/packages/{package}', [ClientPackageController::class, 'show']);
    Route::post('/packages/{package}/customize', [ClientPackageController::class, 'customize']);
    Route::post('/packages/{package}/validate-customization', [ClientPackageController::class, 'validateCustomization']);

    // Checkout endpoints
    Route::post('/checkout/process', [CheckoutController::class, 'process']);
    Route::post('/checkout/calculate-price', [CheckoutController::class, 'calculatePrice']);

    // Payment endpoints
    Route::get('/payment/{order}', [ClientPaymentController::class, 'show']);
    Route::post('/payment/{order}/stripe', [ClientPaymentController::class, 'processStripe']);
    Route::post('/payment/{order}/bkash', [ClientPaymentController::class, 'processBkash']);
    Route::get('/payment/confirmation', [ClientPaymentController::class, 'confirmation']);
});

// Webhook endpoints (public, but verified by gateway)
Route::post('/webhooks/{gateway}', [ClientPaymentController::class, 'webhook']);
