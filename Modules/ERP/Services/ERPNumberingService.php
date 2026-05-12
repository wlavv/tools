<?php

namespace Modules\ERP\Services;

use Illuminate\Support\Facades\DB;
use Modules\ERP\Models\ERPNumberingSequence;

class ERPNumberingService
{
    public function next(string $documentTypeCode, ?int $year = null): string
    {
        $year = $year ?: (int) now()->year;

        return DB::transaction(function () use ($documentTypeCode, $year) {
            $sequence = ERPNumberingSequence::query()
                ->where('document_type_code', $documentTypeCode)
                ->where('year', $year)
                ->lockForUpdate()
                ->firstOrFail();

            $sequence->current_number++;
            $sequence->save();

            $number = str_pad((string) $sequence->current_number, $sequence->padding, '0', STR_PAD_LEFT);

            return str_replace(
                ['{Y}', '{00000}'],
                [(string) $year, $number],
                $sequence->pattern
            );
        });
    }
}
