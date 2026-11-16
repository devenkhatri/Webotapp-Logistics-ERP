<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Hub;
use App\Models\PostalCode;
use App\Models\Shipment;
use App\Services\RateCalculationService;
use App\Services\ShipmentBookingService;
use App\Services\ShipmentTrackingService;
use App\Services\ShippingLabelService;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function __construct(
        protected ShipmentBookingService $bookingService,
        protected ShipmentTrackingService $trackingService,
        protected RateCalculationService $rateService,
        protected ShippingLabelService $labelService
    ) {}

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $query = Shipment::where('company_id', $companyId)
            ->with(['customer', 'branch', 'originHub', 'destinationHub']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('awb_number', 'like', "%{$s}%")
                    ->orWhere('reference_number', 'like', "%{$s}%")
                    ->orWhere('sender_name', 'like', "%{$s}%")
                    ->orWhere('receiver_name', 'like', "%{$s}%")
                    ->orWhere('receiver_phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $shipments = $query->latest()->paginate(15)->withQueryString();
        $branches = Branch::where('company_id', $companyId)->get();

        return view('shipments.index', compact('shipments', 'branches'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $branches = Branch::where('company_id', $companyId)->get();
        $hubs = Hub::where('company_id', $companyId)->get();
        $customers = Customer::where('company_id', $companyId)->where('status', 'active')->get();
        $postalCodes = PostalCode::all();

        return view('shipments.create', compact('branches', 'hubs', 'customers', 'postalCodes'));
    }

    public function calculateRate(Request $request)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $validated = $request->validate([
            'origin_postal_code' => 'required',
            'destination_postal_code' => 'required',
            'chargeable_weight' => 'required|numeric|min:0.1',
            'service_type' => 'nullable|string',
            'payment_type' => 'nullable|string',
            'cod_amount' => 'nullable|numeric',
            'declared_value' => 'nullable|numeric',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $customer = ! empty($validated['customer_id']) ? Customer::find($validated['customer_id']) : null;

        $quote = $this->rateService->calculate(
            companyId: $companyId,
            originPin: $validated['origin_postal_code'],
            destinationPin: $validated['destination_postal_code'],
            chargeableWeight: (float) $validated['chargeable_weight'],
            serviceType: $validated['service_type'] ?? 'standard',
            paymentType: $validated['payment_type'] ?? 'prepaid',
            codAmount: (float) ($validated['cod_amount'] ?? 0),
            declaredValue: (float) ($validated['declared_value'] ?? 0),
            customer: $customer
        );

        return response()->json($quote);
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'customer_id' => 'nullable|exists:customers,id',
            'reference_number' => 'nullable|string|max:100',
            'service_type' => 'required|in:standard,express,same_day,heavy',
            'payment_type' => 'required|in:prepaid,cod,to_pay,corporate_credit',
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:50',
            'sender_email' => 'nullable|email|max:255',
            'sender_address' => 'required|string',
            'sender_city' => 'required|string|max:100',
            'sender_state' => 'nullable|string|max:100',
            'sender_postal_code' => 'required|string|max:20',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:50',
            'receiver_email' => 'nullable|email|max:255',
            'receiver_address' => 'required|string',
            'receiver_city' => 'required|string|max:100',
            'receiver_state' => 'nullable|string|max:100',
            'receiver_postal_code' => 'required|string|max:20',
            'content_description' => 'required|string|max:255',
            'declared_value' => 'nullable|numeric|min:0',
            'cod_amount' => 'nullable|numeric|min:0',
            'actual_weight' => 'required|numeric|min:0.05',
            'length' => 'nullable|numeric|min:1',
            'width' => 'nullable|numeric|min:1',
            'height' => 'nullable|numeric|min:1',
            'special_instructions' => 'nullable|string',
        ]);

        $validated['company_id'] = $companyId;

        $shipment = $this->bookingService->book($validated, auth()->id());

        return redirect()->route('shipments.show', $shipment->id)
            ->with('success', "Shipment booked successfully! AWB: {$shipment->awb_number}");
    }

    public function show($id)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $shipment = Shipment::where('company_id', $companyId)
            ->with([
                'packages',
                'trackingEvents' => fn ($q) => $q->orderBy('id', 'desc'),
                'customer',
                'branch',
                'originHub',
                'destinationHub',
                'currentHub',
                'deliveryAgent',
                'pickupAgent',
                'deliveryProof',
                'deliveryAttempts',
                'codCollection',
            ])
            ->findOrFail($id);

        $labelData = $this->labelService->generateLabelData($shipment);

        return view('shipments.show', compact('shipment', 'labelData'));
    }

    public function label($id)
    {
        $companyId = auth()->user()->company_id ?? 1;
        $shipment = Shipment::where('company_id', $companyId)->with(['company', 'packages'])->findOrFail($id);

        $data = $this->labelService->generateLabelData($shipment);

        return view('labels.thermal', $data);
    }

    public function invoice($id)
    {
        $companyId = auth()->user()->company_id ?? 1;
        $shipment = Shipment::where('company_id', $companyId)->with(['company', 'customer', 'packages'])->findOrFail($id);

        return view('shipments.invoice', compact('shipment'));
    }

    public function updateStatus(Request $request, $id)
    {
        $companyId = auth()->user()->company_id ?? 1;
        $shipment = Shipment::where('company_id', $companyId)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $statusTitles = [
            'booked' => 'Shipment Booked',
            'pickup_scheduled' => 'Pickup Scheduled',
            'picked_up' => 'Picked Up',
            'received_at_hub' => 'Received at Hub',
            'sorted' => 'Sorted for Destination',
            'in_transit' => 'In Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'failed_attempt' => 'Delivery Failed',
            'cancelled' => 'Cancelled',
        ];

        $title = $statusTitles[$validated['status']] ?? ucwords(str_replace('_', ' ', $validated['status']));

        $this->trackingService->updateStatus(
            shipment: $shipment,
            newStatus: $validated['status'],
            statusTitle: $title,
            location: $validated['location'],
            description: $validated['description'],
            userId: auth()->id()
        );

        return back()->with('success', "Shipment status updated to {$title}.");
    }
}
