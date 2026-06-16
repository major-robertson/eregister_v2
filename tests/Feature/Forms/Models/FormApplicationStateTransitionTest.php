<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Enums\FormApplicationStateAdminStatus;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;

function makeStateForTransition(): FormApplicationState
{
    $user = User::factory()->create();
    $business = Business::factory()->create();

    $application = FormApplication::create([
        'business_id' => $business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => ['CA'],
        'status' => 'submitted',
        'current_phase' => 'review',
        'core_data' => [],
        'created_by_user_id' => $user->id,
        'paid_at' => now(),
    ]);

    return FormApplicationState::create([
        'form_application_id' => $application->id,
        'state_code' => 'CA',
        'status' => 'complete',
        'data' => [],
    ]);
}

it('writes a transition row and updates current_admin_status atomically', function () {
    $state = makeStateForTransition();
    $admin = User::factory()->create();

    expect($state->current_admin_status)
        ->toBe(FormApplicationStateAdminStatus::New);

    $state->transitionAdminStatusTo(
        next: FormApplicationStateAdminStatus::NeedsReview,
        by: $admin,
        comment: 'Looking into this',
    );

    $state->refresh();

    expect($state->current_admin_status)
        ->toBe(FormApplicationStateAdminStatus::NeedsReview)
        ->and($state->current_admin_status_changed_at)->not->toBeNull()
        ->and($state->transitions()->count())->toBe(1);

    $transition = $state->transitions()->first();
    expect($transition->from_status)->toBe(FormApplicationStateAdminStatus::New)
        ->and($transition->to_status)->toBe(FormApplicationStateAdminStatus::NeedsReview)
        ->and($transition->changed_by_user_id)->toBe($admin->id)
        ->and($transition->comment)->toBe('Looking into this');
});

it('throws when transitioning out of a terminal status', function () {
    $state = makeStateForTransition();
    $state->update(['current_admin_status' => FormApplicationStateAdminStatus::Approved]);

    expect(fn () => $state->transitionAdminStatusTo(
        FormApplicationStateAdminStatus::New,
    ))->toThrow(\DomainException::class);
});

it('is a no-op when transitioning to the same status', function () {
    $state = makeStateForTransition();

    $state->transitionAdminStatusTo(FormApplicationStateAdminStatus::New);

    expect($state->transitions()->count())->toBe(0);
});

it('keeps the denormalized current_admin_status in sync with the latest transition', function () {
    $state = makeStateForTransition();

    $state->transitionAdminStatusTo(FormApplicationStateAdminStatus::NeedsReview);
    $state->transitionAdminStatusTo(FormApplicationStateAdminStatus::SubmittedToState);
    $state->transitionAdminStatusTo(FormApplicationStateAdminStatus::Rejected);
    $state->transitionAdminStatusTo(FormApplicationStateAdminStatus::SubmittedToState);
    $state->transitionAdminStatusTo(FormApplicationStateAdminStatus::Approved);

    $state->refresh();

    expect($state->current_admin_status)
        ->toBe(FormApplicationStateAdminStatus::Approved)
        ->and($state->transitions()->count())->toBe(5);

    $latest = $state->transitions()->first(); // transitions() default-orders latest first
    expect($latest->to_status)->toBe(FormApplicationStateAdminStatus::Approved)
        ->and($latest->from_status)->toBe(FormApplicationStateAdminStatus::SubmittedToState);
});

it('allows SubmittedToState to transition to any non-terminal status (including Rejected/Hold)', function () {
    $state = makeStateForTransition();
    $state->update(['current_admin_status' => FormApplicationStateAdminStatus::SubmittedToState]);

    $allowedNext = [
        FormApplicationStateAdminStatus::Rejected,
        FormApplicationStateAdminStatus::NeedsReview,
        FormApplicationStateAdminStatus::AwaitingClient,
        FormApplicationStateAdminStatus::Hold,
        FormApplicationStateAdminStatus::Approved,
        FormApplicationStateAdminStatus::New,
    ];

    foreach ($allowedNext as $next) {
        $state->update(['current_admin_status' => FormApplicationStateAdminStatus::SubmittedToState]);
        $state->transitionAdminStatusTo($next);
        expect($state->fresh()->current_admin_status)->toBe($next);
    }
});

it('exposes Approved as the only terminal status', function () {
    foreach (FormApplicationStateAdminStatus::cases() as $status) {
        if ($status === FormApplicationStateAdminStatus::Approved) {
            expect($status->isTerminal())->toBeTrue()
                ->and($status->allowedTransitions())->toBe([]);
        } else {
            expect($status->isTerminal())->toBeFalse()
                ->and($status->allowedTransitions())->not->toBeEmpty();
        }
    }
});
