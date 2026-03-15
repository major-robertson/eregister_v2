<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingCheckout;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Mail\HawaiiAttorneyReferral;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create([
        'timezone' => 'Pacific/Honolulu',
        'lien_onboarding_completed_at' => now(),
        'phone' => '808-555-1234',
    ]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

function createHawaiiProject(object $context, string $state = 'HI'): void
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
        'address1' => '456 Kalakaua Ave',
        'city' => 'Honolulu',
        'state' => $state,
        'zip' => '96815',
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

describe('Hawaii mechanics lien restriction', function () {
    it('shows Hawaii court filing message on step 4 for HI mechanics lien', function () {
        createHawaiiProject($this);

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->lienDeadline,
        ])
            ->set('step', 4)
            ->assertSee('Hawaii Liens Must Be Filed at Court')
            ->assertSee('Connect Me with a Hawaii Attorney')
            ->assertDontSee('Choose Your Service Level')
            ->assertDontSee('Proceed to Payment');
    });

    it('blocks proceedToCheckout for Hawaii mechanics lien', function () {
        createHawaiiProject($this);

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->lienDeadline,
        ])
            ->set('step', 4)
            ->set('service_level', 'full_service')
            ->set('disclaimerAccepted', true)
            ->call('proceedToCheckout')
            ->assertForbidden();
    });

    it('sends referral email when attorney button is clicked', function () {
        Mail::fake();

        createHawaiiProject($this);

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->lienDeadline,
        ])
            ->set('step', 4)
            ->call('requestAttorneyReferral')
            ->assertSet('referralSent', true)
            ->assertSee('Your request has been sent');

        Mail::assertQueued(HawaiiAttorneyReferral::class, function ($mail) {
            return $mail->hasTo('contact@eregister.com')
                && $mail->firstName === $this->user->first_name
                && $mail->lastName === $this->user->last_name
                && $mail->userEmail === $this->user->email
                && $mail->phone === '808-555-1234';
        });
    });

    it('only sends referral email once', function () {
        Mail::fake();

        createHawaiiProject($this);

        $component = Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->lienDeadline,
        ]);

        $component->set('step', 4)
            ->call('requestAttorneyReferral')
            ->call('requestAttorneyReferral');

        Mail::assertQueuedCount(1);
    });

    it('blocks FilingCheckout for Hawaii mechanics lien', function () {
        createHawaiiProject($this);

        $this->filing->update(['status' => FilingStatus::AwaitingPayment]);

        Livewire::test(FilingCheckout::class, ['filing' => $this->filing])
            ->assertForbidden();
    });
});

describe('non-Hawaii lien filings are unaffected', function () {
    it('shows normal service level selection for non-HI states', function () {
        createHawaiiProject($this, 'AZ');

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->lienDeadline,
        ])
            ->set('step', 4)
            ->assertSee('Choose Your Service Level')
            ->assertDontSee('Hawaii Liens Must Be Filed at Court');
    });
});

describe('Hawaii non-lien documents are unaffected', function () {
    it('allows prelim notice filing in Hawaii', function () {
        createHawaiiProject($this);

        $prelimDeadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        if (! $prelimDeadline) {
            $this->markTestSkipped('No prelim_notice deadline for HI project.');
        }

        $filing = LienFiling::factory()->forProject($this->project)->create([
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
            ->assertDontSee('Hawaii Liens Must Be Filed at Court')
            ->assertSee('Choose Your Service Level');
    });
});
