<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label - {{ $shipment->awb_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }
        body {
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .label-container {
            width: 4in;
            max-width: 100%;
            min-height: 6in;
            background: #fff;
            border: 2px solid #000;
            padding: 12px;
            font-size: 11px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        @media screen and (max-width: 440px) {
            body {
                padding: 6px;
            }
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }
        .company-title {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .service-badge {
            font-size: 13px;
            font-weight: 800;
            border: 2px solid #000;
            padding: 2px 6px;
            text-transform: uppercase;
        }
        .route-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding: 8px 0;
        }
        .routing-code {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 1px;
        }
        .barcode-section {
            text-align: center;
            padding: 10px 0;
            border-bottom: 2px solid #000;
        }
        .barcode-img {
            max-width: 95%;
            height: 48px;
        }
        .awb-text {
            font-family: monospace;
            font-size: 16px;
            font-weight: 900;
            margin-top: 4px;
            letter-spacing: 2px;
        }
        .address-section {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 8px 0;
            border-bottom: 2px solid #000;
        }
        .address-box {
            font-size: 11px;
            line-height: 1.35;
        }
        .address-title {
            font-weight: 900;
            font-size: 10px;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .receiver-highlight {
            font-size: 13px;
            font-weight: 900;
        }
        .receiver-pin {
            font-size: 16px;
            font-weight: 900;
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 2px 6px;
            margin-top: 4px;
        }
        .footer-metrics {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid #000;
            font-size: 10px;
            font-weight: bold;
        }
        .cod-banner {
            background-color: #000;
            color: #fff;
            text-align: center;
            padding: 6px;
            font-size: 16px;
            font-weight: 900;
            margin-top: 6px;
        }
        .print-btn-bar {
            position: fixed;
            top: 20px;
            right: 20px;
        }
        .btn {
            background: #2563eb;
            color: #fff;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .label-container {
                border: 2px solid #000;
                width: 100%;
                min-height: 100vh;
            }
            .print-btn-bar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-btn-bar">
        <button class="btn" onclick="window.print()">Print Thermal Label</button>
    </div>

    <div class="label-container">
        <!-- Header -->
        <div class="header">
            <div class="company-title">Apex Logistics</div>
            <div class="service-badge">{{ $shipment->service_type }}</div>
        </div>

        <!-- Route & Routing Code -->
        <div class="route-box">
            <div>
                <div style="font-size: 9px; text-transform: uppercase;">Origin → Destination</div>
                <div class="routing-code">{{ $route_code }}</div>
            </div>
            <div style="text-align: right; font-size: 10px;">
                <div>Date: {{ $shipment->created_at->format('d/m/Y') }}</div>
                <div>Pieces: <strong>1 / {{ $shipment->pieces_count }}</strong></div>
            </div>
        </div>

        <!-- Barcode -->
        <div class="barcode-section">
            @if(isset($barcode))
                <img src="data:image/png;base64,{{ $barcode }}" class="barcode-img" alt="AWB Barcode">
            @endif
            <div class="awb-text">{{ $shipment->awb_number }}</div>
            @if($shipment->reference_number)
                <div style="font-size: 10px; font-weight: bold; margin-top: 2px;">Order Ref: {{ $shipment->reference_number }}</div>
            @endif
        </div>

        <!-- Addresses -->
        <div class="address-section">
            <div class="address-box">
                <div class="address-title">Ship To (Consignee):</div>
                <div class="receiver-highlight">{{ $shipment->receiver_name }}</div>
                <div>{{ $shipment->receiver_address }}</div>
                <div>{{ $shipment->receiver_city }} {{ $shipment->receiver_state ? ', ' . $shipment->receiver_state : '' }}</div>
                <div class="receiver-pin">PIN: {{ $shipment->receiver_postal_code }}</div>
                <div style="margin-top: 3px; font-weight: bold;">Tel: {{ $shipment->receiver_phone }}</div>
            </div>

            <div class="address-box" style="border-top: 1px dashed #666; padding-top: 6px;">
                <div class="address-title">Return Address (Shipper):</div>
                <div><strong>{{ $shipment->sender_name }}</strong></div>
                <div>{{ $shipment->sender_address }}, {{ $shipment->sender_city }} - {{ $shipment->sender_postal_code }}</div>
                <div>Tel: {{ $shipment->sender_phone }}</div>
            </div>
        </div>

        <!-- Metrics -->
        <div class="footer-metrics">
            <div>Weight: <strong>{{ $shipment->chargeable_weight }} kg</strong></div>
            <div>Mode: <strong>{{ strtoupper($shipment->payment_type) }}</strong></div>
            <div>Declared: <strong>₹{{ number_format($shipment->declared_value, 2) }}</strong></div>
        </div>

        <!-- COD or Verification Banner -->
        @if($shipment->isCod() && $shipment->cod_amount > 0)
        <div class="cod-banner">
            COLLECT CASH ON DELIVERY: ₹{{ number_format($shipment->cod_amount, 2) }}
        </div>
        @else
        <div style="text-align: center; padding: 4px; font-size: 10px; font-weight: bold; border-top: 1px solid #000;">
            PREPAID - NO PAYMENT TO BE COLLECTED
        </div>
        @endif

        <div style="font-size: 8px; text-align: center; margin-top: 4px; color: #444;">
            Track package live at: {{ url('/track/' . $shipment->awb_number) }}
        </div>
    </div>
</body>
</html>

