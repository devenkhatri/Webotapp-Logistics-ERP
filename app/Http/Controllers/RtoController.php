<?php

namespace App\Http\Controllers;

use App\Models\RtoRecord;
use App\Services\RtoManagementService;
use Illuminate\Http\Request;

class RtoController extends Controller
{
    public function __construct(
        protected RtoManagementService $rtoService
    ) {}

    public function index()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $rtoRecords = RtoRecord::where('company_id', $companyId)
            ->with(['originalShipment' => fn ($q) => $q->with('customer')])
            ->latest()
            ->paginate(15);

        return view('rto.index', compact('rtoRecords'));
    }

    public function complete($id)
    {
        $rto = RtoRecord::findOrFail($id);
        $this->rtoService->completeRto($rto, auth()->id());

        return back()->with('success', "RTO {$rto->rto_awb} marked returned and delivered back to merchant!");
    }
}

