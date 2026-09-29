<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Documents\LienDocumentGenerator;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('s3');
    LienDocumentRegistry::flush();
});

afterEach(fn () => LienDocumentRegistry::flush());

if (! function_exists('docdetAdmin')) {
    function docdetAdmin(string ...$permissions): User
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(...($permissions ?: ['lien.view', 'lien.update']));

        return $admin;
    }

    function docdetProject(string $state = 'TX', array $overrides = []): LienProject
    {
        $business = Business::factory()->create([
            'contractor_license_number' => 'TECL-44120',
            'responsible_people' => [['user_id' => 1, 'name' => 'Kathryn Melendez', 'title' => 'Owner', 'can_sign_liens' => true]],
        ]);

        $project = LienProject::factory()->forBusiness($business)->create(array_merge([
            'claimant_type' => ClaimantType::Gc,
            'hired_by' => 'owner',
            'jobsite_address1' => '5609 E County Rd 110',
            'jobsite_city' => 'Midland',
            'jobsite_state' => $state,
            'jobsite_zip' => '79706',
            'jobsite_county' => 'Midland',
            'legal_description' => 'Acres 1.000, SE/4, Sec 3, Blk 38-T2S',
            'apn' => 'R000003338',
            'first_furnish_date' => '2026-06-17',
            'last_furnish_date' => '2026-06-17',
            'base_contract_amount_cents' => 67500,
            'payments_received_cents' => 0,
        ], $overrides));

        foreach ([
            [PartyRole::Claimant, 'Kathryn Melendez', 'The Right Way Heating & A/C LLC', '4400 N Big Spring St', 'Midland'],
            [PartyRole::Owner, 'Richard Avakian', null, '2401 W County Rd 112', 'Midland'],
        ] as [$role, $name, $company, $address, $city]) {
            LienParty::create([
                'business_id' => $project->business_id,
                'project_id' => $project->id,
                'role' => $role,
                'name' => $name,
                'company_name' => $company,
                'address1' => $address,
                'city' => $city,
                'state' => $state,
                'zip' => '79706',
            ]);
        }

        return $project;
    }

    function docdetFiling(LienProject $project, string $kind = 'mechanics_lien', array $overrides = []): LienFiling
    {
        $type = LienDocumentType::where('slug', $kind)->firstOrFail();

        return LienFiling::factory()->forProject($project)->paid()->create(array_merge([
            'document_type_id' => $type->id,
            'amount_claimed_cents' => 67500,
            'description_of_work' => 'A/C repair',
            'payload_json' => ['project' => ['name' => $project->name], 'filing' => ['service_level' => 'full_service']],
        ], $overrides));
    }
}

