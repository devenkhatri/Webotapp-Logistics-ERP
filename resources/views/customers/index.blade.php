@extends('layouts.app')

@section('title', 'Customers & Merchants')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Customers & Merchant Accounts</h1>
            <p class="text-sm text-slate-500">Corporate shippers, e-commerce stores, retail customers, and API credentials</p>
        </div>

        <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Add Customer Account</span>
        </a>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Account No</th>
                        <th class="px-6 py-3.5">Customer / Company</th>
                        <th class="px-6 py-3.5">Category</th>
                        <th class="px-6 py-3.5">Contact Details</th>
                        <th class="px-6 py-3.5">Rate Card</th>
                        <th class="px-6 py-3.5">Credit Limit</th>
                        <th class="px-6 py-3.5">Total Shipments</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $c)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-6 py-4 font-mono font-bold text-blue-600 text-xs">
                            <a href="{{ route('customers.show', $c->id) }}" class="hover:underline">{{ $c->account_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-800">
                            <a href="{{ route('customers.show', $c->id) }}" class="hover:text-blue-600">{{ $c->name }}</a>
                            @if($c->company_name)
                                <div class="text-[11px] text-slate-400 font-normal">{{ $c->company_name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold capitalize {{ $c->customer_type === 'merchant' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $c->customer_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <div class="text-slate-800">{{ $c->phone }}</div>
                            <div class="text-slate-400">{{ $c->email }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs font-medium text-slate-700">
                            {{ $c->rateCard->name ?? 'Default Tariff' }}
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-slate-800">
                            ₹{{ number_format($c->credit_limit, 2) }}
                        </td>
                        <td class="px-6 py-4 text-xs font-extrabold text-blue-600">
                            {{ $c->shipments_count }} pkgs
                        </td>
                        <td class="px-6 py-4 text-right text-xs">
                            <a href="{{ route('customers.show', $c->id) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">
                                Profile
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-400">No customers registered yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

