<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\PostalCode;
use App\Models\RateCard;
use App\Models\RateSlab;

class RateCalculationService
{
    /**
     * Determine shipping zone between origin and destination pincodes.
     */
    public function determineZone(string $originPin, string $destinationPin): string
    {
        if (trim($originPin) === trim($destinationPin)) {
            return 'LOCAL';
        }

        $origin = PostalCode::with('zone')->where('code', trim($originPin))->first();
        $dest = PostalCode::with('zone')->where('code', trim($destinationPin))->first();

        if ($origin && $dest) {
            if ($origin->city && $dest->city && strcasecmp($origin->city, $dest->city) === 0) {
                return 'LOCAL';
            }
            if ($origin->state && $dest->state && strcasecmp($origin->state, $dest->state) === 0) {
                return 'REGIONAL';
            }
        }

        return 'NATIONAL';
    }

    /**
     * Calculate comprehensive shipping quotation.
     */
    public function calculate(
        int $companyId,
        string $originPin,
        string $destinationPin,
        float $chargeableWeight,
        string $serviceType = 'standard',
        string $paymentType = 'prepaid',
        float $codAmount = 0.00,
        float $declaredValue = 0.00,
        ?Customer $customer = null
    ): array {
        $zone = $this->determineZone($originPin, $destinationPin);

        // Find customer's custom rate card or company default rate card
        $rateCard = null;
        if ($customer && $customer->rate_card_id) {
            $rateCard = RateCard::find($customer->rate_card_id);
        }

        if (! $rateCard) {
            $rateCard = RateCard::where('company_id', $companyId)
                ->where('is_default', true)
                ->first();
        }

        if (! $rateCard) {
            $rateCard = RateCard::where('company_id', $companyId)->first();
        }

        // Default tariff if no rate slabs exist
        $baseWeight = 0.5;
        $baseRate = 10.00;
        $addWeight = 0.5;
        $addRate = 5.00;
        $codFixed = 2.00;
        $codPercent = 1.5;
        $fuelPercent = 5.0;
        $taxPercent = 18.0;

        if ($rateCard) {
            $slab = RateSlab::where('rate_card_id', $rateCard->id)
                ->where(function ($q) use ($zone) {
                    $q->where('zone_to', $zone)->orWhere('zone_to', 'ANY');
                })
                ->first();

            if (! $slab) {
                $slab = RateSlab::where('rate_card_id', $rateCard->id)->first();
            }

            if ($slab) {
                $baseWeight = (float) $slab->base_weight_kg;
                $baseRate = (float) $slab->base_rate;
                $addWeight = (float) $slab->additional_weight_kg > 0 ? (float) $slab->additional_weight_kg : 0.5;
                $addRate = (float) $slab->additional_rate;
                $codFixed = (float) $slab->cod_fixed_fee;
                $codPercent = (float) $slab->cod_percent;
                $fuelPercent = (float) $slab->fuel_surcharge_percent;
                $taxPercent = (float) $slab->gst_tax_percent;
            }
        }

        // Zone multipliers if needed
        $zoneMultiplier = match ($zone) {
            'LOCAL' => 1.0,
            'REGIONAL' => 1.3,
            'NATIONAL' => 1.7,
            default => 1.0,
        };

        // Service type multiplier
        $serviceMultiplier = match (strtolower($serviceType)) {
            'express' => 1.5,
            'same_day' => 2.2,
            'heavy' => 0.85,
            default => 1.0,
        };

        $effectiveBaseRate = round($baseRate * $zoneMultiplier * $serviceMultiplier, 2);
        $effectiveAddRate = round($addRate * $zoneMultiplier * $serviceMultiplier, 2);

        // Calculate freight for chargeable weight
        $freight = $effectiveBaseRate;
        if ($chargeableWeight > $baseWeight) {
            $excessWeight = $chargeableWeight - $baseWeight;
            $steps = ceil($excessWeight / $addWeight);
            $freight += ($steps * $effectiveAddRate);
        }

        $freight = round($freight, 2);

        // Fuel surcharge
        $fuelSurcharge = round(($freight * $fuelPercent) / 100, 2);

        // Handling fee
        $handlingFee = 0.00;

        // COD fee
        $codFee = 0.00;
        if (strtolower($paymentType) === 'cod' && $codAmount > 0) {
            $codPercentageFee = ($codAmount * $codPercent) / 100;
            $codFee = round(max($codFixed, $codPercentageFee), 2);
        }

        // Insurance fee (optional on high declared value > $500)
        $insuranceFee = 0.00;
        if ($declaredValue > 500) {
            $insuranceFee = round($declaredValue * 0.01, 2);
        }

        // Subtotal
        $subtotal = $freight + $fuelSurcharge + $handlingFee + $codFee + $insuranceFee;

        // GST / Tax
        $gstTax = round(($subtotal * $taxPercent) / 100, 2);

        // Grand total
        $totalAmount = round($subtotal + $gstTax, 2);

        return [
            'zone' => $zone,
            'base_freight' => $freight,
            'fuel_surcharge' => $fuelSurcharge,
            'handling_fee' => $handlingFee,
            'cod_fee' => $codFee,
            'insurance_fee' => $insuranceFee,
            'subtotal' => $subtotal,
            'gst_tax' => $gstTax,
            'total_freight' => $totalAmount,
            'tax_percent' => $taxPercent,
            'rate_card_id' => $rateCard?->id,
        ];
    }
}

