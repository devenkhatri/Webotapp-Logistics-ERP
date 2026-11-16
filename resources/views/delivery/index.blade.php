@extends('layouts.app')

@section('title', 'Delivery Run Sheets (DRS)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Last-Mile Delivery Run Sheets (DRS)</h1>
            <p class="text-sm text-slate-500">Assign consignments to delivery agents, dispatch riders, and track PODs</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('agent.portal') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                <i data-lucide="smartphone" class="w-4 h-4"></i>
                <span>Open Rider Mobile View</span>
            </a>
            <a href="{{ route('delivery.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Generate DRS Sheet</span>
            </a>
        </div>
    </div>

    <!-- DRS Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">DRS Sheet No</th>
                        <th class="px-6 py-3.5">Run Date</th>
                        <th class="px-6 py-3.5">Delivery Rider / Agent</th>
                        <th class="px-6 py-3.5">Branch</th>
                        <th class="px-6 py-3.5">Parcels (Delivered / Failed)</th>
                        <th class="px-6 py-3.5">COD to Collect</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($runs as $run)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-6 py-4 font-mono font-bold text-blue-600 text-sm">
                            <a href="{{ route('delivery.show', $run->id) }}" class="hover:underline">{{ $run->run_sheet_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-xs font-medium text-slate-700">
                            {{ \Carbon\Carbon::parse($run->run_date)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-800">
                            {{ $run->deliveryAgent->name ?? 'Agent' }}
                            <div class="text-[11px] text-slate-400 font-normal">{{ $run->deliveryAgent->phone ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-600">
                            {{ $run->branch->name ?? 'Branch' }}
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <div class="font-bold text-slate-800">{{ $run->total_shipments }} total</div>
                            <div class="text-[11px] text-slate-500">
                                <span class="text-emerald-600 font-bold">{{ $run->delivered_count }} delivered</span> •
                                <span class="text-rose-600 font-bold">{{ $run->failed_count }} failed</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-amber-700">
                            ${{ number_format($run->total_cod_to_collect, 2) }}
                            <div class="text-[10px] text-slate-400 font-normal">Coll: ${{ number_format($run->total_cod_collected, 2) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold capitalize {{ $run->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                                {{ ucwords(str_replace('_', ' ', $run->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-xs">
                            <a href="{{ route('delivery.show', $run->id) }}" class="px-3 py-1.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">
                                View DRS
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-400">No delivery run sheets generated yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
