@extends('layouts.app')

@section('title', 'General Ledger & Chart of Accounts')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">General Ledger & Double-Entry Accounting</h1>
            <p class="text-sm text-slate-500">Corporate balance sheet accounts, freight revenue recognition, and escrow audit</p>
        </div>

        <a href="{{ route('billing.invoices') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium">← Back to Invoices</a>
    </div>

    <!-- Chart of Accounts Grid -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
            <h3 class="font-bold text-slate-900 text-sm">Chart of Accounts (COA) Summary</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 uppercase font-semibold text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Account Code</th>
                        <th class="px-6 py-3">Account Name</th>
                        <th class="px-6 py-3">Classification</th>
                        <th class="px-6 py-3 text-right">Current Ledger Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($accounts as $acc)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-6 py-3 font-mono font-bold text-blue-600">{{ $acc->account_code }}</td>
                        <td class="px-6 py-3 font-semibold text-slate-800">{{ $acc->account_name }}</td>
                        <td class="px-6 py-3 uppercase text-[10px] font-bold text-slate-500">{{ $acc->account_type }}</td>
                        <td class="px-6 py-3 text-right font-bold text-sm text-slate-900">
                            ${{ number_format($acc->current_balance, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-6 text-center text-slate-400">No ledger accounts registered.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Journal Entries Ledger -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">Recent Journal Vouchers (Audit Trail)</h3>
            <span class="text-xs text-slate-400">Strictly balanced debits and credits</span>
        </div>

        <div class="divide-y divide-slate-100 text-xs">
            @forelse($recentJournalEntries as $je)
            <div class="p-5 space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-mono font-bold text-blue-600">{{ $je->entry_number }}</span>
                        <span class="text-slate-400 ml-2">{{ $je->entry_date->format('d M Y') }}</span>
                    </div>
                    <span class="font-medium text-slate-700">{{ $je->description }}</span>
                </div>

                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 space-y-1">
                    @foreach($je->lines as $line)
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="font-mono {{ $line->debit > 0 ? 'text-slate-800' : 'pl-4 text-slate-600' }}">
                            {{ $line->account->account_name ?? 'Account' }} ({{ $line->account->account_code ?? '' }})
                        </span>
                        <div>
                            @if($line->debit > 0)
                                <span class="font-bold text-slate-900">Dr: ${{ number_format($line->debit, 2) }}</span>
                            @else
                                <span class="font-bold text-slate-600">Cr: ${{ number_format($line->credit, 2) }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-400">
                No posted journal vouchers yet. Automatic entries post upon freight billing and COD settlements.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
