@extends('layouts.app')

@section('title', 'Manifest ' . $manifest->manifest_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between no-print">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-mono font-bold text-slate-900">{{ $manifest->manifest_number }}</h1>
                <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase {{ $manifest->status === 'received' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ $manifest->status }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Linehaul Freight Dispatch Document</p>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-3.5 py-1.5 bg-slate-800 text-white font-semibold text-xs rounded-lg shadow">
                Print Manifest Sheet
            </button>
            @if($manifest->status === 'draft')
            <form action="{{ route('manifests.dispatch', $manifest->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg shadow">
                    Dispatch Vehicle
                </button>
            </form>
            @elseif($manifest->status === 'dispatched')
            <form action="{{ route('manifests.receive', $manifest->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow">
                    Receive Manifest at Destination Hub
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Printable Manifest Layout -->
    <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm space-y-6">
        <div class="flex justify-between items-start border-b border-slate-200 pb-4">
            <div>
                <h2 class="text-xl font-black text-slate-900">APEX LOGISTICS EXPRESS</h2>
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Linehaul Trip Manifest</p>
            </div>
            <div class="text-right">
                <div class="font-mono font-extrabold text-sm text-slate-800">{{ $manifest->manifest_number }}</div>
                <div class="text-xs text-slate-400">Date: {{ $manifest->created_at->format('d M Y, h:i A') }}</div>
            </div>
        </div>

        <!-- Route & Carrier Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-lg text-xs">
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Source Hub</span>
                <strong class="text-slate-900">{{ $manifest->sourceHub->name }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Destination Hub</span>
                <strong class="text-slate-900">{{ $manifest->destinationHub->name }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Vehicle / Carrier</span>
                <strong class="text-slate-900">{{ $manifest->vehicle->vehicle_number ?? 'Air Cargo AA-402' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Driver</span>
                <strong class="text-slate-900">{{ $manifest->driver->name ?? 'Commercial Courier' }}</strong>
            </div>
        </div>

        <!-- Manifest Shipments Table -->
        <div>
            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-2">Manifested Shipments ({{ $manifest->shipments->count() }} Parcels)</h3>
            <table class="w-full text-left text-xs border border-slate-200">
                <thead class="bg-slate-100 border-b border-slate-200 font-bold text-slate-700">
                    <tr>
                        <th class="p-2.5">#</th>
                        <th class="p-2.5">AWB Number</th>
                        <th class="p-2.5">Consignee City</th>
                        <th class="p-2.5">Service</th>
                        <th class="p-2.5 text-right">Weight</th>
                        <th class="p-2.5 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($manifest->shipments as $idx => $s)
                    <tr>
                        <td class="p-2.5 font-bold text-slate-400">{{ $idx + 1 }}</td>
                        <td class="p-2.5 font-mono font-bold text-blue-600">{{ $s->awb_number }}</td>
                        <td class="p-2.5">{{ $s->receiver_city }}</td>
                        <td class="p-2.5 capitalize">{{ $s->service_type }}</td>
                        <td class="p-2.5 text-right font-bold">{{ $s->chargeable_weight }} kg</td>
                        <td class="p-2.5 text-right uppercase font-semibold text-[10px]">{{ $s->pivot->status ?? 'Loaded' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-400">No shipments loaded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Signatures Block -->
        <div class="grid grid-cols-2 gap-12 pt-12 text-xs text-slate-500">
            <div class="border-t border-slate-300 pt-2 text-center">
                Dispatched by Hub Supervisor Signature & Seal
            </div>
            <div class="border-t border-slate-300 pt-2 text-center">
                Received by Destination Hub Receiver Signature
            </div>
        </div>
    </div>
</div>
@endsection