describe('saving document details', function () {
    it('stores the set fields, logs a field-level event and leaves the snapshot untouched', function () {
        $filing = docdetFiling(docdetProject());
        $payloadBefore = json_encode($filing->fresh()->payload_json);

        $this->actingAs($admin = docdetAdmin());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('editDocumentDetails')
            ->assertSet('documentForm.signer_name', 'Kathryn Melendez')
            ->assertSet('documentForm.signer_title', 'Owner')
            ->assertSet('documentForm.license_number', 'TECL-44120')
            ->set('documentForm.signer_title', 'Managing Member')
            ->set('documentForm.contract_date', '2026-06-01')
            ->set('documentForm.contract_type', 'oral')
            ->set('documentForm.notice_served_at', '2026-07-15')
            ->set('documentForm.notice_served_method', 'certified_mail')
            ->set('documentForm.months_of_work', 'June 2026')
            ->set('documentForm.estimated_price', '1234.50')
            ->call('updateDocumentDetails')
            ->assertHasNoErrors()
            ->assertSet('showDocumentModal', false);

        $fresh = $filing->fresh();

        expect($fresh->document_details_json)->toEqual([
            'signer_name' => 'Kathryn Melendez',
            'signer_title' => 'Managing Member',
            'license_number' => 'TECL-44120',
            'contract_date' => '2026-06-01',
            'contract_type' => 'oral',
            'estimated_price_cents' => 123450,
            'notice_served_at' => '2026-07-15',
            'notice_served_method' => 'certified_mail',
            'months_of_work' => 'June 2026',
        ]);
        expect(json_encode($fresh->payload_json))->toBe($payloadBefore);

        $event = $fresh->events()->where('event_type', 'document_details_updated')->first();

        expect($event)->not->toBeNull();
        expect($event->created_by)->toBe($admin->id);
        expect($event->payload_json['changes']['signer_title'])->toEqual(['from' => null, 'to' => 'Managing Member']);
        expect($event->payload_json['changes']['months_of_work'])->toEqual(['from' => null, 'to' => 'June 2026']);
        expect($event->payload_json)->not->toHaveKey('meta');

        // The generated document picks the details up straight away.
        $text = html_entity_decode(strip_tags(app(LienDocumentGenerator::class)->render($fresh)->getHtml()));
        expect($text)->toContain('Managing Member')
            ->toContain('June 2026')
            ->toContain('July 15, 2026')
            ->toContain('certified mail, return receipt requested');
    });

    it('records only the changed fields and writes no event for a no-op save', function () {
        $filing = docdetFiling(docdetProject(), 'mechanics_lien', [
            'document_details_json' => ['signer_name' => 'Kathryn Melendez', 'months_of_work' => 'June 2026'],
        ]);

        $this->actingAs(docdetAdmin());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('editDocumentDetails')
            ->call('updateDocumentDetails')
            ->assertHasNoErrors();

        // The prefilled title and license are new values, so they are logged; the rest is unchanged.
        $events = $filing->fresh()->events()->where('event_type', 'document_details_updated')->get();
        expect($events)->toHaveCount(1);
        expect(array_keys($events->first()->payload_json['changes']))->toEqualCanonicalizing(['signer_title', 'license_number']);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->call('editDocumentDetails')
            ->call('updateDocumentDetails')
            ->assertHasNoErrors();

        expect($filing->fresh()->events()->where('event_type', 'document_details_updated')->count())->toBe(1);
    });

    it('clears the column when every field is blanked', function () {
        $filing = docdetFiling(docdetProject(), 'mechanics_lien', [
            'document_details_json' => ['months_of_work' => 'June 2026'],
        ]);

        $this->actingAs(docdetAdmin());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('editDocumentDetails')
            ->set('documentForm.signer_name', '')
            ->set('documentForm.signer_title', '')
            ->set('documentForm.license_number', '')
            ->set('documentForm.months_of_work', '  ')
            ->call('updateDocumentDetails')
            ->assertHasNoErrors();

        expect($filing->fresh()->document_details_json)->toBeNull();
        expect($filing->fresh()->events()->where('event_type', 'document_details_updated')->first()->payload_json['changes'])
            ->toEqual(['months_of_work' => ['from' => 'June 2026', 'to' => null]]);
    });

    it('validates dates, enums and money', function () {
        $filing = docdetFiling(docdetProject());

        $this->actingAs(docdetAdmin());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('editDocumentDetails')
            ->set('documentForm.contract_date', now()->addDay()->format('Y-m-d'))
            ->set('documentForm.contract_type', 'verbal')
            ->set('documentForm.notice_served_method', 'carrier_pigeon')
            ->set('documentForm.estimated_price', '-5')
            ->call('updateDocumentDetails')
            ->assertHasErrors([
                'documentForm.contract_date',
                'documentForm.contract_type',
                'documentForm.notice_served_method',
                'documentForm.estimated_price',
            ]);

        expect($filing->fresh()->document_details_json)->toBeNull();
    });

    it('prefills the lien being released from the recorded lien filing and stores it nested', function () {
        $project = docdetProject();
        docdetFiling($project, 'mechanics_lien', [
            'recording_reference' => '2026-28904',
            'recorded_at' => '2026-09-10 20:51:00',
            'jurisdiction_county' => 'Midland County',
        ]);
        $release = docdetFiling($project, 'lien_release');

        $this->actingAs(docdetAdmin());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $release])
            ->call('editDocumentDetails')
            ->assertSet('documentForm.original_lien.recording_reference', '2026-28904')
            ->assertSet('documentForm.original_lien.recorded_at', '2026-09-10')
            ->assertSet('documentForm.original_lien.county', 'Midland')
            ->set('documentForm.original_lien.book', '4021')
            ->set('documentForm.original_lien.page', '117')
            ->set('documentForm.original_lien.amount_received', '675.00')
            ->call('updateDocumentDetails')
            ->assertHasNoErrors();

        $details = $release->fresh()->document_details_json;

        expect($details['original_lien'])->toEqual([
            'recording_reference' => '2026-28904',
            'book' => '4021',
            'page' => '117',
            'county' => 'Midland',
            'recorded_at' => '2026-09-10',
            'amount_received_cents' => 67500,
        ]);

        $changes = $release->fresh()->events()->where('event_type', 'document_details_updated')->first()->payload_json['changes'];
        expect($changes['original_lien.book'])->toEqual(['from' => null, 'to' => '4021']);
        expect($changes['original_lien.amount_received_cents'])->toEqual(['from' => null, 'to' => 67500]);

        $text = html_entity_decode(strip_tags(app(LienDocumentGenerator::class)->render($release->fresh())->getHtml()));
        expect($text)->toContain('recorded on September 10, 2026 as 2026-28904')
            ->toContain('Book 4021, Page 117')
            ->toContain('receipt of $675.00 in');
    });

    it('shows the card and the timeline entry, and hides editing from view-only admins', function () {
        $filing = docdetFiling(docdetProject(), 'mechanics_lien', [
            'document_details_json' => ['signer_name' => 'Kathryn Melendez', 'signer_title' => 'Owner', 'months_of_work' => 'June 2026'],
        ]);
        $filing->events()->create([
            'business_id' => $filing->business_id,
            'event_type' => 'document_details_updated',
            'payload_json' => ['changes' => ['months_of_work' => ['from' => null, 'to' => 'June 2026']]],
        ]);

        $this->actingAs(docdetAdmin());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Document details')
            ->assertSee('Kathryn Melendez, Owner')
            ->assertSee('June 2026')
            ->assertSee('Document details updated')
            ->assertSee('Months of work:')
            ->assertSeeHtml('wire:click="editDocumentDetails"');

        $this->actingAs(docdetAdmin('lien.view'));

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Document details')
            ->assertDontSeeHtml('wire:click="editDocumentDetails"')
            ->call('updateDocumentDetails')
            ->assertForbidden();
    });

    it('has no card for a demand letter', function () {
        $filing = docdetFiling(docdetProject(), 'demand_letter');

        $this->actingAs(docdetAdmin());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertDontSee('Document details');
    });
});
