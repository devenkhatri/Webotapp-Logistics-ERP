<?php

namespace App\Http\Controllers;

use App\Services\ShipmentTrackingService;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function __construct(
        protected ShipmentTrackingService $trackingService
    ) {}

    public function track(Request $request, ?string $awb = null)
    {
        $searchAwb = $awb ?? $request->query('awb');
        $shipment = null;

        if ($searchAwb) {
            $shipment = $this->trackingService->track($searchAwb);
        }

        return view('tracking.public', [
            'searchAwb' => $searchAwb,
            'shipment' => $shipment,
        ]);
    }
}

