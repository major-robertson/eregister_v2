<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Actions\UpdateLienParties;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Enums\ServiceLevel;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingRecipient;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Livewire\Livewire;

function editAppAdmin(): User
{
    $admin = User::factory()->create();
    $admin->givePermissionTo('lien.view', 'lien.update');

    return $admin;
}

function editAppProject(?Business $business = null): LienProject
{
    $business ??= Business::factory()->create();

    return LienProject::factory()->forBusiness($business)->create([
        'name' => 'Original Project',
        'jobsite_state' => 'CA',
        'jobsite_county' => 'Los Angeles',
        'jobsite_city' => 'Los Angeles',
        'provided_type' => 'both',
        'hired_by' => 'owner',
        // 'both' + 'owner' derives to Gc; set it so a no-op save produces no diff.
        'claimant_type' => 'gc',
        'property_class' => 'commercial',
        'owner_is_tenant' => false,
        'has_written_contract' => false,
        'first_furnish_date' => '2026-01-01',
        'last_furnish_date' => '2026-02-01',
        'completion_date' => null,
        'noc_status' => 'unknown',
    ]);
}

function editAppParty(LienProject $project, PartyRole $role, array $attrs = []): LienParty
{
    return LienParty::create(array_merge([
        'business_id' => $project->business_id,
        'project_id' => $project->id,
        'role' => $role,
        'name' => 'Owner Name',
        'address1' => '1 Main St',
        'city' => 'Los Angeles',
        'state' => 'CA',
        'zip' => '90001',
    ], $attrs));
}

function editAppPaidFiling(LienProject $project, array $overrides = []): LienFiling
{
    return LienFiling::factory()->forProject($project)->paid()->create(array_merge([
        'payload_json' => [
            'project' => ['name' => $project->name],
            'property_details' => ['multiple_parcels' => 'no'],
            'filing' => ['service_level' => 'full_service'],
        ],
        'parties_snapshot_json' => [],
    ], $overrides));
}

describe('project edits', function () {
    it('updates the live project, logs an audit diff, and re-syncs the snapshot', function () {
        $admin = editAppAdmin();
        $project = editAppProject();
        $owner = editAppParty($project, PartyRole::Owner, ['name' => 'Acme LC']);
        $filing = editAppPaidFiling($project, ['parties_snapshot_json' => [$owner->toSnapshot()]]);

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->set('projectForm.name', 'Corrected Project Name')
            ->set('projectForm.jobsite_city', 'San Diego')
            ->call('updateProjectDetails')
            ->assertHasNoErrors();

        expect($project->fresh()->name)->toBe('Corrected Project Name');
        expect($project->fresh()->jobsite_city)->toBe('San Diego');

        $event = $filing->fresh()->events()->where('event_type', 'application_project_updated')->latest()->first();
        expect($event)->not->toBeNull();
        expect($event->created_by)->toBe($admin->id);
        expect($event->payload_json['changes'])->toHaveKey('name');
        expect($event->payload_json['changes']['name']['to'])->toBe('Corrected Project Name');

        // Snapshot rebuilt from the live record.
        expect($filing->fresh()->payload_json['project']['name'])->toBe('Corrected Project Name');
    });

    it('preserves unknown payload keys and multiple_parcels through a resync', function () {
        $admin = editAppAdmin();
        $project = editAppProject();
        $filing = editAppPaidFiling($project, [
            'payload_json' => [
                'project' => ['name' => $project->name],
                'property_details' => ['multiple_parcels' => 'yes'],
                'custom_admin_field' => 'keep me',
            ],
        ]);

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->set('projectForm.name', 'Renamed')
            ->call('updateProjectDetails')
            ->assertHasNoErrors();

        $payload = $filing->fresh()->payload_json;
        expect($payload['custom_admin_field'])->toBe('keep me');
        expect($payload['property_details']['multiple_parcels'])->toBe('yes');
        expect($payload['project']['name'])->toBe('Renamed');
    });

    it('writes no audit event when nothing changed', function () {
        $admin = editAppAdmin();
        $project = editAppProject();
        $filing = editAppPaidFiling($project);

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('updateProjectDetails')
            ->assertHasNoErrors();

        expect($filing->fresh()->events()->where('event_type', 'application_project_updated')->count())->toBe(0);
    });
});

