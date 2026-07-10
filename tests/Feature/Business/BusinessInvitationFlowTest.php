<?php

use App\Domains\Business\Models\Business;
use App\Domains\Business\Models\BusinessInvitation;
use App\Models\User;

beforeEach(function () {
    $this->business = Business::factory()->onboarded()->create(['name' => 'Acme Contracting']);

    $this->owner = User::factory()->create();
    $this->owner->businesses()->attach($this->business->id, ['role' => 'owner']);

    $this->invitation = BusinessInvitation::factory()->create([
        'business_id' => $this->business->id,
        'email' => 'invitee@example.com',
        'role' => 'member',
        'invited_by_user_id' => $this->owner->id,
    ]);
});

it('redirects a guest with no account to register and stashes the invitation', function () {
    $this->get($this->invitation->acceptUrl())
        ->assertRedirect(route('register'));

    expect(session('url.intended'))->toBe($this->invitation->acceptUrl());
    expect(session('pending_business_invitation_id'))->toBe($this->invitation->id);

    // The register page prefills the invited email and names the business.
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('invitee@example.com')
        ->assertSee('Acme Contracting');
});

it('lets a brand-new invitee register and land on the dashboard without onboarding', function () {
    // Click the emailed signed link as a guest.
    $this->get($this->invitation->acceptUrl())->assertRedirect(route('register'));

    // Register with the invited email — bounced back to the signed accept
    // URL via redirect()->intended(), not to /portal.
    $this->post(route('register.store'), [
        'first_name' => 'New',
        'last_name' => 'Member',
        'email' => 'invitee@example.com',
        'password' => 'super-secret-password',
        'password_confirmation' => 'super-secret-password',
    ])->assertRedirect($this->invitation->acceptUrl());

    // The confirm page offers the accept button.
    $this->get($this->invitation->acceptUrl())
        ->assertOk()
        ->assertSee('Accept invitation');

    // Accept.
    $this->post(route('invitations.accept.store', $this->invitation))
        ->assertRedirect(route('dashboard'));

    $user = User::where('email', 'invitee@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->businesses()->first()->id)->toBe($this->business->id);
    expect($user->businesses()->first()->pivot->role)->toBe('member');
    expect(session('current_business_id'))->toBe($this->business->id);
    expect(BusinessInvitation::find($this->invitation->id))->toBeNull();

    // The dashboard renders — the invitee never sees create-a-business
    // or the onboarding wizard.
    $this->get(route('dashboard'))->assertOk();
});

it('redirects a guest whose email already has an account to login instead', function () {
    User::factory()->create(['email' => 'invitee@example.com']);

    $this->get($this->invitation->acceptUrl())
        ->assertRedirect(route('login'));

    expect(session('url.intended'))->toBe($this->invitation->acceptUrl());
});

it('lets an existing logged-in user accept the invitation', function () {
    $user = User::factory()->create(['email' => 'invitee@example.com']);

    $this->actingAs($user)
        ->get($this->invitation->acceptUrl())
        ->assertOk()
        ->assertSee('Accept invitation');

    $this->actingAs($user)
        ->post(route('invitations.accept.store', $this->invitation))
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->belongsToBusiness($this->business))->toBeTrue();
    expect(session('current_business_id'))->toBe($this->business->id);
    expect(BusinessInvitation::find($this->invitation->id))->toBeNull();
});

it('shows a mismatch state and rejects acceptance from a different account', function () {
    $other = User::factory()->create(['email' => 'other@example.com']);

    $this->actingAs($other)
        ->get($this->invitation->acceptUrl())
        ->assertOk()
        ->assertDontSee('Accept invitation')
        ->assertSee('other@example.com');

    $this->actingAs($other)
        ->post(route('invitations.accept.store', $this->invitation))
        ->assertForbidden();

    expect($other->belongsToBusiness($this->business))->toBeFalse();
    expect(BusinessInvitation::find($this->invitation->id))->not->toBeNull();
});

it('rejects an expired invitation link via the signed middleware', function () {
    $expired = BusinessInvitation::factory()->expired()->create([
        'business_id' => $this->business->id,
        'email' => 'late@example.com',
    ]);

    // The signed URL's signature expiry matches the invitation expiry.
    $this->get($expired->acceptUrl())->assertForbidden();
});

it('rejects acceptance of an invitation that expired after login', function () {
    $user = User::factory()->create(['email' => 'invitee@example.com']);

    $this->travel(8)->days();

    $this->actingAs($user)
        ->post(route('invitations.accept.store', $this->invitation))
        ->assertStatus(410);

    expect($user->belongsToBusiness($this->business))->toBeFalse();
});

it('404s when the invitation has been revoked', function () {
    $url = $this->invitation->acceptUrl();

    $this->invitation->delete();

    $this->get($url)->assertNotFound();
});

it('403s for an unsigned accept URL', function () {
    $this->get(route('invitations.accept', $this->invitation))->assertForbidden();
});

it('consumes the invitation without duplicating membership for an existing member', function () {
    $user = User::factory()->create(['email' => 'invitee@example.com']);
    $user->businesses()->attach($this->business->id, ['role' => 'member']);

    $this->actingAs($user)
        ->post(route('invitations.accept.store', $this->invitation))
        ->assertRedirect(route('dashboard'));

    expect($user->businesses()->where('businesses.id', $this->business->id)->count())->toBe(1);
    expect(BusinessInvitation::find($this->invitation->id))->toBeNull();
});
