<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Hub;
use App\Models\Manifest;
use App\Models\Shipment;
use App\Models\Vehicle;
use App\Services\ManifestService;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    public function __construct(
        protected ManifestService $manifestService
    ) {}

    public function index()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $manifests = Manifest::where('company_id', $companyId)
            ->with(['sourceHub', 'destinationHub', 'vehicle', 'driver'])
            ->latest()
            ->paginate(15);

        return view('manifests.index', compact('manifests'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $hubs = Hub::where('company_id', $companyId)->get();
        $vehicles = Vehicle::where('company_id', $companyId)->get();
        $drivers = Driver::where('company_id', $companyId)->get();
        $availableShipments = Shipment::where('company_id', $companyId)
            ->whereIn('status', ['received_at_hub', 'sorted', 'bagged', 'booked'])
            ->get();

        return view('manifests.create', compact('hubs', 'vehicles', 'drivers', 'availableShipments'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $validated = $request->validate([
            'source_hub_id' => 'required|exists:hubs,id',
            'destination_hub_id' => 'required|exists:hubs,id|different:source_hub_id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'shipment_ids' => 'required|array|min:1',
            'shipment_ids.*' => 'exists:shipments,id',
            'remarks' => 'nullable|string',
        ]);

        $manifest = $this->manifestService->createManifest(
            companyId: $companyId,
            sourceHubId: $validated['source_hub_id'],
            destinationHubId: $validated['destination_hub_id'],
            vehicleId: $validated['vehicle_id'] ?? null,
            driverId: $validated['driver_id'] ?? null,
            shipmentIds: $validated['shipment_ids'],
            remarks: $validated['remarks'] ?? null
        );

        return redirect()->route('manifests.show', $manifest->id)
            ->with('success', "Manifest {$manifest->manifest_number} generated successfully!");
    }

    public function show($id)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $manifest = Manifest::where('company_id', $companyId)
            ->with(['sourceHub', 'destinationHub', 'vehicle', 'driver', 'shipments'])
            ->findOrFail($id);

        return view('manifests.show', compact('manifest'));
    }

    public function dispatchManifest($id)
    {
        $manifest = Manifest::findOrFail($id);
        $this->manifestService->dispatch($manifest, auth()->id());

        return back()->with('success', "Manifest {$manifest->manifest_number} dispatched for transit!");
    }

    public function receiveManifest($id)
    {
        $manifest = Manifest::findOrFail($id);
        $this->manifestService->receive($manifest, auth()->id());

        return back()->with('success', "Manifest {$manifest->manifest_number} received at destination hub!");
    }
}

