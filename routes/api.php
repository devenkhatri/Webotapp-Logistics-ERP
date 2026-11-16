<?php

use App\Http\Controllers\Api\ShipmentApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/shipments/book', [ShipmentApiController::class, 'book']);
    Route::get('/shipments/track/{awb}', [ShipmentApiController::class, 'track']);
    Route::post('/rates/calculate', [ShipmentApiController::class, 'calculateRate']);
});
