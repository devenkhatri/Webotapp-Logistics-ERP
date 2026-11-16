<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Shipment;
use App\Services\RateCalculationService;
use App\Services\ShipmentBookingService;
use App\Services\ShipmentTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipmentApiController extends Controller
{
    public function __construct(
        protected ShipmentBookingService $bookingService,
        protected ShipmentTrackingService $trackingService,
        protected RateCalculationService $rateService
    ) {}

    /**
     * Book shipment via REST API.
     */
    public function book(Request $request): JsonResponse
    {
        // Authenticate merchant via X-API-KEY or Bearer token
        $apiKey = $request->header('X-API-KEY') ?? $request->bearerToken();
        $customer = Customer::where('api_key', $apiKey)->first();

        $companyId = $customer?->company_id ?? 1;

        $validated = $request->validate([
            'reference_number' => 'nullable|string|max:100',
            'service_type' => 'required|in:standard,express,same_day,heavy',
            'payment_type' => 'required|in:prepaid,cod,to_pay,corporate_credit',
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:50',
            'sender_address' => 'required|string',
            'sender_city' => 'required|string|max:100',
            'sender_state' => 'nullable|string|max:100',
            'sender_postal_code' => 'required|string|max:20',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:50',
            'receiver_address' => 'required|string',
            'receiver_city' => 'required|string|max:100',
            'receiver_state' => 'nullable|string|max:100',
            'receiver_postal_code' => 'required|string|max:20',
            'content_description' => 'required|string|max:255',
            'declared_value' => 'nullable|numeric|min:0',
            'cod_amount' => 'nullable|numeric|min:0',
            'actual_weight' => 'required|numeric|min:0.05',
            'length' => 'nullable|numeric|min:1',
            'width' => 'nullable|numeric|min:1',
            'height' => 'nullable|numeric|min:1',
            'special_instructions' => 'nullable|string',
        ]);

        $validated['company_id'] = $companyId;
        $validated['customer_id'] = $customer?->id;

        try {
            $shipment = $this->bookingService->book($validated, $customer?->user_id);

            return response()->json([
                'success' => true,
                'message' => 'Shipment booked successfully',
                'data' => [
                    'awb_number' => $shipment->awb_number,
                    'reference_number' => $shipment->reference_number,
                    'status' => $shipment->status,
                    'chargeable_weight' => $shipment->chargeable_weight,
                    'total_freight' => $shipment->total_freight,
                    'delivery_otp' => $shipment->delivery_otp,
                    'estimated_delivery' => $shipment->estimated_delivery_at?->toIso8601String(),
                ],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Track shipment via REST API.
     */
    public function track(string $awb): JsonResponse
    {
        $shipment = $this->trackingService->track($awb);

        if (! $shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'awb_number' => $shipment->awb_number,
                'reference_number' => $shipment->reference_number,
                'status' => $shipment->status,
                'origin' => $shipment->sender_city,
                'destination' => $shipment->receiver_city,
                'current_location' => $shipment->current_location,
                'delivered_at' => $shipment->delivered_at?->toIso8601String(),
                'events' => $shipment->trackingEvents->map(fn ($e) => [
                    'status' => $e->status,
                    'title' => $e->status_title,
                    'location' => $e->location,
                    'description' => $e->description,
                    'time' => $e->event_time?->toIso8601String(),
                ]),
            ],
        ]);
    }

    /**
     * Rate quote calculator API.
     */
    public function calculateRate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'origin_postal_code' => 'required',
            'destination_postal_code' => 'required',
            'chargeable_weight' => 'required|numeric|min:0.1',
            'service_type' => 'nullable|string',
            'payment_type' => 'nullable|string',
            'cod_amount' => 'nullable|numeric',
            'declared_value' => 'nullable|numeric',
        ]);

        $quote = $this->rateService->calculate(
            companyId: 1,
            originPin: $validated['origin_postal_code'],
            destinationPin: $validated['destination_postal_code'],
            chargeableWeight: (float) $validated['chargeable_weight'],
            serviceType: $validated['service_type'] ?? 'standard',
            paymentType: $validated['payment_type'] ?? 'prepaid',
            codAmount: (float) ($validated['cod_amount'] ?? 0),
            declaredValue: (float) ($validated['declared_value'] ?? 0)
        );

        return response()->json([
            'success' => true,
            'data' => $quote,
        ]);
    }
}

