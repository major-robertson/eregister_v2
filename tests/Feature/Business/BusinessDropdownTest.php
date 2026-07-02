<?php

use App\Domains\Business\Livewire\BusinessDropdown;
use App\Domains\Business\Models\Business;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->business = Business::factory()->create([
        'name' => 'First Business',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);

    $this->otherBusiness = Business::factory()->create([
        'name' => 'Second Business',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->otherBusiness->id, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

it('keeps the user in the workspace they are standing in when switching', function () {
    Livewire::test(BusinessDropdown::class)
        ->set('pageRouteName', 'lien.projects.show')
        ->call('switchBusiness', $this->otherBusiness->id)
        ->assertRedirect(route('lien.dashboard'));

    expect(session('current_business_id'))->toBe($this->otherBusiness->id);
});

it('redirects to the sales tax dashboard when switching from a sales tax page', function () {
    Livewire::test(BusinessDropdown::class)
        ->set('pageRouteName', 'sales-tax.registrations.start')
        ->call('switchBusiness', $this->otherBusiness->id)
        ->assertRedirect(route('sales-tax.dashboard'));
});

it('redirects to onboarding when the target business has incomplete onboarding', function () {
    $incomplete = Business::factory()->create([
        'name' => 'Incomplete Business',
        'onboarding_completed_at' => null,
    ]);
    $this->user->businesses()->attach($incomplete->id, ['role' => 'owner']);

    Livewire::test(BusinessDropdown::class)
        ->set('pageRouteName', 'lien.projects.index')
        ->call('switchBusiness', $incomplete->id)
        ->assertRedirect(route('portal.onboarding'));
});

it('falls back to the portal dashboard when switching outside a workspace', function () {
    Livewire::test(BusinessDropdown::class)
        ->set('pageRouteName', 'dashboard')
        ->call('switchBusiness', $this->otherBusiness->id)
        ->assertRedirect(route('dashboard'));
});

it('sends record-scoped pages to the hub instead of reloading into a 403', function () {
    // portal.checkout/{application} shows a record of the OLD business —
    // reloading it after the switch would abort(403).
    Livewire::test(BusinessDropdown::class)
        ->set('pageRouteName', 'portal.checkout')
        ->set('pageHasRouteParameters', true)
        ->call('switchBusiness', $this->otherBusiness->id)
        ->assertRedirect(route('dashboard'));
});

it('rejects switching to a business the user does not belong to', function () {
    $foreign = Business::factory()->create(['name' => 'Foreign Business']);

    Livewire::test(BusinessDropdown::class)
        ->call('switchBusiness', $foreign->id)
        ->assertHasErrors('business');

    expect(session('current_business_id'))->toBe($this->business->id);
});

it('creates a business inline, switches to it, and sends the user to onboarding', function () {
    Livewire::test(BusinessDropdown::class)
        ->set('newBusinessName', 'Brand New LLC')
        ->call('createBusiness')
        ->assertRedirect(route('portal.onboarding'));

    $created = Business::query()->where('name', 'Brand New LLC')->sole();

    expect(session('current_business_id'))->toBe($created->id)
        ->and($this->user->businesses()->where('businesses.id', $created->id)->exists())->toBeTrue();
});

it('validates the new business name', function () {
    Livewire::test(BusinessDropdown::class)
        ->set('newBusinessName', 'x')
        ->call('createBusiness')
        ->assertHasErrors(['newBusinessName' => 'min']);
});
