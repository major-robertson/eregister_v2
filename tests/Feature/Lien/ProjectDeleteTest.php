<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Livewire\ProjectShow;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Livewire\Livewire;

/*
| EREG-80: deleting a project deletes its filings in the database (the foreign
| key cascades, past the filings' soft deletes), which lost eight paid orders.
| A project with a paid filing can't be deleted.
*/

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create();
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    $this->project = LienProject::factory()->forBusiness($this->business)->create(['jobsite_state' => 'OH']);
});

it('deletes a project whose filings were never paid', function () {
    $draft = LienFiling::factory()->forProject($this->project)->draft()->create();
    Payment::factory()->forPurchasable($draft)->create(['status' => PaymentStatus::Initiated]);

    Livewire::test(ProjectShow::class, ['project' => $this->project])
        ->call('deleteProject')
        ->assertRedirect(route('lien.projects.index'));

    expect(LienProject::find($this->project->id))->toBeNull()
        ->and(LienFiling::withTrashed()->find($draft->id))->toBeNull();
});

it('keeps a project with a paid filing, and says why', function () {
    $filing = LienFiling::factory()->forProject($this->project)->paid()->create();
    Payment::factory()->forPurchasable($filing)->succeeded()->create();

    Livewire::test(ProjectShow::class, ['project' => $this->project])
        ->assertSee('Delete (has paid filings)')
        ->assertDontSee('wire:click="deleteProject"', false)
        ->call('deleteProject')
        ->assertNoRedirect()
        ->assertDispatched('toast-show', dataset: ['variant' => 'warning']);

    expect(LienProject::find($this->project->id))->not->toBeNull()
        ->and(LienFiling::find($filing->id))->not->toBeNull();
});

it('counts a paid filing an admin already deleted', function () {
    $filing = LienFiling::factory()->forProject($this->project)->paid()->create();
    $filing->delete();

    expect($this->project->hasPaidFilings())->toBeTrue()
        ->and($this->user->can('delete', $this->project))->toBeFalse();
});

it('counts a payment held for review that has not marked the filing paid', function () {
    $filing = LienFiling::factory()->forProject($this->project)->draft()->create();
    Payment::factory()->forPurchasable($filing)->succeeded()->create(['requires_manual_review' => true]);

    expect($filing->fresh()->paid_at)->toBeNull()
        ->and($this->project->hasPaidFilings())->toBeTrue();
});

it('counts a refunded or processing payment, not an abandoned checkout', function (PaymentStatus $status, bool $blocks) {
    $filing = LienFiling::factory()->forProject($this->project)->draft()->create();
    Payment::factory()->forPurchasable($filing)->create(['status' => $status]);

    expect($this->project->hasPaidFilings())->toBe($blocks);
})->with([
    'refunded' => [PaymentStatus::Refunded, true],
    'processing' => [PaymentStatus::Processing, true],
    'initiated' => [PaymentStatus::Initiated, false],
    'failed' => [PaymentStatus::Failed, false],
]);

it('lets the owner delete a project with only drafts', function () {
    LienFiling::factory()->forProject($this->project)->draft()->create();

    expect($this->user->can('delete', $this->project))->toBeTrue();
});
