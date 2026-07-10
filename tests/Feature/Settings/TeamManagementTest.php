<?php

use App\Domains\Business\Models\Business;
use App\Domains\Business\Models\BusinessInvitation;
use App\Mail\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->business = Business::factory()->onboarded()->create();

    $this->owner = User::factory()->create();
    $this->owner->businesses()->attach($this->business->id, ['role' => 'owner']);

    $this->admin = User::factory()->create();
    $this->admin->businesses()->attach($this->business->id, ['role' => 'admin']);

    $this->member = User::factory()->create();
    $this->member->businesses()->attach($this->business->id, ['role' => 'member']);
});

it('allows owners and admins to view the team page', function () {
    $this->actingAs($this->owner)
        ->withSession(['current_business_id' => $this->business->id])
        ->get(route('team.edit'))
        ->assertOk();

    $this->actingAs($this->admin)
        ->withSession(['current_business_id' => $this->business->id])
        ->get(route('team.edit'))
        ->assertOk();
});

it('forbids members from the team page', function () {
    $this->actingAs($this->member)
        ->withSession(['current_business_id' => $this->business->id])
        ->get(route('team.edit'))
        ->assertForbidden();
});

it('sends an invitation with the chosen role', function () {
    Mail::fake();

    $this->actingAs($this->owner);
    session(['current_business_id' => $this->business->id]);

    Livewire::test('pages::settings.team')
        ->set('inviteEmail', 'New.Person@Example.com')
        ->set('inviteRole', 'admin')
        ->call('sendInvite')
        ->assertHasNoErrors();

    $invitation = BusinessInvitation::where('business_id', $this->business->id)->first();

    expect($invitation)->not->toBeNull();
    expect($invitation->email)->toBe('new.person@example.com');
    expect($invitation->role)->toBe('admin');
    expect($invitation->expires_at->isSameDay(now()->addDays(7)))->toBeTrue();

    Mail::assertQueued(TeamInvitation::class, fn ($mail) => $mail->hasTo('new.person@example.com'));
});

it('rejects a duplicate pending invitation', function () {
    Mail::fake();

    BusinessInvitation::factory()->create([
        'business_id' => $this->business->id,
        'email' => 'pending@example.com',
    ]);

    $this->actingAs($this->owner);
    session(['current_business_id' => $this->business->id]);

    Livewire::test('pages::settings.team')
        ->set('inviteEmail', 'pending@example.com')
        ->call('sendInvite')
        ->assertHasErrors('inviteEmail');

    Mail::assertNothingQueued();
});

it('rejects inviting an existing member', function () {
    Mail::fake();

    $this->actingAs($this->owner);
    session(['current_business_id' => $this->business->id]);

    Livewire::test('pages::settings.team')
        ->set('inviteEmail', strtoupper($this->member->email))
        ->call('sendInvite')
        ->assertHasErrors('inviteEmail');

    Mail::assertNothingQueued();
});

it('rejects the owner role on invites', function () {
    $this->actingAs($this->owner);
    session(['current_business_id' => $this->business->id]);

    Livewire::test('pages::settings.team')
        ->set('inviteEmail', 'someone@example.com')
        ->set('inviteRole', 'owner')
        ->call('sendInvite')
        ->assertHasErrors('inviteRole');
});

it('blocks invites until business onboarding is complete', function () {
    Mail::fake();

    $unonboarded = Business::factory()->create();
    $this->owner->businesses()->attach($unonboarded->id, ['role' => 'owner']);

    $this->actingAs($this->owner);
    session(['current_business_id' => $unonboarded->id]);

    Livewire::test('pages::settings.team')
        ->set('inviteEmail', 'someone@example.com')
        ->call('sendInvite')
        ->assertHasErrors('inviteEmail');

    Mail::assertNothingQueued();
});

