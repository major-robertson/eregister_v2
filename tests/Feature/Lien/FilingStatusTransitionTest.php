<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Exceptions\InvalidStatusTransitionException;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create();
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->project = LienProject::factory()->forBusiness($this->business)->create();

    $this->docType = LienDocumentType::first();

    $this->filing = LienFiling::factory()->forProject($this->project)->create([
        'document_type_id' => $this->docType->id,
        'status' => FilingStatus::Draft,
    ]);

    $this->actingAs($this->user);
});

it('can transition from draft to awaiting_payment', function () {
    $this->filing->transitionTo(FilingStatus::AwaitingPayment);

    $this->filing->refresh();
    expect($this->filing->status)->toBe(FilingStatus::AwaitingPayment);
    expect($this->filing->events()->count())->toBe(1);
});

it('can transition from awaiting_payment to paid', function () {
    $this->filing->update(['status' => FilingStatus::AwaitingPayment]);

    $this->filing->transitionTo(FilingStatus::Paid);

    $this->filing->refresh();
    expect($this->filing->status)->toBe(FilingStatus::Paid);
    expect($this->filing->paid_at)->not->toBeNull();
});

it('can transition from paid to in_fulfillment for full service', function () {
    $this->filing->update([
        'status' => FilingStatus::Paid,
        'service_level' => 'full_service',
    ]);

    $this->filing->transitionTo(FilingStatus::InFulfillment);

    $this->filing->refresh();
    expect($this->filing->status)->toBe(FilingStatus::InFulfillment);
});

it('can transition from in_fulfillment to mailed with tracking', function () {
    $this->filing->update(['status' => FilingStatus::InFulfillment]);

    $this->filing->transitionTo(FilingStatus::Mailed, ['tracking_number' => '12345']);

    $this->filing->refresh();
    expect($this->filing->status)->toBe(FilingStatus::Mailed);
    expect($this->filing->mailed_at)->not->toBeNull();
    expect($this->filing->mailing_tracking_number)->toBe('12345');
});

it('throws exception for invalid transition', function () {
    $this->filing->transitionTo(FilingStatus::Mailed);
})->throws(InvalidStatusTransitionException::class);

it('creates event for each transition', function () {
    $this->filing->transitionTo(FilingStatus::AwaitingPayment);
    $this->filing->transitionTo(FilingStatus::Paid);

    expect($this->filing->events()->count())->toBe(2);

    $lastEvent = $this->filing->events()->latest('id')->first();
    expect($lastEvent->event_type)->toBe('status_changed');
    expect($lastEvent->payload_json['from'])->toBe('awaiting_payment');
    expect($lastEvent->payload_json['to'])->toBe('paid');
});

it('allows valid status transitions', function (FilingStatus $from, FilingStatus $to, array $extra = []) {
    $this->filing->update(array_merge(['status' => $from], $extra));

    $this->filing->transitionTo($to);

    $this->filing->refresh();
    expect($this->filing->status)->toBe($to);
})->with([
    'paid → awaiting_client' => [FilingStatus::Paid, FilingStatus::AwaitingClient, ['paid_at' => '2026-01-01']],
    'paid → needs_review' => [FilingStatus::Paid, FilingStatus::NeedsReview, ['paid_at' => '2026-01-01']],
    'paid → ready_to_file' => [FilingStatus::Paid, FilingStatus::ReadyToFile, ['paid_at' => '2026-01-01']],
    'paid → hold' => [FilingStatus::Paid, FilingStatus::Hold, ['paid_at' => '2026-01-01']],
    'awaiting_client → needs_review' => [FilingStatus::AwaitingClient, FilingStatus::NeedsReview],
    'awaiting_client → ready_to_file' => [FilingStatus::AwaitingClient, FilingStatus::ReadyToFile],
    'awaiting_esign → in_fulfillment' => [FilingStatus::AwaitingEsign, FilingStatus::InFulfillment],
    'awaiting_notary → awaiting_client' => [FilingStatus::AwaitingNotary, FilingStatus::AwaitingClient],
    'needs_review → ready_to_file' => [FilingStatus::NeedsReview, FilingStatus::ReadyToFile],
    'needs_review → awaiting_client' => [FilingStatus::NeedsReview, FilingStatus::AwaitingClient],
    'ready_to_file → in_fulfillment' => [FilingStatus::ReadyToFile, FilingStatus::InFulfillment],
    'ready_to_file → mailed' => [FilingStatus::ReadyToFile, FilingStatus::Mailed],
    'waiting_on_next_step → needs_review' => [FilingStatus::WaitingOnNextStep, FilingStatus::NeedsReview],
    'hold → needs_review' => [FilingStatus::Hold, FilingStatus::NeedsReview],
    'hold → ready_to_file' => [FilingStatus::Hold, FilingStatus::ReadyToFile],
    'in_fulfillment → mailed' => [FilingStatus::InFulfillment, FilingStatus::Mailed],
    'in_fulfillment → needs_review' => [FilingStatus::InFulfillment, FilingStatus::NeedsReview],
    'in_fulfillment → waiting_on_next_step' => [FilingStatus::InFulfillment, FilingStatus::WaitingOnNextStep],
    'in_fulfillment → complete' => [FilingStatus::InFulfillment, FilingStatus::Complete],
    'recorded → complete' => [FilingStatus::Recorded, FilingStatus::Complete],
    'complete → needs_review' => [FilingStatus::Complete, FilingStatus::NeedsReview],
    'canceled → needs_review' => [FilingStatus::Canceled, FilingStatus::NeedsReview],
]);

