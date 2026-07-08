<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Livewire\Waivers\WaiverDashboard;
use App\Domains\Lien\Livewire\Waivers\WaiverList;
use App\Domains\Lien\Livewire\Waivers\WaiverShow;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

if (! function_exists('waiverPortalProject')) {
    function waiverPortalProject(Business $business, string $state = 'TX'): LienProject
    {
        return LienProject::factory()->forBusiness($business)->inState($state)->create([
            'wizard_completed_at' => now(),
        ]);
    }
}

if (! function_exists('waiverPortalSubscribe')) {
    /** Give the business an active Waiver Pro subscription (stub row, no Stripe). */
    function waiverPortalSubscribe(Business $business): void
    {
        $business->subscriptions()->create([
            'type' => config('lien_waivers.subscription_type'),
            'stripe_id' => 'stub_'.uniqid(),
            'stripe_status' => 'active',
            'stripe_price' => 'stub_price',
            'quantity' => 1,
        ]);
    }
}

beforeEach(function () {
    // Pin mid-month so "this month" counts can't straddle a boundary.
    $this->travelTo(now()->startOfMonth()->addDays(14)->setTime(12, 0));

    Storage::fake('s3');

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create([
        'onboarding_completed_at' => now(),
        'lien_onboarding_completed_at' => now(),
    ]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

describe('dashboard', function () {
    it('renders with status counts', function () {
        $project = waiverPortalProject($this->business);

        LienWaiver::factory()->forProject($project)->create();              // Draft
        LienWaiver::factory()->forProject($project)->generated()->create(); // counts in the drafts tile
        LienWaiver::factory()->forProject($project)->collect()->create([
            'status' => WaiverStatus::AwaitingSignature,
            'sent_at' => now(),
        ]);
        LienWaiver::factory()->forProject($project)->signed()->create();

        $this->get(route('lien.waivers.index'))->assertSuccessful();

        $component = Livewire::test(WaiverDashboard::class)
            ->assertSee('Recent Waivers');

        expect($component->viewData('draftCount'))->toBe(2);
        expect($component->viewData('awaitingCount'))->toBe(1);
        expect($component->viewData('signedThisMonthCount'))->toBe(1);
    });

    it('shows the free-save meter to unsubscribed businesses and hides it for subscribers', function () {
        $project = waiverPortalProject($this->business);
        LienWaiver::factory()->count(2)->forProject($project)->create();

        $unsubscribed = Livewire::test(WaiverDashboard::class)
            ->assertSee('2 of 4 free saves used this month');

        expect($unsubscribed->viewData('hasPaidAccess'))->toBeFalse();
        expect($unsubscribed->viewData('savedThisMonth'))->toBe(2);
        expect($unsubscribed->viewData('freeSavesLimit'))->toBe(4);

        waiverPortalSubscribe($this->business);

        $subscribed = Livewire::test(WaiverDashboard::class)
            ->assertDontSee('free saves used this month');

        expect($subscribed->viewData('hasPaidAccess'))->toBeTrue();
    });

    it('lists signed GA waivers approaching their deemed-effective date', function () {
        $project = waiverPortalProject($this->business, 'GA');

        LienWaiver::factory()->forProject($project)->signed()->create([
            'counterparty_company' => 'Peach State Drywall',
            'deemed_effective_at' => now()->addDays(20)->toDateString(),
        ]);

        Livewire::test(WaiverDashboard::class)
            ->assertSee('Deemed Effective Soon')
            ->assertSee('Peach State Drywall');
    });
});

describe('waiver list', function () {
    it('filters by status', function () {
        $project = waiverPortalProject($this->business);

        LienWaiver::factory()->forProject($project)->signed()->create([
            'counterparty_company' => 'Signed Sign Co',
        ]);
        LienWaiver::factory()->forProject($project)->generated()->create([
            'counterparty_company' => 'Draft Drywall Co',
        ]);

        Livewire::test(WaiverList::class)
            ->assertSee('Signed Sign Co')
            ->assertSee('Draft Drywall Co')
            ->set('statusFilter', WaiverStatus::Signed->value)
            ->assertSee('Signed Sign Co')
            ->assertDontSee('Draft Drywall Co')
            ->set('statusFilter', WaiverStatus::Generated->value)
            ->assertSee('Draft Drywall Co')
            ->assertDontSee('Signed Sign Co');
    });

    it('filters by direction and searches counterparty and project names', function () {
        $project = waiverPortalProject($this->business);
        $project->update(['name' => 'Riverfront Tower']);

        LienWaiver::factory()->forProject($project)->create([
            'counterparty_company' => 'Provide Paving Inc',
        ]);
        LienWaiver::factory()->forProject($project)->collect()->create([
            'counterparty_company' => 'Collect Concrete LLC',
        ]);

        Livewire::test(WaiverList::class)
            ->set('directionFilter', 'collect')
            ->assertSee('Collect Concrete LLC')
            ->assertDontSee('Provide Paving Inc')
            ->set('directionFilter', '')
            ->set('search', 'Provide Paving')
            ->assertSee('Provide Paving Inc')
            ->assertDontSee('Collect Concrete LLC')
            // Project-name search matches every waiver on the project.
            ->set('search', 'Riverfront')
            ->assertSee('Provide Paving Inc')
            ->assertSee('Collect Concrete LLC');
    });
});

describe('signed copy upload', function () {
    it('marks the waiver signed, stores the media, and sets the GA deemed-effective date', function () {
        waiverPortalSubscribe($this->business);
        $project = waiverPortalProject($this->business, 'GA');
        $waiver = LienWaiver::factory()->forProject($project)->generated()->create();

        Livewire::test(WaiverShow::class, ['waiver' => $waiver])
            ->set('signedFile', UploadedFile::fake()->createWithContent('signed-waiver.pdf', '%PDF-1.4 fake signed waiver content'))
            ->call('uploadSigned')
            ->assertHasNoErrors();

        $waiver->refresh();
        expect($waiver->status)->toBe(WaiverStatus::Signed);
        expect($waiver->signed_at)->not->toBeNull();
        expect($waiver->getFirstMedia('signed'))->not->toBeNull();
        // GA: conclusively effective 90 days after execution (Eastern date).
        expect($waiver->deemed_effective_at->toDateString())
            ->toBe(now()->eastern()->addDays(90)->toDateString());
    });

    it('leaves the deemed-effective date empty outside GA/MS', function () {
        waiverPortalSubscribe($this->business);
        $project = waiverPortalProject($this->business, 'TX');
        $waiver = LienWaiver::factory()->forProject($project)->generated()->create();

        Livewire::test(WaiverShow::class, ['waiver' => $waiver])
            ->set('signedFile', UploadedFile::fake()->createWithContent('signed-waiver.pdf', '%PDF-1.4 fake signed waiver content'))
            ->call('uploadSigned');

        $waiver->refresh();
        expect($waiver->status)->toBe(WaiverStatus::Signed);
        expect($waiver->deemed_effective_at)->toBeNull();
    });

    it('gates signed-copy storage behind a subscription for free-tier businesses', function () {
        // No subscription: storing a signed copy is a paid feature.
        $project = waiverPortalProject($this->business, 'TX');
        $waiver = LienWaiver::factory()->forProject($project)->generated()->create();

        $component = Livewire::test(WaiverShow::class, ['waiver' => $waiver])
            ->set('signedFile', UploadedFile::fake()->createWithContent('signed-waiver.pdf', '%PDF-1.4 fake'))
            ->call('uploadSigned');

        expect($component->get('showUpsellModal'))->toBeTrue();

        $waiver->refresh();
        expect($waiver->status)->toBe(WaiverStatus::Generated);
        expect($waiver->getFirstMedia('signed'))->toBeNull();
    });

    it('rejects an upload while the waiver is still a draft', function () {
        waiverPortalSubscribe($this->business);
        $project = waiverPortalProject($this->business, 'TX');
        $waiver = LienWaiver::factory()->forProject($project)->create(); // Draft

        Livewire::test(WaiverShow::class, ['waiver' => $waiver])
            ->set('signedFile', UploadedFile::fake()->createWithContent('signed-waiver.pdf', '%PDF-1.4 fake signed waiver content'))
            ->call('uploadSigned');

        $waiver->refresh();
        expect($waiver->status)->toBe(WaiverStatus::Draft);
        expect($waiver->getFirstMedia('signed'))->toBeNull();
    });
});

describe('download controller', function () {
    it('redirects the unsigned download to a temporary media URL', function () {
        Storage::disk('s3')->buildTemporaryUrlsUsing(fn ($path) => 'https://s3.test/'.$path);

        $project = waiverPortalProject($this->business);
        $waiver = LienWaiver::factory()->forProject($project)->generated()->create();
        $waiver->addMediaFromString('%PDF-1.4 fake unsigned')
            ->usingFileName('waiver.pdf')
            ->toMediaCollection('generated');

        $response = $this->get(route('lien.waivers.download', $waiver));

        $response->assertRedirect();
        expect($response->headers->get('Location'))->toContain('s3.test');
    });

    it('404s the unsigned download before a PDF exists', function () {
        $project = waiverPortalProject($this->business);
        $waiver = LienWaiver::factory()->forProject($project)->create();

        $this->get(route('lien.waivers.download', $waiver))->assertNotFound();
    });

    it('404s the signed download when no signed copy exists', function () {
        $project = waiverPortalProject($this->business);
        $waiver = LienWaiver::factory()->forProject($project)->signed()->create();

        $this->get(route('lien.waivers.download', ['waiver' => $waiver, 'copy' => 'signed']))
            ->assertNotFound();
    });

    it('serves an uploaded signed copy via ?copy=signed', function () {
        Storage::disk('s3')->buildTemporaryUrlsUsing(fn ($path) => 'https://s3.test/'.$path);

        $project = waiverPortalProject($this->business);
        $waiver = LienWaiver::factory()->forProject($project)->signed()->create();
        $waiver->addMediaFromString('%PDF-1.4 fake signed')
            ->usingFileName('waiver-signed.pdf')
            ->toMediaCollection('signed');

        $response = $this->get(route('lien.waivers.download', ['waiver' => $waiver, 'copy' => 'signed']));

        $response->assertRedirect();
        expect($response->headers->get('Location'))->toContain('s3.test');
    });
});

describe('waiver show page', function () {
    it('shows the deemed-effective callout on a signed waiver carrying a date', function () {
        $project = waiverPortalProject($this->business, 'GA');
        $waiver = LienWaiver::factory()->forProject($project)->signed()->create([
            'deemed_effective_at' => now()->addDays(30)->toDateString(),
        ]);

        $this->get(route('lien.waivers.show', $waiver))
            ->assertSuccessful()
            ->assertSee('Becomes conclusively effective')
            ->assertSee('Affidavit of Nonpayment');
    });

    it('does not show the callout when no deemed-effective date exists', function () {
        $project = waiverPortalProject($this->business, 'TX');
        $waiver = LienWaiver::factory()->forProject($project)->signed()->create([
            'deemed_effective_at' => null,
        ]);

        $this->get(route('lien.waivers.show', $waiver))
            ->assertSuccessful()
            ->assertDontSee('Becomes conclusively effective');
    });
});
