@extends('layouts.app')

@section('title', 'Operations Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Top Bar: Title & Quick Branch Filter -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Operations Control Center</h1>
            <p class="text-sm text-slate-500">Live operational logistics metrics and fleet execution</p>
        </div>

        <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-2">
            <select name="branch_id" onchange="this.form.submit()" class="text-sm bg-white border border-slate-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">All Branches & Network</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }} ({{ $b->code }})</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Main KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Shipments -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Consignments</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalShipments) }}</h3>
                <span class="text-xs text-blue-600 font-medium">Network-wide active</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="package" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- In-Transit / Sorting -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">In-Transit / Linehaul</p>
                <h3 class="text-2xl font-extrabold text-cyan-600 mt-1">{{ number_format($inTransitCount) }}</h3>
                <span class="text-xs text-slate-500">{{ $outForDeliveryCount }} out for delivery</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center">
                <i data-lucide="truck" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Delivered & SLA -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Delivered (SLA: {{ $deliverySuccessRate }}%)</p>
                <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($deliveredCount) }}</h3>
                <span class="text-xs text-emerald-600 font-medium">OTP & Signature verified</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Total Revenue & COD -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Freight Revenue</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">₹{{ number_format($totalRevenue, 2) }}</h3>
                <span class="text-xs text-amber-600 font-medium">COD Coll: ₹{{ number_format($totalCodCollected, 2) }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="banknote" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Charts and Status Breakdown Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Donut Chart: Status Distribution -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Consignment Pipeline</h3>
                <p class="text-xs text-slate-500 mt-0.5">Real-time status breakdown</p>
            </div>
            <div class="my-4 relative flex items-center justify-center h-60">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 pt-2 border-t border-slate-100">
                <div><span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500 mr-1.5"></span> Booked: {{ $bookedCount }}</div>
                <div><span class="inline-block w-2.5 h-2.5 rounded-full bg-indigo-500 mr-1.5"></span> Picked: {{ $pickedUpCount }}</div>
                <div><span class="inline-block w-2.5 h-2.5 rounded-full bg-cyan-500 mr-1.5"></span> In Transit: {{ $inTransitCount }}</div>
                <div><span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1.5"></span> Delivered: {{ $deliveredCount }}</div>
            </div>
        </div>

        <!-- Quick Operational Actions & Hub Status -->
        <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Active Sorting Hubs & Operations</h3>
                <p class="text-xs text-slate-500 mt-0.5">Primary regional gateways and fast actions</p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
                    @foreach($hubs as $hub)
                    <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800">{{ $hub->name }}</span>
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 uppercase">{{ $hub->code }}</span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1">{{ $hub->city }}, {{ $hub->state }}</div>
                        <div class="mt-3 flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Type: <span class="capitalize">{{ $hub->hub_type }}</span></span>
                            <a href="{{ route('hubs.inbound') }}" class="text-blue-600 hover:underline font-semibold text-[11px]">Scan Inbound →</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="mt-6 pt-4 border-t border-slate-100 flex flex-wrap gap-2">
                <a href="{{ route('shipments.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> New Booking
                </a>
                <a href="{{ route('hubs.inbound') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold transition">
                    <i data-lucide="barcode" class="w-3.5 h-3.5"></i> Inbound Terminal
                </a>
                <a href="{{ route('manifests.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold transition">
                    <i data-lucide="truck" class="w-3.5 h-3.5"></i> Create Manifest
                </a>
                <a href="{{ route('delivery.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold transition">
                    <i data-lucide="list-checks" class="w-3.5 h-3.5"></i> Dispatch DRS
                </a>
                <a href="{{ route('cod.collections') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-semibold transition">
                    <i data-lucide="banknote" class="w-3.5 h-3.5"></i> Reconcile COD
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Shipments Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Recent Shipments</h3>
                <p class="text-xs text-slate-500">Live operational ledger of incoming and active consignments</p>
            </div>
            <a href="{{ route('shipments.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold transition">View All Shipments →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-3">AWB / Ref</th>
                        <th class="px-6 py-3">Sender → Receiver</th>
                        <th class="px-6 py-3">Service & Mode</th>
                        <th class="px-6 py-3">Weight</th>
                        <th class="px-6 py-3">Total Freight</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentShipments as $s)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-6 py-3.5 font-medium text-slate-900">
                            <a href="{{ route('shipments.show', $s->id) }}" class="font-mono text-blue-600 hover:underline font-bold">{{ $s->awb_number }}</a>
                            @if($s->reference_number)
                                <div class="text-[11px] text-slate-400 font-normal">Ref: {{ $s->reference_number }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            <div class="font-medium text-slate-800">{{ $s->sender_city }} → {{ $s->receiver_city }}</div>
                            <div class="text-xs text-slate-400 truncate max-w-xs">{{ $s->receiver_name }} ({{ $s->receiver_phone }})</div>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold capitalize bg-slate-100 text-slate-700">
                                {{ $s->service_type }}
                            </span>
                            <span class="text-xs uppercase font-bold ml-1 {{ $s->payment_type === 'cod' ? 'text-amber-600' : 'text-slate-500' }}">
                                {{ $s->payment_type }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 font-medium">
                            {{ $s->chargeable_weight }} kg
                        </td>
                        <td class="px-6 py-3.5 font-bold text-slate-900">
                            ₹{{ number_format($s->total_freight, 2) }}
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $s->status_badge }}">
                                {{ ucwords(str_replace('_', ' ', $s->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right space-x-2">
                            <a href="{{ route('shipments.show', $s->id) }}" class="text-slate-500 hover:text-blue-600 text-xs font-medium" title="Details">View</a>
                            <a href="{{ route('shipments.label', $s->id) }}" target="_blank" class="text-slate-500 hover:text-slate-900 text-xs font-medium" title="Label">Label</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400">No shipments registered yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('statusChart').getContext('2d');
        const data = @json(array_values($statusDistribution));
        const labels = @json(array_keys($statusDistribution));

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        '#3b82f6', // Booked
                        '#6366f1', // Picked Up
                        '#06b6d4', // In Transit
                        '#f97316', // Out for Delivery
                        '#10b981', // Delivered
                        '#ef4444'  // Exceptions / RTO
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '72%'
            }
        });
    });
</script>
@endpush

