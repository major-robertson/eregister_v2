<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Livewire\Waivers\WaiverWizard;
use App\Domains\Lien\Models\LienContact;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\WaiverNurture;
use App\Mail\WaiverStartedReminder;
use App\Mail\WaiverUnsignedNurture;
use App\Mail\WelcomeEmail;
use App\Models\EmailSequence;
use App\Models\EmailUnsubscribe;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/*
| Follow-up email for the lien waiver funnel:
|   waiver_started  - signed up from a waiver page, no waiver yet (day 1, day 3;
|                     the welcome email is the first touch)
|   waiver_unsigned - free plan saved a waiver (2 hours, day 2, day 7, day 21)
*/

if (! function_exists('waiverNurtureSubscribe')) {
    /** Active stub Pro subscription (no Stripe) with seats for the given members. */
    function waiverNurtureSubscribe(Business $business, User ...$seatHolders): void
    {
        $business->subscriptions()->create([
            'type' => config('lien_waivers.subscription_type'),
            'stripe_id' => 'stub_'.uniqid(),
            'stripe_status' => 'active',
            'stripe_price' => 'stub_price',
            'quantity' => max(1, count($seatHolders)),
        ]);

        foreach ($seatHolders as $seatHolder) {
            $business->users()->updateExistingPivot($seatHolder->id, ['lien_waiver_seat_at' => now()]);
        }
    }
}

if (! function_exists('waiverNurtureRegister')) {
    /** Register through the real endpoint, as a visitor who came from $landingPath. */
    function waiverNurtureRegister(\Illuminate\Foundation\Testing\TestCase $test, string $email, ?string $landingPath, ?array $intent = null): User
    {
        $test->withSession(array_filter([
            'signup_landing_path' => $landingPath,
            'waiver_intent' => $intent,
        ]))->post(route('register.store'), [
            'first_name' => 'Wendy',
            'last_name' => 'Waiver',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        return User::where('email', $email)->firstOrFail();
    }
}

if (! function_exists('waiverNurtureSequence')) {
    function waiverNurtureSequence(LienWaiver $waiver, User $user, Business $business): EmailSequence
    {
        return EmailSequence::factory()->create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'sequence_type' => 'waiver_unsigned',
            'sequenceable_type' => $waiver->getMorphClass(),
            'sequenceable_id' => $waiver->id,
            'next_send_at' => now()->subMinute(),
        ]);
    }
}

beforeEach(function () {
    $this->travelTo(now()->startOfMonth()->addDays(14)->setTime(12, 0));

    Storage::fake('s3');
});

describe('signup from a waiver page', function () {
    it('starts the finish-your-waiver emails with a link back to the same state and type', function () {
        Mail::fake();

        $user = waiverNurtureRegister($this, 'wendy@example.com', '/lp/lien-waiver/tx', [
            'state' => 'TX', 'direction' => 'provide', 'kind' => 'conditional_progress', 'source' => '/lp/lien-waiver/tx',
        ]);

        $sequence = EmailSequence::where('sequence_type', 'waiver_started')->sole();

        expect($sequence->user_id)->toBe($user->id);
        expect($sequence->business_id)->toBeNull();
        expect($sequence->sequenceable_type)->toBe('user');
        // Day 1: the welcome email is the first-hour touch.
        expect($sequence->next_send_at->equalTo(now()->addDay()))->toBeTrue();

        expect($sequence->resume_url)
            ->toContain('/liens/lien-waivers/start')
            ->toContain('state=tx')
            ->toContain('direction=provide')
            ->toContain('kind=conditional_progress')
            ->toContain('returning=1');
    });

    it('does nothing for signups from anywhere else', function () {
        Mail::fake();

        waiverNurtureRegister($this, 'larry@example.com', '/llc');

        expect(EmailSequence::count())->toBe(0);
    });

    it('falls back to the wizard when the visitor never used the starter', function () {
        $user = User::factory()->create(['signup_landing_path' => '/liens/lien-waivers']);

        expect(WaiverNurture::onSignup($user)->resume_url)->toBe(route('lien.waivers.create'));
    });

    it('sends the link back into the wizard through login, not the register page', function () {
        $url = WaiverNurture::resumeUrl(['state' => 'TX', 'direction' => 'collect', 'kind' => null, 'source' => null]);

        $this->get($url)->assertRedirect(route('login'));

        // After login they come back through the same link (query order aside).
        parse_str((string) parse_url(session('url.intended'), PHP_URL_QUERY), $intended);

        expect(session('url.intended'))->toContain('/liens/lien-waivers/start');
        expect($intended)->toEqualCanonicalizing(['state' => 'tx', 'direction' => 'collect', 'returning' => '1']);
        expect(session('waiver_intent')['state'])->toBe('TX');

        // A fresh visitor on the marketing page still goes to register.
        $this->get(route('liens.lien-waivers.start', ['state' => 'tx']))->assertRedirect(route('register'));
    });
});

