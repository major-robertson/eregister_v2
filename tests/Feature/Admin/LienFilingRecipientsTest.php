<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Actions\UpdateLienFilingRecipient;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Documents\LienDocumentGenerator;
use App\Domains\Lien\Documents\LienDocumentPackage;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Documents\LienServiceDocumentGenerator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\LienPackageDocument;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\LaravelPdf\PdfBuilder;

beforeEach(function () {
    Storage::fake('s3');
    LienDocumentRegistry::flush();
});

afterEach(fn () => LienDocumentRegistry::flush());

if (! function_exists('recipAdmin')) {
    function recipAdmin(string ...$permissions): User
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(...($permissions ?: ['lien.view', 'lien.update']));

        return $admin;
    }

    /** A Texas subcontractor project: claimant, owner, hiring party (= the GC). */
    function recipProject(string $state = 'TX', string $county = 'Bexar', array $overrides = []): LienProject
    {
        $business = Business::factory()->create([
            'responsible_people' => [['user_id' => 1, 'name' => 'David Diligianis', 'title' => 'Owner', 'can_sign_liens' => true]],
        ]);

        $project = LienProject::factory()->forBusiness($business)->create(array_merge([
            'name' => 'Sandwick water mitigation',
            'claimant_type' => ClaimantType::Subcontractor,
            'hired_by' => 'direct_contractor',
            'jobsite_address1' => '6006 Sandwick Dr',
            'jobsite_city' => 'San Antonio',
            'jobsite_state' => $state,
            'jobsite_zip' => '78238',
            'jobsite_county' => $county,
            'legal_description' => 'NCB 14885, Block 9, Lot 29',
            'apn' => '148850090290',
            'first_furnish_date' => '2026-05-08',
            'last_furnish_date' => '2026-06-03',
            'base_contract_amount_cents' => 4987670,
            'payments_received_cents' => 0,
        ], $overrides));

        foreach ([
            [PartyRole::Claimant, 'David Diligianis', 'Restoration Solutions By Elite LLC', '100 Main St', 'Boerne', '78006'],
            [PartyRole::Owner, 'Baraa Jouda', null, '6006 Sandwick Dr', 'San Antonio', '78238'],
            [PartyRole::Customer, 'Alex Rivera', 'Alamo Builders LLC', '900 Commerce St', 'San Antonio', '78205'],
            [PartyRole::Gc, 'Alex Rivera', 'Alamo Builders LLC', '900 Commerce St', 'San Antonio', '78205'],
        ] as [$role, $name, $company, $address, $city, $zip]) {
            LienParty::create([
                'business_id' => $project->business_id,
                'project_id' => $project->id,
                'role' => $role,
                'name' => $name,
                'company_name' => $company,
                'address1' => $address,
                'city' => $city,
                'state' => $state,
                'zip' => $zip,
            ]);
        }

        return $project;
    }

    function recipFiling(LienProject $project, string $kind = 'mechanics_lien', array $overrides = []): LienFiling
    {
        $type = LienDocumentType::where('slug', $kind)->firstOrFail();

        return LienFiling::factory()->forProject($project)->paid()->create(array_merge([
            'document_type_id' => $type->id,
            'amount_claimed_cents' => 4987670,
            'description_of_work' => 'Water damage mitigation',
        ], $overrides));
    }

    function recipParty(LienProject $project, PartyRole $role): LienParty
    {
        return $project->parties()->where('role', $role->value)->firstOrFail();
    }

    function recipText(PdfBuilder $pdf): string
    {
        $html = (string) preg_replace('/<head>.*?<\/head>/s', '', $pdf->getHtml());
        $html = (string) preg_replace('/<(br|\/p|\/div|\/td|\/tr|\/li|\/table)[^>]*>/i', ' ', $html);

        return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }
}

