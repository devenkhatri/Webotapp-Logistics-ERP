@extends('layouts.app')

@section('title', 'Create Linehaul Manifest')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Create Linehaul Dispatch Manifest</h1>
            <p class="text-sm text-slate-500">Assign vehicle, driver, and consolidate consignments for inter-hub transfer</p>
        </div>
        <a href="{{ route('manifests.index') }}" class="text-sm text-slate-500 hover:text-slate-800 font-medium">← Back to Manifests</a>
    </div>

    <form action="{{ route('manifests.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-900 text-sm">Transit Route & Fleet Assignment</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Source Hub *</label>
                    <select name="source_hub_id" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                        @foreach($hubs as $hub)
                            <option value="{{ $hub->id }}">{{ $hub->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Destination Hub *</label>
                    <select name="destination_hub_id" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                        @foreach($hubs as $hub)
                            <option value="{{ $hub->id }}" {{ $loop->last ? 'selected' : '' }}>{{ $hub->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Vehicle / Truck</label>
                    <select name="vehicle_id" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                        <option value="">-- Direct Air Freight / Third-party --</option>
                        @foreach($vehicles as $veh)
                            <option value="{{ $veh->id }}">{{ $veh->vehicle_number }} ({{ $veh->model }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Driver</label>
                    <select name="driver_id" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                        <option value="">-- Carrier Staff --</option>
                        @foreach($drivers as $drv)
                            <option value="{{ $drv->id }}">{{ $drv->name }} ({{ $drv->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Dispatch Remarks</label>
                    <input type="text" name="remarks" placeholder="e.g. Flight AA-402 Transit / Night Linehaul" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                </div>
            </div>
        </div>

        <!-- Available Shipments Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Select Shipments to Load onto Manifest *</h3>
                    <p class="text-xs text-slate-400">Select all packages loaded onto the vehicle</p>
                </div>
                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">
                    {{ $availableShipments->count() }} available
                </span>
            </div>

            <div class="overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 uppercase font-semibold text-slate-500 sticky top-0">
                        <tr>
                            <th class="p-3 w-10 text-center">Load</th>
                            <th class="p-3">AWB</th>
                            <th class="p-3">Route</th>
                            <th class="p-3">Weight</th>
                            <th class="p-3">Service</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($availableShipments as $s)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="p-3 text-center">
                                <input type="checkbox" name="shipment_ids[]" value="{{ $s->id }}" class="rounded text-blue-600">
                            </td>
                            <td class="p-3 font-mono font-bold text-blue-600">{{ $s->awb_number }}</td>
                            <td class="p-3">{{ $s->sender_city }} → {{ $s->receiver_city }}</td>
                            <td class="p-3 font-bold">{{ $s->chargeable_weight }} kg</td>
                            <td class="p-3 capitalize">{{ $s->service_type }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase {{ $s->status_badge }}">{{ $s->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">No shipments ready for linehaul loading.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-lg shadow transition">
                    Generate Manifest Document
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

