@extends('layouts.app')

@section('title', 'Merchant Self-Service Portal')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Merchant Shipping & COD Portal</h1>
            <p class="text-sm text-slate-500">Welcome, <strong>{{ $customer->name ?? 'Merchant' }}</strong> (Account: {{ $customer->account_number ?? 'CUST-1001' }})</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('shipments.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Create Order Consignment</span>
            </a>
        </div>
    </div>

    <!-- Merchant KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Booked</span>
            <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalShipments) }}</h3>
            <span class="text-xs text-blue-600 font-medium">Orders handled</span>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Delivered Orders</span>
            <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($deliveredCount) }}</h3>
            <span class="text-xs text-emerald-600 font-medium">Delivered to customers</span>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">COD Collected in Escrow</span>
            <h3 class="text-2xl font-extrabold text-amber-600 mt-1">₹{{ number_format($codCollected, 2) }}</h3>
            <span class="text-xs text-amber-600 font-medium">Ready for remittance payout</span>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Credit Balance</span>
            <h3 class="text-2xl font-extrabold text-slate-900 mt-1">₹{{ number_format($customer->credit_limit ?? 25000, 2) }}</h3>
            <span class="text-xs text-slate-500">Cycle: {{ ucfirst($customer->billing_cycle ?? 'weekly') }}</span>
        </div>
    </div>

    <!-- API Keys for E-Commerce Integration (Shopify, WooCommerce, Magento) -->
    <div class="bg-slate-900 text-white p-5 rounded-xl border border-slate-800 space-y-3">
        <div class="flex items-center justify-between border-b border-slate-800 pb-2">
            <h3 class="font-bold text-xs uppercase tracking-wider text-slate-300 flex items-center gap-2">
                <i data-lucide="code" class="w-4 h-4 text-emerald-400"></i>
                E-Commerce Store API Keys (Shopify / WooCommerce / Custom)
            </h3>
            <span class="text-[10px] text-emerald-400 font-bold uppercase">Ready for API Booking</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-mono">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-sans">API Endpoint</span>
                <input type="text" readonly value="{{ url('/api/v1/shipments/book') }}" class="w-full mt-1 bg-slate-800 border border-slate-700 text-emerald-400 p-2 rounded text-xs">
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-sans">X-API-KEY Token</span>
                <input type="text" readonly value="{{ $customer->api_key ?? 'fn_live_key_98a72b1640c4' }}" class="w-full mt-1 bg-slate-800 border border-slate-700 text-amber-400 p-2 rounded text-xs">
            </div>
        </div>
    </div>

    <!-- Merchant Orders Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">Your Orders & Tracking</h3>
            <a href="{{ route('shipments.create') }}" class="text-xs text-blue-600 hover:underline font-semibold">+ Book New Consignment</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 uppercase font-semibold text-slate-500">
                    <tr>
                        <th class="p-3">AWB</th>
                        <th class="p-3">Order Ref</th>
                        <th class="p-3">Customer (Consignee)</th>
                        <th class="p-3">Destination</th>
                        <th class="p-3">Mode & COD</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shipments as $s)
                    <tr>
                        <td class="p-3 font-mono font-bold text-blue-600">
                            <a href="{{ route('shipments.show', $s->id) }}" class="hover:underline">{{ $s->awb_number }}</a>
                        </td>
                        <td class="p-3 font-semibold text-slate-800">{{ $s->reference_number ?? '-' }}</td>
                        <td class="p-3 font-medium text-slate-800">{{ $s->receiver_name }}</td>
                        <td class="p-3">{{ $s->receiver_city }} ({{ $s->receiver_postal_code }})</td>
                        <td class="p-3">
                            <span class="uppercase font-bold {{ $s->payment_type === 'cod' ? 'text-amber-600' : 'text-slate-500' }}">
                                {{ $s->payment_type }} @if($s->isCod()) (₹{{ number_format($s->cod_amount, 2) }}) @endif
                            </span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $s->status_badge }}">{{ $s->status }}</span>
                        </td>
                        <td class="p-3 text-right space-x-1">
                            <a href="{{ route('shipments.show', $s->id) }}" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Details</a>
                            <a href="{{ route('shipments.label', $s->id) }}" target="_blank" class="px-2 py-1 rounded bg-blue-50 text-blue-700 font-medium">Label</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">No shipments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shipments->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $shipments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

