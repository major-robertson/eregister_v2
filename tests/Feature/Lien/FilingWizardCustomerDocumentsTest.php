<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingEvent;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/*
| EREG-139: on the Work step the customer may attach the contract, change
| orders and invoices. They are stored on the draft filing as they upload,
| each with what it is (contract, change order, invoice, other) and who
| uploaded it; the review step names them; staff download them from the
| filing's admin page. Nothing reads or parses them.
*/

beforeEach(function () {
    // Tuesday, October 20, 2026, noon Eastern.
    $this->travelTo(CarbonImmutable::parse('2026-10-20 12:00:00', 'America/New_York')->utc());
    Storage::fake('s3');
    Storage::fake('local');
    Mail::fake();
    LienDocumentRegistry::flush();

    $this->user = User::factory()->create(['first_name' => 'Dana', 'last_name' => 'Builder']);
    $this->business = Business::factory()->create(['lien_onboarding_completed_at' => now(), 'name' => 'Dana Drywall LLC']);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    $this->project = custDocsProject($this->business);
    [$this->deadline, $this->filing] = custDocsDraft($this->project);
});

afterEach(fn () => LienDocumentRegistry::flush());

if (! function_exists('custDocsProject')) {
    /**
     * A general contractor's Kansas project with an owner, a legal
     * description and its deadlines: the lien is due December 14, 2026,
     * well clear of any rush fee.
     */
    function custDocsProject(Business $business): LienProject
    {
        $project = LienProject::factory()->forBusiness($business)->create([
            'name' => 'Maple Street Remodel',
            'jobsite_address1' => '100 Maple St',
            'jobsite_city' => 'Wichita',
            'jobsite_state' => 'KS',
            'jobsite_county' => 'Sedgwick',
            'jobsite_zip' => '67202',
            'claimant_type' => ClaimantType::Gc,
            'property_class' => 'residential',
            'first_furnish_date' => '2026-07-01',
            'last_furnish_date' => '2026-08-14',
            'completion_date' => '2026-08-14',
            'legal_description' => 'Lot 12, Block 3, Sunset Hills Subdivision',
        ]);

        app(DeadlineCalculator::class)->calculateForProject($project);

        LienParty::create([
            'business_id' => $business->id,
            'project_id' => $project->id,
            'role' => 'owner',
            'name' => 'Test Owner',
            'address1' => '123 Main St',
            'city' => 'Wichita',
            'state' => 'KS',
            'zip' => '67202',
        ]);

        return $project;
    }

    /**
     * The project's deadline for a document and a draft on it.
     *
     * @return array{0: LienProjectDeadline, 1: LienFiling}
     */
    function custDocsDraft(LienProject $project, string $slug = 'mechanics_lien'): array
    {
        $deadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', $slug))
            ->latest('id')
            ->firstOrFail();

        $filing = LienFiling::factory()->forProject($project)->create([
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => FilingStatus::Draft,
            'service_level' => 'full_service',
            'created_by_user_id' => auth()->id(),
        ]);

        return [$deadline, $filing];
    }

    /** The wizard on the Work step. */
    function custDocsWorkStep(LienProject $project, LienProjectDeadline $deadline): Testable
    {
        return Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $deadline])
            ->call('nextStep')
            ->call('nextStep')
            ->assertSet('step', 3);
    }

    /** The Work step filled in, then on to the review step. */
    function custDocsToReview(Testable $component): Testable
    {
        return $component->set('has_written_contract', '1')
            ->fill(['base_contract_amount' => '5000', 'credits_deductions' => '0', 'payments_received' => '0'])
            ->set('amount_claimed', 5000.00)
            ->set('description_of_work', 'Furnishing all labor, materials, equipment, and services for drywall.')
            ->set('stateAnswers', wizardStateAnswers())
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 4);
    }

    /** A PDF, JPG, PNG or HEIC file as the browser sends it (size in kilobytes). */
    function custDocsFile(string $name, int $kilobytes = 120, ?string $mimeType = null): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kilobytes, $mimeType ?? match (pathinfo($name, PATHINFO_EXTENSION)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'heic' => 'image/heic',
            default => 'application/pdf',
        });
    }

    /** A file put straight into a filing's media, as an earlier upload would have. */
    function custDocsAttach(LienFiling $filing, string $name, string $kind = 'other', string $collection = LienFiling::CUSTOMER_DOCUMENTS): Media
    {
        return $filing->addMediaFromString("%PDF-1.4\n% {$name}\n")
            ->usingFileName($name)
            ->withCustomProperties(['kind' => $kind, 'uploaded_by' => auth()->id()])
            ->toMediaCollection($collection);
    }

    function custDocsAdmin(): User
    {
        $admin = User::factory()->create(['first_name' => 'Lee', 'last_name' => 'Staff']);
        $admin->givePermissionTo('lien.view', 'lien.update', 'lien.change_status');

        return $admin;
    }
}