describe('welcome email', function () {
    it('links a waiver signup back to their waiver', function () {
        $user = User::factory()->create(['first_name' => 'Wendy', 'signup_landing_path' => '/lp/lien-waiver/tx']);
        WaiverNurture::onSignup($user);

        $mailable = new WelcomeEmail($user);

        $mailable->assertHasSubject('Welcome to eRegister. Here is your lien waiver link');
        $mailable->assertSeeInHtml('Wendy');
        $mailable->assertSeeInHtml('Create my waiver');
        $mailable->assertSeeInHtml(route('lien.waivers.create'), false);
    });

    it('stays the plain welcome once the waiver exists, and for every other signup', function () {
        $business = Business::factory()->create();
        $project = LienProject::factory()->forBusiness($business)->inState('TX')->create();

        $waiverSignup = User::factory()->create(['signup_landing_path' => '/lp/lien-waiver/tx']);
        LienWaiver::factory()->forProject($project)->create(['created_by_user_id' => $waiverSignup->id]);

        (new WelcomeEmail($waiverSignup))->assertHasSubject('Welcome to eRegister');
        (new WelcomeEmail($waiverSignup))->assertDontSeeInHtml('Create my waiver');

        (new WelcomeEmail(User::factory()->create(['signup_landing_path' => '/llc'])))
            ->assertHasSubject('Welcome to eRegister');
    });
});

describe('finish-your-waiver sequence', function () {
    beforeEach(function () {
        $this->user = User::factory()->create(['first_name' => 'Wendy', 'signup_landing_path' => '/lp/lien-waiver/tx']);

        session(['waiver_intent' => ['state' => 'TX', 'direction' => 'provide', 'kind' => null, 'source' => null]]);
        $this->sequence = WaiverNurture::onSignup($this->user);
        session()->forget('waiver_intent');
    });

    it('sends day 1 then day 3, then completes', function () {
        Mail::fake();

        $this->travel(1)->days();
        $this->artisan('email:process-sequences');

        Mail::assertQueued(WaiverStartedReminder::class, fn ($mail) => $mail->step === 1 && $mail->hasTo($this->user->email));
        expect($this->sequence->refresh()->next_send_at->equalTo(now()->addDays(2)))->toBeTrue();

        $this->travel(2)->days();
        $this->artisan('email:process-sequences');

        Mail::assertQueued(WaiverStartedReminder::class, fn ($mail) => $mail->step === 2);
        expect($this->sequence->refresh()->completed_at)->not->toBeNull();
        expect(SentEmail::where('email_type', 'like', 'waiver_started_step_%')->count())->toBe(2);
    });

    it('stops as soon as they have made a waiver', function () {
        Mail::fake();

        $business = Business::factory()->create();
        $project = LienProject::factory()->forBusiness($business)->inState('TX')->create();
        LienWaiver::factory()->forProject($project)->create(['created_by_user_id' => $this->user->id]);

        $this->travel(1)->days();
        $this->artisan('email:process-sequences');

        Mail::assertNothingQueued();
        expect($this->sequence->refresh()->suppression_reason)->toBe('waiver_created');
    });

    it('honors a marketing unsubscribe and a bounced address', function () {
        Mail::fake();

        EmailUnsubscribe::unsubscribe($this->user, EmailUnsubscribe::CATEGORY_MARKETING);

        $this->travel(1)->days();
        $this->artisan('email:process-sequences');

        Mail::assertNothingQueued();
        expect($this->sequence->refresh()->suppression_reason)->toBe('unsubscribed');

        $bounced = User::factory()->create(['signup_landing_path' => '/lp/lien-waiver', 'email_bounced_at' => now()]);
        $bouncedSequence = WaiverNurture::onSignup($bounced);

        $this->travel(1)->days();
        $this->artisan('email:process-sequences');

        Mail::assertNothingQueued();
        expect($bouncedSequence->refresh()->suppression_reason)->toBe('email_bounced');
    });

    it('names the state, links back, and carries the unsubscribe footer', function () {
        config(['mail.postal_address' => '100 Main St, Springfield, IL 62701']);

        $mailable = new WaiverStartedReminder($this->sequence, 1);

        $mailable->assertHasSubject('Finish your Texas lien waiver (about 2 minutes)');
        $mailable->assertSeeInHtml('Wendy');
        $mailable->assertSeeInHtml('Finish my waiver');
        $mailable->assertSeeInHtml('state=tx', false);
        $mailable->assertSeeInHtml('Manage email preferences or unsubscribe');
        $mailable->assertSeeInHtml('100 Main St, Springfield, IL 62701');

        (new WaiverStartedReminder($this->sequence, 2))->assertHasSubject('Do you still need a Texas lien waiver?');
    });

    it('leaves the postal address line out until one is configured', function () {
        config(['mail.postal_address' => null]);

        (new WaiverStartedReminder($this->sequence, 1))
            ->assertSeeInHtml('Manage email preferences or unsubscribe')
            ->assertDontSeeInHtml('eRegister, ');
    });
});

