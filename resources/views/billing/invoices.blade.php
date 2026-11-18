@extends('layouts.app')

@section('title', 'Invoicing & Billing')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Commercial Invoices & Billing</h1>
            <p class="text-sm text-slate-500">Generate periodic GST tax invoices for corporate clients and merchants</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('billing.expenses') }}" class="px-3.5 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 shadow-sm transition">
                Expenses
            </a>
            <a href="{{ route('billing.ledger') }}" class="px-3.5 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 shadow-sm transition">
                General Ledger & Chart of Accounts
            </a>
        </div>
    </div>

    <!-- Generate Invoice Card -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="font-bold text-slate-900 text-sm mb-3">Generate Tax Invoice for Client</h3>

        <form action="{{ route('billing.generate_invoice') }}" method="POST" class="flex flex-col sm:flex-row items-center gap-3">
            @csrf
            <div class="flex-1 w-full">
                <select name="customer_id" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Select Corporate / Merchant Account --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->company_name ?? $c->account_number }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-lg shadow transition">
                Create Invoice
            </button>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Invoice No</th>
                        <th class="px-6 py-3.5">Customer / Merchant</th>
                        <th class="px-6 py-3.5">Invoice Date</th>
                        <th class="px-6 py-3.5">Due Date</th>
                        <th class="px-6 py-3.5">Subtotal</th>
                        <th class="px-6 py-3.5">Tax (GST)</th>
                        <th class="px-6 py-3.5">Total Amount</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-6 py-4 font-mono font-bold text-blue-600 text-sm">
                            <a href="{{ route('billing.invoice_detail', $inv->id) }}" class="hover:underline">{{ $inv->invoice_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-800">
                            {{ $inv->customer->name }}
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500">
                            {{ $inv->invoice_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500">
                            {{ $inv->due_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-slate-800">
                            ₹{{ number_format($inv->subtotal, 2) }}
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-slate-500">
                            ₹{{ number_format($inv->tax_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 font-extrabold text-slate-900 text-sm">
                            ₹{{ number_format($inv->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase {{ $inv->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $inv->payment_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-xs">
                            <a href="{{ route('billing.invoice_detail', $inv->id) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">
                                View Invoice
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-10 text-center text-slate-400">No invoices generated yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