describe('the Work step', function () {
    it('offers the upload below the amounts', function () {
        custDocsWorkStep($this->project, $this->deadline)
            ->assertSeeInOrder(['Amount you are claiming', 'Description of Work', 'Attach your contract and invoices (optional)'])
            ->assertSee('PDF, JPG, PNG or HEIC. Up to 20 MB each, 10 files in all.');
    });

    it('stores the files on the draft filing with their kind and who uploaded them', function () {
        $component = custDocsWorkStep($this->project, $this->deadline)
            ->set('customerDocumentUploads', [
                custDocsFile('contract.pdf'),
                custDocsFile('change-order-1.jpg'),
                custDocsFile('invoice-1042.png'),
                custDocsFile('site-photo.heic'),
            ])
            ->assertHasNoErrors()
            ->assertSet('customerDocumentUploads', []);

        $documents = $this->filing->fresh()->getMedia(LienFiling::CUSTOMER_DOCUMENTS);

        expect($documents->pluck('file_name')->all())->toBe(['contract.pdf', 'change-order-1.jpg', 'invoice-1042.png', 'site-photo.heic'])
            ->and($documents->pluck('disk')->unique()->all())->toBe(['s3'])
            ->and($documents->map(fn (Media $media) => $media->getCustomProperty('kind'))->unique()->all())->toBe(['other'])
            ->and($documents->map(fn (Media $media) => $media->getCustomProperty('uploaded_by'))->unique()->all())->toBe([$this->user->id]);

        foreach ($documents as $document) {
            Storage::disk('s3')->assertExists($document->getPathRelativeToRoot());
        }

        $component->assertSeeInOrder(['contract.pdf', 'change-order-1.jpg', 'invoice-1042.png', 'site-photo.heic'])
            ->assertSee('Choose what each file is.');
    });

    it('checks the type by content, stores nothing from a batch with a bad file, and names it', function () {
        $component = custDocsWorkStep($this->project, $this->deadline)
            ->set('customerDocumentUploads', [
                custDocsFile('contract.pdf'),
                // Named like a PDF, but the content is a Windows program.
                custDocsFile('invoice.pdf', 40, 'application/x-msdownload'),
                custDocsFile('notes.txt', 2, 'text/plain'),
            ])
            ->assertHasErrors('customerDocuments')
            ->assertSet('customerDocumentUploads', [])
            ->assertSee('invoice.pdf is not a PDF, JPG, PNG or HEIC file.')
            ->assertSee('notes.txt is not a PDF, JPG, PNG or HEIC file.');

        expect($component->errors()->get('customerDocuments'))->toBe([
            'invoice.pdf is not a PDF, JPG, PNG or HEIC file.',
            'notes.txt is not a PDF, JPG, PNG or HEIC file.',
        ])
            ->and($this->filing->fresh()->getMedia(LienFiling::CUSTOMER_DOCUMENTS))->toHaveCount(0);

        // The rejected batch is gone; the next one stands on its own.
        $component->set('customerDocumentUploads', [custDocsFile('contract.pdf')])
            ->assertHasNoErrors();

        expect($this->filing->fresh()->getMedia(LienFiling::CUSTOMER_DOCUMENTS)->pluck('file_name')->all())->toBe(['contract.pdf']);
    });

    it('takes a file of 20 MB and turns away a larger one', function () {
        $component = custDocsWorkStep($this->project, $this->deadline)
            ->set('customerDocumentUploads', [custDocsFile('scanned-contract.pdf', 20 * 1024 + 1)])
            ->assertHasErrors('customerDocumentUploads.0')
            ->assertSee('A file did not upload. Each file can be up to 20 MB. Try again.');

        expect($this->filing->fresh()->getMedia(LienFiling::CUSTOMER_DOCUMENTS))->toHaveCount(0);

        // Real bytes, so Livewire's upload limit and the media library's
        // (10 MB out of the box) are both put to the test.
        $twentyMegabytes = "%PDF-1.4\n".str_repeat('0', 20 * 1024 * 1024 - 9);

        $component->set('customerDocumentUploads', [UploadedFile::fake()->createWithContent('scanned-contract.pdf', $twentyMegabytes)])
            ->assertHasNoErrors()
            ->assertDontSee('A file did not upload.');

        $document = $this->filing->fresh()->getFirstMedia(LienFiling::CUSTOMER_DOCUMENTS);

        expect($document?->file_name)->toBe('scanned-contract.pdf')
            ->and($document->size)->toBe(20 * 1024 * 1024);
    });

    it('takes up to 10 files in all', function () {
        $files = fn (int $from, int $count) => array_map(fn (int $n) => custDocsFile("invoice-{$n}.pdf"), range($from, $from + $count - 1));

        $component = custDocsWorkStep($this->project, $this->deadline)
            ->set('customerDocumentUploads', $files(1, 8))
            ->assertHasNoErrors()
            ->set('customerDocumentUploads', $files(9, 3))
            ->assertHasErrors('customerDocuments')
            ->assertSee('You can attach up to 10 files. Remove one to add another.');

        expect($this->filing->fresh()->getMedia(LienFiling::CUSTOMER_DOCUMENTS))->toHaveCount(8);

        $component->set('customerDocumentUploads', $files(9, 2))
            ->assertHasNoErrors()
            ->assertSee('You have attached 10 files, the most we take. Remove one to add another.')
            ->assertDontSee('Drop files here or click to browse')
            ->set('customerDocumentUploads', $files(11, 1))
            ->assertHasErrors('customerDocuments');

        expect($this->filing->fresh()->getMedia(LienFiling::CUSTOMER_DOCUMENTS))->toHaveCount(10);
    });

    it('sets what each file is', function () {
        $component = custDocsWorkStep($this->project, $this->deadline)
            ->set('customerDocumentUploads', [custDocsFile('contract.pdf'), custDocsFile('invoice-1042.pdf')]);

        [$contract, $invoice] = $this->filing->fresh()->getMedia(LienFiling::CUSTOMER_DOCUMENTS)->all();

        $component->call('setCustomerDocumentKind', $contract->id, 'contract')
            ->call('setCustomerDocumentKind', $invoice->id, 'invoice')
            ->assertHasNoErrors();

        expect($contract->fresh()->getCustomProperty('kind'))->toBe('contract')
            ->and($contract->fresh()->getCustomProperty('uploaded_by'))->toBe($this->user->id)
            ->and($invoice->fresh()->getCustomProperty('kind'))->toBe('invoice');

        // Each file's select shows its kind.
        preg_match_all('/<option\b[^>]*\bselected\b[^>]*>\s*([^<]*?)\s*<\/option>/', Str::after($component->html(), 'Choose what each file is.'), $selected);
        expect($selected[1])->toBe(['Contract', 'Invoice']);

        $component->call('setCustomerDocumentKind', $contract->id, 'receipt')->assertStatus(422);

        expect($contract->fresh()->getCustomProperty('kind'))->toBe('contract');
    });

    it('removes a file', function () {
        $component = custDocsWorkStep($this->project, $this->deadline)
            ->set('customerDocumentUploads', [custDocsFile('contract.pdf'), custDocsFile('wrong-job-invoice.pdf')]);

        [$contract, $wrong] = $this->filing->fresh()->getMedia(LienFiling::CUSTOMER_DOCUMENTS)->all();
        $path = $wrong->getPathRelativeToRoot();

        $component->call('removeCustomerDocument', $wrong->id)
            ->assertHasNoErrors()
            ->assertSee('contract.pdf')
            ->assertDontSee('wrong-job-invoice.pdf');

        expect(Media::find($wrong->id))->toBeNull()
            ->and($this->filing->fresh()->getMedia(LienFiling::CUSTOMER_DOCUMENTS)->pluck('id')->all())->toBe([$contract->id]);
        Storage::disk('s3')->assertMissing($path);
    });

    it('keeps the files when the customer leaves and comes back', function () {
        custDocsWorkStep($this->project, $this->deadline)
            ->set('customerDocumentUploads', [custDocsFile('contract.pdf')]);

        // A fresh visit resumes the same draft, files and all.
        custDocsWorkStep($this->project, $this->deadline)
            ->assertSet('filing.id', $this->filing->id)
            ->assertSee('contract.pdf');
    });

    it('touches no file but this filing\'s own customer documents', function () {
        [, $otherFiling] = custDocsDraft(custDocsProject($this->business));
        $otherDocument = custDocsAttach($otherFiling, 'other-contract.pdf', 'contract');
        $propertyUpload = custDocsAttach($this->filing, 'deed.pdf', 'other', 'customer_uploads');

        // A fresh wizard for each: a failed request leaves the test component spent.
        custDocsWorkStep($this->project, $this->deadline)->call('removeCustomerDocument', $otherDocument->id)->assertNotFound();
        custDocsWorkStep($this->project, $this->deadline)->call('setCustomerDocumentKind', $otherDocument->id, 'invoice')->assertNotFound();
        custDocsWorkStep($this->project, $this->deadline)->call('removeCustomerDocument', $propertyUpload->id)->assertNotFound();

        expect($otherDocument->fresh()?->getCustomProperty('kind'))->toBe('contract')
            ->and($propertyUpload->fresh())->not->toBeNull();
    });

    it('leaves the files alone once the order is paid', function () {
        // Three tabs open on the Work step (a failed request leaves the test
        // component spent), one with a file attached...
        $tabs = [
            custDocsWorkStep($this->project, $this->deadline)->set('customerDocumentUploads', [custDocsFile('contract.pdf')]),
            custDocsWorkStep($this->project, $this->deadline),
            custDocsWorkStep($this->project, $this->deadline),
        ];

        $document = $this->filing->fresh()->getFirstMedia(LienFiling::CUSTOMER_DOCUMENTS);

        // ...then the order is paid for in another one.
        $this->filing->update(['status' => FilingStatus::Paid, 'paid_at' => now()]);

        $tabs[0]->call('removeCustomerDocument', $document->id)->assertForbidden();
        $tabs[1]->call('setCustomerDocumentKind', $document->id, 'contract')->assertForbidden();
        $tabs[2]->set('customerDocumentUploads', [custDocsFile('invoice.pdf')])->assertForbidden();

        expect($this->filing->fresh()->getMedia(LienFiling::CUSTOMER_DOCUMENTS)->pluck('file_name')->all())->toBe(['contract.pdf'])
            ->and($document->fresh()->getCustomProperty('kind'))->toBe('other');
    });

    it('is closed to another business', function () {
        $stranger = User::factory()->create();
        $otherBusiness = Business::factory()->create(['lien_onboarding_completed_at' => now()]);
        $otherBusiness->users()->attach($stranger, ['role' => 'owner']);

        $this->actingAs($stranger);
        session(['current_business_id' => $otherBusiness->id]);

        Livewire::test(FilingWizard::class, ['project' => $this->project, 'deadline' => $this->deadline])
            ->assertForbidden();
    });
});

