<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Exceptions\InvalidStatusTransitionException;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);

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

    expect($this->filing->fresh()->status)->toBe(FilingStatus::AwaitingPayment);
    expect($this->filing->events()->count())->toBe(1);
});

it('can transition from awaiting_payment to paid', function () {
    $this->filing->update(['status' => FilingStatus::AwaitingPayment]);

    $this->filing->transitionTo(FilingStatus::Paid);

    expect($this->filing->fresh()->status)->toBe(FilingStatus::Paid);
    expect($this->filing->fresh()->paid_at)->not->toBeNull();
});

it('can transition from paid to in_fulfillment for full service', function () {
    $this->filing->update([
        'status' => FilingStatus::Paid,
        'service_level' => 'full_service',
    ]);

    $this->filing->transitionTo(FilingStatus::InFulfillment);

    expect($this->filing->fresh()->status)->toBe(FilingStatus::InFulfillment);
});

it('can transition from in_fulfillment to mailed with tracking', function () {
    $this->filing->update(['status' => FilingStatus::InFulfillment]);

    $this->filing->transitionTo(FilingStatus::Mailed, ['tracking_number' => '12345']);

    $filing = $this->filing->fresh();
    expect($filing->status)->toBe(FilingStatus::Mailed);
    expect($filing->mailed_at)->not->toBeNull();
    expect($filing->mailing_tracking_number)->toBe('12345');
});

it('throws exception for invalid transition', function () {
    // Cannot go from draft directly to mailed
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

it('does not allow transition from complete', function () {
    $this->filing->update(['status' => FilingStatus::Complete]);

    expect($this->filing->allowedTransitions())->toBe([]);
});
