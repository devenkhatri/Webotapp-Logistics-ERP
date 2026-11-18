@extends('layouts.app')

@section('title', 'Sorting & Bagging Station')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Sorting & Bagging Terminal</h1>
            <p class="text-sm text-slate-500">Consolidate parcels into sealed linehaul bags for inter-hub transport</p>
        </div>
        <a href="{{ route('hubs.index') }}" class="text-sm text-slate-500 hover:text-slate-800 font-medium">← Back to Hubs</a>
    </div>

    <form action="{{ route('hubs.create_bag') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf

        <!-- Left 2 Cols: Shipments Selection -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Parcels Available for Bagging</h3>
                    <p class="text-xs text-slate-400">Select shipments to seal into this bag</p>
                </div>
                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">
                    {{ $availableShipments->count() }} available
                </span>
            </div>

            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 uppercase font-semibold text-slate-500 sticky top-0">
                        <tr>
                            <th class="p-3 w-10 text-center">Select</th>
                            <th class="p-3">AWB</th>
                            <th class="p-3">Origin → Dest</th>
                            <th class="p-3">Service</th>
                            <th class="p-3">Weight</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($availableShipments as $s)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="p-3 text-center">
                                <input type="checkbox" name="shipment_ids[]" value="{{ $s->id }}" class="rounded text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="p-3 font-mono font-bold text-blue-600">{{ $s->awb_number }}</td>
                            <td class="p-3 font-medium text-slate-800">{{ $s->sender_city }} → {{ $s->receiver_city }}</td>
                            <td class="p-3 capitalize">{{ $s->service_type }}</td>
                            <td class="p-3 font-bold">{{ $s->chargeable_weight }} kg</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase {{ $s->status_badge }}">
                                    {{ $s->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">No loose shipments currently waiting for bagging.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Col: Bag Config -->
        <div class="space-y-4">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-900 text-sm">Bag Routing & Seal</h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Origin Facility *</label>
                    <select name="origin_hub_id" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                        @foreach($hubs as $hub)
                            <option value="{{ $hub->id }}">{{ $hub->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Destination Facility *</label>
                    <select name="destination_hub_id" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                        @foreach($hubs as $hub)
                            <option value="{{ $hub->id }}" {{ $loop->last ? 'selected' : '' }}>{{ $hub->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Security Seal Tag Number (Optional)</label>
                    <input type="text" name="seal_number" placeholder="e.g. SEAL-990145" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow transition flex items-center justify-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    <span>Seal & Generate Bag Barcode</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

