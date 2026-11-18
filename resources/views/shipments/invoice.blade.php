<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consignment Waybill - {{ $shipment->awb_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: #fff; }
        }
    </style>
</head>
<body class="bg-slate-100 p-3 sm:p-8 text-slate-800 text-sm">
    <div class="max-w-4xl mx-auto bg-white p-4 sm:p-8 rounded-xl border border-slate-300 shadow-sm space-y-6">
        <!-- Print Button -->
        <div class="flex justify-end no-print">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs shadow">
                Print Waybill
            </button>
        </div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 border-b border-slate-200 pb-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-blue-900">WEBOTAPP LOGISTICS EXPRESS INDIA</h1>
                <p class="text-xs text-slate-500">Commercial Consignment Note & Tax Waybill</p>
                <div class="text-xs text-slate-600 mt-2">
                    Head Office: Plot No. C-59, G Block, Bandra Kurla Complex (BKC), Mumbai, Maharashtra 400051<br>
                    GSTIN: 27AAACW1234F1Z5 • Support: +91 22 2650 1000
                </div>
            </div>

            <div class="sm:text-right">
                <div class="text-lg font-mono font-extrabold text-slate-900">{{ $shipment->awb_number }}</div>
                <div class="text-xs text-slate-500 mt-1">Date: {{ $shipment->created_at->format('d M Y, h:i A') }}</div>
                <div class="mt-2 inline-block px-2.5 py-1 bg-slate-100 rounded text-xs font-bold uppercase">
                    Service: {{ $shipment->service_type }}
                </div>
            </div>
        </div>

        <!-- Shipper & Consignee -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 border-b border-slate-200 pb-6">
            <div class="p-4 bg-slate-50 rounded-lg">
                <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-2">Shipper Details</h3>
                <div class="font-bold text-slate-900">{{ $shipment->sender_name }}</div>
                <div class="text-xs text-slate-600 mt-1">{{ $shipment->sender_address }}</div>
                <div class="text-xs text-slate-700 font-medium">{{ $shipment->sender_city }} - {{ $shipment->sender_postal_code }}</div>
                <div class="text-xs text-slate-500 mt-2">Phone: {{ $shipment->sender_phone }}</div>
            </div>

            <div class="p-4 bg-slate-50 rounded-lg">
                <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-2">Consignee Details</h3>
                <div class="font-bold text-slate-900">{{ $shipment->receiver_name }}</div>
                <div class="text-xs text-slate-600 mt-1">{{ $shipment->receiver_address }}</div>
                <div class="text-xs text-slate-700 font-medium">{{ $shipment->receiver_city }} - {{ $shipment->receiver_postal_code }}</div>
                <div class="text-xs text-slate-500 mt-2">Phone: {{ $shipment->receiver_phone }}</div>
            </div>
        </div>

        <!-- Item Details -->
        <div>
            <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-3">Freight & Package Particulars</h3>
            <table class="w-full text-left text-xs border border-slate-200">
                <thead class="bg-slate-100 border-b border-slate-200 font-bold text-slate-700">
                    <tr>
                        <th class="p-2.5">Item Description</th>
                        <th class="p-2.5 text-center">Pieces</th>
                        <th class="p-2.5 text-right">Actual Wt</th>
                        <th class="p-2.5 text-right">Volumetric</th>
                        <th class="p-2.5 text-right">Billable Wt</th>
                        <th class="p-2.5 text-right">Freight Charges</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="p-2.5">{{ $shipment->content_description }}</td>
                        <td class="p-2.5 text-center">{{ $shipment->pieces_count }}</td>
                        <td class="p-2.5 text-right">{{ $shipment->actual_weight }} kg</td>
                        <td class="p-2.5 text-right">{{ $shipment->volumetric_weight }} kg</td>
                        <td class="p-2.5 text-right font-bold">{{ $shipment->chargeable_weight }} kg</td>
                        <td class="p-2.5 text-right font-bold">₹{{ number_format($shipment->base_freight, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Financial Breakdown -->
        <div class="flex justify-end pt-2">
            <div class="w-72 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Base Freight:</span>
                    <span>₹{{ number_format($shipment->base_freight, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Fuel Surcharge:</span>
                    <span>₹{{ number_format($shipment->fuel_surcharge, 2) }}</span>
                </div>
                @if($shipment->cod_fee > 0)
                <div class="flex justify-between text-slate-600">
                    <span>COD Fee:</span>
                    <span>₹{{ number_format($shipment->cod_fee, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-slate-600">
                    <span>Tax (GST 18%):</span>
                    <span>₹{{ number_format($shipment->gst_tax, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-slate-900 border-t border-slate-200 pt-2 text-sm">
                    <span>Grand Total:</span>
                    <span>₹{{ number_format($shipment->total_freight, 2) }}</span>
                </div>
                @if($shipment->isCod())
                <div class="p-2 bg-amber-50 border border-amber-200 rounded text-amber-900 font-bold text-center mt-2">
                    COD to Collect: ₹{{ number_format($shipment->cod_amount, 2) }}
                </div>
                @endif
            </div>
        </div>

        <!-- Signatures Footer -->
        <div class="grid grid-cols-2 gap-12 pt-12 text-xs text-slate-500">
            <div class="border-t border-slate-300 pt-2 text-center">
                Shipper's Signature
            </div>
            <div class="border-t border-slate-300 pt-2 text-center">
                Authorized Courier Signature & Stamp
            </div>
        </div>
    </div>
</body>
</html>

