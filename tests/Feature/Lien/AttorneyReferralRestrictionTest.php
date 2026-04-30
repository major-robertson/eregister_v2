<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingCheckout;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Mail\AttorneyReferral;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create([
        'lien_onboarding_completed_at' => now(),
        'phone' => '555-555-1234',
    ]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

function createReferralStateProject(object $context, string $state): void
{
    $context->project = LienProject::factory()->forBusiness($context->business)->create([
        'jobsite_state' => $state,
        'first_furnish_date' => now()->subDays(30),
        'last_furnish_date' => now()->subDays(5),
        'completion_date' => now()->subDays(3),
        'property_class' => 'residential',
    ]);

    app(\App\Domains\Lien\Engine\DeadlineCalculator::class)->calculateForProject($context->project);

    LienParty::create([
        'business_id' => $context->business->id,
        'project_id' => $context->project->id,
        'role' => 'owner',
        'name' => 'Test Owner',
        'address1' => '123 Main St',
        'city' => 'Anytown',
        'state' => $state,
        'zip' => '12345',
    ]);

    $context->lienDeadline = $context->project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    $context->filing = LienFiling::factory()->forProject($context->project)->create([
        'document_type_id' => $context->lienDeadline->document_type_id,
        'project_deadline_id' => $context->lienDeadline->id,
        'status' => FilingStatus::Draft,
        'created_by_user_id' => $context->user->id,
    ]);
}

dataset('attorney_referral_states', [
    'Hawaii' => ['HI', 'Hawaii'],
    'Maryland' => ['MD', 'Maryland'],
    'Delaware' => ['DE', 'Delaware'],
]);

describe('Attorney-referral states block self-serve mechanics lien filing', function () {
    it('shows attorney-referral message on step 4 for the mechanics lien wizard', function (string $state, string $stateName) {
        createReferralStateProject($this, $state);

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->lienDeadline,
        ])
            ->set('step', 4)
            ->assertSee("{$stateName} Liens Must Be Filed at Court")
            ->assertSee("Connect Me with a {$stateName} Attorney")
            ->assertDontSee('Choose Your Service Level')
            ->assertDontSee('Proceed to Payment');
    })->with('attorney_referral_states');

    it('blocks proceedToCheckout in the wizard', function (string $state, string $stateName) {
        createReferralStateProject($this, $state);

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->lienDeadline,
        ])
            ->set('step', 4)
            ->set('service_level', 'full_service')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertForbidden();
    })->with('attorney_referral_states');

    it('blocks FilingCheckout entry', function (string $state, string $stateName) {
        createReferralStateProject($this, $state);

        $this->filing->update(['status' => FilingStatus::AwaitingPayment]);

        Livewire::test(FilingCheckout::class, ['filing' => $this->filing])
            ->assertForbidden();
    })->with('attorney_referral_states');

    it('sends a state-aware referral email when the attorney button is clicked', function (string $state, string $stateName) {
        Mail::fake();

        createReferralStateProject($this, $state);

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->lienDeadline,
        ])
            ->set('step', 4)
            ->call('requestAttorneyReferral')
            ->assertSet('referralSent', true)
            ->assertSee('Your request has been sent');

        Mail::assertQueued(AttorneyReferral::class, function ($mail) use ($state, $stateName) {
            return $mail->hasTo('contact@eregister.com')
                && $mail->firstName === $this->user->first_name
                && $mail->lastName === $this->user->last_name
                && $mail->userEmail === $this->user->email
                && $mail->stateCode === $state
                && $mail->stateName === $stateName
                && $mail->phone === '555-555-1234';
        });
    })->with('attorney_referral_states');

    it('only sends the referral email once per session', function (string $state, string $stateName) {
        Mail::fake();

        createReferralStateProject($this, $state);

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->lienDeadline,
        ])
            ->set('step', 4)
            ->call('requestAttorneyReferral')
            ->call('requestAttorneyReferral');

        Mail::assertQueuedCount(1);
    })->with('attorney_referral_states');
});

describe('non-referral state lien filings are unaffected', function () {
    it('shows normal service level selection for non-referral states', function () {
        createReferralStateProject($this, 'AZ');

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->lienDeadline,
        ])
            ->set('step', 4)
            ->assertSee('Choose Your Service Level')
            ->assertDontSee('Liens Must Be Filed at Court');
    });
});

describe('non-lien documents in attorney-referral states are unaffected', function () {
    it('allows prelim notice filing in attorney-referral states', function (string $state, string $stateName) {
        createReferralStateProject($this, $state);

        $prelimDeadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        if (! $prelimDeadline) {
            $this->markTestSkipped("No prelim_notice deadline for {$state} project.");
        }

        LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $prelimDeadline->document_type_id,
            'project_deadline_id' => $prelimDeadline->id,
            'status' => FilingStatus::Draft,
            'created_by_user_id' => $this->user->id,
        ]);

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $prelimDeadline,
        ])
            ->set('step', 4)
            ->assertDontSee('Liens Must Be Filed at Court')
            ->assertSee('Choose Your Service Level');
    })->with('attorney_referral_states');
});