describe('saving a waiver on the free plan', function () {
    beforeEach(function () {
        $this->user = User::factory()->create(['first_name' => 'Wendy', 'email_verified_at' => now()]);
        $this->business = Business::factory()->create([
            'onboarding_completed_at' => now(),
            'lien_onboarding_completed_at' => now(),
        ]);
        $this->business->users()->attach($this->user, ['role' => 'owner']);

        $this->actingAs($this->user);
        session(['current_business_id' => $this->business->id]);

        // Drive the real wizard to review, which auto-saves the waiver.
        $this->reachReview = function (string $state = 'TX') {
            $project = LienProject::factory()->forBusiness($this->business)->inState($state)->create([
                'wizard_completed_at' => now(),
            ]);
            LienParty::factory()->forProject($project)->asOwner()->create(['name' => 'Olive Owner']);

            $contact = LienContact::create([
                'created_by_user_id' => $this->user->id,
                'company_name' => 'Counterparty Builders LLC',
                'email' => 'counterparty@builders.test',
            ]);

            return Livewire::test(WaiverWizard::class)
                ->call('selectDirection', 'provide')
                ->call('nextStep')
                ->set('projectId', $project->public_id)
                ->call('nextStep')
                ->call('selectKind', 'conditional_progress')
                ->call('nextStep')
                ->set('amount', '1000.00')
                ->set('through_date', now()->format('Y-m-d'))
                ->set('contactId', (string) $contact->id)
                ->call('nextStep')
                ->assertSet('step', 5);
        };
    });

    it('starts the series for the saved waiver, two hours out', function () {
        ($this->reachReview)();

        $waiver = LienWaiver::sole();
        $sequence = EmailSequence::where('sequence_type', 'waiver_unsigned')->sole();

        expect($sequence->sequenceable_type)->toBe('lien_waiver');
        expect($sequence->sequenceable_id)->toBe($waiver->id);
        expect($sequence->user_id)->toBe($this->user->id);
        expect($sequence->business_id)->toBe($this->business->id);
        expect($sequence->next_send_at->equalTo(now()->addHours(2)))->toBeTrue();
    });

    it('runs one series per person at a time', function () {
        ($this->reachReview)();
        ($this->reachReview)();

        expect(LienWaiver::count())->toBe(2);
        expect(EmailSequence::where('sequence_type', 'waiver_unsigned')->count())->toBe(1);
    });

    it('skips Pro seat holders and states that require signing on paper', function () {
        ($this->reachReview)('GA');
        expect(EmailSequence::count())->toBe(0);

        waiverNurtureSubscribe($this->business, $this->user);
        ($this->reachReview)('TX');

        expect(LienWaiver::count())->toBe(2);
        expect(EmailSequence::count())->toBe(0);
    });
});

