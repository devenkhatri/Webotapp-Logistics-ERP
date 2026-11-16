<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CodCollection;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\DeliveryAttempt;
use App\Models\DeliveryProof;
use App\Models\DeliveryRun;
use App\Models\Driver;
use App\Models\Hub;
use App\Models\Manifest;
use App\Models\PickupRequest;
use App\Models\PostalCode;
use App\Models\RateCard;
use App\Models\RateSlab;
use App\Models\RtoRecord;
use App\Models\ServiceZone;
use App\Models\Shipment;
use App\Models\ShipmentPackage;
use App\Models\ShipmentTrackingEvent;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\AccountingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Company
        $company = Company::create([
            'name' => 'Apex Logistics Express Inc.',
            'code' => 'APEX',
            'email' => 'ops@apexlogistics.example',
            'phone' => '+1 (800) 555-0199',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'address' => '742 Evergreen Terrace, Suite 400',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'USA',
            'tax_number' => 'US-EIN-987654321',
            'status' => 'active',
            'settings' => [
                'volumetric_divisor' => 5000,
                'default_tax_rate' => 18.0,
                'cod_enabled' => true,
                'otp_delivery' => true,
            ],
        ]);

        // 2. Branches
        $branchNyc = Branch::create([
            'company_id' => $company->id,
            'name' => 'New York Central Branch',
            'code' => 'BR-NYC',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'address' => '100 Broadway, 12th Floor, Manhattan',
            'phone' => '+1 (212) 555-0101',
            'email' => 'nyc@apexlogistics.example',
            'is_head_office' => true,
            'status' => 'active',
        ]);

        $branchChi = Branch::create([
            'company_id' => $company->id,
            'name' => 'Chicago Midwest Branch',
            'code' => 'BR-CHI',
            'city' => 'Chicago',
            'state' => 'IL',
            'postal_code' => '60601',
            'address' => '200 Michigan Ave, Chicago',
            'phone' => '+1 (312) 555-0102',
            'email' => 'chi@apexlogistics.example',
            'is_head_office' => false,
            'status' => 'active',
        ]);

        $branchLax = Branch::create([
            'company_id' => $company->id,
            'name' => 'Los Angeles West Branch',
            'code' => 'BR-LAX',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'postal_code' => '90001',
            'address' => '500 S Grand Ave, Los Angeles',
            'phone' => '+1 (213) 555-0103',
            'email' => 'lax@apexlogistics.example',
            'is_head_office' => false,
            'status' => 'active',
        ]);

        // 3. Hubs
        $hubJfk = Hub::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'name' => 'JFK Gateway Air Hub',
            'code' => 'HUB-JFK',
            'hub_type' => 'airport',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '11430',
            'address' => 'Cargo Area D, JFK International Airport',
            'phone' => '+1 (718) 555-0190',
            'status' => 'active',
        ]);

        $hubOrd = Hub::create([
            'company_id' => $company->id,
            'branch_id' => $branchChi->id,
            'name' => 'O\'Hare Distribution Hub',
            'code' => 'HUB-ORD',
            'hub_type' => 'sorting',
            'city' => 'Chicago',
            'state' => 'IL',
            'postal_code' => '60666',
            'address' => 'Air Cargo Complex, Chicago O\'Hare',
            'phone' => '+1 (773) 555-0191',
            'status' => 'active',
        ]);

        $hubLax = Hub::create([
            'company_id' => $company->id,
            'branch_id' => $branchLax->id,
            'name' => 'LAX Super Sorting Hub',
            'code' => 'HUB-LAX',
            'hub_type' => 'sorting',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'postal_code' => '90045',
            'address' => 'World Way West, Los Angeles',
            'phone' => '+1 (310) 555-0192',
            'status' => 'active',
        ]);

        // 4. Service Zones & Postal Codes
        $zoneLocal = ServiceZone::create(['company_id' => $company->id, 'name' => 'Intra-City Local Zone', 'code' => 'LOCAL']);
        $zoneRegional = ServiceZone::create(['company_id' => $company->id, 'name' => 'Tri-State Regional Zone', 'code' => 'REGIONAL']);
        $zoneNational = ServiceZone::create(['company_id' => $company->id, 'name' => 'Coast-to-Coast National', 'code' => 'NATIONAL']);

        PostalCode::create(['code' => '10001', 'city' => 'New York', 'state' => 'NY', 'zone_id' => $zoneLocal->id]);
        PostalCode::create(['code' => '10002', 'city' => 'New York', 'state' => 'NY', 'zone_id' => $zoneLocal->id]);
        PostalCode::create(['code' => '11430', 'city' => 'New York', 'state' => 'NY', 'zone_id' => $zoneLocal->id]);
        PostalCode::create(['code' => '60601', 'city' => 'Chicago', 'state' => 'IL', 'zone_id' => $zoneRegional->id]);
        PostalCode::create(['code' => '60666', 'city' => 'Chicago', 'state' => 'IL', 'zone_id' => $zoneRegional->id]);
        PostalCode::create(['code' => '90001', 'city' => 'Los Angeles', 'state' => 'CA', 'zone_id' => $zoneNational->id]);
        PostalCode::create(['code' => '90045', 'city' => 'Los Angeles', 'state' => 'CA', 'zone_id' => $zoneNational->id]);

        // 5. Rate Cards & Slabs
        $rateCard = RateCard::create([
            'company_id' => $company->id,
            'name' => 'Standard Commercial Tariff',
            'code' => 'RATE-STD',
            'service_type' => 'standard',
            'is_default' => true,
            'status' => 'active',
        ]);

        RateSlab::create([
            'rate_card_id' => $rateCard->id,
            'zone_from' => 'LOCAL',
            'zone_to' => 'LOCAL',
            'base_weight_kg' => 0.5,
            'base_rate' => 8.00,
            'additional_weight_kg' => 0.5,
            'additional_rate' => 3.50,
            'cod_fixed_fee' => 2.00,
            'cod_percent' => 1.50,
            'fuel_surcharge_percent' => 5.00,
            'handling_fee' => 0.00,
            'gst_tax_percent' => 18.00,
        ]);

        RateSlab::create([
            'rate_card_id' => $rateCard->id,
            'zone_from' => 'LOCAL',
            'zone_to' => 'REGIONAL',
            'base_weight_kg' => 0.5,
            'base_rate' => 12.00,
            'additional_weight_kg' => 0.5,
            'additional_rate' => 5.00,
            'cod_fixed_fee' => 2.50,
            'cod_percent' => 1.50,
            'fuel_surcharge_percent' => 6.00,
            'handling_fee' => 1.00,
            'gst_tax_percent' => 18.00,
        ]);

        RateSlab::create([
            'rate_card_id' => $rateCard->id,
            'zone_from' => 'LOCAL',
            'zone_to' => 'NATIONAL',
            'base_weight_kg' => 0.5,
            'base_rate' => 18.00,
            'additional_weight_kg' => 0.5,
            'additional_rate' => 7.50,
            'cod_fixed_fee' => 3.00,
            'cod_percent' => 2.00,
            'fuel_surcharge_percent' => 8.00,
            'handling_fee' => 2.00,
            'gst_tax_percent' => 18.00,
        ]);

        // 6. Users
        $admin = User::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'name' => 'Alexander Vance (System Admin)',
            'email' => 'admin@courierx.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+1 (212) 555-1000',
            'status' => 'active',
        ]);

        $ops = User::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'hub_id' => $hubJfk->id,
            'name' => 'Elena Rostov (Ops Manager)',
            'email' => 'operations@courierx.com',
            'password' => Hash::make('password'),
            'role' => 'operations',
            'phone' => '+1 (212) 555-1001',
            'status' => 'active',
        ]);

        $rider1 = User::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'name' => 'David Miller (Delivery Agent)',
            'email' => 'rider1@courierx.com',
            'password' => Hash::make('password'),
            'role' => 'delivery_agent',
            'phone' => '+1 (917) 555-2001',
            'status' => 'active',
        ]);

        $rider2 = User::create([
            'company_id' => $company->id,
            'branch_id' => $branchChi->id,
            'name' => 'Sarah Jenkins (Delivery Agent)',
            'email' => 'rider2@courierx.com',
            'password' => Hash::make('password'),
            'role' => 'delivery_agent',
            'phone' => '+1 (312) 555-2002',
            'status' => 'active',
        ]);

        $driver = User::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'name' => 'James Rodriguez (Fleet Driver)',
            'email' => 'driver1@courierx.com',
            'password' => Hash::make('password'),
            'role' => 'driver',
            'phone' => '+1 (917) 555-3001',
            'status' => 'active',
        ]);

        $finance = User::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'name' => 'Rachel Green (Accountant)',
            'email' => 'finance@courierx.com',
            'password' => Hash::make('password'),
            'role' => 'accountant',
            'phone' => '+1 (212) 555-4001',
            'status' => 'active',
        ]);

        // 7. Customers & Merchants
        $merchantUser = User::create([
            'company_id' => $company->id,
            'name' => 'FashionNova Logistics Desk',
            'email' => 'merchant@fashionnova.demo',
            'password' => Hash::make('password'),
            'role' => 'merchant',
            'phone' => '+1 (800) 555-9001',
            'status' => 'active',
        ]);

        $customer1 = Customer::create([
            'company_id' => $company->id,
            'user_id' => $merchantUser->id,
            'rate_card_id' => $rateCard->id,
            'customer_type' => 'merchant',
            'account_number' => 'CUST-1001',
            'name' => 'FashionNova Direct',
            'company_name' => 'Fashion Nova Inc.',
            'email' => 'merchant@fashionnova.demo',
            'phone' => '+1 (800) 555-9001',
            'gst_number' => 'US-TAX-889911',
            'credit_limit' => 25000.00,
            'billing_cycle' => 'weekly',
            'api_key' => 'fn_live_key_98a72b1640c4',
            'api_secret' => 'fn_sec_7894561230',
            'status' => 'active',
        ]);

        CustomerAddress::create([
            'customer_id' => $customer1->id,
            'label' => 'Central Warehouse',
            'contact_name' => 'Dispatch Dept',
            'contact_phone' => '+1 (800) 555-9001',
            'address_line1' => '2800 S Alameda St',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'postal_code' => '90001',
            'is_default_pickup' => true,
        ]);

        $customer2 = Customer::create([
            'company_id' => $company->id,
            'rate_card_id' => $rateCard->id,
            'customer_type' => 'retail',
            'account_number' => 'CUST-1002',
            'name' => 'Jonathan Brand',
            'email' => 'jonathan@example.com',
            'phone' => '+1 (917) 555-8822',
            'status' => 'active',
        ]);

        // 8. Fleet & Vehicles
        $vehicle = Vehicle::create([
            'company_id' => $company->id,
            'vehicle_number' => 'NY-VAN-101',
            'model' => 'Ford Transit High Roof 2024',
            'type' => 'van',
            'capacity_kg' => 1800.00,
            'status' => 'available',
        ]);

        Driver::create([
            'company_id' => $company->id,
            'user_id' => $driver->id,
            'name' => 'James Rodriguez',
            'phone' => '+1 (917) 555-3001',
            'license_number' => 'DL-NY-9821415',
            'status' => 'active',
        ]);

        // 9. Accounting
        $accountingService = app(AccountingService::class);
        $accounts = $accountingService->ensureChartOfAccounts($company->id);

        // 10. Sample Shipments in various operational lifecycle stages
        // Shipment 1: Booked
        $s1 = Shipment::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'origin_hub_id' => $hubJfk->id,
            'destination_hub_id' => $hubOrd->id,
            'current_hub_id' => $hubJfk->id,
            'customer_id' => $customer1->id,
            'awb_number' => 'CX100001',
            'reference_number' => 'ORD-NOV-7701',
            'shipment_type' => 'forward',
            'service_type' => 'express',
            'payment_type' => 'corporate_credit',
            'sender_name' => 'FashionNova NYC Hub',
            'sender_phone' => '+1 (212) 555-8800',
            'sender_address' => '350 5th Ave',
            'sender_city' => 'New York',
            'sender_state' => 'NY',
            'sender_postal_code' => '10001',
            'receiver_name' => 'Michael Scott',
            'receiver_phone' => '+1 (312) 555-4433',
            'receiver_address' => '1725 Slough Ave, Suite 200',
            'receiver_city' => 'Chicago',
            'receiver_state' => 'IL',
            'receiver_postal_code' => '60601',
            'content_description' => 'Apparel & Accessories',
            'pieces_count' => 1,
            'actual_weight' => 1.20,
            'volumetric_weight' => 0.80,
            'chargeable_weight' => 1.20,
            'declared_value' => 120.00,
            'cod_amount' => 0.00,
            'base_freight' => 22.00,
            'fuel_surcharge' => 1.50,
            'handling_fee' => 1.00,
            'gst_tax' => 4.41,
            'total_freight' => 28.91,
            'payment_status' => 'pending',
            'status' => 'booked',
            'current_location' => 'New York Central Branch',
            'delivery_otp' => '419283',
            'estimated_delivery_at' => now()->addDays(2),
        ]);
        ShipmentPackage::create([
            'shipment_id' => $s1->id,
            'package_barcode' => 'CX100001-01',
            'weight' => 1.20,
            'length' => 25.00,
            'width' => 20.00,
            'height' => 8.00,
            'volumetric_weight' => 0.80,
            'description' => 'Winter Jacket',
        ]);
        ShipmentTrackingEvent::create([
            'shipment_id' => $s1->id,
            'status' => 'booked',
            'status_title' => 'Shipment Booked',
            'location' => 'New York Central Branch',
            'description' => 'Shipment created and registered in the system.',
            'user_id' => $admin->id,
            'event_time' => now()->subHours(5),
        ]);

        // Shipment 2: Picked Up
        $s2 = Shipment::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'origin_hub_id' => $hubJfk->id,
            'destination_hub_id' => $hubOrd->id,
            'current_hub_id' => $hubJfk->id,
            'customer_id' => $customer1->id,
            'awb_number' => 'CX100002',
            'reference_number' => 'ORD-NOV-7702',
            'shipment_type' => 'forward',
            'service_type' => 'standard',
            'payment_type' => 'prepaid',
            'sender_name' => 'FashionNova Warehouse',
            'sender_phone' => '+1 (212) 555-8800',
            'sender_address' => '500 W 33rd St',
            'sender_city' => 'New York',
            'sender_state' => 'NY',
            'sender_postal_code' => '10001',
            'receiver_name' => 'Pam Beesly',
            'receiver_phone' => '+1 (312) 555-6677',
            'receiver_address' => '440 N Michigan Ave',
            'receiver_city' => 'Chicago',
            'receiver_state' => 'IL',
            'receiver_postal_code' => '60601',
            'content_description' => 'Shoes & Handbag',
            'pieces_count' => 1,
            'actual_weight' => 2.00,
            'volumetric_weight' => 1.50,
            'chargeable_weight' => 2.00,
            'declared_value' => 210.00,
            'cod_amount' => 0.00,
            'base_freight' => 27.00,
            'fuel_surcharge' => 1.62,
            'handling_fee' => 1.00,
            'gst_tax' => 5.33,
            'total_freight' => 34.95,
            'payment_status' => 'paid',
            'status' => 'picked_up',
            'current_location' => 'In transit to JFK Hub',
            'pickup_agent_id' => $rider1->id,
            'delivery_otp' => '652194',
            'estimated_delivery_at' => now()->addDays(2),
        ]);
        ShipmentPackage::create([
            'shipment_id' => $s2->id,
            'package_barcode' => 'CX100002-01',
            'weight' => 2.00,
            'length' => 30.00,
            'width' => 25.00,
            'height' => 10.00,
            'volumetric_weight' => 1.50,
            'description' => 'Designer Boots',
        ]);
        ShipmentTrackingEvent::create([
            'shipment_id' => $s2->id,
            'status' => 'booked',
            'status_title' => 'Shipment Booked',
            'location' => 'New York Central Branch',
            'description' => 'Shipment registered.',
            'event_time' => now()->subHours(10),
        ]);
        ShipmentTrackingEvent::create([
            'shipment_id' => $s2->id,
            'status' => 'picked_up',
            'status_title' => 'Picked Up by Agent',
            'location' => 'New York, NY',
            'description' => 'Collected from merchant by agent David Miller.',
            'user_id' => $rider1->id,
            'event_time' => now()->subHours(4),
        ]);

        // Shipment 3: In Transit on Manifest
        $s3 = Shipment::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'origin_hub_id' => $hubJfk->id,
            'destination_hub_id' => $hubLax->id,
            'current_hub_id' => $hubJfk->id,
            'customer_id' => $customer2->id,
            'awb_number' => 'CX100003',
            'shipment_type' => 'forward',
            'service_type' => 'express',
            'payment_type' => 'prepaid',
            'sender_name' => 'Jonathan Brand',
            'sender_phone' => '+1 (917) 555-8822',
            'sender_address' => '120 W 42nd St',
            'sender_city' => 'New York',
            'sender_state' => 'NY',
            'sender_postal_code' => '10001',
            'receiver_name' => 'Jim Halpert',
            'receiver_phone' => '+1 (213) 555-9988',
            'receiver_address' => '900 Wilshire Blvd, Suite 1500',
            'receiver_city' => 'Los Angeles',
            'receiver_state' => 'CA',
            'receiver_postal_code' => '90001',
            'content_description' => 'Document & Legal Folio',
            'pieces_count' => 1,
            'actual_weight' => 0.40,
            'volumetric_weight' => 0.20,
            'chargeable_weight' => 0.50,
            'declared_value' => 50.00,
            'cod_amount' => 0.00,
            'base_freight' => 27.00,
            'fuel_surcharge' => 2.16,
            'handling_fee' => 2.00,
            'gst_tax' => 5.61,
            'total_freight' => 36.77,
            'payment_status' => 'paid',
            'status' => 'in_transit',
            'current_location' => 'Transit Flight NY-LA',
            'delivery_otp' => '718293',
            'estimated_delivery_at' => now()->addDays(1),
        ]);
        ShipmentPackage::create([
            'shipment_id' => $s3->id,
            'package_barcode' => 'CX100003-01',
            'weight' => 0.40,
            'length' => 30.00,
            'width' => 22.00,
            'height' => 2.00,
            'volumetric_weight' => 0.20,
            'description' => 'Confidential Docket',
        ]);
        ShipmentTrackingEvent::create([
            'shipment_id' => $s3->id,
            'status' => 'booked',
            'status_title' => 'Shipment Booked',
            'location' => 'New York',
            'event_time' => now()->subDay(),
        ]);
        ShipmentTrackingEvent::create([
            'shipment_id' => $s3->id,
            'status' => 'received_at_hub',
            'status_title' => 'Received at JFK Hub',
            'location' => 'JFK Gateway Air Hub',
            'event_time' => now()->subHours(18),
        ]);
        ShipmentTrackingEvent::create([
            'shipment_id' => $s3->id,
            'status' => 'in_transit',
            'status_title' => 'Dispatched on Flight Manifest MNF-202611-01',
            'location' => 'En route to LAX Super Hub',
            'event_time' => now()->subHours(6),
        ]);

        // Shipment 4: Out for Delivery (COD)
        $s4 = Shipment::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'origin_hub_id' => $hubJfk->id,
            'destination_hub_id' => $hubJfk->id,
            'current_hub_id' => $hubJfk->id,
            'customer_id' => $customer1->id,
            'awb_number' => 'CX100004',
            'reference_number' => 'ORD-NOV-7704',
            'shipment_type' => 'forward',
            'service_type' => 'standard',
            'payment_type' => 'cod',
            'sender_name' => 'FashionNova Dispatch',
            'sender_phone' => '+1 (800) 555-9001',
            'sender_address' => '2800 S Alameda St',
            'sender_city' => 'New York',
            'sender_state' => 'NY',
            'sender_postal_code' => '10001',
            'receiver_name' => 'Dwight Schrute',
            'receiver_phone' => '+1 (917) 555-3322',
            'receiver_address' => '350 5th Ave, Apt 14B',
            'receiver_city' => 'New York',
            'receiver_state' => 'NY',
            'receiver_postal_code' => '10001',
            'content_description' => 'Wool Suit & Ties',
            'pieces_count' => 1,
            'actual_weight' => 2.50,
            'volumetric_weight' => 1.80,
            'chargeable_weight' => 2.50,
            'declared_value' => 180.00,
            'cod_amount' => 180.00,
            'base_freight' => 22.00,
            'fuel_surcharge' => 1.10,
            'handling_fee' => 0.00,
            'cod_fee' => 2.70,
            'gst_tax' => 4.64,
            'total_freight' => 30.44,
            'payment_status' => 'pending',
            'status' => 'out_for_delivery',
            'current_location' => 'With rider David Miller',
            'delivery_agent_id' => $rider1->id,
            'delivery_otp' => '581920',
            'estimated_delivery_at' => now(),
        ]);
        ShipmentPackage::create([
            'shipment_id' => $s4->id,
            'package_barcode' => 'CX100004-01',
            'weight' => 2.50,
            'length' => 35.00,
            'width' => 25.00,
            'height' => 10.00,
            'volumetric_weight' => 1.80,
            'description' => 'Apparel Parcel',
        ]);
        ShipmentTrackingEvent::create([
            'shipment_id' => $s4->id,
            'status' => 'booked',
            'status_title' => 'Shipment Booked',
            'location' => 'New York',
            'event_time' => now()->subHours(24),
        ]);
        ShipmentTrackingEvent::create([
            'shipment_id' => $s4->id,
            'status' => 'received_at_hub',
            'status_title' => 'Received at Delivery Hub',
            'location' => 'JFK Hub',
            'event_time' => now()->subHours(8),
        ]);
        ShipmentTrackingEvent::create([
            'shipment_id' => $s4->id,
            'status' => 'out_for_delivery',
            'status_title' => 'Out for Delivery',
            'location' => 'Manhattan Delivery Route',
            'description' => 'Package out with David Miller. Verification OTP: 581920. COD to collect: $180.00.',
            'user_id' => $rider1->id,
            'event_time' => now()->subHours(2),
        ]);

        // Shipment 5: Successfully Delivered (with POD signature & COD collection)
        $s5 = Shipment::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'origin_hub_id' => $hubJfk->id,
            'destination_hub_id' => $hubJfk->id,
            'current_hub_id' => $hubJfk->id,
            'customer_id' => $customer1->id,
            'awb_number' => 'CX100005',
            'reference_number' => 'ORD-NOV-7705',
            'shipment_type' => 'forward',
            'service_type' => 'standard',
            'payment_type' => 'cod',
            'sender_name' => 'FashionNova Dispatch',
            'sender_phone' => '+1 (800) 555-9001',
            'sender_address' => '2800 S Alameda St',
            'sender_city' => 'New York',
            'sender_state' => 'NY',
            'sender_postal_code' => '10001',
            'receiver_name' => 'Angela Martin',
            'receiver_phone' => '+1 (917) 555-5544',
            'receiver_address' => '200 Park Ave, Suite 300',
            'receiver_city' => 'New York',
            'receiver_state' => 'NY',
            'receiver_postal_code' => '10001',
            'content_description' => 'Cat Supplies & Dresses',
            'pieces_count' => 1,
            'actual_weight' => 1.80,
            'volumetric_weight' => 1.20,
            'chargeable_weight' => 1.80,
            'declared_value' => 95.00,
            'cod_amount' => 95.00,
            'base_freight' => 18.50,
            'fuel_surcharge' => 0.93,
            'handling_fee' => 0.00,
            'cod_fee' => 2.00,
            'gst_tax' => 3.86,
            'total_freight' => 25.29,
            'payment_status' => 'collected',
            'status' => 'delivered',
            'current_location' => 'New York, NY',
            'delivery_agent_id' => $rider1->id,
            'delivery_otp' => '392817',
            'otp_verified_at' => now()->subHours(3),
            'recipient_name' => 'Angela Martin',
            'recipient_relation' => 'Self',
            'pod_signature' => 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="40"><path d="M10 20 Q 30 5, 50 20 T 90 20" stroke="black" fill="none"/></svg>',
            'delivered_at' => now()->subHours(3),
        ]);
        ShipmentPackage::create([
            'shipment_id' => $s5->id,
            'package_barcode' => 'CX100005-01',
            'weight' => 1.80,
            'length' => 25.00,
            'width' => 20.00,
            'height' => 12.00,
            'volumetric_weight' => 1.20,
            'description' => 'Goods Box',
        ]);
        ShipmentTrackingEvent::create([
            'shipment_id' => $s5->id,
            'status' => 'booked',
            'status_title' => 'Shipment Booked',
            'location' => 'New York',
            'event_time' => now()->subHours(30),
        ]);
        ShipmentTrackingEvent::create([
            'shipment_id' => $s5->id,
            'status' => 'out_for_delivery',
            'status_title' => 'Out for Delivery',
            'location' => 'New York, NY',
            'event_time' => now()->subHours(6),
        ]);
        ShipmentTrackingEvent::create([
            'shipment_id' => $s5->id,
            'status' => 'delivered',
            'status_title' => 'Delivered to Angela Martin',
            'location' => 'New York, NY',
            'description' => 'Delivered and verified via OTP. COD collected: $95.00.',
            'user_id' => $rider1->id,
            'event_time' => now()->subHours(3),
        ]);
        DeliveryProof::create([
            'shipment_id' => $s5->id,
            'delivery_agent_id' => $rider1->id,
            'recipient_name' => 'Angela Martin',
            'recipient_relation' => 'Self',
            'signature_data' => $s5->pod_signature,
            'otp_code' => '392817',
            'delivered_at' => now()->subHours(3),
        ]);
        CodCollection::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'shipment_id' => $s5->id,
            'delivery_agent_id' => $rider1->id,
            'amount' => 95.00,
            'payment_method' => 'cash',
            'is_deposited_to_branch' => true,
            'deposited_at' => now()->subHours(1),
            'verified_by_id' => $finance->id,
        ]);

        // Shipment 6: Failed Attempt (Customer unavailable)
        $s6 = Shipment::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'origin_hub_id' => $hubJfk->id,
            'destination_hub_id' => $hubJfk->id,
            'current_hub_id' => $hubJfk->id,
            'customer_id' => $customer1->id,
            'awb_number' => 'CX100006',
            'shipment_type' => 'forward',
            'service_type' => 'standard',
            'payment_type' => 'prepaid',
            'sender_name' => 'FashionNova Dispatch',
            'sender_phone' => '+1 (800) 555-9001',
            'sender_address' => '2800 S Alameda St',
            'sender_city' => 'New York',
            'sender_state' => 'NY',
            'sender_postal_code' => '10001',
            'receiver_name' => 'Stanley Hudson',
            'receiver_phone' => '+1 (917) 555-1199',
            'receiver_address' => '700 8th Ave, Apt 9A',
            'receiver_city' => 'New York',
            'receiver_state' => 'NY',
            'receiver_postal_code' => '10001',
            'content_description' => 'Crossword Books & Coffee Mug',
            'pieces_count' => 1,
            'actual_weight' => 0.90,
            'volumetric_weight' => 0.50,
            'chargeable_weight' => 0.90,
            'declared_value' => 40.00,
            'total_freight' => 14.50,
            'payment_status' => 'paid',
            'status' => 'failed_attempt',
            'current_location' => 'JFK Hub (Reattempt Scheduled)',
            'attempt_count' => 1,
            'failed_reason' => 'customer_unavailable',
        ]);
        ShipmentPackage::create([
            'shipment_id' => $s6->id,
            'package_barcode' => 'CX100006-01',
            'weight' => 0.90,
            'length' => 20.00,
            'width' => 15.00,
            'height' => 8.00,
            'volumetric_weight' => 0.50,
            'description' => 'Standard Box',
        ]);
        DeliveryAttempt::create([
            'shipment_id' => $s6->id,
            'delivery_agent_id' => $rider1->id,
            'attempt_no' => 1,
            'reason_code' => 'customer_unavailable',
            'remarks' => 'Door locked, recipient phone unreachable.',
            'attempted_at' => now()->subHours(4),
        ]);
        ShipmentTrackingEvent::create([
            'shipment_id' => $s6->id,
            'status' => 'failed_attempt',
            'status_title' => 'Delivery Failed: Customer Unavailable',
            'location' => 'New York, NY',
            'description' => 'First delivery attempt failed. Package held for reattempt tomorrow.',
            'user_id' => $rider1->id,
            'event_time' => now()->subHours(4),
        ]);

        // Shipment 7: RTO Initiated (Refused by customer)
        $s7 = Shipment::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'origin_hub_id' => $hubJfk->id,
            'destination_hub_id' => $hubJfk->id,
            'current_hub_id' => $hubJfk->id,
            'customer_id' => $customer1->id,
            'awb_number' => 'CX100007',
            'shipment_type' => 'forward',
            'service_type' => 'standard',
            'payment_type' => 'cod',
            'sender_name' => 'FashionNova Dispatch',
            'sender_phone' => '+1 (800) 555-9001',
            'sender_address' => '2800 S Alameda St',
            'sender_city' => 'New York',
            'sender_state' => 'NY',
            'sender_postal_code' => '10001',
            'receiver_name' => 'Ryan Howard',
            'receiver_phone' => '+1 (917) 555-7788',
            'receiver_address' => '150 W 55th St',
            'receiver_city' => 'New York',
            'receiver_state' => 'NY',
            'receiver_postal_code' => '10001',
            'content_description' => 'Electronics Gadget',
            'pieces_count' => 1,
            'actual_weight' => 1.10,
            'volumetric_weight' => 0.80,
            'chargeable_weight' => 1.10,
            'declared_value' => 150.00,
            'cod_amount' => 150.00,
            'total_freight' => 20.00,
            'payment_status' => 'pending',
            'status' => 'rto_initiated',
            'current_location' => 'JFK Hub (Return Transit)',
            'attempt_count' => 1,
            'return_awb_number' => 'RTO-CX100007',
            'return_reason' => 'Customer Refused Delivery / Order Cancelled',
        ]);
        ShipmentPackage::create([
            'shipment_id' => $s7->id,
            'package_barcode' => 'CX100007-01',
            'weight' => 1.10,
            'length' => 20.00,
            'width' => 15.00,
            'height' => 10.00,
            'volumetric_weight' => 0.80,
            'description' => 'Smart Watch',
        ]);
        RtoRecord::create([
            'company_id' => $company->id,
            'original_shipment_id' => $s7->id,
            'rto_awb' => 'RTO-CX100007',
            'return_reason' => 'Customer Refused Acceptance',
            'status' => 'initiated',
            'initiated_by_id' => $rider1->id,
        ]);
        ShipmentTrackingEvent::create([
            'shipment_id' => $s7->id,
            'status' => 'rto_initiated',
            'status_title' => 'Return to Origin Initiated',
            'location' => 'New York, NY',
            'description' => 'Recipient refused acceptance. Return Waybill RTO-CX100007 created.',
            'user_id' => $rider1->id,
            'event_time' => now()->subHours(5),
        ]);

        // 11. Delivery Run Sheet (DRS)
        $drs = DeliveryRun::create([
            'company_id' => $company->id,
            'branch_id' => $branchNyc->id,
            'run_sheet_number' => 'DRS-202611-0001',
            'delivery_agent_id' => $rider1->id,
            'run_date' => now()->toDateString(),
            'status' => 'out_for_delivery',
            'total_shipments' => 3,
            'delivered_count' => 1,
            'failed_count' => 1,
            'total_cod_to_collect' => 275.00,
            'total_cod_collected' => 95.00,
            'notes' => 'Manhattan Mid-town delivery route',
        ]);
        $drs->shipments()->attach($s4->id, ['sequence_order' => 1, 'status' => 'assigned']);
        $drs->shipments()->attach($s5->id, ['sequence_order' => 2, 'status' => 'delivered', 'completed_at' => now()->subHours(3)]);
        $drs->shipments()->attach($s6->id, ['sequence_order' => 3, 'status' => 'failed', 'completed_at' => now()->subHours(4), 'failure_reason' => 'customer_unavailable']);

        // 12. Pickup Request
        $pickup = PickupRequest::create([
            'company_id' => $company->id,
            'customer_id' => $customer1->id,
            'pickup_code' => 'PK-991204',
            'contact_name' => 'FashionNova Dispatch Desk',
            'contact_phone' => '+1 (800) 555-9001',
            'pickup_address' => '2800 S Alameda St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'scheduled_date' => now()->toDateString(),
            'time_slot' => 'morning',
            'expected_packages' => 15,
            'assigned_agent_id' => $rider1->id,
            'status' => 'assigned',
            'notes' => 'Bulk cartons ready on loading bay 3.',
        ]);

        // 13. Manifest
        $manifest = Manifest::create([
            'company_id' => $company->id,
            'manifest_number' => 'MNF-202611-01',
            'source_hub_id' => $hubJfk->id,
            'destination_hub_id' => $hubOrd->id,
            'vehicle_id' => $vehicle->id,
            'driver_id' => 1,
            'status' => 'dispatched',
            'total_bags' => 2,
            'total_shipments' => 12,
            'total_weight' => 45.50,
            'dispatched_at' => now()->subHours(6),
            'remarks' => 'Inter-hub linehaul transfer NY to Chicago',
        ]);
        $manifest->shipments()->attach($s3->id, ['status' => 'loaded']);

        // 14. Support Ticket
        SupportTicket::create([
            'company_id' => $company->id,
            'customer_id' => $customer1->id,
            'shipment_id' => $s6->id,
            'ticket_number' => 'TCK-202611-001',
            'subject' => 'Reattempt Request for AWB CX100006',
            'category' => 'delivery_delay',
            'priority' => 'high',
            'status' => 'open',
            'sla_due_at' => now()->addHours(12),
        ]);
    }
}
