<?php

use App\Domains\Lien\Admin\Actions\UpdateRecordingDetails;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\RecordingMethod;
use App\Domains\Lien\Models\LienFiling;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo(['lien.view', 'lien.update', 'lien.change_status']);

    // Filing in Paid → SubmittedForRecording is an allowed transition.
    $this->filing = LienFiling::factory()->paid()->create();

    $this->actingAs($this->admin);
});

describe('transitioning to SubmittedForRecording via the admin component', function () {
    it('rejects the transition when recording_method is missing', function () {
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('newStatus', FilingStatus::SubmittedForRecording->value)
            ->set('recordingMethod', '')
            ->call('updateStatus')
            ->assertHasErrors(['recordingMethod' => 'required']);

        $this->filing->refresh();
        expect($this->filing->status)->toBe(FilingStatus::Paid)
            ->and($this->filing->recording_method)->toBeNull();
    });

    it('rejects the transition when recording_submitted_at is blank', function () {
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('newStatus', FilingStatus::SubmittedForRecording->value)
            ->set('recordingMethod', RecordingMethod::Erecord->value)
            ->set('recordingSubmittedAt', '')
            ->call('updateStatus')
            ->assertHasErrors(['recordingSubmittedAt' => 'required']);

        $this->filing->refresh();
        expect($this->filing->status)->toBe(FilingStatus::Paid);
    });

    it('prefills recording_submitted_at with the current timestamp when SubmittedForRecording is selected', function () {
        Carbon::setTestNow('2026-04-25 16:42:00');

        $component = Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('newStatus', FilingStatus::SubmittedForRecording->value);

        // The updatedNewStatus hook should have populated the field for us.
        expect($component->get('recordingSubmittedAt'))->toBe('2026-04-25T16:42');

        Carbon::setTestNow();
    });

    it('persists recording_method and the prefilled submitted_at when transitioning with only the required pick', function () {
        Carbon::setTestNow('2026-04-25 12:00:00');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('newStatus', FilingStatus::SubmittedForRecording->value)
            ->set('recordingMethod', RecordingMethod::Erecord->value)
            ->call('updateStatus')
            ->assertHasNoErrors();

        $this->filing->refresh();
        expect($this->filing->status)->toBe(FilingStatus::SubmittedForRecording)
            ->and($this->filing->recording_method)->toBe(RecordingMethod::Erecord)
            ->and($this->filing->recording_provider)->toBeNull()
            ->and($this->filing->recording_reference)->toBeNull()
            ->and($this->filing->recording_submitted_at?->format('Y-m-d H:i'))->toBe('2026-04-25 12:00');

        Carbon::setTestNow();
    });

    it('snapshots recording_method into the status_changed event meta for the activity log', function () {
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('newStatus', FilingStatus::SubmittedForRecording->value)
            ->set('recordingMethod', RecordingMethod::Mail->value)
            ->call('updateStatus')
            ->assertHasNoErrors();

        $event = $this->filing->fresh()->events()
            ->where('event_type', 'status_changed')
            ->latest()
            ->first();

        expect($event)->not->toBeNull()
            ->and($event->payload_json['to'])->toBe(FilingStatus::SubmittedForRecording->value)
            ->and($event->payload_json['meta']['recording_method'] ?? null)->toBe(RecordingMethod::Mail->value);
    });

    it('persists all four recording fields when provided at transition time', function () {
        $sentAt = Carbon::parse('2026-04-25 14:30:00');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('newStatus', FilingStatus::SubmittedForRecording->value)
            ->set('recordingMethod', RecordingMethod::Mail->value)
            ->set('recordingProvider', 'USPS Certified')
            ->set('recordingReference', 'CM-1234-5678')
            ->set('recordingSubmittedAt', $sentAt->format('Y-m-d\TH:i'))
            ->call('updateStatus')
            ->assertHasNoErrors();

        $this->filing->refresh();
        expect($this->filing->status)->toBe(FilingStatus::SubmittedForRecording)
            ->and($this->filing->recording_method)->toBe(RecordingMethod::Mail)
            ->and($this->filing->recording_provider)->toBe('USPS Certified')
            ->and($this->filing->recording_reference)->toBe('CM-1234-5678')
            ->and($this->filing->recording_submitted_at?->format('Y-m-d H:i'))->toBe('2026-04-25 14:30');
    });

    it('does NOT require recording_method for transitions to other statuses', function () {
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('newStatus', FilingStatus::InFulfillment->value)
            ->set('recordingMethod', '')
            ->call('updateStatus')
            ->assertHasNoErrors();

        $this->filing->refresh();
        expect($this->filing->status)->toBe(FilingStatus::InFulfillment)
            ->and($this->filing->recording_method)->toBeNull();
    });
});

