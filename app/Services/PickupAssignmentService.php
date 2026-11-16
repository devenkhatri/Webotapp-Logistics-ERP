<?php

namespace App\Services;

use App\Models\PickupRequest;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PickupAssignmentService
{
    public function __construct(
        protected ShipmentTrackingService $trackingService
    ) {}

    /**
     * Create a new pickup request.
     */
    public function createRequest(array $data): PickupRequest
    {
        $pickupCode = 'PK-' . strtoupper(substr(uniqid(), -6));
        $data['pickup_code'] = $pickupCode;
        $data['status'] = 'pending';

        return PickupRequest::create($data);
    }

    /**
     * Assign pickup to a field agent.
     */
    public function assign(PickupRequest $pickup, int $agentId): PickupRequest
    {
        $agent = User::findOrFail($agentId);
        $pickup->assigned_agent_id = $agent->id;
        $pickup->status = 'assigned';
        $pickup->save();

        return $pickup;
    }

    /**
     * Complete pickup and mark shipments as picked up.
     */
    public function completePickup(PickupRequest $pickup, array $shipmentIds = [], ?int $agentId = null): PickupRequest
    {
        return DB::transaction(function () use ($pickup, $shipmentIds, $agentId) {
            $pickup->status = 'completed';
            $pickup->save();

            if (! empty($shipmentIds)) {
                $pickup->shipments()->syncWithoutDetaching($shipmentIds);

                $shipments = Shipment::whereIn('id', $shipmentIds)->get();
                foreach ($shipments as $shipment) {
                    $shipment->pickup_agent_id = $agentId ?? $pickup->assigned_agent_id;
                    $shipment->save();

                    $this->trackingService->updateStatus(
                        shipment: $shipment,
                        newStatus: 'picked_up',
                        statusTitle: 'Picked Up by Agent',
                        location: $pickup->city ?? $shipment->sender_city,
                        description: 'Shipment collected from sender premises by pickup agent.',
                        userId: $agentId ?? $pickup->assigned_agent_id
                    );
                }
            }

            return $pickup;
        });
    }
}

