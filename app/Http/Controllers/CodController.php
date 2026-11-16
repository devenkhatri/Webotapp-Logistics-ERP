<?php

namespace App\Http\Controllers;

use App\Models\CodCollection;
use App\Models\CodSettlement;
use App\Models\Customer;
use App\Services\CodCollectionService;
use App\Services\CodSettlementService;
use Illuminate\Http\Request;

class CodController extends Controller
{
    public function __construct(
        protected CodCollectionService $collectionService,
        protected CodSettlementService $settlementService
    ) {}

    public function collections()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $collections = CodCollection::where('company_id', $companyId)
            ->with(['shipment', 'agent', 'verifiedBy'])
            ->latest()
            ->paginate(15);

        $totalCollected = CodCollection::where('company_id', $companyId)->sum('amount');
        $totalDeposited = CodCollection::where('company_id', $companyId)->where('is_deposited_to_branch', true)->sum('amount');
        $pendingDeposit = CodCollection::where('company_id', $companyId)->where('is_deposited_to_branch', false)->sum('amount');

        return view('cod.collections', compact('collections', 'totalCollected', 'totalDeposited', 'pendingDeposit'));
    }

    public function depositToBranch($id)
    {
        $collection = CodCollection::findOrFail($id);
        $this->collectionService->depositToBranch($collection, auth()->id());

        return back()->with('success', "COD cash verified and deposited to branch account.");
    }

    public function settlements()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $settlements = CodSettlement::where('company_id', $companyId)
            ->with(['customer', 'items'])
            ->latest()
            ->paginate(15);

        $merchants = Customer::where('company_id', $companyId)->where('customer_type', 'merchant')->get();

        return view('cod.settlements', compact('settlements', 'merchants'));
    }

    public function generateSettlement(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $settlement = $this->settlementService->generateSettlement($customer, auth()->id());

        if (! $settlement) {
            return back()->with('error', "No un-settled delivered COD shipments found for {$customer->name}.");
        }

        return back()->with('success', "COD Settlement {$settlement->settlement_number} generated for \${$settlement->net_payable}!");
    }

    public function markPaid(Request $request, $id)
    {
        $validated = $request->validate([
            'transaction_reference' => 'required|string|max:100',
        ]);

        $settlement = CodSettlement::findOrFail($id);
        $this->settlementService->markPaid($settlement, $validated['transaction_reference'], auth()->id());

        return back()->with('success', "Settlement marked as PAID. Reference: {$validated['transaction_reference']}");
    }
}
