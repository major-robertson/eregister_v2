<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Documents\DemandLetterGenerator;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Livewire\Livewire;

function demandAdmin(): User
{
    $admin = User::factory()->create();
    $admin->givePermissionTo('lien.view');

    return $admin;
}

function demandParty(LienProject $project, PartyRole $role, string $name, ?string $company = null, array $extra = []): LienParty
{
    return LienParty::create(array_merge([
        'business_id' => $project->business_id,
        'project_id' => $project->id,
        'role' => $role,
        'name' => $name,
        'company_name' => $company,
        'address1' => '1 Main St',
        'city' => 'Los Angeles',
        'state' => 'CA',
        'zip' => '90001',
    ], $extra));
}

/** Project with a claimant (sender) + three recipient parties (owner, customer, gc). */
function demandProject(): LienProject
{
    $project = LienProject::factory()->forBusiness(Business::factory()->create())->create([
        'name' => 'Riverside Remodel',
        'first_furnish_date' => '2026-01-05',
        'last_furnish_date' => '2026-03-10',
    ]);

    demandParty($project, PartyRole::Claimant, 'Carl Claimant', 'Carl Construction LLC', [
        'address1' => '1 Builder Ave', 'city' => 'San Diego', 'zip' => '92101',
        'email' => 'carl@example.com', 'phone' => '619-555-0100',
    ]);
    demandParty($project, PartyRole::Owner, 'Olivia Owner', 'Owner Holdings');
    demandParty($project, PartyRole::Customer, 'Casey Customer', 'Customer Co');
    demandParty($project, PartyRole::Gc, 'Gary GC', 'GC Builders');

    return $project;
}

function demandFiling(LienProject $project, string $slug, array $overrides = []): LienFiling
{
    $type = LienDocumentType::where('slug', $slug)->firstOrFail();

    return LienFiling::factory()->forProject($project)->paid()->create(array_merge([
        'document_type_id' => $type->id,
        'amount_claimed_cents' => 1_250_000,
        'description_of_work' => 'framing and drywall',
    ], $overrides));
}

describe('per-recipient download', function () {
    it('streams a one-page PDF addressed to a recipient party', function () {
        $admin = demandAdmin();
        $project = demandProject();
        $filing = demandFiling($project, 'demand_letter');
        $owner = $project->ownerParty();

        $this->actingAs($admin);

        $response = $this->get(route('admin.liens.demand-letter', [$filing->public_id, $owner->id]));

        $response->assertOk();
        expect($response->headers->get('content-type'))->toContain('application/pdf');
        expect($response->headers->get('content-disposition'))
            ->toContain('attachment')
            ->toContain("demand-letter-{$filing->public_id}");
        expect(substr(pdfBytes($response), 0, 4))->toBe('%PDF');
    });

    it('populates the letter from the application (recipient, contractor, amount, work)', function () {
        $project = demandProject();
        $filing = demandFiling($project, 'demand_letter');
        $owner = $project->ownerParty();
        $gc = $project->gcParty();

        $generator = app(DemandLetterGenerator::class);

        expect($generator->render($filing, $owner)->contains([
            'Olivia Owner', 'Owner Holdings',           // recipient
            'Carl Claimant', 'Carl Construction LLC',   // contractor / sender
            '619-555-0100', 'carl@example.com',
            '12,500.00',                                // amount due
            'framing and drywall',                      // work
            'January 5, 2026', 'March 10, 2026',        // start / end dates
        ]))->toBeTrue();

        // Each letter is addressed to its own recipient only.
        expect($generator->render($filing, $owner)->contains(['Gary GC']))->toBeFalse();
        expect($generator->render($filing, $gc)->contains(['Gary GC']))->toBeTrue();
    });

    it('404s for an unknown party, the claimant, or a non-demand-letter filing', function () {
        $admin = demandAdmin();
        $project = demandProject();
        $filing = demandFiling($project, 'demand_letter');
        $claimant = $project->claimantParty();

        $this->actingAs($admin);

        $this->get(route('admin.liens.demand-letter', [$filing->public_id, 999999]))->assertNotFound();
        $this->get(route('admin.liens.demand-letter', [$filing->public_id, $claimant->id]))->assertNotFound();

        $lien = demandFiling($project, 'mechanics_lien');
        $this->get(route('admin.liens.demand-letter', [$lien->public_id, $project->ownerParty()->id]))->assertNotFound();
    });
});

describe('download all', function () {
    it('streams one combined PDF covering every recipient', function () {
        $admin = demandAdmin();
        $project = demandProject();
        $filing = demandFiling($project, 'demand_letter');

        $this->actingAs($admin);

        $response = $this->get(route('admin.liens.demand-letters', $filing->public_id));

        $response->assertOk();
        expect($response->headers->get('content-disposition'))
            ->toContain("demand-letters-{$filing->public_id}-all");
        expect(substr(pdfBytes($response), 0, 4))->toBe('%PDF');

        expect(app(DemandLetterGenerator::class)
            ->renderAll($filing, $project->nonClaimantParties())
            ->contains(['Olivia Owner', 'Casey Customer', 'Gary GC']))->toBeTrue();
    });

    it('404s when the project has no recipients', function () {
        $admin = demandAdmin();
        $project = LienProject::factory()->forBusiness(Business::factory()->create())->create();
        demandParty($project, PartyRole::Claimant, 'Solo Claimant', 'Solo LLC');
        $filing = demandFiling($project, 'demand_letter');

        $this->actingAs($admin);

        $this->get(route('admin.liens.demand-letters', $filing->public_id))->assertNotFound();
    });
});

describe('authorization', function () {
    it('forbids a user without lien.view', function () {
        $project = demandProject();
        $filing = demandFiling($project, 'demand_letter');

        $this->actingAs(User::factory()->create());

        $this->get(route('admin.liens.demand-letter', [$filing->public_id, $project->ownerParty()->id]))
            ->assertForbidden();
    });

    it('redirects a guest to login', function () {
        $project = demandProject();
        $filing = demandFiling($project, 'demand_letter');

        $this->get(route('admin.liens.demand-letters', $filing->public_id))->assertRedirect();
    });
});

describe('dropdown affordance', function () {
    it('lists a menu item per recipient plus download-all for a demand letter', function () {
        $admin = demandAdmin();
        $project = demandProject();
        $filing = demandFiling($project, 'demand_letter');

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Owner Holdings')
            ->assertSee('Property Owner')
            ->assertSee('Customer Co')
            ->assertSee('GC Builders')
            ->assertSee('Download all (3)')
            ->assertSeeHtml(route('admin.liens.demand-letter', [$filing->public_id, $project->ownerParty()->id]))
            ->assertSeeHtml(route('admin.liens.demand-letters', $filing->public_id));
    });

    it('is hidden for a non-demand-letter filing', function () {
        $admin = demandAdmin();
        $project = demandProject();
        $filing = demandFiling($project, 'mechanics_lien');

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertDontSee('Download all');
    });

    it('is hidden when a demand letter has no recipients', function () {
        $admin = demandAdmin();
        $project = LienProject::factory()->forBusiness(Business::factory()->create())->create();
        demandParty($project, PartyRole::Claimant, 'Solo Claimant', 'Solo LLC');
        $filing = demandFiling($project, 'demand_letter');

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertDontSee('Download all');
    });
});

/**
 * Pull the raw body out of the download response regardless of whether the PDF
 * package returns a standard or streamed response.
 */
function pdfBytes(\Illuminate\Testing\TestResponse $response): string
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