describe('adding recipients', function () {
    it('snapshots the party address, logs the event and offers the remaining parties', function () {
        $project = recipProject();
        $filing = recipFiling($project);
        $owner = recipParty($project, PartyRole::Owner);
        $gc = recipParty($project, PartyRole::Gc);
        $claimant = recipParty($project, PartyRole::Claimant);

        $this->actingAs($admin = recipAdmin());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Recipients')
            ->assertSee('No recipients yet.')
            ->assertSeeHtml("addRecipient({$owner->id})")
            ->assertDontSeeHtml("addRecipient({$claimant->id})")
            ->call('addRecipient', $owner->id)
            ->assertHasNoErrors()
            ->assertSee('Baraa Jouda')
            ->assertSee('Not sent yet')
            ->assertDontSeeHtml("addRecipient({$owner->id})")
            ->assertSeeHtml("addRecipient({$gc->id})");

        $recipient = $filing->recipients()->first();

        expect($recipient)->not->toBeNull();
        expect($recipient->party_id)->toBe($owner->id);
        expect($recipient->delivery_method)->toBe('certified_mail');
        expect($recipient->address_snapshot_json['address1'])->toBe('6006 Sandwick Dr');
        expect($recipient->sent_at)->toBeNull();

        $event = $filing->events()->where('event_type', 'recipient_added')->first();
        expect($event->created_by)->toBe($admin->id);
        expect($event->payload_json['recipient']['name'])->toBe('Baraa Jouda');
        expect($event->payload_json['recipient']['address'])->toBe('6006 Sandwick Dr, San Antonio, TX, 78238');
    });

    it('refuses the same party twice and view-only admins', function () {
        $project = recipProject();
        $filing = recipFiling($project);
        $owner = recipParty($project, PartyRole::Owner);

        $this->actingAs(recipAdmin());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('addRecipient', $owner->id)
            ->call('addRecipient', $owner->id);

        expect($filing->recipients()->count())->toBe(1);
        expect($filing->events()->where('event_type', 'recipient_added')->count())->toBe(1);

        expect(fn () => app(UpdateLienFilingRecipient::class)->add($filing, $owner))
            ->toThrow(InvalidArgumentException::class, 'already a recipient');

        $this->actingAs(recipAdmin('lien.view'));

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertDontSeeHtml('addRecipient(')
            ->call('addRecipient', recipParty($project, PartyRole::Gc)->id)
            ->assertForbidden();
    });
});

describe('service facts', function () {
    it('stores Eastern times as UTC, logs the diff and skips a no-op save', function () {
        $project = recipProject();
        $filing = recipFiling($project);
        $recipient = app(UpdateLienFilingRecipient::class)->add($filing, recipParty($project, PartyRole::Owner));

        $this->actingAs(recipAdmin());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('editRecipient', $recipient->id)
            ->assertSet('recipientForm.delivery_method', 'certified_mail')
            ->assertSet('recipientForm.sent_at', null)
            ->set('recipientForm.tracking_number', '7018 1830 0000 4535 9376')
            ->set('recipientForm.sent_at', '2026-09-10T15:30')
            ->call('updateRecipient')
            ->assertHasNoErrors()
            ->assertSet('showRecipientModal', false)
            ->assertSee('Sent Sep 10, 2026 3:30 PM');

        $fresh = $recipient->fresh();
        expect($fresh->sent_at->toDateTimeString())->toBe('2026-09-10 19:30:00');
        expect($fresh->tracking_number)->toBe('7018 1830 0000 4535 9376');
        expect($fresh->delivered_at)->toBeNull();

        $event = $filing->events()->where('event_type', 'recipient_updated')->first();
        expect($event->payload_json['changes']['tracking_number'])->toEqual(['from' => null, 'to' => '7018 1830 0000 4535 9376']);
        expect($event->payload_json['changes']['sent_at']['to'])->toContain('2026-09-10T19:30:00');
        expect($event->payload_json['changes'])->not->toHaveKey('delivery_method');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->call('editRecipient', $recipient->id)
            ->assertSet('recipientForm.sent_at', '2026-09-10T15:30')
            ->call('updateRecipient')
            ->assertHasNoErrors();

        expect($filing->events()->where('event_type', 'recipient_updated')->count())->toBe(1);
    });

    it('validates the method and the delivered-after-sent order', function () {
        $project = recipProject();
        $filing = recipFiling($project);
        $recipient = app(UpdateLienFilingRecipient::class)->add($filing, recipParty($project, PartyRole::Owner));

        $this->actingAs(recipAdmin());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('editRecipient', $recipient->id)
            ->set('recipientForm.delivery_method', 'carrier_pigeon')
            ->set('recipientForm.sent_at', '2026-09-12T09:00')
            ->set('recipientForm.delivered_at', '2026-09-11T09:00')
            ->call('updateRecipient')
            ->assertHasErrors(['recipientForm.delivery_method', 'recipientForm.delivered_at']);

        expect($recipient->fresh()->sent_at)->toBeNull();
    });
});

