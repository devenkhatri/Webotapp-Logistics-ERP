@extends('layouts.app')

@section('title', 'Linehaul Manifests')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Linehaul Manifests & Dispatch</h1>
            <p class="text-sm text-slate-500">Inter-hub freight transfer manifests, vehicle transit, and arrival receiving</p>
        </div>

        <a href="{{ route('manifests.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Create Linehaul Manifest</span>
        </a>
    </div>

    <!-- Manifests Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Manifest No</th>
                        <th class="px-6 py-3.5">Route</th>
                        <th class="px-6 py-3.5">Fleet & Driver</th>
                        <th class="px-6 py-3.5">Total Parcels</th>
                        <th class="px-6 py-3.5">Total Weight</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($manifests as $m)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-6 py-4 font-mono font-bold text-blue-600 text-sm">
                            <a href="{{ route('manifests.show', $m->id) }}" class="hover:underline">{{ $m->manifest_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-xs font-medium text-slate-800">
                            {{ $m->sourceHub->name }} → {{ $m->destinationHub->name }}
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <div class="font-semibold text-slate-800">{{ $m->vehicle->vehicle_number ?? 'Third-Party Air Cargo' }}</div>
                            <div class="text-slate-400">{{ $m->driver->name ?? 'Commercial Carrier' }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-slate-800">
                            {{ $m->total_shipments }} pkgs
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-slate-800">
                            {{ $m->total_weight }} kg
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold capitalize {{ $m->status === 'received' ? 'bg-emerald-100 text-emerald-800' : ($m->status === 'dispatched' ? 'bg-cyan-100 text-cyan-800' : 'bg-slate-100 text-slate-700') }}">
                                {{ $m->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-xs space-x-1 whitespace-nowrap">
                            <a href="{{ route('manifests.show', $m->id) }}" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">View</a>

                            @if($m->status === 'draft')
                            <form action="{{ route('manifests.dispatch', $m->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white font-bold">
                                    Dispatch
                                </button>
                            </form>
                            @elseif($m->status === 'dispatched')
                            <form action="{{ route('manifests.receive', $m->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                                    Receive at Hub
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-400">No manifests generated yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

