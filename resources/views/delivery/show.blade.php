@extends('layouts.app')

@section('title', 'DRS ' . $run->run_sheet_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 no-print">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-mono font-bold text-slate-900">{{ $run->run_sheet_number }}</h1>
                <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase {{ $run->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                    {{ $run->status }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Delivery Run Sheet (DRS) & Proof Manifest</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button onclick="window.print()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs rounded-lg shadow transition">
                Print Run Sheet
            </button>
            <a href="{{ route('agent.portal') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow transition">
                Open in Rider Mobile View
            </a>
        </div>
    </div>

    <!-- Printable Layout -->
    <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm space-y-6">
        <div class="flex justify-between items-start border-b border-slate-200 pb-4">
            <div>
                <h2 class="text-xl font-black text-slate-900">APEX LOGISTICS EXPRESS</h2>
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Delivery Run Sheet (DRS)</p>
            </div>
            <div class="text-right">
                <div class="font-mono font-extrabold text-sm text-slate-800">{{ $run->run_sheet_number }}</div>
                <div class="text-xs text-slate-400">Date: {{ \Carbon\Carbon::parse($run->run_date)->format('d M Y') }}</div>
            </div>
        </div>

        <!-- Rider & Branch Info -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-lg text-xs">
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Delivery Agent</span>
                <strong class="text-slate-900">{{ $run->deliveryAgent->name }}</strong>
                <div class="text-[11px] text-slate-500">{{ $run->deliveryAgent->phone }}</div>
            </div>
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Branch Office</span>
                <strong class="text-slate-900">{{ $run->branch->name ?? 'Head Office' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Total Consignments</span>
                <strong class="text-slate-900 text-sm">{{ $run->total_shipments }} ({{ $run->delivered_count }} delivered)</strong>
            </div>
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">COD Collectable</span>
                <strong class="text-amber-700 text-sm">₹{{ number_format($run->total_cod_to_collect, 2) }}</strong>
            </div>
        </div>

        <!-- Shipments Table -->
        <div>
            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-2">Assigned Stops</h3>
            <table class="w-full text-left text-xs border border-slate-200">
                <thead class="bg-slate-100 border-b border-slate-200 font-bold text-slate-700">
                    <tr>
                        <th class="p-2.5">Seq</th>
                        <th class="p-2.5">AWB Number</th>
                        <th class="p-2.5">Consignee Name & Address</th>
                        <th class="p-2.5">Phone</th>
                        <th class="p-2.5 text-right">COD Due</th>
                        <th class="p-2.5 text-center">Status</th>
                        <th class="p-2.5">Recipient Sign / OTP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($run->shipments as $s)
                    <tr>
                        <td class="p-2.5 font-bold text-slate-400">{{ $s->pivot->sequence_order }}</td>
                        <td class="p-2.5 font-mono font-bold text-blue-600">{{ $s->awb_number }}</td>
                        <td class="p-2.5">
                            <div class="font-bold text-slate-800">{{ $s->receiver_name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $s->receiver_address }}, {{ $s->receiver_city }}</div>
                        </td>
                        <td class="p-2.5 font-mono">{{ $s->receiver_phone }}</td>
                        <td class="p-2.5 text-right font-bold text-amber-700">
                            {{ $s->isCod() ? '₹' . number_format($s->cod_amount, 2) : 'PREPAID' }}
                        </td>
                        <td class="p-2.5 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $s->status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : ($s->status === 'failed_attempt' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700') }}">
                                {{ $s->pivot->status }}
                            </span>
                        </td>
                        <td class="p-2.5 text-slate-400 text-center">
                            @if($s->status === 'delivered')
                                <span class="text-emerald-700 font-bold">✓ Signed by {{ $s->recipient_name }}</span>
                            @else
                                _____________________
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-2 gap-12 pt-12 text-xs text-slate-500">
            <div class="border-t border-slate-300 pt-2 text-center">
                Delivery Agent Signature
            </div>
            <div class="border-t border-slate-300 pt-2 text-center">
                Cashier / Branch Deposit Acknowledgement
            </div>
        </div>
    </div>
</div>
@endsection

