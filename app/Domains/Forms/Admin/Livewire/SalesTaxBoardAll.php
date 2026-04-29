<?php

namespace App\Domains\Forms\Admin\Livewire;

/**
 * All-statuses sales tax admin board (/admin/sales-tax/board/all).
 * Identical to SalesTaxBoard except all 7 columns render — including
 * AwaitingClient (parked on customer) and Approved (terminal).
 *
 * Shares the same Blade view as SalesTaxBoard via the showAll flag.
 */
class SalesTaxBoardAll extends SalesTaxBoard
{
    protected function showAll(): bool
    {
        return true;
    }
}
