@extends('layouts.app')

@section('title', 'Shipments & Consignments')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Shipments & Waybills</h1>
            <p class="text-sm text-slate-500">Manage consignments, AWB allocation, freight status, and labels</p>
        </div>

        <a href="{{ route('shipments.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Book New Shipment</span>
        </a>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form action="{{ route('shipments.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="md:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by AWB, Ref, Phone, Receiver..." class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="w-full py-2 px-3 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    <option value="">All Statuses</option>
                    <option value="booked" {{ request('status') == 'booked' ? 'selected' : '' }}>Booked</option>
                    <option value="picked_up" {{ request('status') == 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                    <option value="received_at_hub" {{ request('status') == 'received_at_hub' ? 'selected' : '' }}>At Hub</option>
                    <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                    <option value="out_for_delivery" {{ request('status') == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="failed_attempt" {{ request('status') == 'failed_attempt' ? 'selected' : '' }}>Failed Attempt</option>
                    <option value="rto_initiated" {{ request('status') == 'rto_initiated' ? 'selected' : '' }}>RTO Initiated</option>
                </select>
            </div>

            <!-- Payment Type Filter -->
            <div>
                <select name="payment_type" class="w-full py-2 px-3 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    <option value="">All Payments</option>
                    <option value="prepaid" {{ request('payment_type') == 'prepaid' ? 'selected' : '' }}>Prepaid</option>
                    <option value="cod" {{ request('payment_type') == 'cod' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                    <option value="to_pay" {{ request('payment_type') == 'to_pay' ? 'selected' : '' }}>To Pay</option>
                    <option value="corporate_credit" {{ request('payment_type') == 'corporate_credit' ? 'selected' : '' }}>Corporate Credit</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'payment_type', 'branch_id']))
                <a href="{{ route('shipments.index') }}" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Clear Filters">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Shipments Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">AWB / Ref</th>
                        <th class="px-6 py-3.5">Booking Date</th>
                        <th class="px-6 py-3.5">Customer / Merchant</th>
                        <th class="px-6 py-3.5">Route</th>
                        <th class="px-6 py-3.5">Service & Pay</th>
                        <th class="px-6 py-3.5">Weight</th>
                        <th class="px-6 py-3.5">Freight</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shipments as $s)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-6 py-4 font-medium text-slate-900">
                            <a href="{{ route('shipments.show', $s->id) }}" class="font-mono text-blue-600 hover:underline font-bold text-sm">{{ $s->awb_number }}</a>
                            @if($s->reference_number)
                                <div class="text-[11px] text-slate-400 font-normal">Ref: {{ $s->reference_number }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 whitespace-nowrap">
                            {{ $s->created_at->format('M d, Y') }}
                            <div class="text-[10px] text-slate-400">{{ $s->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <div class="font-semibold text-slate-800">{{ $s->customer->name ?? $s->sender_name }}</div>
                            <div class="text-slate-400 truncate max-w-[150px]">{{ $s->customer->company_name ?? 'Retail Sender' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800 text-xs">{{ $s->sender_city }} → {{ $s->receiver_city }}</div>
                            <div class="text-[11px] text-slate-400 truncate max-w-[180px]">{{ $s->receiver_name }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded font-semibold capitalize bg-slate-100 text-slate-700 text-[11px]">
                                {{ $s->service_type }}
                            </span>
                            <div class="mt-0.5 font-bold uppercase text-[11px] {{ $s->payment_type === 'cod' ? 'text-amber-600' : 'text-slate-500' }}">
                                {{ $s->payment_type }}
                                @if($s->isCod()) (${{ number_format($s->cod_amount, 2) }}) @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs font-medium">
                            {{ $s->chargeable_weight }} kg
                            <div class="text-[10px] text-slate-400">{{ $s->pieces_count }} pcs</div>
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900 text-xs">
                            ${{ number_format($s->total_freight, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $s->status_badge }}">
                                {{ ucwords(str_replace('_', ' ', $s->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-xs space-x-1 whitespace-nowrap">
                            <a href="{{ route('shipments.show', $s->id) }}" class="inline-flex items-center px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition" title="View Details">
                                View
                            </a>
                            <a href="{{ route('shipments.label', $s->id) }}" target="_blank" class="inline-flex items-center px-2 py-1 rounded bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium transition" title="Thermal Label">
                                Label
                            </a>
                            <a href="{{ route('shipments.invoice', $s->id) }}" target="_blank" class="inline-flex items-center px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition" title="Waybill Invoice">
                                Waybill
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                            <i data-lucide="package-x" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                            <p class="font-medium text-slate-600">No shipments found matching criteria.</p>
                            <a href="{{ route('shipments.create') }}" class="text-blue-600 font-semibold text-xs hover:underline mt-2 inline-block">Book your first shipment →</a>
                        </td>
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
