<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CodCollection;
use App\Models\Hub;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id ?? 1;

        // Base query for shipments
        $shipmentsQuery = Shipment::where('company_id', $companyId);

        // Branch filter if selected
        if ($request->filled('branch_id')) {
            $shipmentsQuery->where('branch_id', $request->branch_id);
        }

        // Real counts and sums
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

        // Success rate
        $closedDeliveries = $deliveredCount + $failedCount + $rtoCount;
        $deliverySuccessRate = $closedDeliveries > 0 ? round(($deliveredCount / $closedDeliveries) * 100, 1) : 100.0;

        // Recent Shipments
        $recentShipments = (clone $shipmentsQuery)->with(['customer', 'branch', 'originHub', 'destinationHub'])
            ->latest()
            ->take(8)
            ->get();

        // Branches for filter dropdown
        $branches = Branch::where('company_id', $companyId)->get();
        $hubs = Hub::where('company_id', $companyId)->get();

        // Status Distribution data for Chart.js
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
