@extends('layouts.app')

@section('title', 'Branch & Fleet Expenses')

@section('content')
<div class="space-y-6" x-data="{ showExpenseModal: false }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Branch & Fleet Expenses</h1>
            <p class="text-sm text-slate-500">Record fuel disbursements, vehicle maintenance, and facility overheads</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="showExpenseModal = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Record Operational Expense</span>
            </button>
            <a href="{{ route('billing.ledger') }}" class="px-3.5 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold shadow-sm">
                General Ledger
            </a>
        </div>
    </div>

    <!-- Summary Total Card -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm max-w-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Recorded Expenses</span>
        <h3 class="text-2xl font-extrabold text-rose-600 mt-1">${{ number_format($totalExpenses, 2) }}</h3>
        <span class="text-xs text-slate-500">Operational overheads</span>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Date</th>
                        <th class="px-6 py-3.5">Category</th>
                        <th class="px-6 py-3.5">Branch</th>
                        <th class="px-6 py-3.5">Description</th>
                        <th class="px-6 py-3.5">Amount</th>
                        <th class="px-6 py-3.5 text-right">Recorded By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $exp)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-6 py-4 text-xs font-mono text-slate-500">
                            {{ $exp->expense_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold capitalize bg-slate-100 text-slate-700">
                                {{ $exp->category }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-800">
                            {{ $exp->branch->name ?? 'Head Office' }}
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-600">
                            {{ $exp->description }}
                        </td>
                        <td class="px-6 py-4 font-bold text-rose-600 text-sm">
                            ${{ number_format($exp->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right text-xs text-slate-400">
                            {{ $exp->approvedBy->name ?? 'Staff' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-400">No operational expenses recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Record Expense -->
    <div x-show="showExpenseModal" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white max-w-md w-full rounded-2xl shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base">Record Operational Expense</h3>
                <button type="button" @click="showExpenseModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form action="{{ route('billing.store_expense') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Expense Category *</label>
                    <select name="category" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                        <option value="fuel">Vehicle Fleet Fuel</option>
                        <option value="maintenance">Fleet Servicing & Repairs</option>
                        <option value="packaging">Packaging Boxes & Polybags</option>
                        <option value="rent">Facility / Hub Rent</option>
                        <option value="utilities">Electricity & Internet</option>
                        <option value="toll">Highway Tolls & Parking</option>
                        <option value="salary">Daily Driver Allowances</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Branch Facility</label>
                    <select name="branch_id" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Amount ($) *</label>
                    <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5 font-bold">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Date *</label>
                    <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Description *</label>
                    <input type="text" name="description" required placeholder="e.g. Diesel for Van NY-VAN-101 (50 Liters)" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow transition">
                        Post Expense & Debit Accounts
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