describe('the review step', function () {
    it('names the attached files', function () {
        $component = custDocsWorkStep($this->project, $this->deadline)
            ->set('customerDocumentUploads', [custDocsFile('contract.pdf'), custDocsFile('invoice-1042.pdf')]);

        custDocsToReview($component)
            ->assertSeeInOrder(['Attached files', 'contract.pdf', 'invoice-1042.pdf']);
    });

    it('leaves the line out when nothing is attached', function () {
        custDocsToReview(custDocsWorkStep($this->project, $this->deadline))
            ->assertDontSee('Attached files');
    });
});

describe('the admin page', function () {
    beforeEach(function () {
        $this->admin = custDocsAdmin();
    });

    it('lists the files with what they are, when they came in and a download link', function () {
        $contract = custDocsAttach($this->filing, 'contract.pdf', 'contract');
        $this->travel(90)->minutes();
        $invoice = custDocsAttach($this->filing, 'invoice-1042.pdf', 'invoice');
        $this->filing->update(['status' => FilingStatus::Paid, 'paid_at' => now()]);

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->assertSeeInOrder(['Documents', 'From the customer at order time', 'contract.pdf', 'Contract · Uploaded Oct 20, 2026 12:00 PM', 'invoice-1042.pdf', 'Invoice · Uploaded Oct 20, 2026 1:30 PM'])
            ->assertSeeHtml(route('admin.liens.customer-documents.download', [$this->filing->public_id, $contract->id]))
            ->assertSeeHtml(route('admin.liens.customer-documents.download', [$this->filing->public_id, $invoice->id]))
            ->assertDontSee('Check the dates against the attached contract.');
    });

    it('shows the files on a demand letter, which has no generated documents', function () {
        $letter = LienFiling::factory()->forProject($this->project)->paid()->create([
            'document_type_id' => LienDocumentType::where('slug', 'demand_letter')->firstOrFail()->id,
        ]);
        custDocsAttach($letter, 'invoice-1042.pdf', 'invoice');

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $letter->fresh()])
            ->assertSee('From the customer at order time')
            ->assertSee('invoice-1042.pdf')
            // Only the documents card opens up; document details stay with the generated documents.
            ->assertDontSee('Nothing set yet. Until then the documents use');
    });

    it('hints to check the dates against the contract once they changed after a deadline notice', function () {
        custDocsAttach($this->filing, 'contract.pdf', 'contract');

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->assertDontSee('Check the dates against the attached contract.');

        $this->filing->events()->create([
            'business_id' => $this->filing->business_id,
            'event_type' => LienFilingEvent::DATES_CHANGED_AFTER_DEADLINE_BLOCK,
            'payload_json' => ['before' => [], 'after' => []],
            'created_by' => $this->user->id,
        ]);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->assertSee('Dates changed after deadline notice')
            ->assertSee('Check the dates against the attached contract.');
    });

    it('gives no hint without a contract among the files', function () {
        custDocsAttach($this->filing, 'invoice-1042.pdf', 'invoice');
        $this->filing->events()->create([
            'business_id' => $this->filing->business_id,
            'event_type' => LienFilingEvent::DATES_CHANGED_AFTER_DEADLINE_BLOCK,
            'payload_json' => ['before' => [], 'after' => []],
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->assertSee('Dates changed after deadline notice')
            ->assertSee('invoice-1042.pdf')
            ->assertDontSee('Check the dates against the attached contract.');
    });

    it('downloads a file through a short-lived link', function () {
        $contract = custDocsAttach($this->filing, 'contract.pdf', 'contract');

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        $location = $this->get(route('admin.liens.customer-documents.download', [$this->filing->public_id, $contract->id]))
            ->assertRedirect()
            ->headers->get('Location');

        expect($location)->toContain($contract->getPathRelativeToRoot())
            ->toContain('expiration='.now()->addMinutes(5)->getTimestamp());
    });

    it('finds only this filing\'s customer documents', function () {
        [, $otherFiling] = custDocsDraft(custDocsProject($this->business));
        $otherDocument = custDocsAttach($otherFiling, 'other-contract.pdf', 'contract');
        $deed = custDocsAttach($this->filing, 'deed.pdf', 'other', 'customer_uploads');

        $this->actingAs($this->admin);
        session()->forget('current_business_id');

        $this->get(route('admin.liens.customer-documents.download', [$this->filing->public_id, $otherDocument->id]))->assertNotFound();
        $this->get(route('admin.liens.customer-documents.download', [$this->filing->public_id, $deed->id]))->assertNotFound();
        $this->get(route('admin.liens.customer-documents.download', ['01UNKNOWNPUBLICID00000000', $otherDocument->id]))->assertNotFound();
        $this->get("/admin/liens/{$this->filing->public_id}/customer-documents/latest")->assertNotFound();
    });

    it('forbids the download to customers, their own business or another, and sends a guest to log in', function () {
        $contract = custDocsAttach($this->filing, 'contract.pdf', 'contract');
        $url = route('admin.liens.customer-documents.download', [$this->filing->public_id, $contract->id]);

        // The filing's own customer is not staff.
        $this->get($url)->assertForbidden();

        $stranger = User::factory()->create();
        $otherBusiness = Business::factory()->create(['lien_onboarding_completed_at' => now()]);
        $otherBusiness->users()->attach($stranger, ['role' => 'owner']);
        $this->actingAs($stranger);
        session(['current_business_id' => $otherBusiness->id]);

        $this->get($url)->assertForbidden();

        auth()->logout();
        $this->get($url)->assertRedirect();
    });
});
