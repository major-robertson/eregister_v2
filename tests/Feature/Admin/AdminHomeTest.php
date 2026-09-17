<?php

use App\Models\User;

describe('admin home', function () {
    it('redirects guests to login', function () {
        $this->get(route('admin.home'))
            ->assertRedirect(route('login'));
    });

    it('sends each role to the first board it can open', function (string $role, string $board) {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('admin.home'))
            ->assertRedirect(route($board));
    })->with([
        'admin' => ['admin', 'admin.liens.board'],
        'lien agent' => ['lien_agent', 'admin.liens.board'],
        'viewer' => ['viewer', 'admin.liens.board'],
        'tax agent' => ['tax_agent', 'admin.sales-tax.board'],
        'llc agent' => ['llc_agent', 'admin.formations.board'],
    ]);

    it('forbids users who cannot open any board', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.home'))
            ->assertForbidden();
    });

    it('lands a sales tax agent on the sales tax board after login', function () {
        $user = User::factory()->create();
        $user->assignRole('tax_agent');

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.home'));

        $this->get(route('admin.home'))
            ->assertRedirect(route('admin.sales-tax.board'));

        $this->get(route('admin.sales-tax.board'))
            ->assertSuccessful()
            ->assertSee('Sales Tax Registrations Board');
    });
});

describe('sidebar', function () {
    it('hides the liens link from users without lien access', function () {
        $user = User::factory()->create();
        $user->assignRole('tax_agent');

        $this->actingAs($user)
            ->get(route('admin.sales-tax.board'))
            ->assertSuccessful()
            ->assertDontSee(route('admin.liens.board'), false);
    });

    it('shows the liens link to users with lien access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo('tax.view', 'lien.view');

        $this->actingAs($user)
            ->get(route('admin.sales-tax.board'))
            ->assertSuccessful()
            ->assertSee(route('admin.liens.board'), false);
    });
});
