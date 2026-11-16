<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Shipment;
use Illuminate\Http\Request;

class PublicWebsiteController extends Controller
{
    public function index()
    {
        $company = Company::first() ?? new Company([
            'name' => 'Apex Logistics Express',
            'phone' => '+1 (800) 555-0199',
            'email' => 'contact@apexlogistics.example',
        ]);

        $stats = [
            'total_shipments' => Shipment::count() + 1500,
            'on_time_rate' => '99.4%',
            'cities_covered' => '250+',
            'happy_clients' => '10,000+',
        ];

        return view('welcome', compact('company', 'stats'));
    }
}
