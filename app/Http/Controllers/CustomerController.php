<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\RateCard;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $customers = Customer::where('company_id', $companyId)
            ->with(['rateCard'])
            ->withCount('shipments')
            ->latest()
            ->paginate(15);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id ?? 1;
        $rateCards = RateCard::where('company_id', $companyId)->get();

        return view('customers.create', compact('rateCards'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $validated = $request->validate([
            'customer_type' => 'required|in:retail,corporate,merchant',
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'gst_number' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
            'rate_card_id' => 'nullable|exists:rate_cards,id',
            'address_line1' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
        ]);

        $accountNumber = 'CUST-' . rand(1000, 9999);

        $customer = Customer::create([
            'company_id' => $companyId,
            'customer_type' => $validated['customer_type'],
            'account_number' => $accountNumber,
            'name' => $validated['name'],
            'company_name' => $validated['company_name'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'gst_number' => $validated['gst_number'] ?? null,
            'credit_limit' => $validated['credit_limit'] ?? 0.00,
            'rate_card_id' => $validated['rate_card_id'] ?? null,
            'api_key' => 'key_' . Str::random(24),
            'api_secret' => 'sec_' . Str::random(32),
            'status' => 'active',
        ]);

        CustomerAddress::create([
            'customer_id' => $customer->id,
            'contact_name' => $validated['name'],
            'contact_phone' => $validated['phone'],
            'address_line1' => $validated['address_line1'],
            'city' => $validated['city'],
            'state' => $validated['state'] ?? null,
            'postal_code' => $validated['postal_code'],
            'is_default_pickup' => true,
        ]);

        return redirect()->route('customers.index')->with('success', "Customer {$customer->name} ({$customer->account_number}) created!");
    }

    public function show($id)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $customer = Customer::where('company_id', $companyId)
            ->with(['addresses', 'rateCard', 'shipments' => fn ($q) => $q->latest()->take(10)])
            ->findOrFail($id);

        return view('customers.show', compact('customer'));
    }
}
