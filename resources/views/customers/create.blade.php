@extends('layouts.app')

@section('title', 'Add Customer Account')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add Customer / Merchant Account</h1>
            <p class="text-sm text-slate-500">Register shipper, assign custom rate cards, and grant API keys</p>
        </div>
        <a href="{{ route('customers.index') }}" class="text-sm text-slate-500 hover:text-slate-800 font-medium">← Back to Customers</a>
    </div>

    <form action="{{ route('customers.store') }}" method="POST" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Account Category *</label>
                <select name="customer_type" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                    <option value="retail">Retail Consignor</option>
                    <option value="corporate">Corporate Contract Account</option>
                    <option value="merchant">E-Commerce Merchant</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Assigned Rate Card</label>
                <select name="rate_card_id" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                    <option value="">-- Company Default Tariff --</option>
                    @foreach($rateCards as $rc)
                        <option value="{{ $rc->id }}">{{ $rc->name }} ({{ $rc->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Customer / Contact Name *</label>
                <input type="text" name="name" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Company / Store Name</label>
                <input type="text" name="company_name" placeholder="e.g. Acme Online Store Inc." class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Email Address *</label>
                <input type="email" name="email" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Phone Number *</label>
                <input type="text" name="phone" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tax ID / GST Number</label>
                <input type="text" name="gst_number" placeholder="e.g. US-TAX-12345" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Credit Limit ($)</label>
                <input type="number" step="0.01" name="credit_limit" value="0.00" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5 font-bold">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Registered Pickup Address *</label>
                <textarea name="address_line1" rows="2" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">City *</label>
                <input type="text" name="city" value="New York" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Postal Code (PIN) *</label>
                <input type="text" name="postal_code" value="10001" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-lg shadow transition">
                Register Customer Account
            </button>
        </div>
    </form>
</div>
@endsection
