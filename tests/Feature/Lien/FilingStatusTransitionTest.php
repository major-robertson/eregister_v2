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

    $lastEvent = $this->filing->events()->latest()->first();
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
    'paid → awaiting_esign' => [FilingStatus::Paid, FilingStatus::AwaitingEsign, ['paid_at' => '2026-01-01']],
    'in_fulfillment → awaiting_client' => [FilingStatus::InFulfillment, FilingStatus::AwaitingClient],
    'in_fulfillment → awaiting_esign' => [FilingStatus::InFulfillment, FilingStatus::AwaitingEsign],
    'awaiting_client → in_fulfillment' => [FilingStatus::AwaitingClient, FilingStatus::InFulfillment],
    'awaiting_esign → in_fulfillment' => [FilingStatus::AwaitingEsign, FilingStatus::InFulfillment],
]);

it('can transition to any status except itself', function (FilingStatus $status) {
    $this->filing->update(['status' => $status]);

    $allowed = $this->filing->allowedTransitions();

    expect($allowed)->not->toContain($status);
    expect(count($allowed))->toBe(count(FilingStatus::cases()) - 1);
})->with([
    'awaiting_client' => [FilingStatus::AwaitingClient],
    'awaiting_esign' => [FilingStatus::AwaitingEsign],
]);

it('does not allow transition from complete', function () {
    $this->filing->update(['status' => FilingStatus::Complete]);

    expect($this->filing->allowedTransitions())->toBe([]);
});