describe('updating recording details after the fact', function () {
    beforeEach(function () {
        $this->filing->update([
            'status' => FilingStatus::SubmittedForRecording,
            'recording_method' => RecordingMethod::Erecord,
        ]);
    });

    it('updates the recording fields without changing status and writes an event', function () {
        $sentAt = Carbon::parse('2026-04-26 09:15:00');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->refresh()])
            ->set('recordingProvider', 'Simplifile')
            ->set('recordingReference', 'SF-9999')
            ->set('recordingSubmittedAt', $sentAt->format('Y-m-d\TH:i'))
            ->call('updateRecordingDetails')
            ->assertHasNoErrors();

        $this->filing->refresh();
        expect($this->filing->status)->toBe(FilingStatus::SubmittedForRecording)
            ->and($this->filing->recording_provider)->toBe('Simplifile')
            ->and($this->filing->recording_reference)->toBe('SF-9999')
            ->and($this->filing->recording_submitted_at?->format('Y-m-d H:i'))->toBe('2026-04-26 09:15');

        $event = $this->filing->events()
            ->where('event_type', 'recording_details_updated')
            ->latest()
            ->first();

        expect($event)->not->toBeNull()
            ->and($event->payload_json['changes'])->toHaveKey('recording_provider')
            ->and($event->payload_json['changes']['recording_provider']['from'])->toBeNull()
            ->and($event->payload_json['changes']['recording_provider']['to'])->toBe('Simplifile');
    });

    it('allows editing recording details on a filing already past SubmittedForRecording', function () {
        $this->filing->update([
            'status' => FilingStatus::Recorded,
            'recorded_at' => now(),
            'recording_submitted_at' => now()->subDay(),
        ]);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->refresh()])
            ->set('recordingProvider', 'Late entry')
            ->call('updateRecordingDetails')
            ->assertHasNoErrors();

        $this->filing->refresh();
        expect($this->filing->status)->toBe(FilingStatus::Recorded)
            ->and($this->filing->recording_provider)->toBe('Late entry');
    });

    it('skips writing an event when nothing actually changed', function () {
        $sentAt = Carbon::parse('2026-04-20 10:00:00');

        $this->filing->update([
            'recording_provider' => 'Simplifile',
            'recording_reference' => 'SF-1',
            'recording_submitted_at' => $sentAt,
        ]);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->refresh()])
            ->set('recordingMethod', RecordingMethod::Erecord->value)
            ->set('recordingProvider', 'Simplifile')
            ->set('recordingReference', 'SF-1')
            ->set('recordingSubmittedAt', $sentAt->format('Y-m-d\TH:i'))
            ->call('updateRecordingDetails')
            ->assertHasNoErrors();

        expect($this->filing->events()
            ->where('event_type', 'recording_details_updated')
            ->count())->toBe(0);
    });

    it('rejects update when recording_method is blank', function () {
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->refresh()])
            ->set('recordingMethod', '')
            ->set('recordingSubmittedAt', now()->format('Y-m-d\TH:i'))
            ->call('updateRecordingDetails')
            ->assertHasErrors(['recordingMethod' => 'required']);
    });

    it('rejects update when recording_submitted_at is blank', function () {
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->refresh()])
            ->set('recordingMethod', RecordingMethod::Erecord->value)
            ->set('recordingSubmittedAt', '')
            ->call('updateRecordingDetails')
            ->assertHasErrors(['recordingSubmittedAt' => 'required']);
    });
});

describe('UpdateRecordingDetails action directly', function () {
    it('writes an event payload with from/to for each changed field', function () {
        $this->filing->update([
            'status' => FilingStatus::SubmittedForRecording,
            'recording_method' => RecordingMethod::Erecord,
            'recording_provider' => 'Old Provider',
        ]);

        app(UpdateRecordingDetails::class)->execute(
            filing: $this->filing->refresh(),
            recordingMethod: RecordingMethod::Mail,
            recordingProvider: 'New Provider',
            recordingReference: 'NEW-123',
            recordingSubmittedAt: null,
        );

        $event = $this->filing->fresh()->events()
            ->where('event_type', 'recording_details_updated')
            ->latest()
            ->first();

        expect($event)->not->toBeNull();
        $changes = $event->payload_json['changes'];

        expect($changes)->toHaveKeys(['recording_method', 'recording_provider', 'recording_reference'])
            ->and($changes['recording_method']['from'])->toBe('erecord')
            ->and($changes['recording_method']['to'])->toBe('mail')
            ->and($changes['recording_provider']['from'])->toBe('Old Provider')
            ->and($changes['recording_provider']['to'])->toBe('New Provider')
            ->and($changes['recording_reference']['from'])->toBeNull()
            ->and($changes['recording_reference']['to'])->toBe('NEW-123');
    });
});
