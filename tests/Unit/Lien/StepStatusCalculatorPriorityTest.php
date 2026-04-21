<?php

use App\Domains\Lien\Engine\StepStatusCalculator;
use App\Domains\Lien\Enums\FilingStatus;

/*
|--------------------------------------------------------------------------
| StepStatusCalculator::getFilingPriority() coverage guard
|--------------------------------------------------------------------------
| Regression for production UnhandledMatchError observed 2026-04-20: the
| match() in getFilingPriority() did not cover every FilingStatus case
| (NeedsReview, ReadyToFile, WaitingOnNextStep, Hold, Refunded), so any
| filing in those statuses crashed StepStatusCalculator::getActiveFiling()
| and in turn the ProjectShow Livewire render.
|
| This test instantiates the calculator directly (no container, no DB) so
| the guard runs even in environments without the test database available.
*/

describe('StepStatusCalculator::getFilingPriority', function () {
    it('returns an int for every FilingStatus case', function () {
        $calculator = new StepStatusCalculator;
        $method = new ReflectionMethod($calculator, 'getFilingPriority');
        $method->setAccessible(true);

        foreach (FilingStatus::cases() as $status) {
            $priority = $method->invoke($calculator, $status);

            expect($priority)->toBeInt(
                "getFilingPriority() must handle FilingStatus::{$status->name}"
            );
        }
    });

    it('orders post-paid statuses above pre-paid statuses', function () {
        $calculator = new StepStatusCalculator;
        $method = new ReflectionMethod($calculator, 'getFilingPriority');
        $method->setAccessible(true);

        $priority = fn (FilingStatus $s) => $method->invoke($calculator, $s);

        // Terminal success outranks every in-progress status.
        expect($priority(FilingStatus::Complete))
            ->toBeGreaterThan($priority(FilingStatus::Recorded))
            ->and($priority(FilingStatus::Recorded))
            ->toBeGreaterThan($priority(FilingStatus::Mailed))
            ->and($priority(FilingStatus::Mailed))
            ->toBeGreaterThan($priority(FilingStatus::InFulfillment));

        // Anything paid/post-paid outranks AwaitingPayment / Draft.
        foreach ([
            FilingStatus::Paid,
            FilingStatus::AwaitingClient,
            FilingStatus::AwaitingEsign,
            FilingStatus::AwaitingNotary,
            FilingStatus::NeedsReview,
            FilingStatus::ReadyToFile,
            FilingStatus::WaitingOnNextStep,
            FilingStatus::Hold,
        ] as $postPaid) {
            expect($priority($postPaid))
                ->toBeGreaterThan($priority(FilingStatus::AwaitingPayment))
                ->and($priority($postPaid))
                ->toBeGreaterThan($priority(FilingStatus::Draft));
        }

        // Terminal-cancel statuses sit at the bottom.
        expect($priority(FilingStatus::Canceled))->toBe(0)
            ->and($priority(FilingStatus::Refunded))->toBe(0);
    });
});
