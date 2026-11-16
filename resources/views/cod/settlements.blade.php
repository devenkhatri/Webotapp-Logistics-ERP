@extends('layouts.app')

@section('title', 'Merchant COD Settlements')

@section('content')
<div class="space-y-6" x-data="{ showPayModal: false, activeSettlement: null }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Merchant COD Reconciliation & Settlements</h1>
            <p class="text-sm text-slate-500">Calculate net remittances, deduct shipping fees, and process merchant payouts</p>
        </div>

        <a href="{{ route('cod.collections') }}" class="text-sm text-slate-500 hover:text-slate-800 font-medium">← Back to Collections</a>
    </div>

    <!-- Settlement Generator Card -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="font-bold text-slate-900 text-sm mb-3">Generate Merchant Settlement Statement</h3>

        <form action="{{ route('cod.generate_settlement') }}" method="POST" class="flex flex-col sm:flex-row items-center gap-3">
            @csrf
            <div class="flex-1 w-full">
                <select name="customer_id" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Select Merchant / Corporate Client --</option>
                    @foreach($merchants as $m)
                        <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->company_name ?? $m->account_number }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-lg shadow transition">
                Calculate & Generate Statement
            </button>
        </form>
    </div>

    <!-- Settlements Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Statement No</th>
                        <th class="px-6 py-3.5">Merchant</th>
                        <th class="px-6 py-3.5">Gross COD Collected</th>
                        <th class="px-6 py-3.5">Courier Deductions</th>
                        <th class="px-6 py-3.5">Net Payout</th>
                        <th class="px-6 py-3.5">Payment Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($settlements as $set)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-6 py-4 font-mono font-bold text-blue-600 text-sm">
                            {{ $set->settlement_number }}
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-800">
                            {{ $set->customer->name }}
                            <div class="text-[11px] text-slate-400 font-normal">{{ $set->customer->account_number }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-slate-800">
                            ${{ number_format($set->total_cod_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-rose-600">
                            -${{ number_format($set->total_deductions, 2) }}
                        </td>
                        <td class="px-6 py-4 font-extrabold text-emerald-600 text-sm">
                            ${{ number_format($set->net_payable, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase {{ $set->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $set->status }}
                            </span>
                            @if($set->transaction_reference)
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">Ref: {{ $set->transaction_reference }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-xs">
                            @if($set->status !== 'paid')
                            <button type="button" @click="activeSettlement = {{ $set }}; showPayModal = true" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow transition">
                                Mark as Paid
                            </button>
                            @else
                            <span class="text-xs text-slate-400">Settled on {{ $set->settled_at?->format('d M') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-400">No COD settlements generated yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mark Paid Modal -->
    <div x-show="showPayModal" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white max-w-sm w-full rounded-2xl shadow-2xl p-6 space-y-4">
            <h3 class="font-bold text-slate-900 text-base">Record Settlement Payout</h3>
            
            <form :action="'{{ url('/cod/settlements') }}/' + (activeSettlement ? activeSettlement.id : 0) + '/mark-paid'" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Bank Reference / UTR Number *</label>
                    <input type="text" name="transaction_reference" required placeholder="e.g. UTR-99881122" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showPayModal = false" class="px-3 py-2 text-xs font-semibold text-slate-500">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow">Confirm Paid</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

