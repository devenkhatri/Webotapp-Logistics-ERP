<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentTrackingEvent;
use Illuminate\Support\Facades\DB;

class ShipmentTrackingService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Update shipment status and record timeline event.
     */
    public function updateStatus(
        Shipment $shipment,
        string $newStatus,
        string $statusTitle,
        ?string $location = null,
        ?string $description = null,
        ?int $userId = null
    ): Shipment {
        return DB::transaction(function () use ($shipment, $newStatus, $statusTitle, $location, $description, $userId) {
            $shipment->status = $newStatus;
            if ($location) {
                $shipment->current_location = $location;
            }

            if ($newStatus === 'delivered' && ! $shipment->delivered_at) {
                $shipment->delivered_at = now();
                $this->notificationService->sendDeliveredNotification($shipment);
            }

            if ($newStatus === 'out_for_delivery') {
                $this->notificationService->sendOutForDeliveryNotification($shipment);
            }

            $shipment->save();

            ShipmentTrackingEvent::create([
                'shipment_id' => $shipment->id,
                'status' => $newStatus,
                'status_title' => $statusTitle,
                'location' => $location ?? $shipment->current_location,
                'description' => $description ?? "Shipment status changed to {$statusTitle}",
                'user_id' => $userId,
                'event_time' => now(),
            ]);

            return $shipment;
        });
    }

    /**
     * Get shipment with full tracking events for public or dashboard view.
     */
    public function track(string $awbOrRef): ?Shipment
    {
        $term = trim($awbOrRef);
        return Shipment::with(['trackingEvents' => function ($q) {
            $q->orderBy('event_time', 'desc')->orderBy('id', 'desc');
        }, 'packages', 'deliveryProof', 'originHub', 'destinationHub'])
            ->where('awb_number', $term)
            ->orWhere('reference_number', $term)
            ->first();
    }
}

