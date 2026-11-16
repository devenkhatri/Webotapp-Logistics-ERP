<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Hub;
use App\Models\PostalCode;
use App\Models\Shipment;
use App\Models\ShipmentPackage;
use App\Models\ShipmentTrackingEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipmentBookingService
{
    public function __construct(
        protected AwbGeneratorService $awbGenerator,
        protected WeightCalculationService $weightCalculator,
        protected RateCalculationService $rateCalculator,
        protected NotificationService $notificationService
    ) {}

    /**
     * Book a new shipment.
     */
    public function book(array $data, ?int $userId = null): Shipment
    {
        return DB::transaction(function () use ($data, $userId) {
            $companyId = $data['company_id'];
            $branchId = $data['branch_id'] ?? null;

            // Generate unique AWB
            $awb = $this->awbGenerator->generate($companyId);

            // Calculate weights
            $packages = $data['packages'] ?? [
                [
                    'weight' => $data['actual_weight'] ?? 0.5,
                    'length' => $data['length'] ?? 10,
                    'width' => $data['width'] ?? 10,
                    'height' => $data['height'] ?? 10,
                    'description' => $data['content_description'] ?? 'Standard Parcel',
                ]
            ];

            $weightMetrics = $this->weightCalculator->computeMultiple($packages);

            // Determine origin and destination hubs if not passed
            $originHubId = $data['origin_hub_id'] ?? null;
            $destHubId = $data['destination_hub_id'] ?? null;

            if (! $originHubId) {
                $originHubId = Hub::where('company_id', $companyId)->first()?->id;
            }
            if (! $destHubId) {
                $destHubId = Hub::where('company_id', $companyId)->first()?->id;
            }

            // Customer
            $customer = ! empty($data['customer_id']) ? Customer::find($data['customer_id']) : null;

            // Calculate rates and charges
            $chargeableWeight = $weightMetrics['chargeable_weight'];
            $serviceType = $data['service_type'] ?? 'standard';
            $paymentType = $data['payment_type'] ?? 'prepaid';
            $codAmount = (float) ($data['cod_amount'] ?? 0);
            $declaredValue = (float) ($data['declared_value'] ?? 0);

            $rates = $this->rateCalculator->calculate(
                companyId: $companyId,
                originPin: $data['sender_postal_code'],
                destinationPin: $data['receiver_postal_code'],
                chargeableWeight: $chargeableWeight,
                serviceType: $serviceType,
                paymentType: $paymentType,
                codAmount: $codAmount,
                declaredValue: $declaredValue,
                customer: $customer
            );

            // Delivery OTP
            $deliveryOtp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            // Create Shipment
            $shipment = Shipment::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'origin_hub_id' => $originHubId,
                'destination_hub_id' => $destHubId,
                'current_hub_id' => $originHubId,
                'customer_id' => $customer?->id,
                'awb_number' => $awb,
                'reference_number' => $data['reference_number'] ?? null,
                'shipment_type' => $data['shipment_type'] ?? 'forward',
                'service_type' => $serviceType,
                'payment_type' => $paymentType,
                'sender_name' => $data['sender_name'],
                'sender_phone' => $data['sender_phone'],
                'sender_email' => $data['sender_email'] ?? null,
                'sender_address' => $data['sender_address'],
                'sender_city' => $data['sender_city'],
                'sender_state' => $data['sender_state'] ?? null,
                'sender_postal_code' => $data['sender_postal_code'],
                'receiver_name' => $data['receiver_name'],
                'receiver_phone' => $data['receiver_phone'],
                'receiver_email' => $data['receiver_email'] ?? null,
                'receiver_address' => $data['receiver_address'],
                'receiver_city' => $data['receiver_city'],
                'receiver_state' => $data['receiver_state'] ?? null,
                'receiver_postal_code' => $data['receiver_postal_code'],
                'content_description' => $data['content_description'] ?? 'General Goods',
                'pieces_count' => $weightMetrics['pieces_count'],
                'actual_weight' => $weightMetrics['actual_weight'],
                'volumetric_weight' => $weightMetrics['volumetric_weight'],
                'chargeable_weight' => $weightMetrics['chargeable_weight'],
                'declared_value' => $declaredValue,
                'cod_amount' => $codAmount,
                'base_freight' => $rates['base_freight'],
                'fuel_surcharge' => $rates['fuel_surcharge'],
                'handling_fee' => $rates['handling_fee'],
                'cod_fee' => $rates['cod_fee'],
                'insurance_fee' => $rates['insurance_fee'],
                'gst_tax' => $rates['gst_tax'],
                'total_freight' => $rates['total_freight'],
                'payment_status' => $paymentType === 'prepaid' ? 'paid' : 'pending',
                'status' => 'booked',
                'current_location' => $data['sender_city'] ?? 'Origin Booking Office',
                'delivery_otp' => $deliveryOtp,
                'special_instructions' => $data['special_instructions'] ?? null,
                'estimated_delivery_at' => now()->addDays(match ($serviceType) {
                    'same_day' => 0,
                    'express' => 1,
                    default => 3,
                }),
            ]);

            // Create Packages
            foreach ($packages as $idx => $pkg) {
                $pkgWeight = (float) ($pkg['weight'] ?? 0.5);
                $l = (float) ($pkg['length'] ?? 10);
                $w = (float) ($pkg['width'] ?? 10);
                $h = (float) ($pkg['height'] ?? 10);
                $vol = round(($l * $w * $h) / 5000, 2);

                ShipmentPackage::create([
                    'shipment_id' => $shipment->id,
                    'package_barcode' => sprintf('%s-%02d', $awb, $idx + 1),
                    'weight' => $pkgWeight,
                    'length' => $l,
                    'width' => $w,
                    'height' => $h,
                    'volumetric_weight' => $vol,
                    'description' => $pkg['description'] ?? 'Package ' . ($idx + 1),
                ]);
            }

            // Create Initial Tracking Event
            ShipmentTrackingEvent::create([
                'shipment_id' => $shipment->id,
                'status' => 'booked',
                'status_title' => 'Shipment Booked',
                'location' => $shipment->current_location,
                'description' => 'Shipment has been registered and booking confirmed. AWB: ' . $awb,
                'user_id' => $userId,
                'event_time' => now(),
            ]);

            // Notify sender and receiver
            $this->notificationService->sendBookingNotification($shipment);

            return $shipment;
        });
    }
}
