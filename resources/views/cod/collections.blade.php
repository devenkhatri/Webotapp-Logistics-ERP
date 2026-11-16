@extends('layouts.app')

@section('title', 'COD Collections')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Cash on Delivery (COD) Collections</h1>
            <p class="text-sm text-slate-500">Track rider cash collections, branch deposits, and reconciliation</p>
        </div>

        <a href="{{ route('cod.settlements') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition">
            <i data-lucide="calculator" class="w-4 h-4"></i>
            <span>Merchant Settlements</span>
        </a>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total COD Collected</span>
            <h3 class="text-2xl font-extrabold text-slate-900 mt-1">${{ number_format($totalCollected, 2) }}</h3>
            <span class="text-xs text-slate-500">Across all delivery agents</span>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Deposited at Branch</span>
            <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">${{ number_format($totalDeposited, 2) }}</h3>
            <span class="text-xs text-emerald-600 font-medium">Verified by cashier</span>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Pending Rider Deposit</span>
            <h3 class="text-2xl font-extrabold text-amber-600 mt-1">${{ number_format($pendingDeposit, 2) }}</h3>
            <span class="text-xs text-amber-600 font-medium">Cash in hand with riders</span>
        </div>
    </div>

    <!-- Collections Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Shipment AWB</th>
                        <th class="px-6 py-3.5">Delivery Rider</th>
                        <th class="px-6 py-3.5">Amount Collected</th>
                        <th class="px-6 py-3.5">Method</th>
                        <th class="px-6 py-3.5">Collection Time</th>
                        <th class="px-6 py-3.5">Deposit Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($collections as $col)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-6 py-4 font-mono font-bold text-blue-600">
                            <a href="{{ route('shipments.show', $col->shipment->id) }}" class="hover:underline">{{ $col->shipment->awb_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-800">
                            {{ $col->agent->name }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900 text-sm">
                            ${{ number_format($col->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 uppercase font-bold text-xs text-slate-600">
                            {{ $col->payment_method }}
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 font-mono">
                            {{ $col->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($col->is_deposited_to_branch)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">
                                    ✓ Deposited & Verified
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">
                                    Pending Deposit
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-xs">
                            @if(! $col->is_deposited_to_branch)
                            <form action="{{ route('cod.deposit', $col->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow transition">
                                    Verify Deposit
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-slate-400">By {{ $col->verifiedBy->name ?? 'Staff' }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-400">No COD collections registered yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
