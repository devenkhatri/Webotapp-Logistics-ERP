<?php

namespace App\Http\Controllers;

use App\Models\Hub;
use App\Models\HubScan;
use App\Models\Shipment;
use App\Models\ShipmentBag;
use App\Services\HubProcessingService;
use Illuminate\Http\Request;

class HubController extends Controller
{
    public function __construct(
        protected HubProcessingService $hubService
    ) {}

    public function index()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $hubs = Hub::where('company_id', $companyId)->withCount(['inboundManifests', 'outboundManifests'])->get();
        $recentScans = HubScan::with(['hub', 'shipment', 'scannedByUser'])->latest()->take(10)->get();
        $recentBags = ShipmentBag::where('company_id', $companyId)->with(['originHub', 'destinationHub', 'shipments'])->latest()->take(10)->get();

        return view('hubs.index', compact('hubs', 'recentScans', 'recentBags'));
    }

    public function inbound()
    {
        $companyId = auth()->user()->company_id ?? 1;
        $hubs = Hub::where('company_id', $companyId)->get();
        $recentScans = HubScan::where('scan_type', 'inbound')->with(['hub', 'shipment'])->latest()->take(15)->get();

        return view('hubs.inbound', compact('hubs', 'recentScans'));
    }

    public function processInbound(Request $request)
    {
        $validated = $request->validate([
            'hub_id' => 'required|exists:hubs,id',
            'awb_number' => 'required|string',
        ]);

        $hub = Hub::findOrFail($validated['hub_id']);

        try {
            $shipment = $this->hubService->inboundScan($hub, $validated['awb_number'], auth()->id());
            return back()->with('success', "AWB {$shipment->awb_number} scanned successfully into {$hub->name}!");
        } catch (\Throwable $e) {
            return back()->with('error', "Inbound scan failed: " . $e->getMessage());
        }
    }

    public function sorting()
    {
        $companyId = auth()->user()->company_id ?? 1;
        $hubs = Hub::where('company_id', $companyId)->get();

        // Shipments eligible for bagging/sorting
        $availableShipments = Shipment::where('company_id', $companyId)
            ->whereIn('status', ['received_at_hub', 'sorted', 'booked', 'picked_up'])
            ->get();

        $bags = ShipmentBag::where('company_id', $companyId)
            ->with(['originHub', 'destinationHub', 'shipments'])
            ->latest()
            ->paginate(15);

        return view('hubs.sorting', compact('hubs', 'availableShipments', 'bags'));
    }

    public function createBag(Request $request)
    {
        $validated = $request->validate([
            'origin_hub_id' => 'required|exists:hubs,id',
            'destination_hub_id' => 'required|exists:hubs,id|different:origin_hub_id',
            'seal_number' => 'nullable|string|max:50',
            'shipment_ids' => 'required|array|min:1',
            'shipment_ids.*' => 'exists:shipments,id',
        ]);

        $bag = $this->hubService->createBag(
            originHubId: $validated['origin_hub_id'],
            destHubId: $validated['destination_hub_id'],
            shipmentIds: $validated['shipment_ids'],
            sealNumber: $validated['seal_number'],
            userId: auth()->id()
        );

        return back()->with('success', "Bag {$bag->bag_number} sealed with " . count($validated['shipment_ids']) . " shipments!");
    }
}
