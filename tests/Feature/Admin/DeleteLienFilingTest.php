<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienBoardAll;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Models\EmailSequence;
use App\Models\User;
use Livewire\Livewire;

function createDeletableFiling(): array
{
    $business = Business::factory()->create();
    $project = LienProject::factory()->create(['business_id' => $business->id]);
    $filing = LienFiling::factory()->forProject($project)->create([
        'status' => FilingStatus::AwaitingClient,
        'paid_at' => now(),
    ]);

    return [$filing, $business, $project];
}

describe('delete authorization', function () {
    it('allows admin with lien.delete permission to delete a filing', function () {
        [$filing] = createDeletableFiling();

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'lien.delete');

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('confirmDelete')
            ->assertSet('showDeleteModal', true)
            ->call('deleteFiling')
            ->assertRedirect(route('admin.liens.board'));

        $filing->refresh();
        expect($filing->trashed())->toBeTrue();
    });

    it('denies users without lien.delete permission from deleting', function () {
        [$filing] = createDeletableFiling();

        $agent = User::factory()->create();
        $agent->givePermissionTo('lien.view', 'lien.update', 'lien.change_status');

        $this->actingAs($agent);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('confirmDelete')
            ->assertForbidden();

        $filing->refresh();
        expect($filing->trashed())->toBeFalse();
    });

    it('denies users without lien.delete permission from deleteFiling action', function () {
        [$filing] = createDeletableFiling();

        $agent = User::factory()->create();
        $agent->givePermissionTo('lien.view');

        $this->actingAs($agent);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('deleteFiling')
            ->assertForbidden();

        $filing->refresh();
        expect($filing->trashed())->toBeFalse();
    });

    it('hides the delete button for users without lien.delete permission', function () {
        [$filing] = createDeletableFiling();

        $agent = User::factory()->create();
        $agent->givePermissionTo('lien.view');

        $this->actingAs($agent);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertDontSee('Delete Filing')
            ->assertDontSee('Deleting this filing will hide');
    });

    it('shows the delete button for admins with lien.delete permission', function () {
        [$filing] = createDeletableFiling();

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'lien.delete');

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Delete Filing')
            ->assertSee('Deleting this filing will hide');
    });
});

describe('soft delete behavior', function () {
    it('hides soft-deleted filings from customer-facing queries', function () {
        [$filing, $business] = createDeletableFiling();

        $owner = User::factory()->create();
        $business->users()->attach($owner, ['role' => 'owner']);
        $owner->update(['current_business_id' => $business->id]);

        $this->actingAs($owner);

        expect(LienFiling::query()->find($filing->id))->not->toBeNull();

        $filing->delete();

        expect(LienFiling::query()->find($filing->id))->toBeNull();
        expect(LienFiling::withTrashed()->find($filing->id))->not->toBeNull();
    });

    it('still loads soft-deleted filings on the admin detail page with a deleted banner', function () {
        [$filing] = createDeletableFiling();

        $filing->delete();
        $filing->refresh();

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'lien.delete');

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Filing deleted')
            ->assertSee('Deleted')
            ->assertSee('no longer visible to the customer')
            ->assertDontSee('Delete Filing')
            ->assertDontSee('Refund Payment');
    });

    it('admin detail page loads a soft-deleted filing via public_id route', function () {
        [$filing] = createDeletableFiling();

        $filing->delete();

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'lien.delete');

        $this->actingAs($admin)
            ->get(route('admin.liens.show', $filing->public_id))
            ->assertSuccessful()
            ->assertSee('Filing deleted')
            ->assertSee('Deleted');
    });

    it('shows soft-deleted filings on the LienBoardAll board with a Deleted badge', function () {
        [$filing] = createDeletableFiling();

        $filing->delete();

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $this->actingAs($admin);

        Livewire::test(LienBoardAll::class)
            ->assertSee('Deleted');
    });

    it('returns soft-deleted filings on the LienBoard kanban search with a Deleted badge', function () {
        [$filing, $business] = createDeletableFiling();
        $business->update(['name' => 'Findable Deleted Co']);

        $filing->delete();

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $this->actingAs($admin);

        Livewire::test(\App\Domains\Lien\Admin\Livewire\LienBoard::class)
            ->set('search', 'Findable Deleted Co')
            ->assertSee('Findable Deleted Co')
            ->assertSee('Deleted');
    });

    it('returns soft-deleted filings on the LienBoard liens search mode with a Deleted badge', function () {
        [$filing, $business] = createDeletableFiling();
        $business->update(['name' => 'Searchable Trashed LLC']);

        $filing->delete();

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $this->actingAs($admin);

        Livewire::test(\App\Domains\Lien\Admin\Livewire\LienBoard::class)
            ->set('search', 'Searchable Trashed LLC')
            ->call('searchLiens')
            ->assertSee('Searchable Trashed LLC')
            ->assertSee('Deleted');
    });

    it('hides soft-deleted filings from the default kanban (no search active)', function () {
        [$filing] = createDeletableFiling();

        $filing->delete();

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $this->actingAs($admin);

        $component = Livewire::test(\App\Domains\Lien\Admin\Livewire\LienBoard::class);
        $filings = $component->instance()->getFilings()->flatten();

        expect($filings->pluck('id'))->not->toContain($filing->id);
    });

    it('records a filing_deleted audit event', function () {
        [$filing] = createDeletableFiling();

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'lien.delete');

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('deleteFiling');

        $this->assertDatabaseHas('lien_filing_events', [
            'filing_id' => $filing->id,
            'event_type' => 'filing_deleted',
            'created_by' => $admin->id,
        ]);
    });
});

describe('email sequence suppression on delete', function () {
    it('suppresses an active filing_action_reminder sequence when the filing is deleted', function () {
        [$filing, $business] = createDeletableFiling();

        $owner = User::factory()->create();
        $business->users()->attach($owner, ['role' => 'owner']);
        $filing->update(['created_by_user_id' => $owner->id]);

        $sequence = EmailSequence::startReminderFor(
            $filing,
            FilingStatus::AwaitingClient,
            $owner,
            $business,
        );

        expect($sequence->suppressed_at)->toBeNull();

        $filing->delete();

        $sequence->refresh();
        expect($sequence->suppressed_at)->not->toBeNull();
    });

    it('suppresses non-trigger sequences referencing the deleted filing', function () {
        [$filing, $business] = createDeletableFiling();

        $owner = User::factory()->create();
        $business->users()->attach($owner, ['role' => 'owner']);

        $sequence = EmailSequence::create([
            'sequence_type' => 'abandon_checkout',
            'sequenceable_type' => $filing->getMorphClass(),
            'sequenceable_id' => $filing->id,
            'user_id' => $owner->id,
            'business_id' => $business->id,
            'next_send_at' => now()->addHour(),
        ]);

        $filing->delete();

        $sequence->refresh();
        expect($sequence->suppressed_at)->not->toBeNull();
        expect($sequence->suppression_reason)->toBe('filing_deleted');
    });
});
