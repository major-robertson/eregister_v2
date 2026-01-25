<?php

namespace App\Domains\Lien\Admin\Actions;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Support\Facades\DB;

class ChangeFilingStatus
{
    /**
     * Execute the status change action.
     *
     * @throws \App\Domains\Lien\Exceptions\InvalidStatusTransitionException
     */
    public function execute(LienFiling $filing, FilingStatus $newStatus, ?string $note = null): void
    {
        DB::transaction(function () use ($filing, $newStatus, $note) {
            $meta = [];

            if ($note) {
                $meta['note'] = $note;
            }

            // Uses existing transitionTo() which:
            // 1. Validates transition is allowed (throws InvalidStatusTransitionException if not)
            // 2. Updates status + relevant timestamps
            // 3. Creates LienFilingEvent with from/to/meta
            $filing->transitionTo($newStatus, $meta);
        });
    }
}
