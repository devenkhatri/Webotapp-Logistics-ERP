<?php

namespace App\Services;

use App\Models\RtoRecord;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class RtoManagementService
{
    public function __construct(
        protected ShipmentTrackingService $trackingService
    ) {}

    /**
     * Initiate Return to Origin (RTO).
     */
    public function initiateRto(Shipment $shipment, string $reason, ?int $userId = null): RtoRecord
    {
        return DB::transaction(function () use ($shipment, $reason, $userId) {
            $rtoAwb = 'RTO-' . $shipment->awb_number;

            $shipment->return_awb_number = $rtoAwb;
            $shipment->return_reason = $reason;
            $shipment->status = 'rto_initiated';
            $shipment->save();

            $record = RtoRecord::create([
                'company_id' => $shipment->company_id,
                'original_shipment_id' => $shipment->id,
                'rto_awb' => $rtoAwb,
                'return_reason' => $reason,
                'status' => 'initiated',
                'initiated_by_id' => $userId,
            ]);

            $this->trackingService->updateStatus(
                shipment: $shipment,
                newStatus: 'rto_initiated',
                statusTitle: 'Return to Origin Initiated',
                location: $shipment->current_location,
                description: "RTO initiated. Reason: {$reason}. Return Tracking No: {$rtoAwb}",
                userId: $userId
            );

            return $record;
        });
    }

    /**
     * Mark RTO completed / returned to merchant.
     */
    public function completeRto(RtoRecord $rto, ?int $userId = null): RtoRecord
    {
        return DB::transaction(function () use ($rto, $userId) {
            $rto->status = 'delivered_to_merchant';
            $rto->delivered_to_merchant_at = now();
            $rto->save();

            $shipment = $rto->originalShipment;
            $shipment->status = 'rto_delivered';
            $shipment->save();

            $this->trackingService->updateStatus(
                shipment: $shipment,
                newStatus: 'rto_delivered',
                statusTitle: 'RTO Delivered to Merchant',
                location: $shipment->sender_city,
                description: 'Shipment safely returned and delivered back to merchant.',
                userId: $userId
            );

            return $rto;
        });
    }
}

