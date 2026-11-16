<?php

namespace App\Services;

use App\Models\CodSettlement;
use App\Models\CodSettlementItem;
use App\Models\Customer;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class CodSettlementService
{
    /**
     * Generate COD settlement for eligible delivered shipments of a customer.
     */
    public function generateSettlement(Customer $customer, ?int $processedById = null): ?CodSettlement
    {
        return DB::transaction(function () use ($customer, $processedById) {
            // Find delivered COD shipments that are not part of any existing settlement
            $shipments = Shipment::where('customer_id', $customer->id)
                ->where('payment_type', 'cod')
                ->where('status', 'delivered')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('cod_settlement_items')
                        ->whereColumn('cod_settlement_items.shipment_id', 'shipments.id');
                })
                ->get();

            if ($shipments->isEmpty()) {
                return null;
            }

            $settlementNumber = 'SETTLE-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $totalCod = 0.0;
            $totalDeductions = 0.0;

            foreach ($shipments as $s) {
                $totalCod += (float) $s->cod_amount;
                $totalDeductions += (float) ($s->total_freight ?? 0.0);
            }

            $netPayable = max(0.0, round($totalCod - $totalDeductions, 2));

            $settlement = CodSettlement::create([
                'company_id' => $customer->company_id,
                'settlement_number' => $settlementNumber,
                'customer_id' => $customer->id,
                'total_cod_amount' => round($totalCod, 2),
                'total_deductions' => round($totalDeductions, 2),
                'net_payable' => $netPayable,
                'payment_mode' => 'bank_transfer',
                'status' => 'pending',
                'processed_by_id' => $processedById,
            ]);

            foreach ($shipments as $s) {
                CodSettlementItem::create([
                    'cod_settlement_id' => $settlement->id,
                    'shipment_id' => $s->id,
                    'cod_amount' => (float) $s->cod_amount,
                    'deduction_fee' => (float) $s->total_freight,
                    'net_amount' => round((float) $s->cod_amount - (float) $s->total_freight, 2),
                ]);
            }

            return $settlement;
        });
    }

    /**
     * Mark settlement as paid.
     */
    public function markPaid(CodSettlement $settlement, string $transactionRef, ?int $userId = null): CodSettlement
    {
        $settlement->status = 'paid';
        $settlement->transaction_reference = $transactionRef;
        $settlement->settled_at = now();
        $settlement->processed_by_id = $userId ?? $settlement->processed_by_id;
        $settlement->save();

        return $settlement;
    }
}
