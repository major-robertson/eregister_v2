<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Actions\ChangeFilingStatus;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Enums\RecordingMethod;
use App\Domains\Lien\Livewire\FilingShow;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingRecipient;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Mail\FilingMilestone;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/*
| EREG-130: a full-service filing emails the customer at Submitted for
| Recording, Recorded, Mailed to Parties and Complete, with the recorded copy
| and proof of service the admin uploads on the status form; the filing page
| shows a timeline of the milestones and of the requests and answers.
*/

beforeEach(function () {
    Storage::fake('s3');
    Mail::fake();
    LienDocumentRegistry::flush();

    $this->customer = User::factory()->create([
        'first_name' => 'Dana',
        'last_name' => 'Builder',
        'email' => 'dana@example.test',
    ]);
    $this->business = Business::factory()->onboarded()->withLienOnboarding()->create(['name' => 'Dana Drywall LLC']);
    $this->business->users()->attach($this->customer, ['role' => 'owner']);

    $this->project = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Maple Street Remodel',
        'created_by_user_id' => $this->customer->id,
        'jobsite_state' => 'CA',
        'jobsite_county' => 'Los Angeles County',
    ]);
    $this->lien = LienDocumentType::where('slug', 'mechanics_lien')->firstOrFail();

    $this->filing = LienFiling::factory()->forProject($this->project)->fullService()->paid()->create([
        'document_type_id' => $this->lien->id,
        'created_by_user_id' => $this->customer->id,
    ]);

    $this->admin = User::factory()->create(['first_name' => 'Major', 'last_name' => 'Robertson']);
    $this->admin->givePermissionTo(['lien.view', 'lien.update', 'lien.change_status']);
});

afterEach(function () {
    LienDocumentRegistry::flush();
});

if (! function_exists('milestonePdf')) {
    /** A small upload that reads as a real PDF (the milestone collections check the type). */
    function milestonePdf(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n% milestone document\n");
    }
}

if (! function_exists('milestoneChange')) {
    /**
     * Change the status as the admin, the way the status form does.
     *
     * @param  array<string, mixed>  $args  named ChangeFilingStatus arguments
     */
    function milestoneChange(LienFiling $filing, User $admin, FilingStatus $status, array $args = []): LienFiling
    {
        test()->actingAs($admin);

        app(ChangeFilingStatus::class)->execute(...['filing' => $filing, 'newStatus' => $status, ...$args]);

        return $filing->fresh();
    }
}

if (! function_exists('milestoneSubmit')) {
    /** Move the filing to Submitted for Recording by e-recording. */
    function milestoneSubmit(LienFiling $filing, User $admin, RecordingMethod $method = RecordingMethod::Erecord, ?string $provider = null): LienFiling
    {
        return milestoneChange($filing, $admin, FilingStatus::SubmittedForRecording, [
            'recordingMethod' => $method,
            'recordingProvider' => $provider,
            'recordingSubmittedAt' => now(),
        ]);
    }
}

if (! function_exists('milestoneRecipient')) {
    function milestoneRecipient(LienFiling $filing, array $party, ?string $tracking = null): LienFilingRecipient
    {
        $party = LienParty::factory()->forProject($filing->project)->create(array_merge([
            'role' => PartyRole::Owner,
            'company_name' => null,
            'address2' => null,
        ], $party));

        return LienFilingRecipient::create(array_merge(LienFilingRecipient::fromParty($party), [
            'filing_id' => $filing->id,
            'tracking_number' => $tracking,
        ]));
    }
}

if (! function_exists('milestoneMail')) {
    function milestoneMail(): FilingMilestone
    {
        Mail::assertQueued(FilingMilestone::class, 1);

        return Mail::queued(FilingMilestone::class)->first();
    }
}

