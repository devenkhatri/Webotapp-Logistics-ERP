@extends('layouts.app')

@section('title', 'Financial & Accounting Control')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Finance & Accounts Control Center</h1>
            <p class="text-sm text-slate-500">Corporate receivables, merchant COD escrow reconciliation, operational expenses & ledger</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('billing.invoices') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                <span>Generate Invoice</span>
            </a>
            <a href="{{ route('cod.settlements') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-sm transition">
                <i data-lucide="calculator" class="w-4 h-4"></i>
                <span>COD Settlements</span>
            </a>
            <a href="{{ route('billing.ledger') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-sm transition">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                <span>General Ledger</span>
            </a>
        </div>
    </div>

    <!-- Financial KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Billed Invoices</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">${{ number_format($totalInvoiced, 2) }}</div>
            <div class="text-xs text-emerald-600 font-medium mt-1">Paid: ${{ number_format($paidInvoices, 2) }}</div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Outstanding Receivables</span>
            <div class="text-2xl font-extrabold text-rose-600 mt-1">${{ number_format($unpaidInvoices, 2) }}</div>
            <span class="text-xs text-slate-500">Net 15-day terms due</span>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">COD Cash In Escrow</span>
            <div class="text-2xl font-extrabold text-amber-600 mt-1">${{ number_format($totalCodCollected, 2) }}</div>
            <span class="text-xs text-amber-600 font-medium">Un-deposited: ${{ number_format($pendingCodDeposit, 2) }}</span>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Pending Merchant Payouts</span>
            <div class="text-2xl font-extrabold text-blue-600 mt-1">${{ number_format($pendingCodSettlements, 2) }}</div>
            <span class="text-xs text-slate-500">Expenses: ${{ number_format($totalExpenses, 2) }}</span>
        </div>
    </div>

    <!-- Financial Ledger & Invoices Preview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Invoices -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-sm">Recent Commercial Invoices</h3>
                <a href="{{ route('billing.invoices') }}" class="text-xs text-blue-600 hover:underline font-semibold">All Invoices →</a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentInvoices as $inv)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <a href="{{ route('billing.invoice_detail', $inv->id) }}" class="font-mono font-bold text-blue-600 hover:underline">{{ $inv->invoice_number }}</a>
                        <div class="text-slate-500 mt-0.5">{{ $inv->customer->name }}</div>
                    </div>
                    <div class="text-right">
                        <span class="font-bold text-slate-900">${{ number_format($inv->total_amount, 2) }}</span>
                        <div class="mt-0.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $inv->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $inv->payment_status }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">No invoices generated yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Expenses -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-sm">Recent Operational Expenses</h3>
                <a href="{{ route('billing.expenses') }}" class="text-xs text-blue-600 hover:underline font-semibold">All Expenses →</a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentExpenses as $exp)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <span class="font-semibold text-slate-800 capitalize">{{ $exp->category }}</span>
                        <div class="text-slate-400 mt-0.5 truncate max-w-xs">{{ $exp->description }}</div>
                    </div>
                    <div class="text-right">
                        <span class="font-bold text-rose-600">-${{ number_format($exp->amount, 2) }}</span>
                        <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $exp->expense_date->format('d M') }}</div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">No expenses recorded.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
