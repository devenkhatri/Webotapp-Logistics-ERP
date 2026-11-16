<?php

namespace App\Http\Controllers;

use App\Models\CodSettlement;
use App\Models\Customer;
use App\Models\Shipment;
use Illuminate\Http\Request;

class MerchantPortalController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Find customer record associated with logged in user or first merchant
        $customer = Customer::where('user_id', $user->id)->first();
        if (! $customer) {
            $customer = Customer::where('company_id', $user->company_id)->first();
        }

        $shipments = Shipment::where('customer_id', $customer?->id)
            ->latest()
            ->paginate(10);

        $totalShipments = Shipment::where('customer_id', $customer?->id)->count();
        $deliveredCount = Shipment::where('customer_id', $customer?->id)->where('status', 'delivered')->count();
        $codCollected = Shipment::where('customer_id', $customer?->id)->where('status', 'delivered')->where('payment_type', 'cod')->sum('cod_amount');
        
        $settlements = CodSettlement::where('customer_id', $customer?->id)->latest()->take(5)->get();

        return view('portal.index', compact('customer', 'shipments', 'totalShipments', 'deliveredCount', 'codCollected', 'settlements'));
    }
}