describe('Submitted for Recording', function () {
    it('says how and where it went and how long recording takes', function (RecordingMethod $method, ?string $provider, string $sentence, ?string $turnaround) {
        milestoneSubmit($this->filing, $this->admin, $method, $provider);

        $mail = milestoneMail();

        expect($mail->hasTo('dana@example.test'))->toBeTrue()
            ->and($mail->afterCommit)->toBeTrue()
            ->and($mail->hasReplyTo('contact@eregister.com'))->toBeTrue()
            ->and($mail->attachments())->toBe([]);
        $mail->assertHasSubject('Your Mechanics Lien was submitted to Los Angeles County — eRegister');
        $mail->assertSeeInText('Hi Dana,');
        $mail->assertSeeInText($sentence);
        $mail->assertSeeInText("We'll email you the recorded copy.");
        $mail->assertSeeInText('Maple Street Remodel');
        $mail->assertSeeInHtml(route('lien.filings.show', $this->filing));

        if ($turnaround === null) {
            $mail->assertDontSeeInText('Recording typically takes');
        } else {
            $mail->assertSeeInText("Recording typically takes {$turnaround}.");
        }
    })->with([
        'e-recording' => [RecordingMethod::Erecord, null, 'We submitted your Mechanics Lien to Los Angeles County for recording by e-recording.', '1 to 5 business days'],
        'mailed to the county' => [RecordingMethod::Mail, null, 'We mailed your Mechanics Lien to Los Angeles County for recording.', '2 to 6 weeks'],
        // No in-person words in config: no turnaround sentence.
        'taken in person' => [RecordingMethod::Mail, 'In person', 'We took your Mechanics Lien to Los Angeles County in person for recording.', null],
    ]);

    it('takes the turnaround words from config', function () {
        config(['lien.recording_turnaround.erecord' => '2 to 3 business days']);

        milestoneSubmit($this->filing, $this->admin);

        milestoneMail()->assertSeeInText('Recording typically takes 2 to 3 business days.');
    });

    it('lets the county data file override the turnaround', function (mixed $turnaround, string $expected) {
        // As if database/data/lien_counties/ca/los-angeles.php had recording.turnaround.
        (new ReflectionProperty(LienDocumentRegistry::class, 'countyCache'))->setValue(null, [
            'CA/los-angeles' => [
                'county' => 'Los Angeles',
                'recording' => ['turnaround' => $turnaround],
                'notes' => [],
                'state' => 'CA',
                'key' => 'los-angeles',
            ],
        ]);

        milestoneSubmit($this->filing, $this->admin);

        milestoneMail()->assertSeeInText("Recording typically takes {$expected}.");
    })->with([
        'one string' => ['about 10 business days', 'about 10 business days'],
        'by method' => [['erecord' => '1 to 2 business days', 'mail' => '3 weeks'], '1 to 2 business days'],
        'by method, this one missing' => [['mail' => '3 weeks'], '1 to 5 business days'],
    ]);

    it('says "the county" when the filing has none', function () {
        $this->filing->update(['jurisdiction_county' => null]);
        $this->project->update(['jobsite_county' => null, 'jobsite_county_google' => null]);

        milestoneSubmit($this->filing->fresh(), $this->admin);

        $mail = milestoneMail();
        $mail->assertHasSubject('Your Mechanics Lien was submitted for recording — eRegister');
        $mail->assertSeeInText('We submitted your Mechanics Lien to the county for recording by e-recording.');
    });
});

describe('Recorded', function () {
    beforeEach(function () {
        $this->filing = milestoneSubmit($this->filing, $this->admin);
        Mail::fake();
    });

    it('gives the recording reference and attaches the recorded copy', function () {
        $filing = milestoneChange($this->filing, $this->admin, FilingStatus::Recorded, [
            'recordingReference' => '2026-0012345',
            'recordedCopy' => milestonePdf('recorded-lien.pdf'),
        ]);

        $media = $filing->getFirstMedia('recorded');
        $mail = milestoneMail();

        expect($mail->hasAttachment($media))->toBeTrue()
            ->and($mail->attachments())->toHaveCount(1)
            ->and($mail->attachments()[0]->toMailAttachment()->as)->toBe('recorded-lien.pdf');
        $mail->assertHasSubject('Your Mechanics Lien was recorded — eRegister');
        $mail->assertSeeInText('Los Angeles County recorded your Mechanics Lien.');
        $mail->assertSeeInText('2026-0012345');
        $mail->assertSeeInText('We attached the recorded copy to this email.');
        $mail->assertDontSeeInText('as soon as the county returns it');
    });

    it('promises the recorded copy when there is none yet', function () {
        milestoneChange($this->filing, $this->admin, FilingStatus::Recorded);

        $mail = milestoneMail();

        expect($mail->attachments())->toBe([]);
        $mail->assertSeeInText("We'll send the recorded copy as soon as the county returns it.");
        $mail->assertDontSeeInText('Recording reference');
        $mail->assertDontSeeInText('We attached');
    });
});

