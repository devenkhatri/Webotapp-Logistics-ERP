@extends('layouts.app')

@section('title', 'Rider Mobile Delivery App')

@section('content')
<div class="max-w-xl mx-auto space-y-5" x-data="agentPortal()">
    <!-- Top Rider Header -->
    <div class="bg-slate-900 text-white p-5 rounded-2xl shadow-lg border border-slate-800 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-500 text-slate-900 font-extrabold flex items-center justify-center text-sm">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-base font-bold leading-tight">{{ $user->name }}</h2>
                    <span class="text-xs text-emerald-400 font-semibold flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        On Duty ({{ $activeRun->run_sheet_number ?? 'No Active DRS' }})
                    </span>
                </div>
            </div>

            <div class="text-right">
                <div class="text-[10px] uppercase font-bold text-slate-400">Cash in Hand</div>
                <div class="text-lg font-mono font-extrabold text-amber-400">
                    ${{ number_format($deliveredToday->where('payment_type', 'cod')->sum('cod_amount'), 2) }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-800 text-center text-xs">
            <div class="bg-slate-800/80 p-2 rounded-lg">
                <span class="text-slate-400 block text-[10px] uppercase">Pending Deliveries</span>
                <strong class="text-base font-extrabold text-orange-400">{{ $assignedShipments->count() }}</strong>
            </div>
            <div class="bg-slate-800/80 p-2 rounded-lg">
                <span class="text-slate-400 block text-[10px] uppercase">Completed Today</span>
                <strong class="text-base font-extrabold text-emerald-400">{{ $deliveredToday->count() }}</strong>
            </div>
        </div>
    </div>

    <!-- Active Delivery Stops List -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="font-bold text-slate-900 text-sm">Assigned Stops ({{ $assignedShipments->count() }})</h3>
            <span class="text-xs text-slate-400">Tap to deliver or fail</span>
        </div>

        @forelse($assignedShipments as $s)
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="font-mono font-extrabold text-blue-600 text-sm">{{ $s->awb_number }}</span>
                <span class="px-2 py-0.5 rounded text-xs font-extrabold uppercase {{ $s->payment_type === 'cod' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                    {{ $s->payment_type }} @if($s->isCod()) - ${{ number_format($s->cod_amount, 2) }} @endif
                </span>
            </div>

            <div>
                <h4 class="font-bold text-slate-900 text-sm">{{ $s->receiver_name }}</h4>
                <p class="text-xs text-slate-600 mt-0.5">{{ $s->receiver_address }}, {{ $s->receiver_city }}</p>
                @if($s->special_instructions)
                    <div class="text-[11px] text-amber-700 bg-amber-50 p-1.5 rounded mt-1.5">Note: {{ $s->special_instructions }}</div>
                @endif
            </div>

            <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100">
                <a href="tel:{{ $s->receiver_phone }}" class="inline-flex items-center gap-1.5 text-blue-600 font-bold hover:underline">
                    <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                    <span>Call {{ $s->receiver_phone }}</span>
                </a>
                <a href="https://maps.google.com/?q={{ urlencode($s->receiver_address . ' ' . $s->receiver_city) }}" target="_blank" class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-800">
                    <i data-lucide="navigation" class="w-3.5 h-3.5"></i>
                    <span>Navigate</span>
                </a>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-2 pt-2">
                <button type="button" @click="openDeliverModal({{ $s->id }}, '{{ $s->awb_number }}', '{{ $s->receiver_name }}', {{ $s->isCod() ? 1 : 0 }}, '{{ $s->cod_amount }}', '{{ $s->delivery_otp }}')" class="py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow transition flex items-center justify-center gap-1.5">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Deliver (e-POD)</span>
                </button>
                <button type="button" @click="openFailureModal({{ $s->id }}, '{{ $s->awb_number }}')" class="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center justify-center gap-1.5">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <span>Report Failed</span>
                </button>
            </div>
        </div>
        @empty
        <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center shadow-sm space-y-2">
            <i data-lucide="check-check" class="w-10 h-10 text-emerald-500 mx-auto"></i>
            <h4 class="font-bold text-slate-800 text-sm">All deliveries completed!</h4>
            <p class="text-xs text-slate-500">You have no remaining packages out for delivery on your run sheet.</p>
        </div>
        @endforelse
    </div>

    <!-- Modal: Mark Delivered -->
    <div x-show="showDeliverModal" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white max-w-md w-full rounded-2xl shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Confirm Delivery (e-POD)</h3>
                    <p class="text-xs text-slate-400 font-mono" x-text="activeShipment.awb"></p>
                </div>
                <button type="button" @click="showDeliverModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form action="{{ route('delivery.record_delivery') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="shipment_id" :value="activeShipment.id">

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Recipient Name *</label>
                    <input type="text" name="recipient_name" x-model="activeShipment.recipient" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Relationship to Consignee *</label>
                    <select name="recipient_relation" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                        <option value="Self">Self</option>
                        <option value="Family Member">Family Member</option>
                        <option value="Colleague / Reception">Colleague / Reception</option>
                        <option value="Security Guard">Security Guard</option>
                        <option value="Neighbor">Neighbor</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Delivery PIN / OTP</label>
                    <input type="text" name="otp_code" placeholder="6-digit PIN given to customer" class="w-full text-center tracking-widest font-mono text-lg font-bold bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500">
                    <div class="text-[10px] text-slate-400 mt-0.5">Demo OTP for this shipment is: <strong x-text="activeShipment.otp"></strong></div>
                </div>

                <template x-if="activeShipment.isCod">
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 space-y-2">
                        <div class="text-xs font-bold text-amber-900">Collect Cash on Delivery: $<span x-text="activeShipment.codAmount"></span></div>
                        <select name="payment_method" class="w-full text-xs bg-white border border-amber-300 rounded p-1.5">
                            <option value="cash">Collected Cash</option>
                            <option value="upi">Collected UPI / QR</option>
                        </select>
                    </div>
                </template>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow transition">
                        Complete Delivery & Save Proof
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Report Failure -->
    <div x-show="showFailureModal" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white max-w-md w-full rounded-2xl shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Record Delivery Exception</h3>
                    <p class="text-xs text-slate-400 font-mono" x-text="activeShipment.awb"></p>
                </div>
                <button type="button" @click="showFailureModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form action="{{ route('delivery.record_failure') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="shipment_id" :value="activeShipment.id">

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Reason for Non-Delivery *</label>
                    <select name="reason_code" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                        <option value="customer_unavailable">Customer Unavailable / Door Closed</option>
                        <option value="wrong_address">Incorrect Address / Landmark Missing</option>
                        <option value="refused_to_accept">Customer Refused Acceptance</option>
                        <option value="cash_not_ready">COD Cash Not Ready</option>
                        <option value="future_delivery_requested">Customer Requested Future Delivery</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Rider Remarks</label>
                    <textarea name="remarks" rows="2" placeholder="e.g. Called twice, phone unreachable..." class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm rounded-xl shadow transition">
                        Submit Exception & Reschedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function agentPortal() {
        return {
            showDeliverModal: false,
            showFailureModal: false,
            activeShipment: { id: null, awb: '', recipient: '', isCod: false, codAmount: '0.00', otp: '' },
            openDeliverModal(id, awb, recipient, isCod, codAmount, otp) {
                this.activeShipment = { id, awb, recipient, isCod: !!isCod, codAmount, otp };
                this.showDeliverModal = true;
            },
            openFailureModal(id, awb) {
                this.activeShipment = { id, awb };
                this.showFailureModal = true;
            }
        }
    }
</script>
@endpush