describe('filing edits', function () {
    it('rebuilds service_level in the snapshot from the live filing, not the stale payload', function () {
        $admin = editAppAdmin();
        $project = editAppProject();
        $filing = editAppPaidFiling($project, [
            'service_level' => ServiceLevel::FullService,
            'payload_json' => [
                'filing' => ['service_level' => 'full_service'],
                'property_details' => ['multiple_parcels' => 'no'],
            ],
        ]);

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->set('filingForm.service_level', ServiceLevel::SelfServe->value)
            ->set('filingForm.amount_claimed', '1300.00')
            ->call('updateFilingDetails')
            ->assertHasNoErrors();

        $fresh = $filing->fresh();
        expect($fresh->service_level)->toBe(ServiceLevel::SelfServe);
        expect($fresh->amount_claimed_cents)->toBe(130000);
        expect($fresh->payload_json['filing']['service_level'])->toBe('self_serve');

        $event = $fresh->events()->where('event_type', 'application_filing_updated')->first();
        expect($event->payload_json['changes'])->toHaveKey('service_level');
        expect($event->created_by)->toBe($admin->id);
    });
});

describe('party edits', function () {
    it('adds a party and includes it in the resynced parties snapshot', function () {
        $admin = editAppAdmin();
        $project = editAppProject();
        $filing = editAppPaidFiling($project);

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('addParty')
            ->set('partyForm.role', PartyRole::Owner->value)
            ->set('partyForm.name', 'New Owner')
            ->set('partyForm.address1', '5 Elm St')
            ->set('partyForm.city', 'Los Angeles')
            ->set('partyForm.state', 'CA')
            ->set('partyForm.zip', '90002')
            ->call('saveParty')
            ->assertHasNoErrors();

        expect($project->parties()->where('name', 'New Owner')->exists())->toBeTrue();
        expect(collect($filing->fresh()->parties_snapshot_json)->pluck('name'))->toContain('New Owner');

        $event = $filing->fresh()->events()->where('event_type', 'application_parties_updated')->latest()->first();
        expect($event->payload_json['party']['action'])->toBe('added');
        expect($event->created_by)->toBe($admin->id);
    });

    it('edits a party and refreshes its unsent recipient address', function () {
        $admin = editAppAdmin();
        $project = editAppProject();
        $owner = editAppParty($project, PartyRole::Owner, ['name' => 'Old Owner', 'address1' => '1 Old St']);
        $filing = editAppPaidFiling($project, ['parties_snapshot_json' => [$owner->toSnapshot()]]);
        $recipient = LienFilingRecipient::create(
            LienFilingRecipient::fromParty($owner) + ['filing_id' => $filing->id]
        );

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('editParty', $owner->id)
            ->set('partyForm.address1', '99 New St')
            ->call('saveParty')
            ->assertHasNoErrors();

        expect($recipient->fresh()->address_snapshot_json['address1'])->toBe('99 New St');

        $snap = collect($filing->fresh()->parties_snapshot_json)->firstWhere('id', $owner->id);
        expect($snap['address1'])->toBe('99 New St');
    });

    it('removes a party and deletes its unsent recipient', function () {
        $admin = editAppAdmin();
        $project = editAppProject();
        $owner = editAppParty($project, PartyRole::Owner);
        $filing = editAppPaidFiling($project, ['parties_snapshot_json' => [$owner->toSnapshot()]]);
        $recipient = LienFilingRecipient::create(
            LienFilingRecipient::fromParty($owner) + ['filing_id' => $filing->id]
        );

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('confirmRemoveParty', $owner->id)
            ->call('removeParty')
            ->assertHasNoErrors();

        expect(LienParty::find($owner->id))->toBeNull();
        expect(LienFilingRecipient::find($recipient->id))->toBeNull();
        expect(collect($filing->fresh()->parties_snapshot_json)->pluck('id'))->not->toContain($owner->id);
    });

    it('warns and leaves an already-sent recipient untouched when its party is edited', function () {
        // Asserted at the Action layer: Livewire component tests don't expose
        // session()->flash() to the test, but the warning originates in the
        // SyncResult the component relays.
        $admin = editAppAdmin();
        $project = editAppProject();
        $owner = editAppParty($project, PartyRole::Owner, ['address1' => '1 Old St']);
        $filing = editAppPaidFiling($project, ['parties_snapshot_json' => [$owner->toSnapshot()]]);
        $recipient = LienFilingRecipient::create(
            LienFilingRecipient::fromParty($owner) + ['filing_id' => $filing->id, 'sent_at' => now()]
        );

        $this->actingAs($admin);

        $result = app(UpdateLienParties::class)
            ->saveParty($filing, $owner->id, [
                'role' => 'owner', 'name' => 'Owner Name', 'address1' => '2 New St',
                'city' => 'Los Angeles', 'state' => 'CA', 'zip' => '90001',
            ]);

        // Already-mailed recipient address is preserved...
        expect($recipient->fresh()->address_snapshot_json['address1'])->toBe('1 Old St');
        // ...and the now-stale address is surfaced as a warning.
        expect($result->staleSentRecipientWarnings)->not->toBeEmpty();
        expect(str_contains($result->staleSentRecipientWarnings[0], 'already mailed'))->toBeTrue();
    });
});