describe('Mailed to Parties', function () {
    beforeEach(function () {
        $this->filing->update(['status' => FilingStatus::Recorded]);

        milestoneRecipient($this->filing, ['name' => 'Owen Owner', 'address1' => '1 Main St', 'city' => 'Pasadena', 'state' => 'CA', 'zip' => '91101'], '9407 1118 9876 5432 1012 34');
        milestoneRecipient($this->filing, ['role' => PartyRole::Gc, 'name' => 'Gina General', 'company_name' => 'Big Build Inc', 'address1' => '9 Oak Ave', 'city' => 'Glendale', 'state' => 'CA', 'zip' => '91201']);
    });

    it('lists who was mailed, links the tracking number and attaches the proof of service', function () {
        $filing = milestoneChange($this->filing, $this->admin, FilingStatus::Mailed, [
            'trackingNumber' => '9400 1118 9922 3100 0012 34',
            'proofOfService' => milestonePdf('proof-of-service.pdf'),
        ]);

        $media = $filing->getFirstMedia('proof_of_service');
        $mail = milestoneMail();

        expect($mail->hasAttachment($media))->toBeTrue()
            ->and($mail->attachments())->toHaveCount(1)
            ->and($filing->mailing_tracking_number)->toBe('9400 1118 9922 3100 0012 34');
        $mail->assertHasSubject('Your Mechanics Lien was mailed — eRegister');
        $mail->assertSeeInText('We mailed your Mechanics Lien.');
        $mail->assertSeeInText('Owen Owner, 1 Main St, Pasadena, CA, 91101 (by certified mail, return receipt requested)');
        $mail->assertSeeInText('Big Build Inc, 9 Oak Ave, Glendale, CA, 91201');
        $mail->assertSeeInHtml('href="https://tools.usps.com/go/TrackConfirmAction?tLabels=9400111899223100001234"', false);
        $mail->assertSeeInHtml('href="https://tools.usps.com/go/TrackConfirmAction?tLabels=9407111898765432101234"', false);
        $mail->assertSeeInText('We attached the proof of service to this email.');
        $mail->assertSeeInText('Certified mail proves we mailed it. It does not prove the other side read it.');
    });

    it('goes out without a proof of service or tracking number', function () {
        milestoneChange($this->filing, $this->admin, FilingStatus::Mailed);

        $mail = milestoneMail();

        expect($mail->attachments())->toBe([]);
        $mail->assertDontSeeInText('Certified mail tracking');
        $mail->assertDontSeeInText('We attached');
        $mail->assertSeeInText('Certified mail proves we mailed it. It does not prove the other side read it.');
    });
});

