<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\PostalCode;
use App\Models\RateCard;
use App\Models\RateSlab;
use App\Models\ServiceZone;
use App\Services\AwbGeneratorService;
use App\Services\RateCalculationService;
use App\Services\WeightCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogisticsCalculationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_volumetric_weight_calculation(): void
    {
        $weightService = new WeightCalculationService();

        // 50 x 40 x 30 cm package / 5000 = 12.00 kg
        $vol = $weightService->calculatePackageVolumetric(50, 40, 30);
        $this->assertEquals(12.00, $vol);

        // When actual weight (5.00 kg) is less than volumetric (12.00 kg), chargeable is 12.00 kg
        $res = $weightService->compute(5.00, 50, 40, 30);
        $this->assertEquals(5.00, $res['actual_weight']);
        $this->assertEquals(12.00, $res['volumetric_weight']);
        $this->assertEquals(12.00, $res['chargeable_weight']);

        // When actual weight (15.00 kg) is greater than volumetric (12.00 kg), chargeable is 15.00 kg
        $res2 = $weightService->compute(15.00, 50, 40, 30);
        $this->assertEquals(15.00, $res2['chargeable_weight']);
    }

    public function test_awb_generator_service_sequence(): void
    {
        $company = Company::create([
            'name' => 'Apex Test Logistics',
            'code' => 'APEX_TEST',
        ]);

        $awbService = new AwbGeneratorService();

        $awb1 = $awbService->generate($company->id, 'CX');
        $awb2 = $awbService->generate($company->id, 'CX');
        $awb3 = $awbService->generate($company->id, 'CX');

        $this->assertEquals('CX100001', $awb1);
        $this->assertEquals('CX100002', $awb2);
        $this->assertEquals('CX100003', $awb3);
        $this->assertNotEquals($awb1, $awb2);
    }

    public function test_rate_calculation_engine_with_slabs(): void
    {
        $company = Company::create(['name' => 'Apex Test', 'code' => 'APT']);
        $zone = ServiceZone::create(['company_id' => $company->id, 'name' => 'Local Zone', 'code' => 'LOCAL']);
        PostalCode::create(['code' => '10001', 'city' => 'New York', 'state' => 'NY', 'zone_id' => $zone->id]);
        PostalCode::create(['code' => '10002', 'city' => 'New York', 'state' => 'NY', 'zone_id' => $zone->id]);

        $card = RateCard::create([
            'company_id' => $company->id,
            'name' => 'Test Card',
            'code' => 'TEST_CARD',
            'is_default' => true,
        ]);

        RateSlab::create([
            'rate_card_id' => $card->id,
            'zone_from' => 'LOCAL',
            'zone_to' => 'LOCAL',
            'base_weight_kg' => 0.5,
            'base_rate' => 10.00,
            'additional_weight_kg' => 0.5,
            'additional_rate' => 4.00,
            'cod_fixed_fee' => 2.00,
            'cod_percent' => 1.5,
            'fuel_surcharge_percent' => 5.0,
            'gst_tax_percent' => 18.0,
        ]);

        $rateService = new RateCalculationService();

        // 1.0 kg Local package: Base 0.5kg ($10) + 1 excess slab ($4) = $14.00 freight
        // Fuel 5% = $0.70
        // Subtotal = $14.70
        // Tax 18% = $2.65
        // Total = $17.35
        $quote = $rateService->calculate(
            companyId: $company->id,
            originPin: '10001',
            destinationPin: '10002',
            chargeableWeight: 1.0,
            serviceType: 'standard',
            paymentType: 'prepaid'
        );

        $this->assertEquals('LOCAL', $quote['zone']);
        $this->assertEquals(14.00, $quote['base_freight']);
        $this->assertEquals(0.70, $quote['fuel_surcharge']);
        $this->assertEquals(17.35, $quote['total_freight']);
    }
}
