<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\DeliveryAttempt;
use App\Models\Hub;
use App\Models\PostalCode;
use App\Models\ServiceZone;
use App\Models\Shipment;
use App\Models\User;
use App\Services\CodSettlementService;
use App\Services\DeliveryAssignmentService;
use App\Services\DeliveryProofService;
use App\Services\HubProcessingService;
use App\Services\ManifestService;
use App\Services\ShipmentBookingService;
use App\Services\ShipmentTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Branch $branch;
    protected Hub $originHub;
    protected Hub $destHub;
    protected User $rider;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['name' => 'Apex Test ERP', 'code' => 'APEX']);
        $this->branch = Branch::create(['company_id' => $this->company->id, 'name' => 'NY Branch', 'code' => 'BR-NY', 'city' => 'New York']);
        $this->originHub = Hub::create(['company_id' => $this->company->id, 'branch_id' => $this->branch->id, 'name' => 'JFK Hub', 'code' => 'HUB-JFK', 'city' => 'New York']);
        $this->destHub = Hub::create(['company_id' => $this->company->id, 'branch_id' => $this->branch->id, 'name' => 'Chicago Hub', 'code' => 'HUB-ORD', 'city' => 'Chicago']);

        $zone = ServiceZone::create(['company_id' => $this->company->id, 'name' => 'Local', 'code' => 'LOCAL']);
        PostalCode::create(['code' => '10001', 'city' => 'New York', 'state' => 'NY', 'zone_id' => $zone->id]);
        PostalCode::create(['code' => '60601', 'city' => 'Chicago', 'state' => 'IL', 'zone_id' => $zone->id]);

        $this->rider = User::create([
            'company_id' => $this->company->id,
            'name' => 'Rider John',
            'email' => 'rider@test.com',
            'password' => bcrypt('password'),
            'role' => 'delivery_agent',
        ]);

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'customer_type' => 'merchant',
            'account_number' => 'CUST-TEST',
            'name' => 'Acme Merchant',
            'email' => 'merchant@test.com',
            'phone' => '1234567890',
            'api_key' => 'test_api_key_123',
        ]);
    }

    public function test_complete_booking_workflow(): void
    {
        $bookingService = app(ShipmentBookingService::class);

        $shipment = $bookingService->book([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'origin_hub_id' => $this->originHub->id,
            'destination_hub_id' => $this->destHub->id,
            'customer_id' => $this->customer->id,
            'sender_name' => 'Acme Warehouse',
            'sender_phone' => '1112223333',
            'sender_address' => '100 Main St',
            'sender_city' => 'New York',
            'sender_postal_code' => '10001',
            'receiver_name' => 'Jane Doe',
            'receiver_phone' => '4445556666',
            'receiver_address' => '200 State St',
            'receiver_city' => 'Chicago',
            'receiver_postal_code' => '60601',
            'content_description' => 'Apparel',
            'service_type' => 'express',
            'payment_type' => 'cod',
            'cod_amount' => 150.00,
            'actual_weight' => 2.0,
            'length' => 20,
            'width' => 15,
            'height' => 10,
        ]);

        $this->assertNotNull($shipment->id);
        $this->assertStringStartsWith('CX', $shipment->awb_number);
        $this->assertEquals('booked', $shipment->status);
        $this->assertNotNull($shipment->delivery_otp);
        $this->assertCount(1, $shipment->packages);
        $this->assertCount(1, $shipment->trackingEvents);

        // Test public tracking endpoint
        $response = $this->get('/track/' . $shipment->awb_number);
        $response->assertStatus(200);
        $response->assertSee($shipment->awb_number);
        $response->assertSee('Jane Doe');
    }

    public function test_hub_inbound_and_manifest_dispatch(): void
    {
        $bookingService = app(ShipmentBookingService::class);
        $shipment = $bookingService->book([
            'company_id' => $this->company->id,
            'sender_name' => 'Sender',
            'sender_phone' => '111',
            'sender_address' => 'Add 1',
            'sender_city' => 'New York',
            'sender_postal_code' => '10001',
            'receiver_name' => 'Receiver',
            'receiver_phone' => '222',
            'receiver_address' => 'Add 2',
            'receiver_city' => 'Chicago',
            'receiver_postal_code' => '60601',
            'actual_weight' => 1.5,
        ]);

        // Hub Inbound scan
        $hubService = app(HubProcessingService::class);
        $hubService->inboundScan($this->originHub, $shipment->awb_number);

        $shipment->refresh();
        $this->assertEquals('received_at_hub', $shipment->status);

        // Manifest Dispatch
        $manifestService = app(ManifestService::class);
        $manifest = $manifestService->createManifest(
            companyId: $this->company->id,
            sourceHubId: $this->originHub->id,
            destinationHubId: $this->destHub->id,
            shipmentIds: [$shipment->id]
        );

        $manifestService->dispatch($manifest);
        $shipment->refresh();
        $this->assertEquals('in_transit', $shipment->status);

        // Manifest Receive at Destination Hub
        $manifestService->receive($manifest);
        $shipment->refresh();
        $this->assertEquals('received_at_hub', $shipment->status);
        $this->assertEquals($this->destHub->id, $shipment->current_hub_id);
    }

    public function test_last_mile_delivery_and_pod(): void
    {
        $bookingService = app(ShipmentBookingService::class);
        $shipment = $bookingService->book([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'sender_name' => 'Sender',
            'sender_phone' => '111',
            'sender_address' => 'Add 1',
            'sender_city' => 'New York',
            'sender_postal_code' => '10001',
            'receiver_name' => 'Bob Smith',
            'receiver_phone' => '999',
            'receiver_address' => 'Add 2',
            'receiver_city' => 'New York',
            'receiver_postal_code' => '10001',
            'payment_type' => 'cod',
            'cod_amount' => 75.00,
            'actual_weight' => 1.0,
        ]);

        // DRS Dispatch
        $drsService = app(DeliveryAssignmentService::class);
        $run = $drsService->createRunSheet(
            companyId: $this->company->id,
            agentId: $this->rider->id,
            shipmentIds: [$shipment->id]
        );

        $shipment->refresh();
        $this->assertEquals('out_for_delivery', $shipment->status);

        // Deliver with OTP verification & COD
        $proofService = app(DeliveryProofService::class);
        $proof = $proofService->deliver(
            shipment: $shipment,
            recipientName: 'Bob Smith',
            recipientRelation: 'Self',
            enteredOtp: $shipment->delivery_otp,
            agentId: $this->rider->id,
            codPaymentMethod: 'cash'
        );

        $shipment->refresh();
        $this->assertEquals('delivered', $shipment->status);
        $this->assertNotNull($shipment->delivered_at);
        $this->assertNotNull($shipment->codCollection);
        $this->assertEquals(75.00, (float) $shipment->codCollection->amount);

        // Test COD Settlement generation for merchant
        $settlementService = app(CodSettlementService::class);
        $settlement = $settlementService->generateSettlement($this->customer);

        $this->assertNotNull($settlement);
        $this->assertEquals(75.00, (float) $settlement->total_cod_amount);
        $this->assertEquals('pending', $settlement->status);
    }

    public function test_delivery_failure_and_rto_initiation(): void
    {
        $bookingService = app(ShipmentBookingService::class);
        $shipment = $bookingService->book([
            'company_id' => $this->company->id,
            'sender_name' => 'Sender',
            'sender_phone' => '111',
            'sender_address' => 'Add 1',
            'sender_city' => 'New York',
            'sender_postal_code' => '10001',
            'receiver_name' => 'Refusing Customer',
            'receiver_phone' => '888',
            'receiver_address' => 'Add 2',
            'receiver_city' => 'New York',
            'receiver_postal_code' => '10001',
            'actual_weight' => 1.0,
        ]);

        $proofService = app(DeliveryProofService::class);

        // Customer refuses delivery -> auto triggers RTO
        $proofService->recordFailure(
            shipment: $shipment,
            reasonCode: 'refused_to_accept',
            remarks: 'Customer cancelled the purchase on doorstep',
            agentId: $this->rider->id
        );

        $shipment->refresh();
        $this->assertEquals('rto_initiated', $shipment->status);
        $this->assertStringStartsWith('RTO-', $shipment->return_awb_number);
    }

    public function test_rest_api_shipment_booking_and_tracking(): void
    {
        // 1. Book via API
        $response = $this->withHeader('X-API-KEY', $this->customer->api_key)
            ->postJson('/api/v1/shipments/book', [
                'reference_number' => 'API-ORD-555',
                'service_type' => 'express',
                'payment_type' => 'prepaid',
                'sender_name' => 'API Store',
                'sender_phone' => '5551112222',
                'sender_address' => '100 API Way',
                'sender_city' => 'New York',
                'sender_postal_code' => '10001',
                'receiver_name' => 'Online Shopper',
                'receiver_phone' => '5553334444',
                'receiver_address' => '500 Delivery Blvd',
                'receiver_city' => 'Chicago',
                'receiver_postal_code' => '60601',
                'content_description' => 'Smartphone Gadget',
                'actual_weight' => 0.8,
            ]);

        $response->assertStatus(201);
        $data = $response->json('data');
        $this->assertNotEmpty($data['awb_number']);
        $this->assertEquals('API-ORD-555', $data['reference_number']);

        // 2. Track via API
        $trackResponse = $this->getJson('/api/v1/shipments/track/' . $data['awb_number']);
        $trackResponse->assertStatus(200);
        $this->assertEquals($data['awb_number'], $trackResponse->json('data.awb_number'));
        $this->assertEquals('booked', $trackResponse->json('data.status'));
    }
}