describe('Complete', function () {
    it('wraps up with everything attached and the filing page link', function () {
        $this->filing->update(['status' => FilingStatus::Mailed]);
        $this->actingAs($this->admin);
        $recorded = $this->filing->addMedia(milestonePdf('recorded-lien.pdf'))->toMediaCollection('recorded');
        $proof = $this->filing->addMedia(milestonePdf('proof-of-service.pdf'))->toMediaCollection('proof_of_service');

        milestoneChange($this->filing, $this->admin, FilingStatus::Complete);

        $mail = milestoneMail();

        expect($mail->hasAttachment($recorded))->toBeTrue()
            ->and($mail->hasAttachment($proof))->toBeTrue()
            ->and($mail->attachments())->toHaveCount(2);
        $mail->assertHasSubject('Your Mechanics Lien is complete — eRegister');
        $mail->assertSeeInText('Your Mechanics Lien is complete.');
        $mail->assertSeeInText('We attached the recorded copy and the proof of service to this email.');
        $mail->assertSeeInHtml(route('lien.filings.show', $this->filing));
    });

    it('attaches the newest file of each kind', function () {
        $this->filing->update(['status' => FilingStatus::Mailed]);
        $this->filing->addMedia(milestonePdf('first-recorded.pdf'))->toMediaCollection('recorded');

        $filing = milestoneChange($this->filing, $this->admin, FilingStatus::Complete, [
            'recordedCopy' => milestonePdf('corrected-recorded.pdf'),
        ]);

        $mail = milestoneMail();

        expect($mail->attachments())->toHaveCount(1)
            ->and($mail->attachments()[0]->toMailAttachment()->as)->toBe('corrected-recorded.pdf')
            ->and($filing->getMedia('recorded'))->toHaveCount(2);
        $mail->assertSeeInText('We attached the recorded copy to this email.');
    });

    it('goes out with nothing to attach', function () {
        milestoneChange($this->filing, $this->admin, FilingStatus::Complete);

        $mail = milestoneMail();

        expect($mail->attachments())->toBe([]);
        $mail->assertSeeInText('Your Mechanics Lien is complete.');
        $mail->assertDontSeeInText('We attached');
    });

    it('leaves a file too large to email on the filing page', function () {
        $this->filing->update(['status' => FilingStatus::Mailed]);
        $recorded = $this->filing->addMedia(milestonePdf('recorded-lien.pdf'))->toMediaCollection('recorded');
        $proof = $this->filing->addMedia(milestonePdf('proof-of-service.pdf'))->toMediaCollection('proof_of_service');
        $recorded->update(['size' => 5 * 1024 * 1024]);
        $proof->update(['size' => 3 * 1024 * 1024]);

        milestoneChange($this->filing, $this->admin, FilingStatus::Complete);

        $mail = milestoneMail();

        expect($mail->attachments())->toHaveCount(1)
            ->and($mail->hasAttachment($recorded->fresh()))->toBeTrue();
        $mail->assertSeeInText('We attached the recorded copy to this email.');
        $mail->assertSeeInText('Some files are too large to email. You can download them from your filing page.');
    });
});

it('sends nothing for the internal statuses', function (FilingStatus $status) {
    milestoneChange($this->filing, $this->admin, $status);

    expect($this->filing->fresh()->status)->toBe($status);
    Mail::assertNotQueued(FilingMilestone::class);
})->with([
    'needs review' => [FilingStatus::NeedsReview],
    'ready to file' => [FilingStatus::ReadyToFile],
    'hold' => [FilingStatus::Hold],
    'in fulfillment' => [FilingStatus::InFulfillment],
    'waiting on next step' => [FilingStatus::WaitingOnNextStep],
]);

it('sends nothing for a self-serve filing', function () {
    $this->filing->update(['service_level' => 'self_serve']);

    milestoneSubmit($this->filing->fresh(), $this->admin);
    milestoneChange($this->filing->fresh(), $this->admin, FilingStatus::Recorded);

    Mail::assertNotQueued(FilingMilestone::class);
});

it('sends nothing when the filing has no customer', function () {
    $this->filing->update(['created_by_user_id' => null]);

    milestoneChange($this->filing->fresh(), $this->admin, FilingStatus::Complete);

    expect($this->filing->fresh()->status)->toBe(FilingStatus::Complete);
    Mail::assertNotQueued(FilingMilestone::class);
});

it('emails again when a milestone is reached again', function () {
    $this->filing->update(['status' => FilingStatus::Recorded]);

    milestoneChange($this->filing, $this->admin, FilingStatus::Mailed, ['trackingNumber' => '1111']);
    milestoneChange($this->filing->fresh(), $this->admin, FilingStatus::NeedsReview);
    $filing = milestoneChange($this->filing->fresh(), $this->admin, FilingStatus::Mailed, ['trackingNumber' => '2222']);

    // The new tracking number replaces the old one.
    expect($filing->mailing_tracking_number)->toBe('2222');
    Mail::assertQueued(FilingMilestone::class, 2);
});

