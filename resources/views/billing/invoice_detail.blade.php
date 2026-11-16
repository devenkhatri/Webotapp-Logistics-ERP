@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between no-print">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-mono font-bold text-slate-900">{{ $invoice->invoice_number }}</h1>
                <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase {{ $invoice->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $invoice->payment_status }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Tax Invoice & Freight Billing Statement</p>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-3.5 py-1.5 bg-slate-800 text-white font-semibold text-xs rounded-lg shadow">
                Print Invoice PDF
            </button>
            <a href="{{ route('billing.invoices') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium">← Back to Invoices</a>
        </div>
    </div>

    <!-- Printable Invoice -->
    <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm space-y-6">
        <div class="flex justify-between items-start border-b border-slate-200 pb-6">
            <div>
                <h2 class="text-2xl font-black text-slate-900">APEX LOGISTICS EXPRESS INC.</h2>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Official Tax Invoice</p>
                <div class="text-xs text-slate-600 mt-2">
                    100 Broadway, 12th Floor, New York, NY 10001<br>
                    Tax ID / EIN: US-EIN-987654321 • billing@apexlogistics.example
                </div>
            </div>

            <div class="text-right">
                <div class="font-mono font-extrabold text-base text-slate-800">{{ $invoice->invoice_number }}</div>
                <div class="text-xs text-slate-400 mt-1">Invoice Date: {{ $invoice->invoice_date->format('d M Y') }}</div>
                <div class="text-xs text-slate-500">Payment Due: {{ $invoice->due_date->format('d M Y') }}</div>
            </div>
        </div>

        <!-- Bill To -->
        <div class="p-4 bg-slate-50 rounded-lg text-xs">
            <span class="font-bold uppercase text-slate-400 block mb-1">Billed To (Client):</span>
            <div class="text-sm font-bold text-slate-900">{{ $invoice->customer->name }}</div>
            <div class="text-slate-600">{{ $invoice->customer->company_name ?? '' }}</div>
            <div class="text-slate-500">Account: {{ $invoice->customer->account_number }} • Email: {{ $invoice->customer->email }}</div>
            @if($invoice->customer->gst_number)
                <div class="text-slate-500 mt-1">Tax / GST Reg No: <strong>{{ $invoice->customer->gst_number }}</strong></div>
            @endif
        </div>

        <!-- Invoice Line Items -->
        <div>
            <table class="w-full text-left text-xs border border-slate-200">
                <thead class="bg-slate-100 border-b border-slate-200 font-bold text-slate-700">
                    <tr>
                        <th class="p-2.5">#</th>
                        <th class="p-2.5">Description & Service Particulars</th>
                        <th class="p-2.5 text-center">Qty</th>
                        <th class="p-2.5 text-right">Base Amount</th>
                        <th class="p-2.5 text-right">GST (18%)</th>
                        <th class="p-2.5 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoice->items as $idx => $item)
                    <tr>
                        <td class="p-2.5 font-bold text-slate-400">{{ $idx + 1 }}</td>
                        <td class="p-2.5 font-medium text-slate-800">{{ $item->description }}</td>
                        <td class="p-2.5 text-center">{{ $item->quantity }}</td>
                        <td class="p-2.5 text-right font-medium">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="p-2.5 text-right text-slate-500">${{ number_format($item->tax_amount, 2) }}</td>
                        <td class="p-2.5 text-right font-bold text-slate-900">${{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-400">No items on this invoice.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Summary Totals -->
        <div class="flex justify-end pt-2">
            <div class="w-72 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span>${{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Tax (GST 18%):</span>
                    <span>${{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-slate-900 border-t border-slate-200 pt-2 text-base">
                    <span>Total Due:</span>
                    <span class="text-blue-600">${{ number_format($invoice->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200 pt-4 text-[11px] text-slate-400 text-center">
            Payment Terms: Net 15 Days. Remit wire transfers to Apex Logistics Express Escrow A/C #99881122001.
        </div>
    </div>
</div>
@endsection