describe('free-plan series', function () {
    beforeEach(function () {
        $this->user = User::factory()->create(['first_name' => 'Wendy']);
        $this->business = Business::factory()->create();
        $this->business->users()->attach($this->user, ['role' => 'owner']);

        $this->project = LienProject::factory()->forBusiness($this->business)->inState('CA')->create([
            'name' => 'Market Street Lofts',
            'claimant_type' => 'subcontractor',
            'wizard_completed_at' => now(),
        ]);

        $this->waiver = LienWaiver::factory()->forProject($this->project)->generated()->create([
            'created_by_user_id' => $this->user->id,
            'counterparty_company' => 'Bay Builders Inc',
        ]);

        $this->sequence = waiverNurtureSequence($this->waiver, $this->user, $this->business);
    });

    it('sends 2 hours, day 2, day 7 and day 21, then completes', function () {
        Mail::fake();

        $expectedGaps = [2 => now()->addDays(2), 3 => now()->addDays(7), 4 => now()->addDays(21)];

        foreach ([1, 2, 3, 4] as $step) {
            $this->artisan('email:process-sequences');

            Mail::assertQueued(WaiverUnsignedNurture::class, fn ($mail) => $mail->step === $step && $mail->hasTo($this->user->email));

            if ($step < 4) {
                $this->travelTo($this->sequence->refresh()->next_send_at->addMinute());
            }
        }

        expect($this->sequence->refresh()->completed_at)->not->toBeNull();
        expect(SentEmail::where('email_type', 'like', 'waiver_unsigned_step_%')->count())->toBe(4);

        // 2h + 2d + 5d + 14d after the save (give or take the minute added per hop).
        expect((int) round($this->sequence->created_at->diffInDays(now())))->toBe(21);
    });

    it('stops when they subscribe, void or delete the waiver, or unsubscribe', function (Closure $change, string $reason) {
        Mail::fake();

        $change($this);

        $this->artisan('email:process-sequences');

        Mail::assertNothingQueued();
        expect($this->sequence->refresh()->suppression_reason)->toBe($reason);
    })->with([
        'subscribed' => [fn ($test) => waiverNurtureSubscribe($test->business, $test->user), 'subscribed'],
        'voided' => [fn ($test) => $test->waiver->update(['status' => WaiverStatus::Voided, 'voided_at' => now()]), 'waiver_voided'],
        'deleted' => [fn ($test) => $test->waiver->delete(), 'sequenceable_deleted'],
        'unsubscribed' => [fn ($test) => EmailUnsubscribe::unsubscribe($test->user, EmailUnsubscribe::CATEGORY_MARKETING), 'unsubscribed'],
    ]);

    it('pitches signing your own waiver, with the Pro price', function () {
        $mailable = new WaiverUnsignedNurture($this->sequence, 1);

        $mailable->assertHasSubject('Your California waiver is ready');
        $mailable->assertSeeInHtml('Market Street Lofts');
        $mailable->assertSeeInHtml('Sign my waiver online');
        $mailable->assertSeeInHtml('$49 a month per person. Cancel anytime.');
        $mailable->assertSeeInHtml(route('lien.waivers.show', $this->waiver), false);
        $mailable->assertSeeInHtml('Manage email preferences or unsubscribe');
    });

    it('pitches collecting a signature when the waiver is for someone else to sign', function () {
        $waiver = LienWaiver::factory()->forProject($this->project)->collect()->generated()->create([
            'created_by_user_id' => $this->user->id,
            'counterparty_company' => 'Vendor Concrete LLC',
        ]);

        $mailable = new WaiverUnsignedNurture(waiverNurtureSequence($waiver, $this->user, $this->business), 1);

        $mailable->assertHasSubject('Your California waiver is ready to send');
        $mailable->assertSeeInHtml('Vendor Concrete LLC');
        $mailable->assertSeeInHtml('Send it for signature');
    });

    it('never says "sign your waiver" once it was signed on paper', function () {
        $this->waiver->update(['status' => WaiverStatus::Signed, 'signed_at' => now()]);

        $first = new WaiverUnsignedNurture($this->sequence->refresh(), 1);
        $first->assertDontSeeInHtml('Sign my waiver online');
        $first->assertSeeInHtml('View my waiver');

        (new WaiverUnsignedNurture($this->sequence, 2))->assertSeeInHtml('Create my next waiver');
    });

    it('explains that an e-signed waiver is legal', function () {
        $mailable = new WaiverUnsignedNurture($this->sequence, 2);

        $mailable->assertHasSubject('Is an e-signed lien waiver legal?');
        $mailable->assertSeeInHtml('ESIGN Act');
        $mailable->assertSeeInHtml('signing record');
        $mailable->assertSeeInHtml('We offer e-signing for waivers in California.');
    });

    it('names the real deadline on day 7 when the project has its first day on the job', function () {
        $this->project->update(['first_furnish_date' => today()->subDays(5)]);
        app(DeadlineCalculator::class)->calculateForProject($this->project->fresh());

        $mailable = new WaiverUnsignedNurture($this->sequence, 3);

        // California subcontractor: preliminary notice is due 20 days after first furnishing.
        $due = today()->subDays(5)->addDays(20);

        $mailable->assertHasSubject('Your Preliminary Notice is due '.$due->format('M j'));
        $mailable->assertSeeInHtml('Preliminary Notice:');
        $mailable->assertSeeInHtml('due '.$due->format('F j, Y'));
        $mailable->assertSeeInHtml('15 days left');
        $mailable->assertSeeInHtml('See my deadlines');
        $mailable->assertSeeInHtml(route('lien.projects.show', $this->project), false);
    });

    it('asks for the job dates on day 7 when there is nothing to calculate from', function () {
        app(DeadlineCalculator::class)->calculateForProject($this->project->fresh());

        $mailable = new WaiverUnsignedNurture($this->sequence, 3);

        $mailable->assertHasSubject("What if you don't get paid on this job?");
        $mailable->assertSeeInHtml('Add my job dates');
        $mailable->assertDontSeeInHtml('coming up, based on');
    });

    it('invites the next waiver on day 21', function () {
        $mailable = new WaiverUnsignedNurture($this->sequence, 4);

        $mailable->assertHasSubject('Need another lien waiver?');
        $mailable->assertSeeInHtml('Create my next waiver');
        $mailable->assertSeeInHtml(route('lien.waivers.create'), false);
    });
});

