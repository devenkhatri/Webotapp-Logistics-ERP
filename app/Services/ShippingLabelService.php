<?php

namespace App\Services;

use App\Models\Shipment;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

class ShippingLabelService
{
    /**
     * Generate HTML or PDF shipping label.
     */
    public function generateLabelData(Shipment $shipment): array
    {
        $generator = new BarcodeGeneratorPNG();
        $barcodeBase64 = base64_encode($generator->getBarcode($shipment->awb_number, $generator::TYPE_CODE_128, 2, 50));

        return [
            'shipment' => $shipment,
            'barcode' => $barcodeBase64,
            'company' => $shipment->company,
            'route_code' => sprintf('%s-%s', strtoupper(substr($shipment->sender_city, 0, 3)), strtoupper(substr($shipment->receiver_city, 0, 3))),
        ];
    }

    /**
     * Render PDF thermal label.
     */
    public function generatePdf(Shipment $shipment)
    {
        $data = $this->generateLabelData($shipment);
        $pdf = Pdf::loadView('labels.thermal', $data);
        $pdf->setPaper([0, 0, 288, 432], 'portrait'); // 4x6 inches in points (72dpi * 4 = 288, * 6 = 432)

        return $pdf;
    }
}
