@extends('layouts.app')

@section('title', 'Create Delivery Run Sheet (DRS)')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Generate Delivery Run Sheet (DRS)</h1>
            <p class="text-sm text-slate-500">Assign consignments to last-mile delivery riders and dispatch out for delivery</p>
        </div>
        <a href="{{ route('delivery.index') }}" class="text-sm text-slate-500 hover:text-slate-800 font-medium">← Back to DRS</a>
    </div>

    <form action="{{ route('delivery.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-900 text-sm">Rider & Branch Allocation</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Delivery Agent / Rider *</label>
                    <select name="delivery_agent_id" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }} ({{ $agent->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Branch</label>
                    <select name="branch_id" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Route / Area Notes</label>
                    <input type="text" name="notes" placeholder="e.g. Midtown Downtown Cluster" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                </div>
            </div>
        </div>

        <!-- Available Shipments Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Select Shipments to Assign *</h3>
                    <p class="text-xs text-slate-400">Marking will change status to 'Out for Delivery' and trigger OTP SMS</p>
                </div>
                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">
                    {{ $availableShipments->count() }} available
                </span>
            </div>

            <div class="overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 uppercase font-semibold text-slate-500 sticky top-0">
                        <tr>
                            <th class="p-3 w-10 text-center">Assign</th>
                            <th class="p-3">AWB</th>
                            <th class="p-3">Consignee & Address</th>
                            <th class="p-3">Payment & COD</th>
                            <th class="p-3">Weight</th>
                            <th class="p-3">Current Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($availableShipments as $s)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="p-3 text-center">
                                <input type="checkbox" name="shipment_ids[]" value="{{ $s->id }}" class="rounded text-blue-600">
                            </td>
                            <td class="p-3 font-mono font-bold text-blue-600">{{ $s->awb_number }}</td>
                            <td class="p-3">
                                <div class="font-bold text-slate-800">{{ $s->receiver_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $s->receiver_address }}, {{ $s->receiver_city }}</div>
                            </td>
                            <td class="p-3">
                                <span class="uppercase font-bold {{ $s->payment_type === 'cod' ? 'text-amber-600' : 'text-slate-600' }}">
                                    {{ $s->payment_type }}
                                    @if($s->isCod()) (₹{{ number_format($s->cod_amount, 2) }}) @endif
                                </span>
                            </td>
                            <td class="p-3 font-bold">{{ $s->chargeable_weight }} kg</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase {{ $s->status_badge }}">{{ $s->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">No shipments available for last-mile delivery assignment.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-lg shadow transition">
                    Dispatch Delivery Run Sheet
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

