@extends('layouts.app')

@section('title', 'Hub Inbound Scan Terminal')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Hub Inbound Scan Terminal</h1>
            <p class="text-sm text-slate-500">Scan incoming consignments or linehaul bags arriving at this sorting facility</p>
        </div>
        <a href="{{ route('hubs.index') }}" class="text-sm text-slate-500 hover:text-slate-800 font-medium">← Back to Hubs</a>
    </div>

    <!-- Scan Console -->
    <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-lg border border-slate-800 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs uppercase font-bold tracking-wider text-slate-300">Scanner Station Active</span>
            </div>
            <span class="text-xs text-slate-400">USB / Bluetooth / Camera Barcode Scanner Ready</span>
        </div>

        <form action="{{ route('hubs.process_inbound') }}" method="POST" id="inboundForm" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Scanning Hub Facility</label>
                    <select name="hub_id" required class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach($hubs as $hub)
                            <option value="{{ $hub->id }}">{{ $hub->name }} ({{ $hub->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Scan AWB / Package Barcode *</label>
                    <div class="relative">
                        <input type="text" name="awb_number" id="barcodeInput" autofocus placeholder="Scan or type AWB (e.g. CX100001) & press Enter..." required class="w-full bg-slate-800 border-2 border-blue-500 text-white font-mono text-lg rounded-lg pl-10 pr-24 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <i data-lucide="barcode" class="w-5 h-5 text-blue-400 absolute left-3 top-3.5"></i>
                        <button type="submit" class="absolute right-2 top-2 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-md shadow">
                            Scan In
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Live Scanned Log -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">Arrival Scan Manifest Log</h3>
            <span class="text-xs text-slate-400">Live operational ledger</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 uppercase font-semibold text-slate-500">
                    <tr>
                        <th class="px-5 py-3">AWB Number</th>
                        <th class="px-5 py-3">Facility</th>
                        <th class="px-5 py-3">Route</th>
                        <th class="px-5 py-3">Scan Type</th>
                        <th class="px-5 py-3 text-right">Scan Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentScans as $s)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-5 py-3 font-mono font-bold text-blue-600">
                            {{ $s->shipment->awb_number ?? 'AWB' }}
                        </td>
                        <td class="px-5 py-3 font-medium text-slate-800">
                            {{ $s->hub->name ?? 'Hub' }}
                        </td>
                        <td class="px-5 py-3 text-slate-500">
                            {{ $s->shipment->sender_city ?? '' }} → {{ $s->shipment->receiver_city ?? '' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold uppercase text-[10px]">
                                {{ $s->scan_type }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right font-mono text-slate-400">
                            {{ $s->created_at->format('d M, h:i:s A') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400">No arrivals scanned yet in this session.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
