<?php

namespace App\Services;

use App\Models\Hub;
use App\Models\Manifest;
use App\Models\Shipment;
use App\Models\ShipmentBag;
use Illuminate\Support\Facades\DB;

class ManifestService
{
    public function __construct(
        protected ShipmentTrackingService $trackingService
    ) {}

    /**
     * Create manifest with bags and direct shipments.
     */
    public function createManifest(
        int $companyId,
        int $sourceHubId,
        int $destinationHubId,
        ?int $vehicleId = null,
        ?int $driverId = null,
        array $shipmentIds = [],
        ?string $remarks = null
    ): Manifest {
        return DB::transaction(function () use ($companyId, $sourceHubId, $destinationHubId, $vehicleId, $driverId, $shipmentIds, $remarks) {
            $manifestNumber = 'MNF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $manifest = Manifest::create([
                'company_id' => $companyId,
                'manifest_number' => $manifestNumber,
                'source_hub_id' => $sourceHubId,
                'destination_hub_id' => $destinationHubId,
                'vehicle_id' => $vehicleId,
                'driver_id' => $driverId,
                'status' => 'draft',
                'remarks' => $remarks,
            ]);

            $totalWeight = 0.0;
            $totalCount = 0;

            if (! empty($shipmentIds)) {
                $shipments = Shipment::whereIn('id', $shipmentIds)->get();
                foreach ($shipments as $shipment) {
                    $manifest->shipments()->attach($shipment->id, ['status' => 'loaded']);
                    $totalWeight += (float) $shipment->chargeable_weight;
                    $totalCount++;
                }
            }

            $manifest->total_shipments = $totalCount;
            $manifest->total_weight = round($totalWeight, 2);
            $manifest->save();

            return $manifest;
        });
    }

    /**
     * Dispatch manifest for inter-hub transit.
     */
    public function dispatch(Manifest $manifest, ?int $userId = null): Manifest
    {
        return DB::transaction(function () use ($manifest, $userId) {
            $manifest->status = 'dispatched';
            $manifest->dispatched_at = now();
            $manifest->save();

            $sourceHub = Hub::find($manifest->source_hub_id);
            $destHub = Hub::find($manifest->destination_hub_id);

            foreach ($manifest->shipments as $shipment) {
                $this->trackingService->updateStatus(
                    shipment: $shipment,
                    newStatus: 'in_transit',
                    statusTitle: 'In Transit',
                    location: "En route to {$destHub->name}",
                    description: "Dispatched from {$sourceHub->name} on Manifest {$manifest->manifest_number}",
                    userId: $userId
                );
            }

            return $manifest;
        });
    }

    /**
     * Receive manifest at destination hub.
     */
    public function receive(Manifest $manifest, ?int $userId = null): Manifest
    {
        return DB::transaction(function () use ($manifest, $userId) {
            $manifest->status = 'received';
            $manifest->received_at = now();
            $manifest->save();

            $destHub = Hub::find($manifest->destination_hub_id);

            foreach ($manifest->shipments as $shipment) {
                $shipment->current_hub_id = $destHub->id;
                $shipment->save();

                $this->trackingService->updateStatus(
                    shipment: $shipment,
                    newStatus: 'received_at_hub',
                    statusTitle: 'Received at Destination Hub',
                    location: "Hub {$destHub->name}, {$destHub->city}",
                    description: "Arrived on Manifest {$manifest->manifest_number} and received at destination sorting center.",
                    userId: $userId
                );
            }

            return $manifest;
        });
    }
}
