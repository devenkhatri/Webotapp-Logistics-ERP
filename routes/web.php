<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CodController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\HubController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\MerchantPortalController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\PublicWebsiteController;
use App\Http\Controllers\RtoController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

// Public Website & Tracking
Route::get('/', [PublicWebsiteController::class, 'index'])->name('home');
Route::get('/track/{awb?}', [TrackingController::class, 'track'])->name('tracking.public');

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Application Routes
Route::middleware(['auth'])->group(function () {
    // Master Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shipment Booking & Consignments
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
    Route::post('/shipments/calculate-rate', [ShipmentController::class, 'calculateRate'])->name('shipments.calculate_rate');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    Route::get('/shipments/{id}', [ShipmentController::class, 'show'])->name('shipments.show');
    Route::get('/shipments/{id}/label', [ShipmentController::class, 'label'])->name('shipments.label');
    Route::get('/shipments/{id}/invoice', [ShipmentController::class, 'invoice'])->name('shipments.invoice');
    Route::post('/shipments/{id}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.update_status');

    // Pickup Operations
    Route::get('/pickups', [PickupController::class, 'index'])->name('pickups.index');
    Route::get('/pickups/create', [PickupController::class, 'create'])->name('pickups.create');
    Route::post('/pickups', [PickupController::class, 'store'])->name('pickups.store');
    Route::post('/pickups/{id}/assign', [PickupController::class, 'assign'])->name('pickups.assign');
    Route::post('/pickups/{id}/complete', [PickupController::class, 'complete'])->name('pickups.complete');

    // Hub & Sorting Operations
    Route::get('/hubs', [HubController::class, 'index'])->name('hubs.index');
    Route::get('/hubs/inbound', [HubController::class, 'inbound'])->name('hubs.inbound');
    Route::post('/hubs/inbound', [HubController::class, 'processInbound'])->name('hubs.process_inbound');
    Route::get('/hubs/sorting', [HubController::class, 'sorting'])->name('hubs.sorting');
    Route::post('/hubs/bags', [HubController::class, 'createBag'])->name('hubs.create_bag');

    // Manifests & Linehaul Dispatch
    Route::get('/manifests', [ManifestController::class, 'index'])->name('manifests.index');
    Route::get('/manifests/create', [ManifestController::class, 'create'])->name('manifests.create');
    Route::post('/manifests', [ManifestController::class, 'store'])->name('manifests.store');
    Route::get('/manifests/{id}', [ManifestController::class, 'show'])->name('manifests.show');
    Route::post('/manifests/{id}/dispatch', [ManifestController::class, 'dispatchManifest'])->name('manifests.dispatch');
    Route::post('/manifests/{id}/receive', [ManifestController::class, 'receiveManifest'])->name('manifests.receive');

    // Last-Mile Delivery & Run Sheets
    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::get('/delivery/create', [DeliveryController::class, 'create'])->name('delivery.create');
    Route::post('/delivery', [DeliveryController::class, 'store'])->name('delivery.store');
    Route::get('/delivery/{id}', [DeliveryController::class, 'show'])->name('delivery.show');
    Route::get('/agent/deliveries', [DeliveryController::class, 'agentPortal'])->name('agent.portal');
    Route::post('/delivery/record-delivery', [DeliveryController::class, 'recordDelivery'])->name('delivery.record_delivery');
    Route::post('/delivery/record-failure', [DeliveryController::class, 'recordFailure'])->name('delivery.record_failure');

    // COD Collections & Settlements
    Route::get('/cod/collections', [CodController::class, 'collections'])->name('cod.collections');
    Route::post('/cod/collections/{id}/deposit', [CodController::class, 'depositToBranch'])->name('cod.deposit');
    Route::get('/cod/settlements', [CodController::class, 'settlements'])->name('cod.settlements');
    Route::post('/cod/settlements/generate', [CodController::class, 'generateSettlement'])->name('cod.generate_settlement');
    Route::post('/cod/settlements/{id}/mark-paid', [CodController::class, 'markPaid'])->name('cod.mark_paid');

    // Reverse Logistics & RTO
    Route::get('/rto', [RtoController::class, 'index'])->name('rto.index');
    Route::post('/rto/{id}/complete', [RtoController::class, 'complete'])->name('rto.complete');

    // Billing, Invoicing & Ledger
    Route::get('/billing/invoices', [BillingController::class, 'invoices'])->name('billing.invoices');
    Route::post('/billing/invoices/generate', [BillingController::class, 'generateInvoice'])->name('billing.generate_invoice');
    Route::get('/billing/invoices/{id}', [BillingController::class, 'showInvoice'])->name('billing.invoice_detail');
    Route::get('/billing/expenses', [BillingController::class, 'expenses'])->name('billing.expenses');
    Route::post('/billing/expenses', [BillingController::class, 'storeExpense'])->name('billing.store_expense');
    Route::get('/billing/ledger', [BillingController::class, 'ledger'])->name('billing.ledger');

    // Customer & Merchant Management
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');

    // Customer / Merchant Self-Service Portal
    Route::get('/portal', [MerchantPortalController::class, 'index'])->name('merchant.portal');
});
