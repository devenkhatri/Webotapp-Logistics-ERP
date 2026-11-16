@extends('layouts.app')

@section('title', 'Hubs & Sorting Centers')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Hubs & Sorting Centers</h1>
            <p class="text-sm text-slate-500">Regional distribution centers, gateway air hubs, and sorting operations</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('hubs.inbound') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
                <i data-lucide="barcode" class="w-4 h-4"></i>
                <span>Inbound Scan Terminal</span>
            </a>
            <a href="{{ route('hubs.sorting') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-sm transition">
                <i data-lucide="archive" class="w-4 h-4"></i>
                <span>Bagging Station</span>
            </a>
        </div>
    </div>

    <!-- Hubs Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($hubs as $hub)
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 rounded bg-blue-50 text-blue-700 text-xs font-bold uppercase">{{ $hub->code }}</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold uppercase bg-emerald-50 text-emerald-700">Active</span>
            </div>

            <div>
                <h3 class="text-base font-bold text-slate-900">{{ $hub->name }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ $hub->address }}, {{ $hub->city }}</p>
            </div>

            <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs">
                <div>
                    <span class="text-slate-400 block">Hub Type</span>
                    <strong class="text-slate-800 capitalize">{{ $hub->hub_type }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block">Contact Phone</span>
                    <strong class="text-slate-800">{{ $hub->phone ?? 'N/A' }}</strong>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between text-xs">
                <a href="{{ route('hubs.inbound') }}" class="text-blue-600 hover:underline font-semibold">Open Inbound Scan →</a>
                <a href="{{ route('hubs.sorting') }}" class="text-slate-500 hover:underline">Bagging →</a>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Recent Sealed Bags & Scans -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Bags -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Recently Sealed Bags</h3>
                <a href="{{ route('hubs.sorting') }}" class="text-xs text-blue-600 hover:underline font-semibold">New Bag →</a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentBags as $bag)
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <span class="font-mono font-bold text-slate-800">{{ $bag->bag_number }}</span>
                        <div class="text-[11px] text-slate-400">{{ $bag->originHub->name ?? 'Origin' }} → {{ $bag->destinationHub->name ?? 'Dest' }}</div>
                    </div>
                    <div class="text-right">
                        <span class="font-bold text-slate-800">{{ $bag->weight_kg }} kg</span>
                        <div class="text-[10px] text-slate-400">{{ $bag->shipments->count() }} pkgs</div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">No bags sealed yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Inbound Scans Activity -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
            <h3 class="font-bold text-slate-900 text-sm">Live Scan Activity Stream</h3>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentScans as $scan)
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <span class="font-mono font-bold text-blue-600">{{ $scan->shipment->awb_number ?? 'AWB' }}</span>
                        <div class="text-[11px] text-slate-400">{{ $scan->hub->name }} • {{ $scan->remarks }}</div>
                    </div>
                    <div class="text-right text-[11px] text-slate-400 font-mono">
                        {{ $scan->created_at->format('h:i A') }}
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">No scans recorded today.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