describe('the admin status form', function () {
    beforeEach(function () {
        $this->actingAs($this->admin);
    });

    it('takes the recording reference and recorded copy at Recorded', function () {
        $this->filing->update(['status' => FilingStatus::SubmittedForRecording, 'recording_method' => RecordingMethod::Erecord]);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->set('newStatus', FilingStatus::Recorded->value)
            ->assertSee('Documents for the customer')
            ->assertSee('Recording reference (instrument number, or book and page)')
            ->assertSee('Recorded copy (optional)')
            ->assertDontSee('Proof of service (optional)')
            ->assertSee('email Dana Builder <dana@example.test> when you save.')
            ->assertSee('Your Mechanics Lien was recorded — eRegister')
            ->set('recordingReference', 'Book 1234 Page 56')
            ->set('recordedCopy', milestonePdf('recorded-lien.pdf'))
            ->call('updateStatus')
            ->assertHasNoErrors()
            ->assertSee('Status updated. We emailed dana@example.test.');

        $filing = $this->filing->fresh();
        $media = $filing->getFirstMedia('recorded');

        expect($filing->status)->toBe(FilingStatus::Recorded)
            ->and($filing->recording_reference)->toBe('Book 1234 Page 56')
            ->and($media)->not->toBeNull()
            ->and($media->file_name)->toBe('recorded-lien.pdf')
            ->and($media->disk)->toBe('s3')
            ->and($media->getCustomProperty('uploaded_by_user_id'))->toBe($this->admin->id);
        Storage::disk('s3')->assertExists($media->getPathRelativeToRoot());

        $event = $filing->events()->where('event_type', 'status_changed')->latest('id')->first();
        expect($event->meta('recorded_media_id'))->toBe($media->id)
            ->and($event->meta('recording_reference'))->toBe('Book 1234 Page 56');

        expect(milestoneMail()->hasAttachment($media))->toBeTrue();
    });

    it('takes the tracking number and proof of service at Mailed', function () {
        $this->filing->update(['status' => FilingStatus::Recorded]);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->set('newStatus', FilingStatus::Mailed->value)
            ->assertSee('Certified mail tracking number (optional)')
            ->assertSee('Proof of service (optional)')
            ->assertDontSee('Recorded copy (optional)')
            ->set('trackingNumber', '9400 1118 9922 3100 0012 34')
            ->set('proofOfService', milestonePdf('proof-of-service.pdf'))
            ->call('updateStatus')
            ->assertHasNoErrors();

        $filing = $this->filing->fresh();
        $media = $filing->getFirstMedia('proof_of_service');

        expect($filing->status)->toBe(FilingStatus::Mailed)
            ->and($filing->mailing_tracking_number)->toBe('9400 1118 9922 3100 0012 34')
            ->and($media->file_name)->toBe('proof-of-service.pdf')
            ->and($filing->events()->where('event_type', 'status_changed')->latest('id')->first()->meta('proof_of_service_media_id'))->toBe($media->id);
        expect(milestoneMail()->hasAttachment($media))->toBeTrue();
    });

    it('takes both at Complete', function () {
        $this->filing->update(['status' => FilingStatus::Mailed]);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->set('newStatus', FilingStatus::Complete->value)
            ->assertSee('Recorded copy (optional)')
            ->assertSee('Proof of service (optional)')
            ->set('recordedCopy', milestonePdf('recorded-lien.pdf'))
            ->set('proofOfService', milestonePdf('proof-of-service.pdf'))
            ->call('updateStatus')
            ->assertHasNoErrors();

        $filing = $this->filing->fresh();

        expect($filing->status)->toBe(FilingStatus::Complete)
            ->and($filing->getMedia('recorded'))->toHaveCount(1)
            ->and($filing->getMedia('proof_of_service'))->toHaveCount(1)
            ->and(milestoneMail()->attachments())->toHaveCount(2);
    });

    it('only takes PDFs of 10 MB or less', function (string $field, UploadedFile $file, string $rule, string $message) {
        $this->filing->update(['status' => FilingStatus::Mailed]);

        $component = Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->set('newStatus', FilingStatus::Complete->value)
            ->set($field, $file)
            ->call('updateStatus')
            ->assertHasErrors([$field => $rule]);

        expect($component->errors()->first($field))->toBe($message)
            ->and($this->filing->fresh()->status)->toBe(FilingStatus::Mailed);
        Mail::assertNotQueued(FilingMilestone::class);
    })->with([
        'an image as the recorded copy' => fn () => ['recordedCopy', UploadedFile::fake()->create('photo.png', 100, 'image/png'), 'mimes', 'The recorded copy must be a PDF.'],
        'a large recorded copy' => fn () => ['recordedCopy', UploadedFile::fake()->create('big.pdf', 10241, 'application/pdf'), 'max', 'The recorded copy must be 10 MB or smaller.'],
        'an image as the proof of service' => fn () => ['proofOfService', UploadedFile::fake()->create('photo.png', 100, 'image/png'), 'mimes', 'The proof of service must be a PDF.'],
    ]);

    it('says a self-serve filing sends no email', function () {
        $this->filing->update(['service_level' => 'self_serve']);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->set('newStatus', FilingStatus::Complete->value)
            ->assertSee('Self-serve filing: no email goes out.')
            ->assertDontSee('when you save.')
            ->call('updateStatus')
            ->assertSee('Status updated successfully.');

        Mail::assertNotQueued(FilingMilestone::class);
    });

    it('lists the recorded copies and proofs of service', function () {
        $this->filing->update(['status' => FilingStatus::Recorded]);
        milestoneChange($this->filing, $this->admin, FilingStatus::Mailed, ['proofOfService' => milestonePdf('proof-of-service.pdf')]);
        milestoneChange($this->filing->fresh(), $this->admin, FilingStatus::Complete, ['recordedCopy' => milestonePdf('recorded-lien.pdf')]);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->assertSee('Recorded copy and proof of service')
            ->assertSeeInOrder(['recorded-lien.pdf', 'Recorded copy', 'proof-of-service.pdf', 'Proof of service'])
            ->assertSee('expiration=');
    });

    it('stores no file for any other status', function () {
        milestoneChange($this->filing, $this->admin, FilingStatus::ReadyToFile, [
            'recordedCopy' => milestonePdf('recorded-lien.pdf'),
            'proofOfService' => milestonePdf('proof-of-service.pdf'),
            'trackingNumber' => '1234',
        ]);

        $filing = $this->filing->fresh();
        expect($filing->status)->toBe(FilingStatus::ReadyToFile)
            ->and($filing->getMedia('recorded'))->toHaveCount(0)
            ->and($filing->getMedia('proof_of_service'))->toHaveCount(0)
            ->and($filing->mailing_tracking_number)->toBeNull();
    });
});

