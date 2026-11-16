@extends('layouts.app')

@section('title', 'Schedule Pickup Request')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Schedule Pickup Request</h1>
            <p class="text-sm text-slate-500">Request field pickup agent visit to customer / merchant warehouse</p>
        </div>
        <a href="{{ route('pickups.index') }}" class="text-sm text-slate-500 hover:text-slate-800 font-medium">← Back to Pickups</a>
    </div>

    <form action="{{ route('pickups.store') }}" method="POST" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Customer / Merchant (Optional)</label>
                <select name="customer_id" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                    <option value="">-- Non-Account Retail Pickup --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->account_number }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Contact Name *</label>
                <input type="text" name="contact_name" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Contact Phone *</label>
                <input type="text" name="contact_phone" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Expected Package Count *</label>
                <input type="number" name="expected_packages" value="1" min="1" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pickup Address *</label>
                <textarea name="pickup_address" rows="2" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">City *</label>
                <input type="text" name="city" value="New York" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Postal Code (PIN) *</label>
                <input type="text" name="postal_code" value="10001" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Scheduled Date *</label>
                <input type="date" name="scheduled_date" value="{{ date('Y-m-d') }}" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Preferred Time Slot *</label>
                <select name="time_slot" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                    <option value="morning">Morning (09:00 AM - 01:00 PM)</option>
                    <option value="afternoon">Afternoon (01:00 PM - 05:00 PM)</option>
                    <option value="evening">Evening (05:00 PM - 08:00 PM)</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pickup Notes / Gate Instructions</label>
                <input type="text" name="notes" placeholder="e.g. Loading dock #3, contact security on arrival" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-lg shadow transition">
                Confirm Pickup Request
            </button>
        </div>
    </form>
</div>
@endsection
