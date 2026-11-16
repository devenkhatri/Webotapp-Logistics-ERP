<?php

namespace App\Services;

use App\Models\DeliveryRun;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeliveryAssignmentService
{
    public function __construct(
        protected ShipmentTrackingService $trackingService
    ) {}

    /**
     * Create Delivery Run Sheet (DRS) and dispatch shipments out for delivery.
     */
    public function createRunSheet(int $companyId, int $agentId, array $shipmentIds, ?int $branchId = null, ?string $notes = null): DeliveryRun
    {
        return DB::transaction(function () use ($companyId, $agentId, $shipmentIds, $branchId, $notes) {
            $agent = User::findOrFail($agentId);
            $runNumber = 'DRS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $run = DeliveryRun::create([
                'company_id' => $companyId,
                'branch_id' => $branchId ?? $agent->branch_id,
                'run_sheet_number' => $runNumber,
                'delivery_agent_id' => $agent->id,
                'run_date' => now()->toDateString(),
                'status' => 'out_for_delivery',
                'notes' => $notes,
            ]);

            $shipments = Shipment::whereIn('id', $shipmentIds)->get();
            $totalCod = 0.0;
            $count = 0;

            foreach ($shipments as $idx => $shipment) {
                $count++;
                if ($shipment->isCod()) {
                    $totalCod += (float) $shipment->cod_amount;
                }

                $shipment->delivery_agent_id = $agent->id;
                $shipment->save();

                $run->shipments()->attach($shipment->id, [
                    'sequence_order' => $idx + 1,
                    'status' => 'assigned',
                ]);

                $this->trackingService->updateStatus(
                    shipment: $shipment,
                    newStatus: 'out_for_delivery',
                    statusTitle: 'Out for Delivery',
                    location: $shipment->receiver_city,
                    description: "Package is out for delivery with agent {$agent->name} (DRS: {$runNumber}).",
                    userId: $agent->id
                );
            }

            $run->total_shipments = $count;
            $run->total_cod_to_collect = round($totalCod, 2);
            $run->save();

            return $run;
        });
    }
}

