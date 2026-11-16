<?php

namespace App\Services;

use App\Models\CodCollection;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class CodCollectionService
{
    /**
     * Record COD cash/UPI collection from recipient by delivery agent.
     */
    public function recordCollection(
        Shipment $shipment,
        float $amount,
        string $paymentMethod = 'cash',
        ?int $agentId = null
    ): CodCollection {
        return CodCollection::create([
            'company_id' => $shipment->company_id,
            'branch_id' => $shipment->branch_id,
            'shipment_id' => $shipment->id,
            'delivery_agent_id' => $agentId ?? $shipment->delivery_agent_id,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'is_deposited_to_branch' => false,
        ]);
    }

    /**
     * Verify and deposit collected cash at branch office.
     */
    public function depositToBranch(CodCollection $collection, int $verifiedByUserId): CodCollection
    {
        $collection->is_deposited_to_branch = true;
        $collection->deposited_at = now();
        $collection->verified_by_id = $verifiedByUserId;
        $collection->save();

        return $collection;
    }
}

