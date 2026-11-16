<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PickupRequest;
use App\Models\User;
use App\Services\PickupAssignmentService;
use Illuminate\Http\Request;

class PickupController extends Controller
{
    public function __construct(
        protected PickupAssignmentService $pickupService
    ) {}

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $pickups = PickupRequest::where('company_id', $companyId)
            ->with(['customer', 'assignedAgent'])
            ->latest()
            ->paginate(15);

        $agents = User::where('company_id', $companyId)
            ->whereIn('role', ['pickup_agent', 'delivery_agent', 'operations'])
            ->get();

        return view('pickups.index', compact('pickups', 'agents'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id ?? 1;
        $customers = Customer::where('company_id', $companyId)->get();

        return view('pickups.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:50',
            'pickup_address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'scheduled_date' => 'required|date',
            'time_slot' => 'required|string',
            'expected_packages' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $companyId;
        $pickup = $this->pickupService->createRequest($validated);

        return redirect()->route('pickups.index')->with('success', "Pickup request {$pickup->pickup_code} created successfully!");
    }

    public function assign(Request $request, $id)
    {
        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
        ]);

        $pickup = PickupRequest::findOrFail($id);
        $this->pickupService->assign($pickup, $validated['agent_id']);

        return back()->with('success', "Pickup request assigned to {$pickup->assignedAgent->name}.");
    }

    public function complete(Request $request, $id)
    {
        $pickup = PickupRequest::findOrFail($id);
        $this->pickupService->completePickup($pickup, [], auth()->id());

        return back()->with('success', "Pickup marked completed.");
    }
}