it('resends an invitation with a fresh expiry', function () {
    Mail::fake();

    $invitation = BusinessInvitation::factory()->create([
        'business_id' => $this->business->id,
        'email' => 'pending@example.com',
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($this->owner);
    session(['current_business_id' => $this->business->id]);

    Livewire::test('pages::settings.team')
        ->call('resendInvite', $invitation->id);

    expect($invitation->fresh()->expires_at->isSameDay(now()->addDays(7)))->toBeTrue();

    Mail::assertQueued(TeamInvitation::class, fn ($mail) => $mail->hasTo('pending@example.com'));
});

it('revokes an invitation', function () {
    $invitation = BusinessInvitation::factory()->create([
        'business_id' => $this->business->id,
        'email' => 'pending@example.com',
    ]);

    $this->actingAs($this->owner);
    session(['current_business_id' => $this->business->id]);

    Livewire::test('pages::settings.team')
        ->call('revokeInvite', $invitation->id);

    expect(BusinessInvitation::find($invitation->id))->toBeNull();
});

it('cannot manage invitations belonging to another business', function () {
    $foreign = BusinessInvitation::factory()->create([
        'email' => 'pending@other-business.com',
    ]);

    $this->actingAs($this->owner);
    session(['current_business_id' => $this->business->id]);

    // The scoped lookup refuses the cross-tenant id (404 in a real request).
    try {
        Livewire::test('pages::settings.team')->call('revokeInvite', $foreign->id);
        $this->fail('Expected the cross-tenant lookup to throw.');
    } catch (Illuminate\Database\Eloquent\ModelNotFoundException) {
        // expected
    }

    expect(BusinessInvitation::find($foreign->id))->not->toBeNull();
});

it('changes a member role', function () {
    $this->actingAs($this->owner);
    session(['current_business_id' => $this->business->id]);

    Livewire::test('pages::settings.team')
        ->call('changeRole', $this->member->id, 'admin');

    expect($this->business->users()->find($this->member->id)->pivot->role)->toBe('admin');
});

it('cannot change the owner role', function () {
    $this->actingAs($this->admin);
    session(['current_business_id' => $this->business->id]);

    Livewire::test('pages::settings.team')
        ->call('changeRole', $this->owner->id, 'member')
        ->assertForbidden();

    expect($this->business->users()->find($this->owner->id)->pivot->role)->toBe('owner');
});

it('cannot change your own role', function () {
    $this->actingAs($this->admin);
    session(['current_business_id' => $this->business->id]);

    Livewire::test('pages::settings.team')
        ->call('changeRole', $this->admin->id, 'member')
        ->assertForbidden();
});

it('rejects roles other than admin and member on role changes', function () {
    $this->actingAs($this->owner);
    session(['current_business_id' => $this->business->id]);

    Livewire::test('pages::settings.team')
        ->call('changeRole', $this->member->id, 'owner')
        ->assertStatus(422);

    expect($this->business->users()->find($this->member->id)->pivot->role)->toBe('member');
});

it('removes a member', function () {
    $this->actingAs($this->owner);
    session(['current_business_id' => $this->business->id]);

    Livewire::test('pages::settings.team')
        ->call('removeMember', $this->member->id);

    expect($this->member->fresh()->belongsToBusiness($this->business))->toBeFalse();
});

it('cannot remove the owner', function () {
    $this->actingAs($this->admin);
    session(['current_business_id' => $this->business->id]);

    Livewire::test('pages::settings.team')
        ->call('removeMember', $this->owner->id)
        ->assertForbidden();

    expect($this->owner->fresh()->belongsToBusiness($this->business))->toBeTrue();
});

it('cannot remove yourself', function () {
    $this->actingAs($this->admin);
    session(['current_business_id' => $this->business->id]);

    Livewire::test('pages::settings.team')
        ->call('removeMember', $this->admin->id)
        ->assertForbidden();
});

it('re-checks authorization on every action', function () {
    $this->actingAs($this->admin);
    session(['current_business_id' => $this->business->id]);

    $component = Livewire::test('pages::settings.team');

    // Demoted mid-session: mount() passed, but actions must still refuse.
    $this->business->users()->updateExistingPivot($this->admin->id, ['role' => 'member']);

    $component->call('removeMember', $this->member->id)
        ->assertForbidden();

    expect($this->member->fresh()->belongsToBusiness($this->business))->toBeTrue();
});

it('redirects a removed member with a stale session instead of erroring', function () {
    $this->business->users()->detach($this->member->id);

    $this->actingAs($this->member)
        ->withSession(['current_business_id' => $this->business->id])
        ->get(route('dashboard'))
        ->assertRedirect(route('portal.select-business'));
});

it('prefers the owner email for stripe', function () {
    $business = Business::factory()->create();

    $member = User::factory()->create(['email' => 'member@example.com']);
    $business->users()->attach($member->id, ['role' => 'member']);

    $owner = User::factory()->create(['email' => 'owner@example.com']);
    $business->users()->attach($owner->id, ['role' => 'owner']);

    expect($business->stripeEmail())->toBe('owner@example.com');
});
