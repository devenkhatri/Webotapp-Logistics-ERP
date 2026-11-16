<?php

namespace App\Services;

use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LedgerAccount;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Ensure default standard chart of accounts exists for company.
     */
    public function ensureChartOfAccounts(int $companyId): array
    {
        $defaultAccounts = [
            ['code' => '1010', 'name' => 'Cash in Hand', 'type' => 'asset'],
            ['code' => '1020', 'name' => 'Bank Account', 'type' => 'asset'],
            ['code' => '1030', 'name' => 'Accounts Receivable', 'type' => 'asset'],
            ['code' => '2010', 'name' => 'COD Escrow Liability', 'type' => 'liability'],
            ['code' => '4010', 'name' => 'Freight Revenue', 'type' => 'revenue'],
            ['code' => '5010', 'name' => 'Operational Expenses', 'type' => 'expense'],
            ['code' => '5020', 'name' => 'Fuel Expenses', 'type' => 'expense'],
        ];

        $accounts = [];
        foreach ($defaultAccounts as $acc) {
            $accounts[$acc['code']] = LedgerAccount::firstOrCreate(
                ['company_id' => $companyId, 'account_code' => $acc['code']],
                ['account_name' => $acc['name'], 'account_type' => $acc['type'], 'current_balance' => 0.00]
            );
        }

        return $accounts;
    }

    /**
     * Post a double-entry balanced journal entry.
     *
     * @param array<array{account_id: int, debit: float, credit: float}> $lines
     */
    public function postJournalEntry(
        int $companyId,
        string $description,
        array $lines,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): JournalEntry {
        return DB::transaction(function () use ($companyId, $description, $lines, $referenceType, $referenceId) {
            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($lines as $line) {
                $totalDebit += (float) ($line['debit'] ?? 0);
                $totalCredit += (float) ($line['credit'] ?? 0);
            }

            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \InvalidArgumentException("Journal entry is not balanced. Debits: {$totalDebit}, Credits: {$totalCredit}");
            }

            $entryNumber = 'JE-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $entry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $entryNumber,
                'entry_date' => now()->toDateString(),
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            foreach ($lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'ledger_account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0.00,
                    'credit' => $line['credit'] ?? 0.00,
                ]);

                // Update account running balance
                $account = LedgerAccount::find($line['account_id']);
                if ($account) {
                    $effect = ($line['debit'] ?? 0) - ($line['credit'] ?? 0);
                    if (in_array($account->account_type, ['liability', 'revenue', 'equity'])) {
                        $effect = ($line['credit'] ?? 0) - ($line['debit'] ?? 0);
                    }
                    $account->current_balance += $effect;
                    $account->save();
                }
            }

            return $entry;
        });
    }
}

