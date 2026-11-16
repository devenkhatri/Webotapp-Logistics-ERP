<?php

namespace App\Services;

use App\Models\Hub;
use App\Models\HubScan;
use App\Models\Shipment;
use App\Models\ShipmentBag;
use Illuminate\Support\Facades\DB;

class HubProcessingService
{
    public function __construct(
        protected ShipmentTrackingService $trackingService
    ) {}

    /**
     * Process inbound scan of a shipment at a hub.
     */
    public function inboundScan(Hub $hub, string $awbNumber, ?int $userId = null): Shipment
    {
        return DB::transaction(function () use ($hub, $awbNumber, $userId) {
            $shipment = Shipment::where('awb_number', trim($awbNumber))->firstOrFail();
            $shipment->current_hub_id = $hub->id;
            $shipment->current_location = "Hub - {$hub->name} ({$hub->city})";
            $shipment->status = 'received_at_hub';
            $shipment->save();

            HubScan::create([
                'hub_id' => $hub->id,
                'shipment_id' => $shipment->id,
                'scan_type' => 'inbound',
                'scanned_by' => $userId,
                'remarks' => 'Inbound arrival scan at hub',
            ]);

            $this->trackingService->updateStatus(
                shipment: $shipment,
                newStatus: 'received_at_hub',
                statusTitle: 'Received at Hub',
                location: "Hub {$hub->name}, {$hub->city}",
                description: "Package received and scanned at sorting center: {$hub->name}",
                userId: $userId
            );

            return $shipment;
        });
    }

    /**
     * Sort shipment into destination bin.
     */
    public function sortScan(Hub $hub, string $awbNumber, ?string $bin = null, ?int $userId = null): Shipment
    {
        return DB::transaction(function () use ($hub, $awbNumber, $bin, $userId) {
            $shipment = Shipment::where('awb_number', trim($awbNumber))->firstOrFail();
            $shipment->status = 'sorted';
            $shipment->save();

            HubScan::create([
                'hub_id' => $hub->id,
                'shipment_id' => $shipment->id,
                'scan_type' => 'sorting',
                'scanned_by' => $userId,
                'remarks' => $bin ? "Sorted to bin {$bin}" : 'Sorted for destination',
            ]);

            $this->trackingService->updateStatus(
                shipment: $shipment,
                newStatus: 'sorted',
                statusTitle: 'Sorted at Hub',
                location: "Hub {$hub->name}, {$hub->city}",
                description: "Package sorted for destination route. " . ($bin ? "[Bin: {$bin}]" : ''),
                userId: $userId
            );

            return $shipment;
        });
    }

    /**
     * Create a shipment bag and pack shipments into it.
     */
    public function createBag(int $originHubId, int $destHubId, array $shipmentIds, ?string $sealNumber = null, ?int $userId = null): ShipmentBag
    {
        return DB::transaction(function () use ($originHubId, $destHubId, $shipmentIds, $sealNumber, $userId) {
            $originHub = Hub::findOrFail($originHubId);
            $bagNumber = 'BAG-' . strtoupper(substr(uniqid(), -7));

            $bag = ShipmentBag::create([
                'company_id' => $originHub->company_id,
                'origin_hub_id' => $originHubId,
                'destination_hub_id' => $destHubId,
                'bag_number' => $bagNumber,
                'seal_number' => $sealNumber ?? ('SEAL-' . rand(100000, 999999)),
                'status' => 'sealed',
                'created_by_id' => $userId,
            ]);

            $totalWeight = 0.0;
            $shipments = Shipment::whereIn('id', $shipmentIds)->get();

            foreach ($shipments as $shipment) {
                $totalWeight += (float) $shipment->chargeable_weight;
                $bag->shipments()->attach($shipment->id);

                $this->trackingService->updateStatus(
                    shipment: $shipment,
                    newStatus: 'bagged',
                    statusTitle: 'Bagged for Transport',
                    location: "Hub {$originHub->name}",
                    description: "Package sealed in Bag: {$bagNumber}",
                    userId: $userId
                );
            }

            $bag->weight_kg = round($totalWeight, 2);
            $bag->save();

            return $bag;
        });
    }
}

