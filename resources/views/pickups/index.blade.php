@extends('layouts.app')

@section('title', 'Pickup Operations')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pickup Operations</h1>
            <p class="text-sm text-slate-500">Manage first-mile pickup requests, assign field agents, and track execution</p>
        </div>

        <a href="{{ route('pickups.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Schedule Pickup Request</span>
        </a>
    </div>

    <!-- Pickups Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Pickup Code</th>
                        <th class="px-6 py-3.5">Customer / Merchant</th>
                        <th class="px-6 py-3.5">Scheduled Date & Slot</th>
                        <th class="px-6 py-3.5">Address</th>
                        <th class="px-6 py-3.5">Pkgs</th>
                        <th class="px-6 py-3.5">Assigned Agent</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pickups as $p)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-6 py-4 font-mono font-bold text-blue-600 text-sm">
                            {{ $p->pickup_code }}
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <div class="font-bold text-slate-800">{{ $p->contact_name }}</div>
                            <div class="text-slate-400">{{ $p->contact_phone }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-700">
                            <strong>{{ \Carbon\Carbon::parse($p->scheduled_date)->format('M d, Y') }}</strong>
                            <div class="text-[11px] text-slate-400 capitalize">{{ $p->time_slot }} slot</div>
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <div class="truncate max-w-[200px]">{{ $p->pickup_address }}</div>
                            <div class="text-slate-500 font-medium">{{ $p->city }} - {{ $p->postal_code }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-slate-800">
                            {{ $p->expected_packages }} pcs
                        </td>
                        <td class="px-6 py-4 text-xs">
                            @if($p->assignedAgent)
                                <span class="font-semibold text-slate-800">{{ $p->assignedAgent->name }}</span>
                            @else
                                <form action="{{ route('pickups.assign', $p->id) }}" method="POST" class="flex items-center gap-1">
                                    @csrf
                                    <select name="agent_id" required class="text-xs p-1 bg-slate-50 border border-slate-200 rounded">
                                        <option value="">Assign...</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-2 py-1 bg-blue-600 text-white rounded text-xs">Save</button>
                                </form>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold capitalize {{ $p->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($p->status === 'assigned' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-xs">
                            @if($p->status !== 'completed')
                            <form action="{{ route('pickups.complete', $p->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs transition">
                                    Mark Picked
                                </button>
                            </form>
                            @else
                            <span class="text-slate-400 text-xs">Completed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-400">No pickup requests registered.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
