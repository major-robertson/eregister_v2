<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
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

if (! function_exists('liendocAdmin')) {
    function liendocAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        return $admin;
    }

    /** A Georgia subcontractor project with claimant, owner and GC parties. */
    function liendocDownloadProject(string $state = 'GA', ?string $county = 'Cherokee', array $overrides = []): LienProject
    {
        $project = LienProject::factory()->forBusiness(Business::factory()->create([
            'responsible_people' => [['user_id' => 1, 'name' => 'Andrew Redden', 'title' => 'Director of Operations', 'can_sign_liens' => true]],
        ]))->create(array_merge([
            'claimant_type' => ClaimantType::Subcontractor,
            'hired_by' => 'direct_contractor',
            'jobsite_address1' => '10451 Bells Ferry Rd',
            'jobsite_city' => 'Canton',
            'jobsite_state' => $state,
            'jobsite_zip' => '30114',
            'jobsite_county' => $county,
            'legal_description' => 'Land Lots 135 and 136, 15th District',
            'apn' => '15N07 047 A',
            'first_furnish_date' => '2026-04-01',
            'last_furnish_date' => '2026-05-31',
            'base_contract_amount_cents' => 6329000,
            'payments_received_cents' => 0,
        ], $overrides));

        foreach ([
            [PartyRole::Claimant, 'Andrew Redden', 'Yard-Nique Inc', '10014 Chapel Hill Rd', 'Morrisville', 'NC', '27560'],
            [PartyRole::Owner, 'JO-ASH Bells Ferry LLC', 'JO-ASH Bells Ferry LLC', '1140 Avenue of the Americas Ste 1701', 'New York', 'NY', '10036'],
            [PartyRole::Gc, 'Jonathan Kaplan', 'United Group', '1609 6th Ave 3rd Fl', 'Troy', 'NY', '12180'],
        ] as [$role, $name, $company, $address1, $city, $st, $zip]) {
            LienParty::create([
                'business_id' => $project->business_id,
                'project_id' => $project->id,
                'role' => $role,
                'name' => $name,
                'company_name' => $company,
                'address1' => $address1,
                'city' => $city,
                'state' => $st,
                'zip' => $zip,
            ]);
        }

        return $project;
    }

    function liendocDownloadFiling(LienProject $project, string $kind, array $overrides = []): LienFiling
    {
        $type = LienDocumentType::where('slug', $kind)->firstOrFail();

        return LienFiling::factory()->forProject($project)->paid()->create(array_merge([
            'document_type_id' => $type->id,
            'amount_claimed_cents' => 6329000,
            'description_of_work' => 'Landscaping and irrigation',
        ], $overrides));
    }

    /**
     * The raw body of a download response, standard or streamed.
     */
    function liendocPdfBytes(\Illuminate\Testing\TestResponse $response): string
    {
        $base = $response->baseResponse;

        if ($base instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
            return $response->streamedContent();
        }

        if ($base instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
            return (string) file_get_contents($base->getFile()->getPathname());
        }

        return (string) $response->getContent();
    }
}

describe('main document download', function () {
    it('streams the lien as a PDF named after the claimant and title', function () {
        $filing = liendocDownloadFiling(liendocDownloadProject(), 'mechanics_lien');

        $this->actingAs(liendocAdmin());

        $response = $this->get(route('admin.liens.documents.download', [$filing->public_id, 'main']));

        $response->assertOk();
        expect($response->headers->get('content-type'))->toContain('application/pdf');
        expect($response->headers->get('content-disposition'))
            ->toContain('attachment')
            ->toContain('Yard-Nique Inc Claim of Lien');
        expect(substr(liendocPdfBytes($response), 0, 4))->toBe('%PDF');
    });

    it('still downloads for a soft-deleted filing', function () {
        $filing = liendocDownloadFiling(liendocDownloadProject(), 'mechanics_lien');
        $filing->delete();

        $this->actingAs(liendocAdmin());

        $this->get(route('admin.liens.documents.download', [$filing->public_id, 'main']))->assertOk();
    });

    it('404s for a demand letter, an attorney-only state, a letter kind without a layout yet, and the service pieces', function () {
        $project = liendocDownloadProject();
        $demand = liendocDownloadFiling($project, 'demand_letter');
        $lien = liendocDownloadFiling($project, 'mechanics_lien');
        $prelim = liendocDownloadFiling($project, 'prelim_notice');
        $hawaii = liendocDownloadFiling(liendocDownloadProject('HI', 'Honolulu'), 'mechanics_lien');

        $this->actingAs(liendocAdmin());

        $this->get(route('admin.liens.documents.download', [$demand->public_id, 'main']))->assertNotFound();
        $this->get(route('admin.liens.documents.download', [$hawaii->public_id, 'main']))->assertNotFound();
        $this->get(route('admin.liens.documents.download', [$prelim->public_id, 'main']))->assertNotFound();
        $this->get(route('admin.liens.documents.download', [$lien->public_id, 'labels']))->assertNotFound();
        $this->get(route('admin.liens.documents.download', [$lien->public_id, 'proof-of-service', 1]))->assertNotFound();
        $this->get(route('admin.liens.documents.download', [$lien->public_id, 'main', 1]))->assertNotFound();
        $this->get("/admin/liens/{$lien->public_id}/documents/bogus")->assertNotFound();
        $this->get(route('admin.liens.documents.download', ['01UNKNOWNPUBLICID00000000', 'main']))->assertNotFound();
    });

    it('forbids a user without lien.view and redirects a guest', function () {
        $filing = liendocDownloadFiling(liendocDownloadProject(), 'mechanics_lien');
        $url = route('admin.liens.documents.download', [$filing->public_id, 'main']);

        $this->get($url)->assertRedirect();

        $this->actingAs(User::factory()->create());
        $this->get($url)->assertForbidden();
    });
});

describe('Documents card', function () {
    it('shows the rules, the warnings and the download link for a lien', function () {
        $filing = liendocDownloadFiling(liendocDownloadProject('GA', 'Cherokee', ['legal_description' => null]), 'mechanics_lien');

        $this->actingAs(liendocAdmin());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Documents')
            ->assertSee('Claim of Lien')
            ->assertSee('Georgia · Cherokee County · template v1')
            ->assertSee('Clerk of Superior Court (county where the property is located), e-recording via GSCCCA eFile')
            ->assertSee('Sworn to and signed before a notary (jurat)')
            ->assertSee('The owner and the general contractor by certified mail, return receipt requested within 2 days after recording.')
            ->assertSee('Check before generating')
            ->assertSee('No legal description on the project; recorders reject instruments without one.')
            ->assertSeeHtml(route('admin.liens.documents.download', [$filing->public_id, 'main']))
            ->assertDontSee('Demand Letters');
    });

    it('explains an attorney-only state instead of linking', function () {
        $filing = liendocDownloadFiling(liendocDownloadProject('HI', 'Honolulu'), 'mechanics_lien');

        $this->actingAs(liendocAdmin());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Documents')
            ->assertSee('Hawaii liens are filed through an attorney; eRegister does not prepare the document.')
            ->assertDontSeeHtml(route('admin.liens.documents.download', [$filing->public_id, 'main']));
    });

    it('keeps the Demand Letters card for a demand letter and shows no Documents card', function () {
        $filing = liendocDownloadFiling(liendocDownloadProject(), 'demand_letter');

        $this->actingAs(liendocAdmin());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Demand Letters')
            ->assertDontSee('Check before generating')
            ->assertDontSeeHtml(route('admin.liens.documents.download', [$filing->public_id, 'main']));
    });
});