describe('service documents', function () {
    it('prints the proof of service from the address snapshot, not the live party', function () {
        $project = recipProject();
        $filing = recipFiling($project);
        $owner = recipParty($project, PartyRole::Owner);
        $recipient = app(UpdateLienFilingRecipient::class)->add($filing, $owner);
        app(UpdateLienFilingRecipient::class)->update($filing, $recipient, [
            'delivery_method' => 'certified_mail',
            'tracking_number' => '7018 1830 0000 4535 9376',
            'sent_at' => now()->parse('2026-09-11 16:00:00', 'UTC'),
        ]);

        // The party moves after mailing; the proof must still show where the copy went.
        $owner->update(['address1' => '1 Moved Away Blvd']);

        $filing = $filing->fresh();
        $text = recipText(app(LienServiceDocumentGenerator::class)->proofOfService($filing, $recipient->fresh()));

        expect($text)
            ->toContain('PROOF OF SERVICE')
            ->toContain('Affidavit Claiming a Mechanic\'s Lien · Sandwick water mitigation · 6006 Sandwick Dr, San Antonio, TX 78238 (Bexar County, Texas)')
            ->toContain('On September 11, 2026, I served a true and correct copy of the Affidavit Claiming a Mechanic\'s Lien')
            ->toContain('Served on Baraa Jouda (Property Owner) 6006 Sandwick Dr San Antonio, TX 78238')
            ->toContain('X Certified mail, return receipt requested')
            ->toContain('Tracking or article number 7018 1830 0000 4535 9376')
            ->toContain('I declare under penalty of perjury under the laws of the State of Kentucky that the foregoing is true and correct.')
            ->not->toContain('1 Moved Away Blvd')
            ->not->toContain('being first duly sworn');

        // California declarations recite California law wherever they are signed.
        $california = recipFiling(recipProject('CA', 'Los Angeles'));
        $californian = app(UpdateLienFilingRecipient::class)->add($california, recipParty($california->project, PartyRole::Owner));

        expect(recipText(app(LienServiceDocumentGenerator::class)->proofOfService($california->fresh(), $californian)))
            ->toContain('under the laws of the State of California');
    });

    it('writes an enclosure letter before recording and a notice of recording after', function () {
        $project = recipProject();
        $filing = recipFiling($project);
        $recipient = app(UpdateLienFilingRecipient::class)->add($filing, recipParty($project, PartyRole::Owner));
        $service = app(LienServiceDocumentGenerator::class);

        $before = recipText($service->coverLetter($filing->fresh(), $recipient));
        expect($before)
            ->toContain('Re: Affidavit Claiming a Mechanic\'s Lien · Sandwick water mitigation')
            ->toContain('Dear Baraa Jouda:')
            ->toContain('Enclosed is a true and correct copy of the Affidavit Claiming a Mechanic\'s Lien concerning the property described above, served on you on behalf of Restoration Solutions By Elite LLC.')
            ->toContain('You are receiving this copy as the owner of the property, as provided by Tex. Prop. Code § 53.054.')
            ->toContain('for Restoration Solutions By Elite LLC')
            ->not->toContain('Notice of recording');

        $filing->update(['recorded_at' => '2026-09-11 18:09:00', 'recording_reference' => '20260178790']);

        $after = recipText($service->coverLetter($filing->fresh(), $recipient));
        expect($after)
            ->toContain('Re: Notice of recording: Affidavit Claiming a Mechanic\'s Lien')
            ->toContain('recorded on September 11, 2026 in the official records of Bexar County, Texas, as 20260178790, against the property described above.');
    });

    it('lays labels out in Avery 5160 sheets with a return label after each recipient', function () {
        config(['lien.documents.return_label_lines' => ['eRegister', '100 Return Way Ste 1', 'Louisville, KY 40207']]);

        $project = recipProject();
        $filing = recipFiling($project);
        $action = app(UpdateLienFilingRecipient::class);
        $action->add($filing, recipParty($project, PartyRole::Owner));
        $action->add($filing, recipParty($project, PartyRole::Gc));

        $service = app(LienServiceDocumentGenerator::class);
        $filing = $filing->fresh();
        $doc = app(LienDocumentGenerator::class)->data($filing);
        $labels = $service->labelSheet($doc);

        expect($labels)->toHaveCount(4);
        expect($labels[0])->toBe(['Baraa Jouda', '6006 Sandwick Dr', 'San Antonio, TX 78238']);
        expect($labels[1])->toBe(['eRegister', '100 Return Way Ste 1', 'Louisville, KY 40207']);
        expect($labels[2])->toBe(['Alamo Builders LLC', 'Attn: Alex Rivera', '900 Commerce St', 'San Antonio, TX 78205']);

        // Avery 5160 geometry: 0.5in / 0.1875in margins, a 2.75in column pitch, 1in rows.
        $html = $service->labels($filing)->getHtml();
        expect($html)->toContain('@page { margin: 0.5in 0.1875in; }')
            ->toContain('left: 0in; top: 0in;')
            ->toContain('left: 2.75in; top: 0in;')
            ->toContain('left: 5.5in; top: 0in;')
            ->toContain('left: 0in; top: 1in;')
            ->not->toContain('class="sheet break"');
        expect(substr_count($html, 'class="label"'))->toBe(4);

        // Sixteen recipients make 32 labels: two sheets, the first breaking to the second.
        $many = array_fill(0, 16, ['id' => 1, 'party_id' => 1, 'role' => 'owner', 'role_label' => 'Owner', 'name' => 'Owner', 'company' => null, 'display_name' => 'Owner', 'address_lines' => ['1 Main St', 'Austin, TX 78701'], 'address_line' => null, 'state' => 'TX', 'delivery_method' => null, 'delivery_method_label' => null, 'tracking_number' => null, 'sent_at' => null, 'delivered_at' => null]);
        expect(array_chunk($service->labelSheet(['recipients' => $many, 'preparer' => $doc['preparer']]), LienServiceDocumentGenerator::LABELS_PER_SHEET))->toHaveCount(2);
    });

    it('prints a filing cover sheet only for mail-in offices and lists every piece in the package', function () {
        $texas = recipFiling(recipProject());
        $carolina = recipFiling(recipProject('NC', 'Mecklenburg'));
        $action = app(UpdateLienFilingRecipient::class);
        $owner = $action->add($texas, recipParty($texas->project, PartyRole::Owner));
        $action->add($carolina, recipParty($carolina->project, PartyRole::Owner));

        $texasItems = collect(LienDocumentPackage::forFiling($texas->fresh())->items);
        expect($texasItems->pluck('document')->map->value->all())
            ->toBe(['main', 'proof-of-service', 'cover-letter', 'labels']);
        expect($texasItems->firstWhere('document', LienPackageDocument::ProofOfService)['label'])->toBe('Proof of service: Baraa Jouda');
        expect($texasItems->firstWhere('document', LienPackageDocument::ProofOfService)['url'])
            ->toBe(route('admin.liens.documents.download', [$texas->public_id, 'proof-of-service', $owner->id]));
        expect(LienDocumentPackage::forFiling($texas->fresh())->warnings)
            ->toContain('The general contractor (Alamo Builders LLC) is not a recipient yet; add them under Recipients for the proof of service and labels.')
            ->not->toContain('The owner (Baraa Jouda) is not a recipient yet; add them under Recipients for the proof of service and labels.');

        $carolinaItems = collect(LienDocumentPackage::forFiling($carolina->fresh())->items);
        expect($carolinaItems->pluck('document')->map->value->all())
            ->toBe(['main', 'proof-of-service', 'cover-letter', 'labels', 'filing-cover-sheet']);

        $sheet = recipText(app(LienServiceDocumentGenerator::class)->filingCoverSheet($carolina->fresh()));
        expect($sheet)
            ->toContain('FILING COVER SHEET')
            ->toContain('Clerk of Superior Court (county where the property is located), Mecklenburg County, North Carolina')
            ->toContain('Original Claim of Lien on Real Property, signed and notarized')
            ->toContain('Claimant Restoration Solutions By Elite LLC');
    });

    it('downloads each piece and 404s the impossible ones', function () {
        $project = recipProject();
        $filing = recipFiling($project);
        $carolina = recipFiling(recipProject('NC', 'Mecklenburg'));
        $recipient = app(UpdateLienFilingRecipient::class)->add($filing, recipParty($project, PartyRole::Owner));
        $other = app(UpdateLienFilingRecipient::class)->add($carolina, recipParty($carolina->project, PartyRole::Owner));

        $this->actingAs(recipAdmin());

        $proof = $this->get(route('admin.liens.documents.download', [$filing->public_id, 'proof-of-service', $recipient->id]));
        $proof->assertOk();
        expect($proof->headers->get('content-disposition'))->toContain('Proof of Service Baraa Jouda');

        $this->get(route('admin.liens.documents.download', [$filing->public_id, 'cover-letter', $recipient->id]))->assertOk();
        $this->get(route('admin.liens.documents.download', [$filing->public_id, 'labels']))->assertOk();
        $this->get(route('admin.liens.documents.download', [$carolina->public_id, 'filing-cover-sheet']))->assertOk();

        $this->get(route('admin.liens.documents.download', [$filing->public_id, 'filing-cover-sheet']))->assertNotFound();
        $this->get(route('admin.liens.documents.download', [$filing->public_id, 'proof-of-service', $other->id]))->assertNotFound();
        $this->get(route('admin.liens.documents.download', [$filing->public_id, 'proof-of-service']))->assertNotFound();
        $this->get(route('admin.liens.documents.download', [$filing->public_id, 'labels', $recipient->id]))->assertNotFound();
    });
});
