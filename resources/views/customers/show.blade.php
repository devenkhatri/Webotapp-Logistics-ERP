@extends('layouts.app')

@section('title', 'Customer ' . $customer->account_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $customer->name }}</h1>
                <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase bg-blue-100 text-blue-800">
                    {{ $customer->account_number }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ $customer->company_name ?? 'Retail Account' }} • Category: <strong class="capitalize">{{ $customer->customer_type }}</strong></p>
        </div>

        <a href="{{ route('customers.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium">← Back to Customers</a>
    </div>

    <!-- API Credentials Card (for Merchants) -->
    @if($customer->api_key)
    <div class="bg-slate-900 text-white p-5 rounded-xl border border-slate-800 space-y-3">
        <div class="flex items-center justify-between border-b border-slate-800 pb-2">
            <h3 class="font-bold text-xs uppercase tracking-wider text-slate-300 flex items-center gap-2">
                <i data-lucide="key" class="w-4 h-4 text-amber-400"></i>
                REST API & Webhook Credentials
            </h3>
            <span class="text-[10px] text-emerald-400 font-bold uppercase">Integration Active</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-mono">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-sans">API Key (X-API-KEY Header)</span>
                <input type="text" readonly value="{{ $customer->api_key }}" class="w-full mt-1 bg-slate-800 border border-slate-700 text-amber-400 p-2 rounded text-xs">
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-sans">API Secret</span>
                <input type="text" readonly value="{{ $customer->api_secret }}" class="w-full mt-1 bg-slate-800 border border-slate-700 text-slate-400 p-2 rounded text-xs">
            </div>
        </div>
    </div>
    @endif

    <!-- Profile & Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Assigned Rate Card</span>
            <strong class="text-slate-900 text-base mt-1 block">{{ $customer->rateCard->name ?? 'Default Tariff' }}</strong>
            <span class="text-xs text-slate-500">Service: {{ $customer->rateCard->service_type ?? 'Standard' }}</span>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Approved Credit Limit</span>
            <strong class="text-slate-900 text-base mt-1 block">${{ number_format($customer->credit_limit, 2) }}</strong>
            <span class="text-xs text-slate-500">Billing Cycle: {{ ucfirst($customer->billing_cycle) }}</span>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Tax / GST Number</span>
            <strong class="text-slate-900 text-base mt-1 block font-mono">{{ $customer->gst_number ?? 'N/A' }}</strong>
            <span class="text-xs text-slate-500">Verified identity</span>
        </div>
    </div>

    <!-- Recent Shipments Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">Recent Consignments</h3>
            <span class="text-xs text-slate-400">Total: {{ $customer->shipments->count() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 uppercase font-semibold text-slate-500">
                    <tr>
                        <th class="p-3">AWB</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Receiver & Destination</th>
                        <th class="p-3">Payment</th>
                        <th class="p-3 text-right">Freight</th>
                        <th class="p-3 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customer->shipments as $s)
                    <tr>
                        <td class="p-3 font-mono font-bold text-blue-600">
                            <a href="{{ route('shipments.show', $s->id) }}" class="hover:underline">{{ $s->awb_number }}</a>
                        </td>
                        <td class="p-3 text-slate-400">{{ $s->created_at->format('d M Y') }}</td>
                        <td class="p-3">
                            <span class="font-bold text-slate-800">{{ $s->receiver_name }}</span> ({{ $s->receiver_city }})
                        </td>
                        <td class="p-3 uppercase font-semibold">{{ $s->payment_type }}</td>
                        <td class="p-3 text-right font-bold text-slate-900">${{ number_format($s->total_freight, 2) }}</td>
                        <td class="p-3 text-right">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $s->status_badge }}">{{ $s->status }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-400">No shipments found for this customer.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