describe('the filing page timeline', function () {
    beforeEach(function () {
        $this->asCustomer = function (): void {
            $this->actingAs($this->customer);
            session(['current_business_id' => $this->business->id]);
        };
    });

    it('shows each milestone with its date, method, reference, tracking and documents', function () {
        $this->travelTo(now()->setTime(15, 0));
        $submittedAt = now();
        milestoneSubmit($this->filing, $this->admin);

        $this->travel(2)->days();
        milestoneChange($this->filing->fresh(), $this->admin, FilingStatus::Recorded, [
            'recordingReference' => '2026-0012345',
            'recordedCopy' => milestonePdf('recorded-lien.pdf'),
        ]);

        $this->travel(1)->days();
        milestoneChange($this->filing->fresh(), $this->admin, FilingStatus::Mailed, [
            'trackingNumber' => '9400 1118 9922 3100 0012 34',
            'proofOfService' => milestonePdf('proof-of-service.pdf'),
        ]);

        $this->travel(1)->days();
        $filing = milestoneChange($this->filing->fresh(), $this->admin, FilingStatus::Complete);

        ($this->asCustomer)();

        $component = Livewire::test(FilingShow::class, ['filing' => $filing])
            ->assertSee('Timeline')
            ->assertSee(FilingStatus::SubmittedForRecording->userDescription())
            ->assertSee('Method: E-Recording')
            ->assertSee('County: Los Angeles County')
            ->assertSee(FilingStatus::Recorded->userDescription())
            ->assertSee('Recording reference: 2026-0012345')
            ->assertSee('Recorded copy (recorded-lien.pdf)')
            ->assertSee(FilingStatus::Mailed->userDescription())
            ->assertSee('https://tools.usps.com/go/TrackConfirmAction?tLabels=9400111899223100001234')
            ->assertSee('Proof of service (proof-of-service.pdf)')
            ->assertSee(FilingStatus::Complete->userDescription());

        // Oldest first, dates in Eastern.
        expect(array_column($component->viewData('timeline'), 'title'))
            ->toBe(['Submitted for Recording', 'Recorded', 'Mailed to Parties', 'Complete']);
        $component->assertSeeInOrder([
            $submittedAt->eastern()->format('M j, Y \a\t g:i A T'),
            $submittedAt->copy()->addDays(4)->eastern()->format('M j, Y \a\t g:i A T'),
        ]);
    });

    it('shows the requests, answers, uploads and draft reviews', function () {
        // A question, answered on the page.
        milestoneChange($this->filing, $this->admin, FilingStatus::AwaitingClient, ['customerMessage' => 'Please send the signed contract.']);
        ($this->asCustomer)();
        Livewire::test(FilingShow::class, ['filing' => $this->filing->fresh()])
            ->set('answer', 'Here is the contract.')
            ->call('submitAnswer')
            ->assertHasNoErrors();

        // A document to notarize, uploaded on the page.
        milestoneChange($this->filing->fresh(), $this->admin, FilingStatus::AwaitingNotary, ['customerMessage' => 'Please get this notarized.']);
        ($this->asCustomer)();
        Livewire::test(FilingShow::class, ['filing' => $this->filing->fresh()])
            ->set('signedDocument', UploadedFile::fake()->createWithContent('notarized-lien.pdf', "%PDF-1.4\n% notarized\n"))
            ->call('uploadSignedDocument')
            ->assertHasNoErrors();

        // A draft sent for review, approved on the page.
        $this->actingAs($this->admin);
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->set('draftFile', milestonePdf('lien-draft.pdf'))
            ->call('uploadDraft')
            ->call('confirmSendDraftForReview')
            ->call('sendDraftForReview')
            ->assertHasNoErrors();
        ($this->asCustomer)();
        Livewire::test(FilingShow::class, ['filing' => $this->filing->fresh()])
            ->set('approveConfirmed', true)
            ->call('approveDraft')
            ->assertHasNoErrors();

        $component = Livewire::test(FilingShow::class, ['filing' => $this->filing->fresh()])
            ->assertSee('We asked you:')
            ->assertSee('Please send the signed contract.')
            ->assertSee('You answered')
            ->assertSee('Here is the contract.')
            ->assertSee('You uploaded notarized-lien.pdf')
            ->assertSee('We sent you a draft to review (version 1)')
            ->assertSee('You approved the draft (version 1)');

        expect(array_column($component->viewData('timeline'), 'title'))->toBe([
            'We asked you:',
            'You answered',
            'Needs Review',
            'We asked you:',
            'You uploaded notarized-lien.pdf',
            'Needs Review',
            'We sent you a draft to review (version 1)',
            'You approved the draft (version 1)',
            'Ready to File',
        ]);
    });

    it('shows nothing for Draft and Awaiting Payment', function () {
        $filing = LienFiling::factory()->forProject($this->project)->fullService()->create([
            'document_type_id' => $this->lien->id,
            'created_by_user_id' => $this->customer->id,
        ]);
        ($this->asCustomer)();
        $filing->transitionTo(FilingStatus::AwaitingPayment);

        Livewire::test(FilingShow::class, ['filing' => $filing->fresh()])
            ->assertDontSee('Timeline')
            ->assertViewHas('timeline', []);

        $filing->fresh()->transitionTo(FilingStatus::Paid);

        $component = Livewire::test(FilingShow::class, ['filing' => $filing->fresh()])
            ->assertSee('Timeline');

        expect(array_column($component->viewData('timeline'), 'title'))->toBe(['Submitted']);
    });
});

it('sends nothing when a self-serve customer marks their own filing mailed', function () {
    $this->filing->update(['service_level' => 'self_serve']);
    $this->actingAs($this->customer);
    session(['current_business_id' => $this->business->id]);

    Livewire::test(FilingShow::class, ['filing' => $this->filing->fresh()])
        ->set('tracking_number', '9400 1118')
        ->call('markMailed');

    expect($this->filing->fresh()->status)->toBe(FilingStatus::Mailed);
    Mail::assertNotQueued(FilingMilestone::class);
});
