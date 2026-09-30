<?php

/*
 * Shared builders for the generated lien document tests (one test file per
 * state under tests/Feature/Lien/). Loaded from tests/Pest.php so any single
 * test file can run on its own. Every function is guarded so a file that
 * defines its own copy still loads.
 */

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use Spatie\LaravelPdf\PdfBuilder;

if (! function_exists('lienFixtureProject')) {
    /**
     * A subcontractor's project in $state with claimant, owner, hiring party
     * and general contractor parties (the hiring party is the GC), a legal
     * description, a parcel number, furnishing dates and a $4,213.75 contract.
     * The business carries a lien signer and a contractor license.
     *
     * @param  array<string, mixed>  $overrides  project columns
     * @param  array<string, mixed>  $businessOverrides  business columns
     */
    function lienFixtureProject(string $state, ?string $county = 'Test', array $overrides = [], array $businessOverrides = []): LienProject
    {
        $business = Business::factory()->create(array_merge([
            'name' => 'Roser Construction LLC',
            'business_address' => ['line1' => '100 Lake Dr', 'city' => 'Lakeland', 'state' => 'FL', 'zip' => '33801'],
            'responsible_people' => [['user_id' => 1, 'name' => 'Steven Roser', 'title' => 'President', 'can_sign_liens' => true]],
            'contractor_license_number' => 'CGC-123456',
        ], $businessOverrides));

        $project = LienProject::factory()->forBusiness($business)->create(array_merge([
            'name' => 'Baywood Park drywall',
            'claimant_type' => ClaimantType::Subcontractor,
            'hired_by' => 'direct_contractor',
            'jobsite_address1' => '9025 Baywood Park Dr',
            'jobsite_address2' => null,
            'jobsite_city' => 'Seminole',
            'jobsite_state' => $state,
            'jobsite_zip' => '33777',
            'jobsite_county' => $county,
            'legal_description' => 'BAYWOOD PARK LOT 25',
            'apn' => '35-30-15-05699-000-0250',
            'first_furnish_date' => '2026-06-30',
            'last_furnish_date' => '2026-07-10',
            'base_contract_amount_cents' => 421375,
            'change_orders_cents' => 0,
            'credits_deductions_cents' => 0,
            'payments_received_cents' => 0,
            'uncompleted_work_cents' => 0,
            'prelim_notice_sent_at' => '2026-08-07 14:00:00',
        ], $overrides));

        lienFixtureParty($project, PartyRole::Claimant, 'Steven Roser', 'S G Roser Construction LLC', [
            'address1' => '4200 Lakeland Hwy', 'city' => 'Lakeland', 'state' => 'FL', 'zip' => '33801', 'phone' => '863-555-0100',
        ]);
        lienFixtureParty($project, PartyRole::Owner, 'Mike Stuntz', null, [
            'address1' => '9025 Baywood Park Dr', 'city' => 'Seminole', 'state' => $state, 'zip' => '33777',
        ]);
        lienFixtureParty($project, PartyRole::Customer, 'Ken Walker', 'Ken Walker Builders', [
            'address1' => '13700 58th St N Ste 204', 'city' => 'Clearwater', 'state' => 'FL', 'zip' => '33760',
        ]);
        lienFixtureParty($project, PartyRole::Gc, 'Ken Walker', 'Ken Walker Builders', [
            'address1' => '13700 58th St N Ste 204', 'city' => 'Clearwater', 'state' => 'FL', 'zip' => '33760',
        ]);

        return $project;
    }
}

if (! function_exists('lienFixtureParty')) {
    /**
     * @param  array<string, mixed>  $extra  party columns (address, phone, email)
     */
    function lienFixtureParty(LienProject $project, PartyRole $role, string $name, ?string $company = null, array $extra = []): LienParty
    {
        return LienParty::create(array_merge([
            'business_id' => $project->business_id,
            'project_id' => $project->id,
            'role' => $role,
            'name' => $name,
            'company_name' => $company,
        ], $extra));
    }
}

if (! function_exists('lienFixtureFiling')) {
    /**
     * A paid filing of $kind (prelim_notice, noi, mechanics_lien, lien_release,
     * demand_letter) on the project, claiming $4,213.75 for drywall work,
     * with the relations the generators read already loaded.
     *
     * @param  array<string, mixed>  $overrides  filing columns
     */
    function lienFixtureFiling(LienProject $project, string $kind = 'mechanics_lien', array $overrides = []): LienFiling
    {
        $type = LienDocumentType::where('slug', $kind)->firstOrFail();

        $filing = LienFiling::factory()->forProject($project)->paid()->create(array_merge([
            'document_type_id' => $type->id,
            'amount_claimed_cents' => 421375,
            'description_of_work' => 'Removal of drywall, replacement of drywall, and damage repair throughout the home',
        ], $overrides));

        return $filing->fresh(['documentType', 'project.business', 'project.parties']);
    }
}

if (! function_exists('lienFixtureText')) {
    /**
     * The rendered document as plain text: head dropped, block boundaries
     * turned into spaces, entities decoded, whitespace collapsed.
     */
    function lienFixtureText(PdfBuilder $pdf): string
    {
        $html = (string) preg_replace('/<head>.*?<\/head>/s', '', $pdf->getHtml());
        $html = (string) preg_replace('/<(br|\/p|\/div|\/td|\/tr|\/li|\/table)[^>]*>/i', ' ', $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