describe('finalized filings', function () {
    it('leaves a finalized filing snapshot frozen while syncing an eligible sibling', function () {
        $admin = editAppAdmin();
        $project = editAppProject();
        $finalized = editAppPaidFiling($project, [
            'status' => FilingStatus::Mailed,
            'mailed_at' => now(),
            'payload_json' => ['project' => ['name' => 'frozen']],
        ]);
        $sibling = editAppPaidFiling($project, [
            'payload_json' => ['project' => ['name' => 'old']],
        ]);

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $finalized])
            ->set('projectForm.name', 'New Name')
            ->call('updateProjectDetails')
            ->assertHasNoErrors();

        // Live shared record changes; the finalized snapshot is frozen; the sibling resyncs.
        expect($project->fresh()->name)->toBe('New Name');
        expect($finalized->fresh()->payload_json['project']['name'])->toBe('frozen');
        expect($sibling->fresh()->payload_json['project']['name'])->toBe('New Name');

        $meta = $finalized->fresh()->events()
            ->where('event_type', 'application_project_updated')->first()
            ->payload_json['meta'];
        expect($meta['skipped_filing_ids'])->toContain($finalized->id);
        expect($meta['synced_filing_ids'])->toContain($sibling->id);
    });
});

describe('edit affordances', function () {
    it('shows edit controls to an admin with lien.update', function () {
        $admin = editAppAdmin();
        $project = editAppProject();
        editAppParty($project, PartyRole::Owner);
        $filing = editAppPaidFiling($project);

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSeeHtml('wire:click="editProjectDetails"')
            ->assertSeeHtml('wire:click="editFilingDetails"')
            ->assertSeeHtml('wire:click="addParty"')
            ->assertSee('Edit Project Details');
    });

    it('hides edit controls from a viewer without lien.update', function () {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('lien.view');
        $project = editAppProject();
        $filing = editAppPaidFiling($project);

        $this->actingAs($viewer);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertDontSeeHtml('wire:click="editProjectDetails"')
            ->assertDontSee('Edit Project Details');
    });
});

describe('authorization', function () {
    it('forbids a viewer without lien.update from editing the project', function () {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('lien.view');
        $project = editAppProject();
        $filing = editAppPaidFiling($project);

        $this->actingAs($viewer);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->set('projectForm.name', 'Nope')
            ->call('updateProjectDetails')
            ->assertForbidden();
    });

    it('forbids a viewer without lien.update from editing parties', function () {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('lien.view');
        $project = editAppProject();
        $owner = editAppParty($project, PartyRole::Owner);
        $filing = editAppPaidFiling($project);

        $this->actingAs($viewer);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('removeParty')
            ->assertForbidden();
    });
});