it('returns correct allowed transitions for each status', function (FilingStatus $status, array $expected) {
    $this->filing->update(['status' => $status]);

    $allowed = $this->filing->allowedTransitions();

    expect($allowed)->toBe($expected);
})->with([
    'paid' => [FilingStatus::Paid, [FilingStatus::AwaitingClient, FilingStatus::AwaitingEsign, FilingStatus::AwaitingNotary, FilingStatus::NeedsReview, FilingStatus::ReadyToFile, FilingStatus::WaitingOnNextStep, FilingStatus::Hold, FilingStatus::InFulfillment, FilingStatus::Mailed, FilingStatus::Complete]],
    'awaiting_client' => [FilingStatus::AwaitingClient, [FilingStatus::NeedsReview, FilingStatus::ReadyToFile, FilingStatus::Hold, FilingStatus::Canceled]],
    'awaiting_esign' => [FilingStatus::AwaitingEsign, [FilingStatus::NeedsReview, FilingStatus::ReadyToFile, FilingStatus::Hold, FilingStatus::InFulfillment, FilingStatus::Mailed, FilingStatus::Recorded, FilingStatus::Canceled]],
    'awaiting_notary' => [FilingStatus::AwaitingNotary, [FilingStatus::AwaitingClient, FilingStatus::NeedsReview, FilingStatus::ReadyToFile, FilingStatus::Hold, FilingStatus::InFulfillment, FilingStatus::Mailed, FilingStatus::Recorded, FilingStatus::Canceled]],
    'needs_review' => [FilingStatus::NeedsReview, [FilingStatus::AwaitingClient, FilingStatus::AwaitingEsign, FilingStatus::AwaitingNotary, FilingStatus::ReadyToFile, FilingStatus::WaitingOnNextStep, FilingStatus::Hold, FilingStatus::InFulfillment, FilingStatus::Mailed, FilingStatus::Recorded, FilingStatus::Complete, FilingStatus::Canceled]],
    'ready_to_file' => [FilingStatus::ReadyToFile, [FilingStatus::AwaitingClient, FilingStatus::AwaitingEsign, FilingStatus::AwaitingNotary, FilingStatus::NeedsReview, FilingStatus::Hold, FilingStatus::InFulfillment, FilingStatus::Mailed, FilingStatus::Recorded, FilingStatus::Complete, FilingStatus::Canceled]],
    'waiting_on_next_step' => [FilingStatus::WaitingOnNextStep, [FilingStatus::NeedsReview, FilingStatus::ReadyToFile, FilingStatus::Hold, FilingStatus::InFulfillment, FilingStatus::Mailed, FilingStatus::Recorded, FilingStatus::Complete, FilingStatus::Canceled]],
    'hold' => [FilingStatus::Hold, [FilingStatus::AwaitingClient, FilingStatus::AwaitingEsign, FilingStatus::AwaitingNotary, FilingStatus::NeedsReview, FilingStatus::ReadyToFile, FilingStatus::WaitingOnNextStep, FilingStatus::InFulfillment, FilingStatus::Mailed, FilingStatus::Recorded, FilingStatus::Complete, FilingStatus::Canceled]],
    'in_fulfillment' => [FilingStatus::InFulfillment, [FilingStatus::AwaitingClient, FilingStatus::AwaitingEsign, FilingStatus::AwaitingNotary, FilingStatus::NeedsReview, FilingStatus::ReadyToFile, FilingStatus::WaitingOnNextStep, FilingStatus::Hold, FilingStatus::Mailed, FilingStatus::Complete, FilingStatus::Canceled]],
    'mailed' => [FilingStatus::Mailed, [FilingStatus::NeedsReview, FilingStatus::Hold, FilingStatus::Recorded, FilingStatus::Complete]],
    'recorded' => [FilingStatus::Recorded, [FilingStatus::NeedsReview, FilingStatus::Hold, FilingStatus::Complete, FilingStatus::Canceled]],
    'complete' => [FilingStatus::Complete, [FilingStatus::NeedsReview, FilingStatus::Hold]],
    'canceled' => [FilingStatus::Canceled, [FilingStatus::NeedsReview]],
    'refunded' => [FilingStatus::Refunded, []],
]);