describe('first day on the job (inline project form)', function () {
    beforeEach(function () {
        $this->user = User::factory()->create(['signup_landing_path' => '/lp/lien-waiver/ca']);
        $this->business = Business::factory()->create(['onboarding_completed_at' => now()]);
        $this->business->users()->attach($this->user, ['role' => 'owner']);

        $this->actingAs($this->user);
        session(['current_business_id' => $this->business->id]);
        session(['waiver_intent' => ['state' => 'CA', 'direction' => 'provide', 'kind' => 'conditional_progress', 'source' => null]]);
    });

    it('is optional, and when given it produces a real notice deadline', function () {
        Livewire::test(WaiverWizard::class)
            ->assertSee('First day on this job')
            ->set('project_address1', '1 Market St')
            ->set('project_city', 'San Francisco')
            ->set('project_property_class', 'commercial')
            ->set('project_role', 'subcontractor')
            ->set('project_first_furnish_date', today()->subDays(3)->format('Y-m-d'))
            ->call('createProject')
            ->assertHasNoErrors();

        $project = LienProject::sole();

        expect($project->first_furnish_date->isSameDay(today()->subDays(3)))->toBeTrue();

        $prelim = $project->deadlines()->whereHas('documentType', fn ($query) => $query->where('slug', 'prelim_notice'))->sole();

        expect($prelim->due_date->isSameDay(today()->subDays(3)->addDays(20)))->toBeTrue();
    });

    it('rejects a date in the future and saves fine without one', function () {
        $component = Livewire::test(WaiverWizard::class)
            ->set('project_address1', '1 Market St')
            ->set('project_city', 'San Francisco')
            ->set('project_property_class', 'commercial')
            ->set('project_role', 'subcontractor')
            ->set('project_first_furnish_date', today()->addWeek()->format('Y-m-d'))
            ->call('createProject')
            ->assertHasErrors('project_first_furnish_date');

        expect(LienProject::count())->toBe(0);

        $component->set('project_first_furnish_date', null)
            ->call('createProject')
            ->assertHasNoErrors();

        expect(LienProject::sole()->first_furnish_date)->toBeNull();
    });
});
