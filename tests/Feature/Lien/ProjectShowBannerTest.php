<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Livewire\ProjectShow;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienStateRuleSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['timezone' => 'America/Los_Angeles']);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

describe('next deadline banner', function () {
    it('does not show deadline unknown in the banner when no dates are set', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'OH',
            'first_furnish_date' => null,
            'last_furnish_date' => null,
        ]);

        app(DeadlineCalculator::class)->calculateForProject($project);

        Livewire::test(ProjectShow::class, ['project' => $project])
            ->assertDontSee('deadline unknown');
    });

    it('shows the first required step with a known deadline in the banner', function () {
        Carbon::setTestNow(Carbon::create(2026, 1, 5));

        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'OH',
            'last_furnish_date' => Carbon::create(2026, 1, 1),
        ]);

        app(DeadlineCalculator::class)->calculateForProject($project);

        Livewire::test(ProjectShow::class, ['project' => $project])
            ->assertSee('Mechanics Lien')
            ->assertDontSee('Lien Release deadline unknown');

        Carbon::setTestNow();
    });
});
