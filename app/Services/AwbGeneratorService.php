<?php

namespace App\Services;

use App\Models\AwbSequence;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class AwbGeneratorService
{
    /**
     * Generate the next unique AWB number for a company.
     */
    public function generate(int $companyId, string $prefix = 'CX'): string
    {
        return DB::transaction(function () use ($companyId, $prefix) {
            $seq = AwbSequence::where('company_id', $companyId)->lockForUpdate()->first();

            if (! $seq) {
                $seq = AwbSequence::create([
                    'company_id' => $companyId,
                    'prefix' => $prefix,
                    'next_number' => 100001,
                ]);
            }

            $currentNum = $seq->next_number;
            $seq->next_number += 1;
            $seq->save();

            $awb = sprintf('%s%d', $seq->prefix, $currentNum);
            if ($seq->suffix) {
                $awb .= $seq->suffix;
            }

            return $awb;
        });
    }
}

