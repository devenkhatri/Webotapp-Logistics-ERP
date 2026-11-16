<?php

namespace App\Services;

class WeightCalculationService
{
    protected float $divisor = 5000.0;

    public function setDivisor(float $divisor): self
    {
        $this->divisor = $divisor > 0 ? $divisor : 5000.0;
        return $this;
    }

    /**
     * Calculate volumetric weight for single package in kg.
     * Dimensions in cm.
     */
    public function calculatePackageVolumetric(float $length, float $width, float $height): float
    {
        $vol = ($length * $width * $height) / $this->divisor;
        return round($vol, 2);
    }

    /**
     * Compute chargeable weight given actual weight and dimensions.
     */
    public function compute(float $actualWeight, float $length, float $width, float $height): array
    {
        $volumetric = $this->calculatePackageVolumetric($length, $width, $height);
        $chargeable = max(round($actualWeight, 2), $volumetric);

        return [
            'actual_weight' => round($actualWeight, 2),
            'volumetric_weight' => $volumetric,
            'chargeable_weight' => $chargeable,
        ];
    }

    /**
     * Compute total metrics across multiple packages.
     *
     * @param array<array{weight: float, length: float, width: float, height: float}> $packages
     */
    public function computeMultiple(array $packages): array
    {
        $totalActual = 0.0;
        $totalVolumetric = 0.0;

        foreach ($packages as $pkg) {
            $act = (float) ($pkg['weight'] ?? 0);
            $l = (float) ($pkg['length'] ?? 10);
            $w = (float) ($pkg['width'] ?? 10);
            $h = (float) ($pkg['height'] ?? 10);

            $vol = $this->calculatePackageVolumetric($l, $w, $h);
            $totalActual += $act;
            $totalVolumetric += $vol;
        }

        $totalActual = round($totalActual, 2);
        $totalVolumetric = round($totalVolumetric, 2);
        $chargeable = max($totalActual, $totalVolumetric);

        return [
            'actual_weight' => $totalActual,
            'volumetric_weight' => $totalVolumetric,
            'chargeable_weight' => $chargeable,
            'pieces_count' => count($packages),
        ];
    }
}
