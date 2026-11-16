@extends('layouts.app')

@section('title', 'Book New Shipment')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="shipmentBooking()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Consignment Booking</h1>
            <p class="text-sm text-slate-500">Generate AWB, compute volumetric freight, and book pickup/dispatch</p>
        </div>
        <a href="{{ route('shipments.index') }}" class="text-sm text-slate-500 hover:text-slate-800 font-medium">← Back to Shipments</a>
    </div>

    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-sm space-y-1">
        <div class="font-bold">Please correct the following errors:</div>
        <ul class="list-disc list-inside text-xs">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('shipments.store') }}" method="POST" id="bookingForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Form Sections -->
            <div class="lg:col-span-2 space-y-6">
                <!-- 1. Booking Context -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">1</span>
                        Booking Context & Account
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Booking Branch</label>
                            <select name="branch_id" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Customer / Merchant</label>
                            <select name="customer_id" x-model="customerId" @change="onCustomerChange()" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Retail Walk-in Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-phone="{{ $c->phone }}" data-email="{{ $c->email }}">{{ $c->name }} ({{ $c->account_number }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Merchant Order Ref (Optional)</label>
                            <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="e.g. ORD-98124" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- 2. Sender & Receiver Addresses -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Sender Details -->
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">2</span>
                            Shipper (Sender) Details
                        </h3>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Sender Name *</label>
                            <input type="text" name="sender_name" x-model="senderName" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Sender Phone *</label>
                            <input type="text" name="sender_phone" x-model="senderPhone" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Sender Address *</label>
                            <textarea name="sender_address" x-model="senderAddress" rows="2" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">City *</label>
                                <input type="text" name="sender_city" x-model="senderCity" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Postal Code (PIN) *</label>
                                <input type="text" name="sender_postal_code" x-model="senderPin" @input.debounce.500ms="recalculateRate()" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Receiver Details -->
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">3</span>
                            Consignee (Receiver) Details
                        </h3>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Receiver Name *</label>
                            <input type="text" name="receiver_name" value="{{ old('receiver_name') }}" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Receiver Phone *</label>
                            <input type="text" name="receiver_phone" value="{{ old('receiver_phone') }}" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Receiver Full Address *</label>
                            <textarea name="receiver_address" rows="2" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">{{ old('receiver_address') }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">City *</label>
                                <input type="text" name="receiver_city" value="{{ old('receiver_city', 'Chicago') }}" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Destination PIN *</label>
                                <input type="text" name="receiver_postal_code" x-model="receiverPin" @input.debounce.500ms="recalculateRate()" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Package & Dimensions -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">4</span>
                        Package Dimensions & Weight
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Item Description *</label>
                            <input type="text" name="content_description" value="{{ old('content_description', 'Commercial Goods') }}" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Declared Value ($)</label>
                            <input type="number" step="0.01" name="declared_value" x-model="declaredValue" @input.debounce.500ms="recalculateRate()" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Actual Weight (kg) *</label>
                            <input type="number" step="0.01" name="actual_weight" x-model="actualWeight" @input="updateWeights()" required class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 font-bold focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Dimensions -->
                    <div class="p-3.5 bg-slate-50 rounded-lg border border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-3 items-center">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500">Length (cm)</label>
                            <input type="number" step="0.1" name="length" x-model="length" @input="updateWeights()" class="w-full text-sm bg-white border border-slate-200 rounded p-1.5 focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500">Width (cm)</label>
                            <input type="number" step="0.1" name="width" x-model="width" @input="updateWeights()" class="w-full text-sm bg-white border border-slate-200 rounded p-1.5 focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500">Height (cm)</label>
                            <input type="number" step="0.1" name="height" x-model="height" @input="updateWeights()" class="w-full text-sm bg-white border border-slate-200 rounded p-1.5 focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] text-slate-400">Volumetric: <span x-text="volumetricWeight + ' kg'"></span></div>
                            <div class="text-xs font-bold text-blue-700">Billable: <span x-text="chargeableWeight + ' kg'"></span></div>
                        </div>
                    </div>
                </div>

                <!-- 4. Service & Payment Method -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">5</span>
                        Service Tier & Payment Type
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Service Type *</label>
                            <select name="service_type" x-model="serviceType" @change="recalculateRate()" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                                <option value="standard">Standard Ground</option>
                                <option value="express">Express Priority (Air)</option>
                                <option value="same_day">Same Day Delivery</option>
                                <option value="heavy">Heavy Cargo / Freight</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Payment Type *</label>
                            <select name="payment_type" x-model="paymentType" @change="recalculateRate()" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                                <option value="prepaid">Prepaid</option>
                                <option value="cod">Cash on Delivery (COD)</option>
                                <option value="corporate_credit">Corporate Credit Account</option>
                                <option value="to_pay">To-Pay (Receiver Pays)</option>
                            </select>
                        </div>

                        <div x-show="paymentType === 'cod'" x-cloak>
                            <label class="block text-xs font-semibold text-amber-700 mb-1">COD Amount to Collect ($) *</label>
                            <input type="number" step="0.01" name="cod_amount" x-model="codAmount" @input.debounce.500ms="recalculateRate()" class="w-full text-sm bg-amber-50 border border-amber-300 rounded-lg p-2 font-bold text-amber-900 focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Col: Live Quotation Summary -->
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm sticky top-6 space-y-5">
                    <h3 class="font-bold text-slate-900 text-base pb-3 border-b border-slate-100 flex items-center justify-between">
                        <span>Rate Quotation</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-700 font-semibold" x-text="quote.zone || 'CALCULATING'"></span>
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Base Freight:</span>
                            <span class="font-medium text-slate-900" x-text="'$' + (quote.base_freight || '0.00')"></span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Fuel Surcharge:</span>
                            <span class="font-medium text-slate-900" x-text="'$' + (quote.fuel_surcharge || '0.00')"></span>
                        </div>
                        <div class="flex justify-between text-slate-600" x-show="quote.cod_fee > 0" x-cloak>
                            <span>COD Handling Fee:</span>
                            <span class="font-medium text-slate-900" x-text="'$' + (quote.cod_fee || '0.00')"></span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>GST / Tax (18%):</span>
                            <span class="font-medium text-slate-900" x-text="'$' + (quote.gst_tax || '0.00')"></span>
                        </div>
                        <div class="pt-3 border-t border-slate-200 flex justify-between items-baseline">
                            <span class="font-bold text-slate-900">Total Freight:</span>
                            <span class="text-2xl font-extrabold text-blue-600" x-text="'$' + (quote.total_freight || '0.00')"></span>
                        </div>
                    </div>

                    <div class="text-[11px] text-slate-400 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                        Calculated based on <strong x-text="chargeableWeight + ' kg'"></strong> billable weight via tariff engine.
                    </div>

                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md transition flex items-center justify-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Confirm & Generate AWB</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function shipmentBooking() {
        return {
            customerId: '',
            senderName: 'Apex Central Hub',
            senderPhone: '+1 (212) 555-0101',
            senderAddress: '100 Broadway, 12th Fl',
            senderCity: 'New York',
            senderPin: '10001',
            receiverPin: '60601',
            actualWeight: 1.0,
            length: 20,
            width: 15,
            height: 10,
            volumetricWeight: 0.6,
            chargeableWeight: 1.0,
            declaredValue: 50.0,
            serviceType: 'standard',
            paymentType: 'prepaid',
            codAmount: 0.0,
            quote: {
                zone: 'LOCAL',
                base_freight: '10.00',
                fuel_surcharge: '0.50',
                cod_fee: '0.00',
                gst_tax: '1.89',
                total_freight: '12.39'
            },
            init() {
                this.updateWeights();
                this.recalculateRate();
            },
            onCustomerChange() {
                const select = document.querySelector('select[name="customer_id"]');
                const opt = select.options[select.selectedIndex];
                if (opt && opt.value) {
                    this.senderName = opt.dataset.name || this.senderName;
                    this.senderPhone = opt.dataset.phone || this.senderPhone;
                }
                this.recalculateRate();
            },
            updateWeights() {
                const l = parseFloat(this.length) || 10;
                const w = parseFloat(this.width) || 10;
                const h = parseFloat(this.height) || 10;
                const act = parseFloat(this.actualWeight) || 0.5;

                const vol = (l * w * h) / 5000;
                this.volumetricWeight = Math.round(vol * 100) / 100;
                this.chargeableWeight = Math.max(act, this.volumetricWeight);
                this.recalculateRate();
            },
            recalculateRate() {
                if (!this.senderPin || !this.receiverPin) return;

                fetch("{{ route('shipments.calculate_rate') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        origin_postal_code: this.senderPin,
                        destination_postal_code: this.receiverPin,
                        chargeable_weight: this.chargeableWeight,
                        service_type: this.serviceType,
                        payment_type: this.paymentType,
                        cod_amount: this.paymentType === 'cod' ? this.codAmount : 0,
                        declared_value: this.declaredValue,
                        customer_id: this.customerId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.quote = data;
                })
                .catch(err => console.error('Quote error:', err));
            }
        }
    }
</script>
@endpush
