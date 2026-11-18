<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CodCollection;
use App\Models\CodSettlement;
use App\Models\CodSettlementItem;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\DeliveryAttempt;
use App\Models\DeliveryProof;
use App\Models\DeliveryRun;
use App\Models\Driver;
use App\Models\Expense;
use App\Models\Hub;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LedgerAccount;
use App\Models\Manifest;
use App\Models\PickupRequest;
use App\Models\PostalCode;
use App\Models\RateCard;
use App\Models\RateSlab;
use App\Models\RtoRecord;
use App\Models\ServiceZone;
use App\Models\Shipment;
use App\Models\ShipmentBag;
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
            'name' => 'Webotapp Logistics Express India Pvt. Ltd.',
            'code' => 'WEX',
            'email' => 'ops@webotapp.com',
            'phone' => '+91 22 2650 1000',
            'currency' => 'INR',
            'currency_symbol' => '₹',
            'address' => 'Plot No. C-59, G Block, Bandra Kurla Complex (BKC)',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400051',
            'country' => 'India',
            'tax_number' => '27AAACW1234F1Z5',
            'status' => 'active',
            'settings' => [
                'volumetric_divisor' => 5000,
                'default_tax_rate' => 18.0,
                'cod_enabled' => true,
                'otp_delivery' => true,
            ],
        ]);

        // 2. Branches
        $branchMum = Branch::create([
            'company_id' => $company->id,
            'name' => 'Mumbai Central Head Office',
            'code' => 'BR-MUM',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400051',
            'address' => 'Plot C-59, G Block, Bandra Kurla Complex, Mumbai',
            'phone' => '+91 22 2650 1001',
            'email' => 'mumbai@webotapp.com',
            'is_head_office' => true,
            'status' => 'active',
        ]);

        $branchDel = Branch::create([
            'company_id' => $company->id,
            'name' => 'Delhi NCR Regional Branch',
            'code' => 'BR-DEL',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'postal_code' => '110037',
            'address' => 'Aerocity Terminal 3 Cargo Park, New Delhi',
            'phone' => '+91 11 2565 2002',
            'email' => 'delhi@webotapp.com',
            'is_head_office' => false,
            'status' => 'active',
        ]);

        $branchBlr = Branch::create([
            'company_id' => $company->id,
            'name' => 'Bengaluru South Tech Hub Branch',
            'code' => 'BR-BLR',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'postal_code' => '560100',
            'address' => 'Phase 1, Electronic City, Bengaluru',
            'phone' => '+91 80 4120 3003',
            'email' => 'bangalore@webotapp.com',
            'is_head_office' => false,
            'status' => 'active',
        ]);

        $branches = [$branchMum, $branchDel, $branchBlr];

        // 3. Hubs
        $hubBom = Hub::create([
            'company_id' => $company->id,
            'branch_id' => $branchMum->id,
            'name' => 'Mumbai Air Cargo Gateway Hub',
            'code' => 'HUB-BOM',
            'hub_type' => 'airport',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400099',
            'address' => 'Air Cargo Complex, CSMIA, Sahar, Mumbai',
            'phone' => '+91 22 2682 9000',
            'status' => 'active',
        ]);

        $hubDel = Hub::create([
            'company_id' => $company->id,
            'branch_id' => $branchDel->id,
            'name' => 'Delhi IGI Gateway Distribution Hub',
            'code' => 'HUB-DEL',
            'hub_type' => 'sorting',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'postal_code' => '110037',
            'address' => 'Air Cargo Logistics Complex, IGI Airport, New Delhi',
            'phone' => '+91 11 2567 9000',
            'status' => 'active',
        ]);

        $hubBlr = Hub::create([
            'company_id' => $company->id,
            'branch_id' => $branchBlr->id,
            'name' => 'Bengaluru Kempegowda Super Sorting Hub',
            'code' => 'HUB-BLR',
            'hub_type' => 'sorting',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'postal_code' => '560300',
            'address' => 'Devanahalli Logistics Park, Bengaluru',
            'phone' => '+91 80 6678 9000',
            'status' => 'active',
        ]);

        $hubs = [$hubBom, $hubDel, $hubBlr];

        // 4. Service Zones & Postal Codes (Indian Metros)
        $zoneLocal = ServiceZone::create(['company_id' => $company->id, 'name' => 'Intra-City Local Zone', 'code' => 'LOCAL']);
        $zoneRegional = ServiceZone::create(['company_id' => $company->id, 'name' => 'Western / Regional Zone', 'code' => 'REGIONAL']);
        $zoneNational = ServiceZone::create(['company_id' => $company->id, 'name' => 'Pan-India National Zone', 'code' => 'NATIONAL']);

        $pins = [
            ['code' => '400001', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'zone' => $zoneLocal],
            ['code' => '400018', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'zone' => $zoneLocal],
            ['code' => '400051', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'zone' => $zoneLocal],
            ['code' => '400066', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'zone' => $zoneLocal],
            ['code' => '400093', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'zone' => $zoneLocal],
            ['code' => '400099', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'zone' => $zoneLocal],
            ['code' => '411001', 'city' => 'Pune', 'state' => 'Maharashtra', 'zone' => $zoneRegional],
            ['code' => '411014', 'city' => 'Pune', 'state' => 'Maharashtra', 'zone' => $zoneRegional],
            ['code' => '380006', 'city' => 'Ahmedabad', 'state' => 'Gujarat', 'zone' => $zoneRegional],
            ['code' => '395001', 'city' => 'Surat', 'state' => 'Gujarat', 'zone' => $zoneRegional],
            ['code' => '110001', 'city' => 'New Delhi', 'state' => 'Delhi', 'zone' => $zoneRegional],
            ['code' => '110019', 'city' => 'New Delhi', 'state' => 'Delhi', 'zone' => $zoneRegional],
            ['code' => '110037', 'city' => 'New Delhi', 'state' => 'Delhi', 'zone' => $zoneRegional],
            ['code' => '201301', 'city' => 'Noida', 'state' => 'Uttar Pradesh', 'zone' => $zoneRegional],
            ['code' => '122001', 'city' => 'Gurugram', 'state' => 'Haryana', 'zone' => $zoneRegional],
            ['code' => '560001', 'city' => 'Bengaluru', 'state' => 'Karnataka', 'zone' => $zoneNational],
            ['code' => '560066', 'city' => 'Bengaluru', 'state' => 'Karnataka', 'zone' => $zoneNational],
            ['code' => '560100', 'city' => 'Bengaluru', 'state' => 'Karnataka', 'zone' => $zoneNational],
            ['code' => '500001', 'city' => 'Hyderabad', 'state' => 'Telangana', 'zone' => $zoneNational],
            ['code' => '500081', 'city' => 'Hyderabad', 'state' => 'Telangana', 'zone' => $zoneNational],
            ['code' => '600001', 'city' => 'Chennai', 'state' => 'Tamil Nadu', 'zone' => $zoneNational],
            ['code' => '600017', 'city' => 'Chennai', 'state' => 'Tamil Nadu', 'zone' => $zoneNational],
            ['code' => '700001', 'city' => 'Kolkata', 'state' => 'West Bengal', 'zone' => $zoneNational],
            ['code' => '700019', 'city' => 'Kolkata', 'state' => 'West Bengal', 'zone' => $zoneNational],
            ['code' => '302001', 'city' => 'Jaipur', 'state' => 'Rajasthan', 'zone' => $zoneRegional],
            ['code' => '226001', 'city' => 'Lucknow', 'state' => 'Uttar Pradesh', 'zone' => $zoneRegional],
            ['code' => '160017', 'city' => 'Chandigarh', 'state' => 'Punjab', 'zone' => $zoneRegional],
            ['code' => '682001', 'city' => 'Kochi', 'state' => 'Kerala', 'zone' => $zoneNational],
            ['code' => '452001', 'city' => 'Indore', 'state' => 'Madhya Pradesh', 'zone' => $zoneRegional],
            // Compatibility
            ['code' => '10001', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'zone' => $zoneLocal],
            ['code' => '10002', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'zone' => $zoneLocal],
            ['code' => '60601', 'city' => 'New Delhi', 'state' => 'Delhi', 'zone' => $zoneRegional],
        ];

        foreach ($pins as $p) {
            PostalCode::create([
                'code' => $p['code'],
                'city' => $p['city'],
                'state' => $p['state'],
                'country_code' => 'IND',
                'zone_id' => $p['zone']->id,
            ]);
        }

        // 5. Rate Cards & Slabs (Realistic in Indian Rupees ₹)
        $rateCard = RateCard::create([
            'company_id' => $company->id,
            'name' => 'Standard Commercial Tariff (INR)',
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
            'base_rate' => 50.00,
            'additional_weight_kg' => 0.5,
            'additional_rate' => 25.00,
            'cod_fixed_fee' => 30.00,
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
            'base_rate' => 90.00,
            'additional_weight_kg' => 0.5,
            'additional_rate' => 45.00,
            'cod_fixed_fee' => 40.00,
            'cod_percent' => 1.50,
            'fuel_surcharge_percent' => 6.00,
            'handling_fee' => 10.00,
            'gst_tax_percent' => 18.00,
        ]);

        RateSlab::create([
            'rate_card_id' => $rateCard->id,
            'zone_from' => 'LOCAL',
            'zone_to' => 'NATIONAL',
            'base_weight_kg' => 0.5,
            'base_rate' => 140.00,
            'additional_weight_kg' => 0.5,
            'additional_rate' => 70.00,
            'cod_fixed_fee' => 50.00,
            'cod_percent' => 2.00,
            'fuel_surcharge_percent' => 8.00,
            'handling_fee' => 20.00,
            'gst_tax_percent' => 18.00,
        ]);

        // 6. Users (All Indian Names)
        $admin = User::create([
            'company_id' => $company->id,
            'branch_id' => $branchMum->id,
            'name' => 'Vikramaditya Singhania (System Admin)',
            'email' => 'admin@courierx.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+91 98200 11000',
            'status' => 'active',
        ]);

        $ops = User::create([
            'company_id' => $company->id,
            'branch_id' => $branchMum->id,
            'hub_id' => $hubBom->id,
            'name' => 'Priya Sharma (Ops Manager)',
            'email' => 'operations@courierx.com',
            'password' => Hash::make('password'),
            'role' => 'operations',
            'phone' => '+91 98100 22001',
            'status' => 'active',
        ]);

        $rider1 = User::create([
            'company_id' => $company->id,
            'branch_id' => $branchMum->id,
            'name' => 'Rajesh Kumar (Delivery Agent)',
            'email' => 'rider1@courierx.com',
            'password' => Hash::make('password'),
            'role' => 'delivery_agent',
            'phone' => '+91 98201 33001',
            'status' => 'active',
        ]);

        $rider2 = User::create([
            'company_id' => $company->id,
            'branch_id' => $branchDel->id,
            'name' => 'Suresh Patel (Delivery Agent)',
            'email' => 'rider2@courierx.com',
            'password' => Hash::make('password'),
            'role' => 'delivery_agent',
            'phone' => '+91 98301 44002',
            'status' => 'active',
        ]);

        $rider3 = User::create([
            'company_id' => $company->id,
            'branch_id' => $branchBlr->id,
            'name' => 'Manoj Gaikwad (Delivery Agent)',
            'email' => 'rider3@courierx.com',
            'password' => Hash::make('password'),
            'role' => 'delivery_agent',
            'phone' => '+91 98401 55003',
            'status' => 'active',
        ]);

        $riders = [$rider1, $rider2, $rider3];

        $driver1 = User::create([
            'company_id' => $company->id,
            'branch_id' => $branchMum->id,
            'name' => 'Gurpreet Singh (Fleet Driver)',
            'email' => 'driver1@courierx.com',
            'password' => Hash::make('password'),
            'role' => 'driver',
            'phone' => '+91 98111 55001',
            'status' => 'active',
        ]);

        $driver2 = User::create([
            'company_id' => $company->id,
            'branch_id' => $branchDel->id,
            'name' => 'Harbhajan Sandhu (Linehaul Driver)',
            'email' => 'driver2@courierx.com',
            'password' => Hash::make('password'),
            'role' => 'driver',
            'phone' => '+91 98112 55002',
            'status' => 'active',
        ]);

        $driverUser1 = $driver1;
        $driverUser2 = $driver2;

        $finance = User::create([
            'company_id' => $company->id,
            'branch_id' => $branchMum->id,
            'name' => 'Anita Desai (Head Accountant)',
            'email' => 'finance@courierx.com',
            'password' => Hash::make('password'),
            'role' => 'accountant',
            'phone' => '+91 98212 66001',
            'status' => 'active',
        ]);

        // Demo Merchant User
        $merchantUser = User::create([
            'company_id' => $company->id,
            'name' => 'Rohan Mehra (FabIndia Logistics)',
            'email' => 'merchant@fashionnova.demo',
            'password' => Hash::make('password'),
            'role' => 'merchant',
            'phone' => '+91 98200 77001',
            'status' => 'active',
        ]);

        // Fleet Vehicles
        $vehicle1 = Vehicle::create([
            'company_id' => $company->id,
            'vehicle_number' => 'MH-02-CB-4892',
            'model' => 'Tata Ultra 1518 T.7 Cargo Van',
            'type' => 'van',
            'capacity_kg' => 2500.00,
            'status' => 'available',
        ]);

        $vehicle2 = Vehicle::create([
            'company_id' => $company->id,
            'vehicle_number' => 'DL-01-AZ-8921',
            'model' => 'Ashok Leyland Ecomet 1215 Linehaul',
            'type' => 'light_truck',
            'capacity_kg' => 4500.00,
            'status' => 'available',
        ]);

        $vehicle3 = Vehicle::create([
            'company_id' => $company->id,
            'vehicle_number' => 'KA-05-MN-3310',
            'model' => 'Mahindra Bolero Maxi Truck Plus',
            'type' => 'van',
            'capacity_kg' => 1700.00,
            'status' => 'available',
        ]);

        $vehicles = [$vehicle1, $vehicle2, $vehicle3];

        $driverRecord1 = Driver::create([
            'company_id' => $company->id,
            'user_id' => $driverUser1->id,
            'name' => 'Gurpreet Singh',
            'phone' => '+91 98111 55001',
            'license_number' => 'DL-MH-0220200045123',
            'status' => 'active',
        ]);

        $driverRecord2 = Driver::create([
            'company_id' => $company->id,
            'user_id' => $driverUser2->id,
            'name' => 'Harbhajan Sandhu',
            'phone' => '+91 98112 55002',
            'license_number' => 'DL-DL-0120190089221',
            'status' => 'active',
        ]);

        $drivers = [$driverRecord1, $driverRecord2];

        // Accounting Chart of Accounts
        $accountingService = app(AccountingService::class);
        $accounts = $accountingService->ensureChartOfAccounts($company->id);

        // 7. Seed 56 Indian Customers / Merchants
        $indianBrands = [
            ['name' => 'FabIndia Overseas Pvt. Ltd.', 'contact' => 'Amitabh Sen', 'city' => 'Mumbai', 'pin' => '400093', 'type' => 'merchant'],
            ['name' => 'Nykaa E-Retail Limited', 'contact' => 'Falguni Roy', 'city' => 'Mumbai', 'pin' => '400051', 'type' => 'merchant'],
            ['name' => 'Tata CLiQ Luxury', 'contact' => 'Tanmay Khurana', 'city' => 'Mumbai', 'pin' => '400018', 'type' => 'corporate'],
            ['name' => 'Myntra Fashion Brands', 'contact' => 'Nandita Sengupta', 'city' => 'Bengaluru', 'pin' => '560100', 'type' => 'merchant'],
            ['name' => 'Ajio Commerce Division', 'contact' => 'Siddharth Varma', 'city' => 'Bengaluru', 'pin' => '560001', 'type' => 'merchant'],
            ['name' => 'Lenskart Solutions Ltd.', 'contact' => 'Peyush Bansal', 'city' => 'New Delhi', 'pin' => '110037', 'type' => 'merchant'],
            ['name' => 'Mamaearth Honasa Consumer', 'contact' => 'Varun Alagh', 'city' => 'Gurugram', 'pin' => '122001', 'type' => 'merchant'],
            ['name' => 'Boat Lifestyle Imagine Marketing', 'contact' => 'Aman Gupta', 'city' => 'New Delhi', 'pin' => '110001', 'type' => 'merchant'],
            ['name' => 'Sugar Cosmetics Vellvette', 'contact' => 'Vineeta Singh', 'city' => 'Mumbai', 'pin' => '400093', 'type' => 'merchant'],
            ['name' => 'Bewakoof Brands Pvt. Ltd.', 'contact' => 'Prabhkiran Singh', 'city' => 'Mumbai', 'pin' => '400066', 'type' => 'merchant'],
            ['name' => 'Snitch Menswear Studios', 'contact' => 'Siddharth Dungarwal', 'city' => 'Bengaluru', 'pin' => '560066', 'type' => 'merchant'],
            ['name' => 'Chumbak Lifestyle Crafts', 'contact' => 'Vivek Prabhakar', 'city' => 'Bengaluru', 'pin' => '560038', 'type' => 'merchant'],
            ['name' => 'CaratLane Jewellery Ltd.', 'contact' => 'Mithun Sacheti', 'city' => 'Chennai', 'pin' => '600017', 'type' => 'corporate'],
            ['name' => 'Noise Nexxbase Tech Ltd.', 'contact' => 'Amit Khatri', 'city' => 'Gurugram', 'pin' => '122018', 'type' => 'merchant'],
            ['name' => 'The Souled Store Ltd.', 'contact' => 'Vedang Patel', 'city' => 'Mumbai', 'pin' => '400093', 'type' => 'merchant'],
            ['name' => 'BlueStone Jewellery & Lifestyle', 'contact' => 'Gaurav Kushwaha', 'city' => 'Bengaluru', 'pin' => '560001', 'type' => 'corporate'],
            ['name' => 'Pepperfry Furniture Solutions', 'contact' => 'Ashish Shah', 'city' => 'Mumbai', 'pin' => '400018', 'type' => 'corporate'],
            ['name' => 'Wakefit Innovations Pvt. Ltd.', 'contact' => 'Ankit Garg', 'city' => 'Bengaluru', 'pin' => '560100', 'type' => 'merchant'],
            ['name' => 'Biba Apparels Pvt. Ltd.', 'contact' => 'Siddharath Bindra', 'city' => 'New Delhi', 'pin' => '110019', 'type' => 'merchant'],
            ['name' => 'Manyavar Vedant Fashions Ltd.', 'contact' => 'Ravi Modi', 'city' => 'Kolkata', 'pin' => '700019', 'type' => 'corporate'],
            ['name' => 'W for Woman TCNS Clothing', 'contact' => 'Anant Daga', 'city' => 'New Delhi', 'pin' => '110037', 'type' => 'merchant'],
            ['name' => 'Forest Essentials Ayurveda', 'contact' => 'Mira Kulkarni', 'city' => 'New Delhi', 'pin' => '110001', 'type' => 'corporate'],
            ['name' => 'Kama Ayurveda Pvt. Ltd.', 'contact' => 'Vivek Sahni', 'city' => 'New Delhi', 'pin' => '110037', 'type' => 'merchant'],
            ['name' => 'Himalaya Wellness Company', 'contact' => 'Philipe Haydon', 'city' => 'Bengaluru', 'pin' => '560001', 'type' => 'corporate'],
            ['name' => 'Khadi Natural Healthcare', 'contact' => 'Gaurav Agarwal', 'city' => 'New Delhi', 'pin' => '110001', 'type' => 'merchant'],
            ['name' => 'Titan Company Watch Division', 'contact' => 'CK Venkataraman', 'city' => 'Bengaluru', 'pin' => '560100', 'type' => 'corporate'],
            ['name' => 'Fastrack Youth Accessories', 'contact' => 'Ayushman Chhabra', 'city' => 'Bengaluru', 'pin' => '560001', 'type' => 'merchant'],
            ['name' => 'Hidesign Leather Goods', 'contact' => 'Dilip Kapur', 'city' => 'Chennai', 'pin' => '600001', 'type' => 'corporate'],
            ['name' => 'Da Milano Leathers Ltd.', 'contact' => 'Sahil Malik', 'city' => 'New Delhi', 'pin' => '110001', 'type' => 'corporate'],
            ['name' => 'Metro Brands Footwear', 'contact' => 'Nissan Joseph', 'city' => 'Mumbai', 'pin' => '400051', 'type' => 'corporate'],
            ['name' => 'Relaxo Footwears Limited', 'contact' => 'Ramesh Dua', 'city' => 'New Delhi', 'pin' => '110019', 'type' => 'corporate'],
            ['name' => 'Campus Activewear Ltd.', 'contact' => 'Nikhil Aggarwal', 'city' => 'New Delhi', 'pin' => '110037', 'type' => 'merchant'],
            ['name' => 'Red Tape Lifestyle Stores', 'contact' => 'Shuja Mirza', 'city' => 'New Delhi', 'pin' => '110001', 'type' => 'corporate'],
            ['name' => 'Mufti Denim Credo Brands', 'contact' => 'Kamal Khushlani', 'city' => 'Mumbai', 'pin' => '400093', 'type' => 'merchant'],
            ['name' => 'Raymond Apparel Division', 'contact' => 'Gautam Singhania', 'city' => 'Mumbai', 'pin' => '400018', 'type' => 'corporate'],
            ['name' => 'Louis Philippe Madura Fashions', 'contact' => 'Vishak Kumar', 'city' => 'Bengaluru', 'pin' => '560001', 'type' => 'corporate'],
            ['name' => 'Peter England Fashions', 'contact' => 'Manish Singhai', 'city' => 'Bengaluru', 'pin' => '560066', 'type' => 'merchant'],
            ['name' => 'Allen Solly Casuals', 'contact' => 'Anil S Kumar', 'city' => 'Bengaluru', 'pin' => '560100', 'type' => 'merchant'],
            ['name' => 'Wildcraft India Adventure Gear', 'contact' => 'Gaurav Dublish', 'city' => 'Bengaluru', 'pin' => '560066', 'type' => 'merchant'],
            ['name' => 'Decathlon Sports India', 'contact' => 'Steve Dykes', 'city' => 'Bengaluru', 'pin' => '560001', 'type' => 'corporate'],
            ['name' => 'FirstCry Brainbees Solutions', 'contact' => 'Supam Maheshwari', 'city' => 'Pune', 'pin' => '411014', 'type' => 'merchant'],
            ['name' => 'Hopscotch Kids Fashion', 'contact' => 'Rahul Anand', 'city' => 'Mumbai', 'pin' => '400093', 'type' => 'merchant'],
            ['name' => 'Hamleys India Reliance Retail', 'contact' => 'Darshan Mehta', 'city' => 'Mumbai', 'pin' => '400051', 'type' => 'corporate'],
            ['name' => 'Crossword Bookstores Ltd.', 'contact' => 'Aakash Gupta', 'city' => 'Mumbai', 'pin' => '400001', 'type' => 'corporate'],
            ['name' => 'Haldiram Foods International', 'contact' => 'Pankaj Agarwal', 'city' => 'Nagpur', 'pin' => '400001', 'type' => 'corporate'],
            ['name' => 'Bikanervala Sweet Stores', 'contact' => 'Shyam Sunder Aggarwal', 'city' => 'New Delhi', 'pin' => '110001', 'type' => 'corporate'],
            ['name' => 'Chaayos Sunshine Teahouse', 'contact' => 'Nitin Saluja', 'city' => 'New Delhi', 'pin' => '110037', 'type' => 'merchant'],
            ['name' => 'Blue Tokai Coffee Roasters', 'contact' => 'Matt Chitharanjan', 'city' => 'New Delhi', 'pin' => '110001', 'type' => 'merchant'],
            ['name' => 'Sleepy Owl Coffee Co.', 'contact' => 'Ajai Thandi', 'city' => 'New Delhi', 'pin' => '110019', 'type' => 'merchant'],
            ['name' => 'Arjun Kapoor (Retail Shipper)', 'contact' => 'Arjun Kapoor', 'city' => 'Mumbai', 'pin' => '400018', 'type' => 'retail'],
            ['name' => 'Karan Malhotra (Individual)', 'contact' => 'Karan Malhotra', 'city' => 'New Delhi', 'pin' => '110001', 'type' => 'retail'],
            ['name' => 'Sneha Kulkarni (Designer)', 'contact' => 'Sneha Kulkarni', 'city' => 'Pune', 'pin' => '411014', 'type' => 'retail'],
            ['name' => 'Ananya Iyer (Consignor)', 'contact' => 'Ananya Iyer', 'city' => 'Chennai', 'pin' => '600017', 'type' => 'retail'],
            ['name' => 'Vikramaditya Rao (Boutique)', 'contact' => 'Vikramaditya Rao', 'city' => 'Hyderabad', 'pin' => '500081', 'type' => 'retail'],
            ['name' => 'Diya Banerjee (Craft Studio)', 'contact' => 'Diya Banerjee', 'city' => 'Kolkata', 'pin' => '700019', 'type' => 'retail'],
            ['name' => 'Aditya Singhal (Antiques)', 'contact' => 'Aditya Singhal', 'city' => 'Jaipur', 'pin' => '302001', 'type' => 'retail'],
        ];

        $allCustomers = [];
        foreach ($indianBrands as $idx => $b) {
            $num = 1001 + $idx;
            $isMerchant = ($idx === 0);
            $userId = $isMerchant ? $merchantUser->id : null;
            $email = $isMerchant ? 'merchant@fashionnova.demo' : ('client' . $num . '@webotappdemo.in');
            $phone = '+91 98' . sprintf('%08d', rand(10000000, 99999999));

            $cust = Customer::create([
                'company_id' => $company->id,
                'user_id' => $userId,
                'rate_card_id' => $rateCard->id,
                'customer_type' => $b['type'],
                'account_number' => 'CUST-' . $num,
                'name' => $b['name'],
                'company_name' => ($b['type'] !== 'retail') ? $b['name'] : null,
                'email' => $email,
                'phone' => $phone,
                'gst_number' => ($b['type'] !== 'retail') ? ('27' . strtoupper(substr(md5($b['name']), 0, 10)) . '1Z' . rand(1, 9)) : null,
                'credit_limit' => ($b['type'] !== 'retail') ? (rand(10, 50) * 10000.00) : 0.00,
                'billing_cycle' => 'weekly',
                'api_key' => ($b['type'] === 'merchant') ? ('wex_key_' . substr(md5($b['name']), 0, 16)) : null,
                'api_secret' => ($b['type'] === 'merchant') ? substr(hash('sha256', $b['name']), 0, 32) : null,
                'status' => 'active',
            ]);

            CustomerAddress::create([
                'customer_id' => $cust->id,
                'label' => 'Warehouse Dispatch Hub',
                'contact_name' => $b['contact'],
                'contact_phone' => $phone,
                'address_line1' => 'Plot ' . rand(10, 99) . ', Industrial Estate, ' . $b['city'],
                'city' => $b['city'],
                'state' => 'India',
                'postal_code' => $b['pin'],
                'country' => 'India',
                'is_default_pickup' => true,
            ]);

            $allCustomers[] = $cust;
        }

        // 8. Seed 75+ Indian Shipments across full operational lifecycle
        $indianReceivers = [
            ['name' => 'Neha Gupta', 'city' => 'New Delhi', 'pin' => '110001', 'addr' => 'Flat 501, Heritage Heights, Connaught Place', 'phone' => '+91 98112 33445'],
            ['name' => 'Sunita Rao', 'city' => 'Bengaluru', 'pin' => '560066', 'addr' => 'Villa 12, Palm Meadows, Whitefield', 'phone' => '+91 98450 77889'],
            ['name' => 'Aditya Nair', 'city' => 'Pune', 'pin' => '411014', 'addr' => 'B-304, Marvel Zephyr, Kharadi', 'phone' => '+91 98230 66778'],
            ['name' => 'Kavita Reddy', 'city' => 'Mumbai', 'pin' => '400018', 'addr' => 'Apt 14B, Lodha Bellissimo, Mahalaxmi', 'phone' => '+91 98205 99887'],
            ['name' => 'Pooja Verma', 'city' => 'Mumbai', 'pin' => '400066', 'addr' => 'Flat 802, Oberoi Sky City, Borivali East', 'phone' => '+91 98202 11223'],
            ['name' => 'Sanjay Mukherjee', 'city' => 'Mumbai', 'pin' => '400018', 'addr' => '702, Raheja Atlantis, Lower Parel', 'phone' => '+91 98204 33221'],
            ['name' => 'Ramesh Joshi', 'city' => 'Mumbai', 'pin' => '400051', 'addr' => '404, Kalpataru Aura, Ghatkopar West', 'phone' => '+91 98206 55443'],
            ['name' => 'Deepak Chopra', 'city' => 'New Delhi', 'pin' => '110019', 'addr' => 'M-42, Greater Kailash 2', 'phone' => '+91 98101 22334'],
            ['name' => 'Ananya Sen', 'city' => 'Kolkata', 'pin' => '700019', 'addr' => '18/2, Ballygunge Park Road', 'phone' => '+91 98302 44556'],
            ['name' => 'Rahul Deshmukh', 'city' => 'Pune', 'pin' => '411001', 'addr' => '502, Rohan Tarang, Wakad', 'phone' => '+91 98220 55667'],
            ['name' => 'Sneha Patil', 'city' => 'Mumbai', 'pin' => '400001', 'addr' => 'Flat 11, Sea View Court, Colaba', 'phone' => '+91 98200 66778'],
            ['name' => 'Vikram Rathore', 'city' => 'Jaipur', 'pin' => '302001', 'addr' => '102, Civil Lines Road', 'phone' => '+91 98290 77889'],
            ['name' => 'Meera Krishnan', 'city' => 'Chennai', 'pin' => '600017', 'addr' => '45, Bazullah Road, T. Nagar', 'phone' => '+91 98402 88990'],
            ['name' => 'Aryan Agarwal', 'city' => 'Ahmedabad', 'pin' => '380006', 'addr' => 'B-6, Shivalik Villa, Ambawadi', 'phone' => '+91 98792 99001'],
            ['name' => 'Kabir Batra', 'city' => 'Gurugram', 'pin' => '122001', 'addr' => 'Tower 4, DLF Phase 5', 'phone' => '+91 98110 11223'],
            ['name' => 'Diya Sundaram', 'city' => 'Bengaluru', 'pin' => '560001', 'addr' => 'Flat 301, Brigade Gateway, Malleshwaram', 'phone' => '+91 98451 22334'],
            ['name' => 'Harsh Vardhan', 'city' => 'Lucknow', 'pin' => '226001', 'addr' => '24, Gomti Nagar Extension', 'phone' => '+91 98390 33445'],
            ['name' => 'Ritu Chadha', 'city' => 'Chandigarh', 'pin' => '160017', 'addr' => 'House 420, Sector 18-B', 'phone' => '+91 98150 44556'],
            ['name' => 'Sai Teja', 'city' => 'Hyderabad', 'pin' => '500081', 'addr' => 'Plot 88, Jubilee Hills Road No. 36', 'phone' => '+91 98490 55667'],
            ['name' => 'Anjali Nambiar', 'city' => 'Kochi', 'pin' => '682001', 'addr' => 'Villa 7, Panampilly Nagar', 'phone' => '+91 98470 66778'],
        ];

        $items = [
            'Chanderi Silk Kurta & Dupatta', 'Wireless Noise Cancelling Earbuds', 'Handcrafted Ceramic Dinner Set',
            'Pure Cotton Ethnic Dress Set', 'Embroidered Cushion Covers', 'Brass Pooja Diya & Artifacts',
            'Smart Bluetooth Fitness Band', 'Organic Cold Pressed Hair Oil', 'Matte Longwear Lipstick Duo',
            'Designer Vegan Leather Bag', 'Printed Cotton Casual Shirt', 'Men Chronograph Stainless Watch',
            'Ergonomic Memory Foam Pillow', 'Handmade Pashmina Wool Stole', 'Herbal Face Care Gift Kit',
            'Stainless Steel Thermal Tumbler', 'Waterproof Trekking Backpack', 'Children Educational Board Game'
        ];

        $statuses = [
            'delivered', 'delivered', 'delivered', 'out_for_delivery', 'out_for_delivery',
            'in_transit', 'in_transit', 'picked_up', 'booked', 'failed_attempt', 'rto_initiated', 'rto_delivered'
        ];

        $allShipments = [];

        for ($i = 1; $i <= 75; $i++) {
            $awb = 'CX' . (100000 + $i);
            $rec = $indianReceivers[($i - 1) % count($indianReceivers)];
            $cust = $allCustomers[($i - 1) % count($allCustomers)];
            $st = $statuses[($i - 1) % count($statuses)];
            $isCod = ($i % 2 === 0);
            $codAmt = $isCod ? (float) rand(800, 6500) : 0.00;
            $payType = $isCod ? 'cod' : (($i % 3 === 0) ? 'corporate_credit' : 'prepaid');
            $payStatus = ($st === 'delivered') ? ($isCod ? 'collected' : 'paid') : (($payType === 'prepaid') ? 'paid' : 'pending');
            $weight = round(0.4 + (rand(1, 40) * 0.1), 2);
            $volWeight = round($weight * 0.8, 2);
            $chargeWeight = max($weight, $volWeight);
            $baseFreight = round(50.0 + (rand(1, 10) * 15.0), 2);
            $fuel = round($baseFreight * 0.06, 2);
            $codFee = $isCod ? round(max(30.0, $codAmt * 0.015), 2) : 0.00;
            $tax = round(($baseFreight + $fuel + $codFee) * 0.18, 2);
            $totalFreight = round($baseFreight + $fuel + $codFee + $tax, 2);
            $rider = $riders[($i - 1) % count($riders)];
            $destHub = $hubs[($i - 1) % count($hubs)];
            $origHub = $hubBom;

            $s = Shipment::create([
                'company_id' => $company->id,
                'branch_id' => $branchMum->id,
                'origin_hub_id' => $origHub->id,
                'destination_hub_id' => $destHub->id,
                'current_hub_id' => ($st === 'booked' || $st === 'picked_up') ? $origHub->id : $destHub->id,
                'customer_id' => $cust->id,
                'awb_number' => $awb,
                'reference_number' => 'ORD-NOV-' . (7700 + $i),
                'shipment_type' => 'forward',
                'service_type' => ($i % 2 === 0) ? 'standard' : 'express',
                'payment_type' => $payType,
                'sender_name' => $cust->name,
                'sender_phone' => $cust->phone,
                'sender_address' => 'Plot ' . rand(10, 50) . ', MIDC Industrial Area',
                'sender_city' => 'Mumbai',
                'sender_state' => 'Maharashtra',
                'sender_postal_code' => '400093',
                'receiver_name' => $rec['name'],
                'receiver_phone' => $rec['phone'],
                'receiver_address' => $rec['addr'],
                'receiver_city' => $rec['city'],
                'receiver_state' => 'India',
                'receiver_postal_code' => $rec['pin'],
                'content_description' => $items[($i - 1) % count($items)],
                'pieces_count' => 1,
                'actual_weight' => $weight,
                'volumetric_weight' => $volWeight,
                'chargeable_weight' => $chargeWeight,
                'declared_value' => round(rand(1200, 9500), 2),
                'cod_amount' => $codAmt,
                'base_freight' => $baseFreight,
                'fuel_surcharge' => $fuel,
                'handling_fee' => 0.00,
                'cod_fee' => $codFee,
                'gst_tax' => $tax,
                'total_freight' => $totalFreight,
                'payment_status' => $payStatus,
                'status' => $st,
                'current_location' => $rec['city'] . ' Hub',
                'pickup_agent_id' => $rider->id,
                'delivery_agent_id' => $rider->id,
                'delivery_otp' => sprintf('%06d', rand(100000, 999999)),
                'otp_verified_at' => ($st === 'delivered') ? now()->subHours(rand(1, 48)) : null,
                'recipient_name' => ($st === 'delivered') ? $rec['name'] : null,
                'recipient_relation' => ($st === 'delivered') ? 'Self' : null,
                'delivered_at' => ($st === 'delivered') ? now()->subHours(rand(1, 48)) : null,
                'failed_reason' => ($st === 'failed_attempt') ? 'customer_unavailable' : null,
                'attempt_count' => ($st === 'failed_attempt' || $st === 'rto_initiated') ? 1 : 0,
                'return_awb_number' => ($st === 'rto_initiated' || $st === 'rto_delivered') ? ('RTO-' . $awb) : null,
                'return_reason' => ($st === 'rto_initiated' || $st === 'rto_delivered') ? 'Customer Refused Delivery / Order Cancelled' : null,
                'estimated_delivery_at' => now()->addDays(rand(1, 3)),
            ]);

            ShipmentPackage::create([
                'shipment_id' => $s->id,
                'package_barcode' => $awb . '-01',
                'weight' => $weight,
                'length' => 25.00,
                'width' => 20.00,
                'height' => 10.00,
                'volumetric_weight' => $volWeight,
                'description' => $items[($i - 1) % count($items)],
            ]);

            ShipmentTrackingEvent::create([
                'shipment_id' => $s->id,
                'status' => 'booked',
                'status_title' => 'Consignment Booked',
                'location' => 'Mumbai Central Head Office',
                'description' => 'Waybill registered in logistics network.',
                'user_id' => $admin->id,
                'event_time' => now()->subDays(3),
            ]);

            if ($st === 'delivered') {
                ShipmentTrackingEvent::create([
                    'shipment_id' => $s->id,
                    'status' => 'delivered',
                    'status_title' => 'Delivered to ' . $rec['name'],
                    'location' => $rec['city'],
                    'description' => 'Successfully handed over with digital OTP verification.',
                    'user_id' => $rider->id,
                    'event_time' => now()->subHours(rand(2, 24)),
                ]);

                DeliveryProof::create([
                    'shipment_id' => $s->id,
                    'delivery_agent_id' => $rider->id,
                    'recipient_name' => $rec['name'],
                    'recipient_relation' => 'Self',
                    'otp_code' => $s->delivery_otp,
                    'delivered_at' => now()->subHours(rand(2, 24)),
                ]);
            }

            $allShipments[] = $s;
        }

        // 9. Seed 55+ Delivery Runs (DRS)
        for ($i = 1; $i <= 55; $i++) {
            $rider = $riders[($i - 1) % count($riders)];
            $assignedShipment = $allShipments[($i - 1) % count($allShipments)];
            $st = ($i <= 35) ? 'completed' : 'out_for_delivery';
            $codToColl = $assignedShipment->isCod() ? $assignedShipment->cod_amount : 0.00;
            $codColl = ($st === 'completed') ? $codToColl : 0.00;

            $drs = DeliveryRun::create([
                'company_id' => $company->id,
                'branch_id' => $branchMum->id,
                'run_sheet_number' => 'DRS-202611-' . sprintf('%04d', $i),
                'delivery_agent_id' => $rider->id,
                'run_date' => now()->subDays(55 - $i)->toDateString(),
                'status' => $st,
                'total_shipments' => rand(1, 4),
                'delivered_count' => ($st === 'completed') ? 1 : 0,
                'failed_count' => 0,
                'total_cod_to_collect' => $codToColl,
                'total_cod_collected' => $codColl,
                'notes' => 'Route #' . $i . ' - Metro Delivery Sector',
            ]);

            $drs->shipments()->attach($assignedShipment->id, [
                'sequence_order' => 1,
                'status' => ($st === 'completed') ? 'delivered' : 'assigned',
                'completed_at' => ($st === 'completed') ? now()->subHours(2) : null,
            ]);
        }

        // 10. Seed 55+ Pickup Requests
        for ($i = 1; $i <= 55; $i++) {
            $cust = $allCustomers[($i - 1) % count($allCustomers)];
            $rider = $riders[($i - 1) % count($riders)];
            $pStatus = ($i <= 30) ? 'completed' : (($i <= 45) ? 'assigned' : 'pending');

            PickupRequest::create([
                'company_id' => $company->id,
                'customer_id' => $cust->id,
                'pickup_code' => 'PK-' . sprintf('%06d', 100000 + $i),
                'contact_name' => $cust->name . ' Dispatch Desk',
                'contact_phone' => $cust->phone,
                'pickup_address' => 'Plot ' . rand(10, 80) . ', Industrial Area Phase ' . rand(1, 3),
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postal_code' => '400093',
                'scheduled_date' => now()->subDays(rand(0, 15))->toDateString(),
                'time_slot' => ($i % 3 === 0) ? 'evening' : (($i % 2 === 0) ? 'afternoon' : 'morning'),
                'expected_packages' => rand(2, 25),
                'assigned_agent_id' => ($pStatus !== 'pending') ? $rider->id : null,
                'status' => $pStatus,
                'notes' => 'Commercial shipment ready at loading bay #' . rand(1, 5),
            ]);
        }

        // 11. Seed 55+ Inter-Hub Manifests
        for ($i = 1; $i <= 55; $i++) {
            $src = $hubs[($i - 1) % count($hubs)];
            $dst = $hubs[$i % count($hubs)];
            $veh = $vehicles[($i - 1) % count($vehicles)];
            $drv = $drivers[($i - 1) % count($drivers)];
            $mStatus = ($i <= 30) ? 'received' : (($i <= 45) ? 'dispatched' : 'draft');
            $shipment = $allShipments[($i - 1) % count($allShipments)];

            $mnf = Manifest::create([
                'company_id' => $company->id,
                'manifest_number' => 'MNF-202611-' . sprintf('%02d', $i),
                'source_hub_id' => $src->id,
                'destination_hub_id' => $dst->id,
                'vehicle_id' => $veh->id,
                'driver_id' => $drv->id,
                'status' => $mStatus,
                'total_bags' => rand(1, 5),
                'total_shipments' => rand(5, 30),
                'total_weight' => round(rand(20, 180) * 1.5, 2),
                'dispatched_at' => ($mStatus !== 'draft') ? now()->subHours(rand(6, 48)) : null,
                'received_at' => ($mStatus === 'received') ? now()->subHours(rand(1, 5)) : null,
                'remarks' => "Linehaul Transit: {$src->name} to {$dst->name}",
            ]);

            $mnf->shipments()->attach($shipment->id, ['status' => ($mStatus === 'received') ? 'received' : 'loaded']);
        }

        // 12. Seed 55+ Shipment Bags
        for ($i = 1; $i <= 55; $i++) {
            $src = $hubs[($i - 1) % count($hubs)];
            $dst = $hubs[$i % count($hubs)];
            $bStatus = ($i <= 25) ? 'received' : (($i <= 40) ? 'dispatched' : 'sealed');

            ShipmentBag::create([
                'company_id' => $company->id,
                'origin_hub_id' => $src->id,
                'destination_hub_id' => $dst->id,
                'bag_number' => 'BAG-WEX-' . sprintf('%04d', 1000 + $i),
                'seal_number' => 'SEAL-' . rand(100000, 999999),
                'weight_kg' => round(rand(10, 45) * 1.2, 2),
                'status' => $bStatus,
                'created_by_id' => $ops->id,
            ]);
        }

        // 13. Seed 55+ RTO Records
        $rtoReasons = [
            'Customer Refused Delivery / Order Cancelled',
            'Customer Phone Unreachable & Door Locked',
            'Cash on Delivery Not Ready',
            'Incomplete / Incorrect Delivery Address',
            'Customer Requested Cancellation on Doorstep',
        ];

        for ($i = 1; $i <= 55; $i++) {
            $shipment = $allShipments[($i - 1) % count($allShipments)];
            $rider = $riders[($i - 1) % count($riders)];
            $rStatus = ($i <= 20) ? 'delivered_to_merchant' : (($i <= 35) ? 'received_at_hub' : 'initiated');

            RtoRecord::create([
                'company_id' => $company->id,
                'original_shipment_id' => $shipment->id,
                'rto_awb' => 'RTO-' . $shipment->awb_number . '-R' . $i,
                'return_reason' => $rtoReasons[($i - 1) % count($rtoReasons)],
                'status' => $rStatus,
                'initiated_by_id' => $rider->id,
                'delivered_to_merchant_at' => ($rStatus === 'delivered_to_merchant') ? now()->subDays(rand(1, 5)) : null,
            ]);
        }

        // 14. Seed 55+ COD Collections
        for ($i = 1; $i <= 55; $i++) {
            $shipment = $allShipments[($i - 1) % count($allShipments)];
            $rider = $riders[($i - 1) % count($riders)];
            $amt = round(rand(850, 6800), 2);
            $isDeposited = ($i <= 40);

            CodCollection::create([
                'company_id' => $company->id,
                'branch_id' => $branchMum->id,
                'shipment_id' => $shipment->id,
                'delivery_agent_id' => $rider->id,
                'amount' => $amt,
                'payment_method' => ($i % 3 === 0) ? 'upi' : 'cash',
                'is_deposited_to_branch' => $isDeposited,
                'deposited_at' => $isDeposited ? now()->subHours(rand(2, 36)) : null,
                'verified_by_id' => $isDeposited ? $finance->id : null,
            ]);
        }

        // 15. Seed 55+ COD Settlements
        for ($i = 1; $i <= 55; $i++) {
            $cust = $allCustomers[($i - 1) % count($allCustomers)];
            $shipment = $allShipments[($i - 1) % count($allShipments)];
            $totCod = round(rand(5000, 45000), 2);
            $ded = round($totCod * 0.05, 2);
            $net = $totCod - $ded;
            $setSt = ($i <= 30) ? 'paid' : (($i <= 45) ? 'approved' : 'pending');

            $settlement = CodSettlement::create([
                'company_id' => $company->id,
                'settlement_number' => 'SET-202611-' . sprintf('%03d', $i),
                'customer_id' => $cust->id,
                'total_cod_amount' => $totCod,
                'total_deductions' => $ded,
                'net_payable' => $net,
                'payment_mode' => 'bank_transfer',
                'transaction_reference' => ($setSt === 'paid') ? ('UTR' . rand(1000000000, 9999999999)) : null,
                'status' => $setSt,
                'settled_at' => ($setSt === 'paid') ? now()->subDays(rand(1, 10)) : null,
                'processed_by_id' => $finance->id,
                'notes' => 'Weekly COD Remittance for ' . $cust->name,
            ]);

            CodSettlementItem::create([
                'cod_settlement_id' => $settlement->id,
                'shipment_id' => $shipment->id,
                'cod_amount' => $totCod,
                'deduction_fee' => $ded,
                'net_amount' => $net,
            ]);
        }

        // 16. Seed 55+ Invoices
        for ($i = 1; $i <= 55; $i++) {
            $cust = $allCustomers[($i - 1) % count($allCustomers)];
            $shipment = $allShipments[($i - 1) % count($allShipments)];
            $sub = round(rand(2500, 18000), 2);
            $tax = round($sub * 0.18, 2);
            $tot = round($sub + $tax, 2);
            $invSt = ($i <= 30) ? 'paid' : (($i <= 45) ? 'unpaid' : 'partial');
            $paid = ($invSt === 'paid') ? $tot : (($invSt === 'partial') ? round($tot / 2, 2) : 0.00);

            $inv = Invoice::create([
                'company_id' => $company->id,
                'customer_id' => $cust->id,
                'invoice_number' => 'INV-202611-' . sprintf('%04d', $i),
                'invoice_date' => now()->subDays(rand(2, 30))->toDateString(),
                'due_date' => now()->addDays(rand(5, 20))->toDateString(),
                'subtotal' => $sub,
                'tax_amount' => $tax,
                'total_amount' => $tot,
                'paid_amount' => $paid,
                'payment_status' => $invSt,
                'notes' => 'Standard Commercial Freight Invoice for ' . $cust->name,
            ]);

            InvoiceItem::create([
                'invoice_id' => $inv->id,
                'shipment_id' => $shipment->id,
                'description' => "Consolidated Freight Services for AWB {$shipment->awb_number}",
                'quantity' => 1,
                'unit_price' => $sub,
                'tax_percent' => 18.00,
                'tax_amount' => $tax,
                'total_amount' => $tot,
            ]);
        }

        // 17. Seed 55+ Expenses
        $expCategories = ['fuel', 'maintenance', 'rent', 'packaging', 'utilities', 'salary'];
        $expDescriptions = [
            'Diesel fuel refill for Linehaul Fleet MH-02-CB-4892',
            'Hub warehouse monthly electricity and utility charges',
            'Packaging tape, bubble wrap, and security seal supplies',
            'Preventive maintenance and oil change for delivery vans',
            'Sahar Air Cargo terminal handling and entry pass fees',
            'Staff meal vouchers and tea pantry supplies for sorting hub',
            'Toll plaza FASTag recharge for inter-city Mumbai-Pune corridor',
            'Barcode thermal label roll carton purchases (100 rolls)',
        ];

        for ($i = 1; $i <= 55; $i++) {
            $cat = $expCategories[($i - 1) % count($expCategories)];
            $desc = $expDescriptions[($i - 1) % count($expDescriptions)];
            $br = $branches[($i - 1) % count($branches)];
            $amt = round(rand(450, 28000), 2);

            Expense::create([
                'company_id' => $company->id,
                'branch_id' => $br->id,
                'category' => $cat,
                'amount' => $amt,
                'expense_date' => now()->subDays(55 - $i)->toDateString(),
                'description' => $desc,
                'approved_by_id' => $finance->id,
            ]);
        }

        // 18. Seed 55+ Double-Entry Journal Entries (Ledger)
        for ($i = 1; $i <= 55; $i++) {
            $amt = round(rand(1200, 35000), 2);

            if ($i % 3 === 0) {
                // COD Collection: Dr Cash (1010), Cr COD Escrow (2010)
                $accDr = $accounts['1010']->id;
                $accCr = $accounts['2010']->id;
                $desc = 'Daily COD Collection received from rider Rajesh Kumar (Batch #' . $i . ')';
            } elseif ($i % 2 === 0) {
                // Freight Invoicing: Dr Accounts Receivable (1030), Cr Freight Revenue (4010)
                $accDr = $accounts['1030']->id;
                $accCr = $accounts['4010']->id;
                $desc = 'Commercial Freight Revenue recognition for client batch #' . $i;
            } else {
                // Fuel/Ops Expense: Dr Fuel Expenses (5020), Cr Bank Account (1020)
                $accDr = $accounts['5020']->id;
                $accCr = $accounts['1020']->id;
                $desc = 'FASTag corridor toll & fleet diesel expense disbursement #' . $i;
            }

            $accountingService->postJournalEntry(
                companyId: $company->id,
                description: $desc,
                lines: [
                    ['account_id' => $accDr, 'debit' => $amt, 'credit' => 0.00],
                    ['account_id' => $accCr, 'debit' => 0.00, 'credit' => $amt],
                ]
            );
        }

        // 19. Seed 55+ Support Tickets
        $ticketCats = ['delivery_delay', 'damaged_package', 'billing_issue', 'pickup_delay', 'other'];
        $ticketPriorities = ['low', 'medium', 'high', 'urgent'];
        $ticketStatuses = ['open', 'in_progress', 'resolved', 'closed'];

        for ($i = 1; $i <= 55; $i++) {
            $cust = $allCustomers[($i - 1) % count($allCustomers)];
            $shipment = $allShipments[($i - 1) % count($allShipments)];
            $cat = $ticketCats[($i - 1) % count($ticketCats)];
            $prio = $ticketPriorities[($i - 1) % count($ticketPriorities)];
            $tStatus = $ticketStatuses[($i - 1) % count($ticketStatuses)];

            SupportTicket::create([
                'company_id' => $company->id,
                'customer_id' => $cust->id,
                'shipment_id' => $shipment->id,
                'ticket_number' => 'TCK-202611-' . sprintf('%03d', $i),
                'subject' => "Inquiry regarding consignment AWB {$shipment->awb_number} ({$cust->name})",
                'category' => $cat,
                'priority' => $prio,
                'status' => $tStatus,
                'sla_due_at' => now()->addHours(rand(4, 48)),
            ]);
        }
    }
}
