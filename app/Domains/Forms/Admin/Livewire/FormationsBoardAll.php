<?php

namespace App\Domains\Forms\Admin\Livewire;

/**
 * All-statuses formations admin board (/admin/formations/board/all).
 * Identical to FormationsBoard except all 7 columns render — including
 * AwaitingClient (parked on customer) and Approved (terminal).
 *
 * Shares the same Blade view as FormationsBoard via the showAll flag.
 */
class FormationsBoardAll extends FormationsBoard
{
    protected function showAll(): bool
    {
        return true;
    }
}
