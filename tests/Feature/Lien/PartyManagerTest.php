<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Livewire\PartyManager;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create();
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    $this->project = LienProject::factory()->forBusiness($this->business)->create();
});

describe('PartyManager address requirement', function () {
    it('requires a full mailing address when adding a contractor', function () {
        Livewire::test(PartyManager::class, ['project' => $this->project])
            ->call('openModal')
            ->set('role', 'gc')
            ->set('name', 'Acme General Contractor')
            ->call('save')
            ->assertHasErrors([
                'address1' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required',
            ]);

        expect($this->project->parties()->count())->toBe(0);
    });

    it('saves a contractor once a full address is supplied', function () {
        Livewire::test(PartyManager::class, ['project' => $this->project])
            ->call('openModal')
            ->set('role', 'gc')
            ->set('name', 'Acme General Contractor')
            ->set('address1', '500 Builder Blvd')
            ->set('city', 'Mesa')
            ->set('state', 'az')
            ->set('zip', '85210')
            ->call('save')
            ->assertHasNoErrors();

        $party = $this->project->parties()->firstWhere('role', 'gc');
        expect($party)->not->toBeNull();
        expect($party->state)->toBe('AZ'); // normalized to uppercase
    });

    it('requires an address for a non-contractor party too', function () {
        Livewire::test(PartyManager::class, ['project' => $this->project])
            ->call('openModal')
            ->set('role', 'customer')
            ->set('name', 'Jane Homeowner')
            ->call('save')
            ->assertHasErrors([
                'address1' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required',
            ]);
    });
});
