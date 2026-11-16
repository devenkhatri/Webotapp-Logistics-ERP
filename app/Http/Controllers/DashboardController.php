<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CodCollection;
use App\Models\CodSettlement;
use App\Models\Expense;
use App\Models\Hub;
use App\Models\HubScan;
use App\Models\Invoice;
use App\Models\Manifest;
use App\Models\PickupRequest;
use App\Models\Shipment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Delivery Agents go straight to their mobile app portal
        if ($user && $user->isDeliveryAgent()) {
            return redirect()->route('agent.portal');
        }

        // 2. Merchants & Retail Customers go straight to their client portal
        if ($user && $user->isCustomer()) {
            return redirect()->route('merchant.portal');
        }

        $companyId = $user->company_id ?? 1;

        // Base query for shipments
        $shipmentsQuery = Shipment::where('company_id', $companyId);
        if ($request->filled('branch_id')) {
            $shipmentsQuery->where('branch_id', $request->branch_id);
        }

        // 3. Finance & Accounts Role Dashboard
        if ($user && $user->role === 'accountant') {
            $totalInvoiced = Invoice::where('company_id', $companyId)->sum('total_amount');
            $paidInvoices = Invoice::where('company_id', $companyId)->where('payment_status', 'paid')->sum('total_amount');
            $unpaidInvoices = Invoice::where('company_id', $companyId)->where('payment_status', 'unpaid')->sum('total_amount');
            $totalExpenses = Expense::where('company_id', $companyId)->sum('amount');
            $totalCodCollected = CodCollection::where('company_id', $companyId)->sum('amount');
            $pendingCodDeposit = CodCollection::where('company_id', $companyId)->where('is_deposited_to_branch', false)->sum('amount');
            $pendingCodSettlements = CodSettlement::where('company_id', $companyId)->where('status', 'pending')->sum('net_payable');
            
            $recentInvoices = Invoice::where('company_id', $companyId)->with('customer')->latest()->take(6)->get();
            $recentExpenses = Expense::where('company_id', $companyId)->latest()->take(6)->get();

            return view('dashboard.finance', compact(
                'totalInvoiced',
                'paidInvoices',
                'unpaidInvoices',
                'totalExpenses',
                'totalCodCollected',
                'pendingCodDeposit',
                'pendingCodSettlements',
                'recentInvoices',
                'recentExpenses'
            ));
        }

        // 4. Operations & Hub Manager Dashboard
        if ($user && in_array($user->role, ['operations', 'hub_manager', 'branch_manager'])) {
            $pendingPickups = PickupRequest::where('company_id', $companyId)->where('status', 'pending')->count();
            $atHubCount = (clone $shipmentsQuery)->whereIn('status', ['received_at_hub', 'sorted', 'bagged'])->count();
            $inTransitCount = (clone $shipmentsQuery)->where('status', 'in_transit')->count();
            $outForDeliveryCount = (clone $shipmentsQuery)->where('status', 'out_for_delivery')->count();
            $deliveredToday = (clone $shipmentsQuery)->where('status', 'delivered')->whereDate('delivered_at', today())->count();
            $failedAttempts = (clone $shipmentsQuery)->where('status', 'failed_attempt')->count();

            $activeManifests = Manifest::where('company_id', $companyId)->whereIn('status', ['draft', 'dispatched'])->with(['sourceHub', 'destinationHub'])->latest()->take(5)->get();
            $recentInboundScans = HubScan::with(['hub', 'shipment'])->latest()->take(6)->get();
            $recentShipments = (clone $shipmentsQuery)->latest()->take(8)->get();

            return view('dashboard.operations', compact(
                'pendingPickups',
                'atHubCount',
                'inTransitCount',
                'outForDeliveryCount',
                'deliveredToday',
                'failedAttempts',
                'activeManifests',
                'recentInboundScans',
                'recentShipments'
            ));
        }

        // 5. System Administrator Master Dashboard (Default)
        $totalShipments = (clone $shipmentsQuery)->count();
        $bookedCount = (clone $shipmentsQuery)->where('status', 'booked')->count();
        $pickedUpCount = (clone $shipmentsQuery)->where('status', 'picked_up')->count();
        $inTransitCount = (clone $shipmentsQuery)->whereIn('status', ['in_transit', 'received_at_hub', 'sorted', 'bagged', 'dispatched'])->count();
        $outForDeliveryCount = (clone $shipmentsQuery)->where('status', 'out_for_delivery')->count();
        $deliveredCount = (clone $shipmentsQuery)->where('status', 'delivered')->count();
        $failedCount = (clone $shipmentsQuery)->where('status', 'failed_attempt')->count();
        $rtoCount = (clone $shipmentsQuery)->whereIn('status', ['rto_initiated', 'rto_in_transit', 'rto_delivered'])->count();

        $totalRevenue = (clone $shipmentsQuery)->sum('total_freight');
        $totalCodCollected = CodCollection::where('company_id', $companyId)->sum('amount');
        $pendingBranchDeposit = CodCollection::where('company_id', $companyId)->where('is_deposited_to_branch', false)->sum('amount');

        $closedDeliveries = $deliveredCount + $failedCount + $rtoCount;
        $deliverySuccessRate = $closedDeliveries > 0 ? round(($deliveredCount / $closedDeliveries) * 100, 1) : 100.0;

        $recentShipments = (clone $shipmentsQuery)->with(['customer', 'branch', 'originHub', 'destinationHub'])
            ->latest()
            ->take(8)
            ->get();

        $branches = Branch::where('company_id', $companyId)->get();
        $hubs = Hub::where('company_id', $companyId)->get();

        $statusDistribution = [
            'Booked' => $bookedCount,
            'Picked Up' => $pickedUpCount,
            'In Transit' => $inTransitCount,
            'Out for Delivery' => $outForDeliveryCount,
            'Delivered' => $deliveredCount,
            'Exceptions / RTO' => $failedCount + $rtoCount,
        ];

        return view('dashboard.index', compact(
            'totalShipments',
            'bookedCount',
            'pickedUpCount',
            'inTransitCount',
            'outForDeliveryCount',
            'deliveredCount',
            'failedCount',
            'rtoCount',
            'totalRevenue',
            'totalCodCollected',
            'pendingBranchDeposit',
            'deliverySuccessRate',
            'recentShipments',
            'branches',
            'hubs',
            'statusDistribution'
        ));
    }
}
