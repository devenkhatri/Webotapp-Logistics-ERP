@extends('layouts.app')

@section('title', 'Shipment ' . $shipment->awb_number)

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-mono font-extrabold tracking-tight text-slate-900">{{ $shipment->awb_number }}</h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $shipment->status_badge }}">
                    {{ ucwords(str_replace('_', ' ', $shipment->status)) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Booked on {{ $shipment->created_at->format('d M Y, h:i A') }} • Current Location: <strong class="text-slate-700">{{ $shipment->current_location ?? 'Origin Hub' }}</strong>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('shipments.label', $shipment->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Thermal Label (4x6)</span>
            </a>
            <a href="{{ route('shipments.invoice', $shipment->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-sm transition">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Waybill Invoice</span>
            </a>
            <a href="{{ route('tracking.public', $shipment->awb_number) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-semibold transition">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Track</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Details, Packages & Financials -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Addresses Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Shipper Card -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Shipper (Sender)</span>
                    <h4 class="font-bold text-slate-900 text-sm">{{ $shipment->sender_name }}</h4>
                    <p class="text-xs text-slate-600">{{ $shipment->sender_address }}</p>
                    <div class="text-xs text-slate-500 font-medium">
                        {{ $shipment->sender_city }} {{ $shipment->sender_state ? ', ' . $shipment->sender_state : '' }} - {{ $shipment->sender_postal_code }}
                    </div>
                    <div class="text-xs text-blue-600 font-mono pt-1">Phone: {{ $shipment->sender_phone }}</div>
                </div>

                <!-- Consignee Card -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Consignee (Receiver)</span>
                    <h4 class="font-bold text-slate-900 text-sm">{{ $shipment->receiver_name }}</h4>
                    <p class="text-xs text-slate-600">{{ $shipment->receiver_address }}</p>
                    <div class="text-xs text-slate-500 font-medium">
                        {{ $shipment->receiver_city }} {{ $shipment->receiver_state ? ', ' . $shipment->receiver_state : '' }} - {{ $shipment->receiver_postal_code }}
                    </div>
                    <div class="text-xs text-blue-600 font-mono pt-1">Phone: {{ $shipment->receiver_phone }}</div>
                </div>
            </div>

            <!-- Package Details Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 text-sm">Package & Metric Specifications</h3>
                    <span class="text-xs text-slate-500">Service: <strong class="text-slate-800 capitalize">{{ $shipment->service_type }}</strong></span>
                </div>

                <div class="p-5 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center border-b border-slate-100">
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <div class="text-[11px] text-slate-500 uppercase font-semibold">Pieces</div>
                        <div class="text-lg font-extrabold text-slate-800 mt-0.5">{{ $shipment->pieces_count }}</div>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <div class="text-[11px] text-slate-500 uppercase font-semibold">Actual Weight</div>
                        <div class="text-lg font-extrabold text-slate-800 mt-0.5">{{ $shipment->actual_weight }} kg</div>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <div class="text-[11px] text-slate-500 uppercase font-semibold">Volumetric</div>
                        <div class="text-lg font-extrabold text-slate-800 mt-0.5">{{ $shipment->volumetric_weight }} kg</div>
                    </div>
                    <div class="p-3 bg-blue-50 text-blue-900 rounded-lg border border-blue-100">
                        <div class="text-[11px] text-blue-700 uppercase font-bold">Chargeable</div>
                        <div class="text-lg font-extrabold text-blue-700 mt-0.5">{{ $shipment->chargeable_weight }} kg</div>
                    </div>
                </div>

                @if($shipment->packages->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-2.5">Package Barcode</th>
                                <th class="px-5 py-2.5">Weight</th>
                                <th class="px-5 py-2.5">Dimensions (L×W×H)</th>
                                <th class="px-5 py-2.5">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($shipment->packages as $pkg)
                            <tr>
                                <td class="px-5 py-2.5 font-mono font-bold text-slate-800">{{ $pkg->package_barcode }}</td>
                                <td class="px-5 py-2.5">{{ $pkg->weight }} kg</td>
                                <td class="px-5 py-2.5">{{ $pkg->length }} × {{ $pkg->width }} × {{ $pkg->height }} cm</td>
                                <td class="px-5 py-2.5 text-slate-500">{{ $pkg->description }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <!-- Financials Breakdown -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-900 text-sm">Financial Charges & Tariff</h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="text-slate-500 block">Payment Type</span>
                        <strong class="text-slate-900 uppercase font-bold text-sm">{{ $shipment->payment_type }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Declared Value</span>
                        <strong class="text-slate-900 text-sm">${{ number_format($shipment->declared_value, 2) }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">COD Collectable</span>
                        <strong class="text-amber-600 font-bold text-sm">${{ number_format($shipment->cod_amount, 2) }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Total Freight Amount</span>
                        <strong class="text-blue-600 font-extrabold text-sm">${{ number_format($shipment->total_freight, 2) }}</strong>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-500">
                    <div>Base: ${{ number_format($shipment->base_freight, 2) }} | Fuel: ${{ number_format($shipment->fuel_surcharge, 2) }} | COD Fee: ${{ number_format($shipment->cod_fee, 2) }} | Tax: ${{ number_format($shipment->gst_tax, 2) }}</div>
                    <div>Status: <span class="capitalize font-bold text-slate-700">{{ $shipment->payment_status }}</span></div>
                </div>
            </div>

            <!-- Delivery Proof / Verification (if delivered) -->
            @if($shipment->isDelivered())
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 text-emerald-950 space-y-3">
                <div class="flex items-center gap-2 text-emerald-700 font-bold text-sm">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                    <span>Electronic Proof of Delivery (e-POD)</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <span class="text-emerald-700 block">Received By:</span>
                        <strong class="text-emerald-900">{{ $shipment->recipient_name }} ({{ $shipment->recipient_relation }})</strong>
                    </div>
                    <div>
                        <span class="text-emerald-700 block">Delivery Timestamp:</span>
                        <strong class="text-emerald-900">{{ $shipment->delivered_at?->format('d M Y, h:i A') }}</strong>
                    </div>
                    <div>
                        <span class="text-emerald-700 block">OTP Verification:</span>
                        <strong class="text-emerald-900">Verified ({{ $shipment->delivery_otp }})</strong>
                    </div>
                </div>
                @if($shipment->pod_signature)
                <div class="pt-2">
                    <span class="text-emerald-700 text-xs block mb-1">Signature:</span>
                    <div class="bg-white p-2 rounded border border-emerald-200 inline-block">
                        {!! $shipment->pod_signature !!}
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Failed Attempts / RTO (if any) -->
            @if($shipment->attempt_count > 0)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-amber-900 space-y-2">
                <div class="flex items-center gap-2 font-bold text-sm text-amber-800">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    <span>Delivery Attempts ({{ $shipment->attempt_count }})</span>
                </div>
                @foreach($shipment->deliveryAttempts as $attempt)
                <div class="text-xs border-t border-amber-200/60 pt-2 flex items-center justify-between">
                    <span>Attempt #{{ $attempt->attempt_no }}: {{ ucwords(str_replace('_', ' ', $attempt->reason_code)) }} - {{ $attempt->remarks }}</span>
                    <span class="text-amber-700">{{ $attempt->attempted_at->format('M d, h:i A') }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Right Col: Milestone Timeline & Status Override -->
        <div class="space-y-6">
            <!-- Manual Status Update Form -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <h3 class="font-bold text-slate-900 text-sm">Operational Status Action</h3>

                <form action="{{ route('shipments.update_status', $shipment->id) }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Transition Status</label>
                        <select name="status" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-blue-500">
                            <option value="booked" {{ $shipment->status == 'booked' ? 'selected' : '' }}>Booked</option>
                            <option value="picked_up" {{ $shipment->status == 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                            <option value="received_at_hub" {{ $shipment->status == 'received_at_hub' ? 'selected' : '' }}>Received at Hub</option>
                            <option value="sorted" {{ $shipment->status == 'sorted' ? 'selected' : '' }}>Sorted</option>
                            <option value="in_transit" {{ $shipment->status == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                            <option value="out_for_delivery" {{ $shipment->status == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                            <option value="delivered" {{ $shipment->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="failed_attempt" {{ $shipment->status == 'failed_attempt' ? 'selected' : '' }}>Failed Attempt</option>
                            <option value="cancelled" {{ $shipment->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Current Location (Optional)</label>
                        <input type="text" name="location" placeholder="e.g. JFK Hub, NYC" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Remarks / Note</label>
                        <input type="text" name="description" placeholder="Operational note..." class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-blue-500">
                    </div>

                    <button type="submit" class="w-full py-2 px-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition">
                        Update Status
                    </button>
                </form>
            </div>

            <!-- Milestone Events Timeline -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-900 text-sm">Tracking Milestones</h3>

                <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @forelse($shipment->trackingEvents as $idx => $event)
                    <div class="relative">
                        <!-- Bullet -->
                        <div class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full {{ $idx === 0 ? 'bg-blue-600 ring-4 ring-blue-100' : 'bg-slate-400' }}"></div>

                        <div class="text-xs">
                            <span class="font-bold text-slate-900 block">{{ $event->status_title }}</span>
                            <span class="text-slate-500 block text-[11px]">{{ $event->location }}</span>
                            @if($event->description)
                                <p class="text-slate-600 mt-0.5 text-[11px]">{{ $event->description }}</p>
                            @endif
                            <div class="text-[10px] text-slate-400 mt-1 font-mono">
                                {{ $event->event_time->format('d M Y, h:i A') }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400">No events recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
