@extends('layouts.app')

@section('title', 'Reverse Logistics & RTO')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Reverse Logistics & Return to Origin (RTO)</h1>
        <p class="text-sm text-slate-500">Track undelivered shipments routed back to merchant, return AWBs, and handover receipts</p>
    </div>

    <!-- RTO Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Return AWB (RTO)</th>
                        <th class="px-6 py-3.5">Original Forward AWB</th>
                        <th class="px-6 py-3.5">Merchant / Shipper</th>
                        <th class="px-6 py-3.5">Return Reason</th>
                        <th class="px-6 py-3.5">Initiated Date</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rtoRecords as $rto)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-6 py-4 font-mono font-bold text-red-600 text-sm">
                            {{ $rto->rto_awb }}
                        </td>
                        <td class="px-6 py-4 font-mono font-semibold text-slate-700">
                            <a href="{{ route('shipments.show', $rto->originalShipment->id) }}" class="hover:underline text-blue-600">{{ $rto->originalShipment->awb_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-800">
                            {{ $rto->originalShipment->customer->name ?? $rto->originalShipment->sender_name }}
                            <div class="text-[11px] text-slate-400 font-normal">{{ $rto->originalShipment->sender_city }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <span class="inline-block px-2 py-0.5 rounded bg-rose-50 text-rose-800 font-medium">
                                {{ $rto->return_reason }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 font-mono">
                            {{ $rto->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold uppercase {{ $rto->status === 'delivered_to_merchant' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ str_replace('_', ' ', $rto->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-xs">
                            @if($rto->status !== 'delivered_to_merchant')
                            <form action="{{ route('rto.complete', $rto->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow transition">
                                    Handover to Merchant
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-slate-400">Delivered {{ $rto->delivered_to_merchant_at?->format('d M') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-400">No shipments in reverse RTO pipeline.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

