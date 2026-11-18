<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\LedgerAccount;
use App\Services\AccountingService;
use App\Services\InvoiceGenerationService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(
        protected InvoiceGenerationService $invoiceService,
        protected AccountingService $accountingService
    ) {}

    public function invoices()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $invoices = Invoice::where('company_id', $companyId)
            ->with(['customer', 'items'])
            ->latest()
            ->paginate(15);

        $customers = Customer::where('company_id', $companyId)->get();

        return view('billing.invoices', compact('invoices', 'customers'));
    }

    public function generateInvoice(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $invoice = $this->invoiceService->generateForCustomer($customer);

        if (! $invoice) {
            return back()->with('error', "No unbilled shipments found for {$customer->name}.");
        }

        return back()->with('success', "Invoice {$invoice->invoice_number} generated for ₹{$invoice->total_amount}!");
    }

    public function showInvoice($id)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $invoice = Invoice::where('company_id', $companyId)
            ->with(['customer', 'items.shipment'])
            ->findOrFail($id);

        return view('billing.invoice_detail', compact('invoice'));
    }

    public function expenses()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $expenses = Expense::where('company_id', $companyId)
            ->with(['branch', 'approvedBy'])
            ->latest()
            ->paginate(15);

        $branches = Branch::where('company_id', $companyId)->get();
        $totalExpenses = Expense::where('company_id', $companyId)->sum('amount');

        return view('billing.expenses', compact('expenses', 'branches', 'totalExpenses'));
    }

    public function storeExpense(Request $request)
    {
        $companyId = auth()->user()->company_id ?? 1;

        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'required|string|max:255',
        ]);

        $validated['company_id'] = $companyId;
        $validated['approved_by_id'] = auth()->id();

        Expense::create($validated);

        return back()->with('success', "Expense of ₹{$validated['amount']} recorded successfully.");
    }

    public function ledger()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $accounts = LedgerAccount::where('company_id', $companyId)->get();
        if ($accounts->isEmpty()) {
            $this->accountingService->ensureChartOfAccounts($companyId);
            $accounts = LedgerAccount::where('company_id', $companyId)->get();
        }

        $recentJournalEntries = JournalEntry::where('company_id', $companyId)
            ->with(['lines.account'])
            ->latest()
            ->take(10)
            ->get();

        return view('billing.ledger', compact('accounts', 'recentJournalEntries'));
    }
}

