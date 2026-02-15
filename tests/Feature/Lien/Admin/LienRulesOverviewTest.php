<?php

use App\Domains\Lien\Admin\Livewire\LienRulesOverview;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienStateRuleSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo('lien.view');
});

describe('lien rules overview page', function () {
    it('renders for an admin with lien.view permission', function () {
        $this->actingAs($this->admin);

        Livewire::test(LienRulesOverview::class)
            ->assertStatus(200)
            ->assertSee('Lien Rules Overview');
    });

    it('displays state cards with state codes', function () {
        $this->actingAs($this->admin);

        Livewire::test(LienRulesOverview::class)
            ->assertSee('FL')
            ->assertSee('CA')
            ->assertSee('TX');
    });

    it('shows claimant rights section', function () {
        $this->actingAs($this->admin);

        Livewire::test(LienRulesOverview::class)
            ->assertSee('Claimant Rights');
    });

    it('shows preliminary notice section', function () {
        $this->actingAs($this->admin);

        Livewire::test(LienRulesOverview::class)
            ->assertSee('Preliminary Notice');
    });

    it('shows mechanics lien deadlines section', function () {
        $this->actingAs($this->admin);

        Livewire::test(LienRulesOverview::class)
            ->assertSee('Mechanics Lien Deadlines');
    });

    it('shows filing requirements section', function () {
        $this->actingAs($this->admin);

        Livewire::test(LienRulesOverview::class)
            ->assertSee('Filing Requirements');
    });

    it('redirects unauthenticated users', function () {
        $this->get(route('admin.liens.lien-rules-overview'))
            ->assertRedirect(route('login'));
    });

    it('forbids users without lien.view permission', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.liens.lien-rules-overview'))
            ->assertForbidden();
    });

    it('accepts a state query parameter', function () {
        $this->actingAs($this->admin);

        Livewire::withQueryParams(['state' => 'FL'])
            ->test(LienRulesOverview::class)
            ->assertStatus(200)
            ->assertSet('selectedState', 'FL');
    });
});
