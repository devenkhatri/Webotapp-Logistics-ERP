@extends('layouts.app')

@section('title', 'Operations Dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Operations Control Center</h1>
            <p class="text-sm text-slate-500">Live sorting hub flow, linehaul transit, and delivery dispatch execution</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('hubs.inbound') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
                <i data-lucide="barcode" class="w-4 h-4"></i>
                <span>Inbound Scan Terminal</span>
            </a>
            <a href="{{ route('manifests.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-sm transition">
                <i data-lucide="truck" class="w-4 h-4"></i>
                <span>Dispatch Manifest</span>
            </a>
        </div>
    </div>

    <!-- Operations KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pending Pickups</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $pendingPickups }}</div>
            <span class="text-[11px] text-slate-500">Need assignment</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Parcels at Hub</span>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $atHubCount }}</div>
            <span class="text-[11px] text-slate-500">Sorting / Bagging</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Linehaul Transit</span>
            <div class="text-2xl font-black text-cyan-600 mt-1">{{ $inTransitCount }}</div>
            <span class="text-[11px] text-slate-500">Inter-hub freight</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Out for Delivery</span>
            <div class="text-2xl font-black text-orange-500 mt-1">{{ $outForDeliveryCount }}</div>
            <span class="text-[11px] text-slate-500">With riders today</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Delivered Today</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $deliveredToday }}</div>
            <span class="text-[11px] text-emerald-600 font-semibold">e-POD verified</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Failed / Exceptions</span>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $failedAttempts }}</div>
            <span class="text-[11px] text-rose-500">Reattempts needed</span>
        </div>
    </div>

    <!-- Active Linehauls & Inbound Scans Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Active Linehaul Manifests -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-sm">Active Linehaul Dispatches</h3>
                <a href="{{ route('manifests.index') }}" class="text-xs text-blue-600 hover:underline font-semibold">View All Manifests →</a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($activeManifests as $man)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <a href="{{ route('manifests.show', $man->id) }}" class="font-mono font-bold text-blue-600 hover:underline">{{ $man->manifest_number }}</a>
                        <div class="text-slate-500 mt-0.5">{{ $man->sourceHub->name }} → {{ $man->destinationHub->name }}</div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $man->status === 'dispatched' ? 'bg-cyan-100 text-cyan-800' : 'bg-slate-100 text-slate-700' }}">
                            {{ $man->status }}
                        </span>
                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $man->total_shipments }} pkgs • {{ $man->total_weight }} kg</div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">No active linehaul dispatches.</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Inbound Arrivals -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-sm">Live Inbound Arrivals Stream</h3>
                <a href="{{ route('hubs.inbound') }}" class="text-xs text-blue-600 hover:underline font-semibold">Open Terminal →</a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentInboundScans as $sc)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <span class="font-mono font-bold text-slate-800">{{ $sc->shipment->awb_number ?? 'AWB' }}</span>
                        <div class="text-slate-500 mt-0.5">{{ $sc->hub->name }} • {{ $sc->remarks }}</div>
                    </div>
                    <div class="text-right text-[11px] font-mono text-slate-400">
                        {{ $sc->created_at->format('h:i A') }}
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">No inbound scans today.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
