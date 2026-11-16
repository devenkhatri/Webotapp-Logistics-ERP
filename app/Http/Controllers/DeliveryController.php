<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DeliveryRun;
use App\Models\Shipment;
use App\Models\User;
use App\Services\DeliveryAssignmentService;
use App\Services\DeliveryProofService;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(
        protected DeliveryAssignmentService $assignmentService,
        protected DeliveryProofService $proofService
    ) {}

    public function index()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $runs = DeliveryRun::where('company_id', $companyId)
            ->with(['deliveryAgent', 'branch', 'shipments'])
            ->latest()
            ->paginate(15);

        return view('delivery.index', compact('runs'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $agents = User::where('company_id', $companyId)
            ->whereIn('role', ['delivery_agent', 'operations'])
            ->get();

        $branches = Branch::where('company_id', $companyId)->get();

        $availableShipments = Shipment::where('company_id', $companyId)
            ->whereIn('status', ['received_at_hub', 'sorted', 'booked', 'picked_up'])
            ->get();

        return view('delivery.create', compact('agents', 'branches', 'availableShipments'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $validated = $request->validate([
            'delivery_agent_id' => 'required|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            'shipment_ids' => 'required|array|min:1',
            'shipment_ids.*' => 'exists:shipments,id',
            'notes' => 'nullable|string',
        ]);

        $run = $this->assignmentService->createRunSheet(
            companyId: $companyId,
            agentId: $validated['delivery_agent_id'],
            shipmentIds: $validated['shipment_ids'],
            branchId: $validated['branch_id'] ?? null,
            notes: $validated['notes'] ?? null
        );

        return redirect()->route('delivery.show', $run->id)
            ->with('success', "Delivery Run Sheet {$run->run_sheet_number} generated!");
    }

    public function show($id)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $run = DeliveryRun::where('company_id', $companyId)
            ->with(['deliveryAgent', 'branch', 'shipments'])
            ->findOrFail($id);

        return view('delivery.show', compact('run'));
    }

    /**
     * Mobile portal for delivery agents.
     */
    public function agentPortal(Request $request)
    {
        $user = auth()->user();

        // Active run for this agent
        $activeRun = DeliveryRun::where('delivery_agent_id', $user->id)
            ->whereIn('status', ['created', 'out_for_delivery'])
            ->with('shipments')
            ->latest()
            ->first();

        // Assigned shipments that are out for delivery
        $assignedShipments = Shipment::where('delivery_agent_id', $user->id)
            ->where('status', 'out_for_delivery')
            ->get();

        $deliveredToday = Shipment::where('delivery_agent_id', $user->id)
            ->where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->get();

        return view('delivery.agent_portal', compact('user', 'activeRun', 'assignedShipments', 'deliveredToday'));
    }

    public function recordDelivery(Request $request)
    {
        $validated = $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'recipient_name' => 'required|string|max:255',
            'recipient_relation' => 'required|string|max:50',
            'otp_code' => 'nullable|string|max:6',
            'signature_data' => 'nullable|string',
            'payment_method' => 'nullable|string|in:cash,upi,card',
        ]);

        $shipment = Shipment::findOrFail($validated['shipment_id']);

        try {
            $proof = $this->proofService->deliver(
                shipment: $shipment,
                recipientName: $validated['recipient_name'],
                recipientRelation: $validated['recipient_relation'],
                enteredOtp: $validated['otp_code'] ?? null,
                signatureData: $validated['signature_data'] ?? null,
                agentId: auth()->id(),
                codPaymentMethod: $validated['payment_method'] ?? 'cash'
            );

            return back()->with('success', "Delivery completed for AWB {$shipment->awb_number}!");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function recordFailure(Request $request)
    {
        $validated = $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'reason_code' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $shipment = Shipment::findOrFail($validated['shipment_id']);

        $this->proofService->recordFailure(
            shipment: $shipment,
            reasonCode: $validated['reason_code'],
            remarks: $validated['remarks'] ?? null,
            agentId: auth()->id()
        );

        return back()->with('warning', "Failed delivery recorded for AWB {$shipment->awb_number}.");
    }
}
