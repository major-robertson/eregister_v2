<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Documents\LienDocumentGenerator;
use App\Domains\Lien\Documents\LienDocumentPackage;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Documents\MoneyWords;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\PdfBuilder;

beforeEach(function () {
    Storage::fake('s3');
    LienDocumentRegistry::flush();
});

afterEach(fn () => LienDocumentRegistry::flush());

if (! function_exists('liendocRenderProject')) {
    /**
     * A subcontractor's project with claimant, owner, hiring party and GC
     * parties, modelled on a 2026 Pinellas County claim of lien.
     */
    function liendocRenderProject(string $state = 'FL', ?string $county = 'Pinellas', array $overrides = []): LienProject
    {
        $business = Business::factory()->create([
            'name' => 'Roser Construction LLC',
            'business_address' => ['line1' => '100 Lake Dr', 'city' => 'Lakeland', 'state' => 'FL', 'zip' => '33801'],
            'responsible_people' => [['user_id' => 1, 'name' => 'Steven Roser', 'title' => 'President', 'can_sign_liens' => true]],
            'contractor_license_number' => 'CGC-123456',
        ]);

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

        liendocRenderParty($project, PartyRole::Claimant, 'Steven Roser', 'S G Roser Construction LLC', [
            'address1' => '4200 Lakeland Hwy', 'city' => 'Lakeland', 'state' => 'FL', 'zip' => '33801', 'phone' => '863-555-0100',
        ]);
        liendocRenderParty($project, PartyRole::Owner, 'Mike Stuntz', null, [
            'address1' => '9025 Baywood Park Dr', 'city' => 'Seminole', 'state' => 'FL', 'zip' => '33777',
        ]);
        liendocRenderParty($project, PartyRole::Customer, 'Ken Walker', 'Ken Walker Builders', [
            'address1' => '13700 58th St N Ste 204', 'city' => 'Clearwater', 'state' => 'FL', 'zip' => '33760',
        ]);
        liendocRenderParty($project, PartyRole::Gc, 'Ken Walker', 'Ken Walker Builders', [
            'address1' => '13700 58th St N Ste 204', 'city' => 'Clearwater', 'state' => 'FL', 'zip' => '33760',
        ]);

        return $project;
    }

    function liendocRenderParty(LienProject $project, PartyRole $role, ?string $name, ?string $company, array $extra = []): LienParty
    {
        return LienParty::create(array_merge([
            'business_id' => $project->business_id,
            'project_id' => $project->id,
            'role' => $role,
            'name' => $name,
            'company_name' => $company,
        ], $extra));
    }

    function liendocRenderFiling(LienProject $project, string $kind, array $overrides = []): LienFiling
    {
        $type = LienDocumentType::where('slug', $kind)->firstOrFail();

        $filing = LienFiling::factory()->forProject($project)->paid()->create(array_merge([
            'document_type_id' => $type->id,
            'amount_claimed_cents' => 421375,
            'description_of_work' => 'Removal of drywall, replacement of drywall, and damage repair throughout the home',
        ], $overrides));

        return $filing->fresh(['documentType', 'project.business', 'project.parties']);
    }

    /** The rendered document as plain text, one space between words, without the head. */
    function liendocText(PdfBuilder $pdf): string
    {
        $html = (string) preg_replace('/<head>.*?<\/head>/s', '', $pdf->getHtml());
        $html = (string) preg_replace('/<(br|\/p|\/div|\/td|\/tr|\/li|\/table)[^>]*>/i', ' ', $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}

describe('Florida claim of lien', function () {
    it('renders the statutory form with the warning, the privity sentence and the physical-or-online jurat', function () {
        $project = liendocRenderProject();
        $filing = liendocRenderFiling($project, 'mechanics_lien');
        $generator = app(LienDocumentGenerator::class);

        $pdf = $generator->render($filing);
        $text = liendocText($pdf);
        $html = $pdf->getHtml();

        foreach ([
            'WARNING! THIS LEGAL DOCUMENT REFLECTS THAT A CONSTRUCTION LIEN HAS BEEN PLACED ON THE REAL PROPERTY LISTED HEREIN.',
            'CLAIM OF LIEN',
            'Fla. Stat. § 713.08',
            'STATE OF FLORIDA',
            'COUNTY OF PINELLAS',
            'Before me, the undersigned notary public, personally appeared Steven Roser, who was duly sworn and says that he or she is the President and agent of the lienor herein, S G Roser Construction LLC,',
            'whose address is 4200 Lakeland Hwy, Lakeland, FL 33801',
            'in accordance with a contract with Ken Walker Builders',
            'Removal of drywall, replacement of drywall, and damage repair throughout the home',
            'on the following described real property in Pinellas County, Florida',
            'BAYWOOD PARK LOT 25',
            'Parcel ID: 35-30-15-05699-000-0250',
            'owned by Mike Stuntz of a total value of Four Thousand Two Hundred Thirteen Dollars and Seventy-Five Cents ($4,213.75), of which there remains unpaid $4,213.75',
            'furnished the first of the items on June 30, 2026, and the last of the items on July 10, 2026',
            'served her or his notice to owner on August 7, 2026',
            'Sworn to (or affirmed) and subscribed before me by means of physical presence or online notarization',
            '(Signature of Notary Public - State of Florida)',
            'Personally Known OR Produced Identification',
            'Space above this line for recorder\'s use only',
            'Prepared by, recording requested by and return to: eRegister',
            'Claimant / Lienor S G Roser Construction LLC',
            'Amount claimed $4,213.75',
            'Page',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // No sworn preface above the signature: the body is the sworn statement.
        expect($text)->not->toContain('being first duly sworn');
        expect($text)->not->toContain('N/A')->not->toContain('—');

        // Recorder rules: 1in margins, a 2in space above the rule on page 1, page numbers.
        expect($html)->toContain('@page { margin: 1in; }')
            ->toContain('height: 2in')
            ->toContain('counter(page)');
    });

    it('drops the notice-to-owner sentence when the claimant contracted with the owner', function () {
        $project = liendocRenderProject('FL', 'Pinellas', ['hired_by' => 'owner', 'claimant_type' => ClaimantType::Gc]);
        $filing = liendocRenderFiling($project, 'mechanics_lien');

        $text = liendocText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)->not->toContain('notice to owner on');
    });

    it('renders a release that identifies the recorded lien and acknowledges before a notary', function () {
        $project = liendocRenderProject();
        liendocRenderFiling($project, 'mechanics_lien', [
            'recording_reference' => 'INSTR 2026238608 BK 23684 PG 1546',
            'recorded_at' => '2026-09-04 15:25:00',
        ]);
        $release = liendocRenderFiling($project, 'lien_release');

        $text = liendocText(app(LienDocumentGenerator::class)->render($release));

        expect($text)
            ->toContain('RELEASE OF LIEN')
            ->toContain('Fla. Stat. § 713.21')
            ->toContain('KNOW ALL PERSONS BY THESE PRESENTS that S G Roser Construction LLC')
            ->toContain('Claim of Lien recorded on September 4, 2026 as INSTR 2026238608 BK 23684 PG 1546 in the official records of Pinellas County, Florida')
            ->toContain('releases, discharges and cancels the lien')
            ->toContain('cancel and discharge the Claim of Lien of record in accordance with § 713.21')
            ->toContain('The foregoing instrument was acknowledged before me by means of physical presence or online notarization')
            ->toContain('by Steven Roser as President for S G Roser Construction LLC')
            ->not->toContain('Amount claimed');
    });
});

describe('generic bodies', function () {
    it('renders Georgia in the § 44-14-361.1 form with the 12-point bold expiry statement, the contest notice and the cancellation block', function () {
        $project = liendocRenderProject('GA', 'Cherokee');
        $filing = liendocRenderFiling($project, 'mechanics_lien');

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = liendocText($pdf);

        expect($text)
            ->toContain('CLAIM OF LIEN')
            ->toContain('O.C.G.A. § 44-14-361.1')
            ->toContain('This claim of lien expires and is void 395 days from the date of filing of the claim of lien if no notice of commencement of lien action is filed in that time period.')
            ->toContain('S G Roser Construction LLC, a subcontractor, claims a lien in the amount of $4,213.75 on the building, structure and improvements and the premises or real estate on which they are erected or built, of Mike Stuntz, described as follows:')
            ->toContain('9025 Baywood Park Dr, Seminole, GA 33777 (Cherokee County, Georgia)')
            ->toContain('Tax Parcel ID: 35-30-15-05699-000-0250')
            ->toContain('for satisfaction of a claim which became due on July 10, 2026 (the last date the labor, services or materials were supplied to the premises) for Removal of drywall, replacement of drywall, and damage repair throughout the home.')
            ->toContain('furnished at the instance of Ken Walker Builders, 13700 58th St N Ste 204, Clearwater, FL 33760.')
            ->toContain('Contract amount')
            ->toContain('right to contest this claim of lien')
            ->toContain('being first duly sworn')
            ->toContain('Subscribed and sworn to (or affirmed) before me')
            ->toContain('by Steven Roser, President of S G Roser Construction LLC')
            ->toContain('CANCELLATION OF CLAIM OF LIEN');

        // § 44-14-367: "in at least 12 point bold font" on the face of the lien.
        expect($pdf->getHtml())->toContain('class="bold-statement"')
            ->toContain('.bold-statement { font-size: 12pt; font-weight: bold;');
        expect(strpos($text, 'This claim of lien expires'))->toBeLessThan(strpos($text, 'claims a lien in the amount'));
    });

    it('renders the generic lien body for a state without a prescribed form', function () {
        $project = liendocRenderProject('OH', 'Franklin');
        $filing = liendocRenderFiling($project, 'mechanics_lien');

        $text = liendocText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('CLAIM OF LIEN')
            ->toContain('STATE OF OHIO')
            ->toContain('COUNTY OF FRANKLIN')
            ->toContain('S G Roser Construction LLC ("Claimant") claims a lien upon the real property')
            ->toContain('Claimant furnished the labor, services, equipment or materials described below as a subcontractor.')
            ->toContain('Owner or reputed owner of the property. Mike Stuntz')
            ->toContain('Person who contracted with Claimant. Ken Walker Builders')
            ->toContain('Property subject to the lien. 9025 Baywood Park Dr, Seminole, OH 33777 (Franklin County, Ohio)')
            ->toContain('First furnished: June 30, 2026')
            ->toContain('After deducting all just credits and offsets, the amount claimed is $4,213.75.')
            ->toContain('Prior notice. Claimant served its Preliminary Notice on August 7, 2026')
            ->toContain('being first duly sworn')
            ->toContain('Subscribed and sworn to (or affirmed) before me');

        // The GC is the hiring party here, so it is not listed twice.
        expect(substr_count($text, 'Ken Walker Builders'))->toBe(1);
    });

    it('renders Texas with the months of work, retainage and notice statements', function () {
        $project = liendocRenderProject('TX', 'Bexar');
        $filing = liendocRenderFiling($project, 'mechanics_lien');

        $text = liendocText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('AFFIDAVIT CLAIMING A MECHANIC\'S LIEN')
            ->toContain('Tex. Prop. Code § 53.054')
            ->toContain('Months in which the work was done and materials furnished for which payment is requested:')
            ->toContain('Less retainage withheld')
            ->toContain('Property ID: 35-30-15-05699-000-0250')
            ->toContain('sworn to as provided by Tex. Prop. Code § 53.054(a)(1)');
    });

    it('renders Arizona with the license, contract and completion lines', function () {
        $project = liendocRenderProject('AZ', 'Maricopa', ['has_written_contract' => true]);
        $filing = liendocRenderFiling($project, 'mechanics_lien');

        $text = liendocText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('NOTICE AND CLAIM OF MECHANIC\'S AND MATERIALMAN\'S LIEN')
            ->toContain('Contractor\'s license number: CGC-123456')
            ->toContain('The contract was written; a copy is attached.')
            ->toContain('Date of the contract:')
            ->toContain('Date of completion of the work of improvement:')
            ->toContain('APN: 35-30-15-05699-000-0250')
            ->toContain('Preliminary Twenty Day Lien Notice on August 7, 2026')
            ->toContain('A.R.S. § 33-1002');
    });

    it('renders California verified without a notary, with the boxed notice and the proof of service affidavit', function () {
        $project = liendocRenderProject('CA', 'Los Angeles');
        $filing = liendocRenderFiling($project, 'mechanics_lien');

        $text = liendocText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('CLAIM OF MECHANICS LIEN')
            ->toContain('Cal. Civ. Code § 8416')
            ->toContain('NOTICE OF MECHANICS LIEN ATTENTION! Upon the recording of the enclosed MECHANICS LIEN')
            ->toContain('BECAUSE THE LIEN AFFECTS YOUR PROPERTY, YOU MAY WISH TO SPEAK WITH YOUR CONTRACTOR IMMEDIATELY, OR CONTACT AN ATTORNEY, OR FOR MORE INFORMATION ON MECHANICS LIENS GO TO THE CONTRACTORS\' STATE LICENSE BOARD WEB SITE AT www.cslb.ca.gov.')
            ->toContain('I, Steven Roser, declare that I am the President of S G Roser Construction LLC')
            ->toContain('I declare under penalty of perjury under the laws of the State of California that the foregoing is true and correct.')
            ->toContain('PROOF OF SERVICE AFFIDAVIT')
            ->toContain('on the owner or reputed owner of the property described in it, Mike Stuntz, at 9025 Baywood Park Dr, Seminole, FL 33777')
            ->not->toContain('My commission expires');
    });

    it('renders a California release with the § 1189 acknowledgment', function () {
        $project = liendocRenderProject('CA', 'Los Angeles');
        $release = liendocRenderFiling($project, 'lien_release');

        $text = liendocText(app(LienDocumentGenerator::class)->render($release));

        expect($text)
            ->toContain('A notary public or other officer completing this certificate verifies only the identity of the individual who signed the document to which this certificate is attached, and not the truthfulness, accuracy, or validity of that document.')
            ->toContain('State of California County of')
            ->toContain('who proved to me on the basis of satisfactory evidence to be the person(s) whose name(s) is/are subscribed to the within instrument')
            ->toContain('I certify under PENALTY OF PERJURY under the laws of the State of California that the foregoing paragraph is true and correct.')
            ->toContain('WITNESS my hand and official seal.')
            ->not->toContain('PROOF OF SERVICE AFFIDAVIT');
    });
});

describe('North Carolina claim of lien', function () {
    it('renders the § 44A-12(c) form in order with the 44A-11 certification and the clerk lines', function () {
        $project = liendocRenderProject('NC', 'Mecklenburg');
        $filing = liendocRenderFiling($project, 'mechanics_lien');

        $text = liendocText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            'CLAIM OF LIEN ON REAL PROPERTY',
            'N.C. Gen. Stat. § 44A-12',
            '(1) Name and address of the person claiming the claim of lien on real property: S G Roser Construction LLC Steven Roser 4200 Lakeland Hwy',
            '(2) Name and address of the record owner of the real property',
            'Contractor through which subrogation is asserted: Ken Walker Builders',
            '(3) Description of the real property upon which the claim of lien on real property is claimed:',
            'PIN: 35-30-15-05699-000-0250',
            '(4) Name and address of the person with whom the claimant contracted for the furnishing of labor or materials: Ken Walker Builders',
            '(5) Date upon which labor or materials were first furnished upon said property by the claimant: June 30, 2026',
            '(5a) Date upon which labor or materials were last furnished upon said property by the claimant: July 10, 2026',
            '(6) General description of the labor performed or materials furnished and the amount claimed therefor:',
            'Amount claimed: $4,213.75',
            'I hereby certify that I have served the parties listed in (2) above in accordance with the requirements of G.S. 44A-11.',
            'County, North Carolina Sworn to and subscribed before me this day by Steven Roser.',
            'Official Signature of Notary',
            'Filed this',
            'Clerk of Superior Court',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // The statutory order survives: each fragment starts after the one before it.
        $needles = ['(1) Name', '(2) Name', '(3) Description', '(4) Name', '(5) Date', '(5a) Date', '(6) General', 'I hereby certify', 'Sworn to and subscribed', 'Filed this'];
        $positions = array_map(fn (string $needle) => strpos($text, $needle), $needles);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false);
        expect($positions)->toBe($sorted);
    });
});

describe('county files and blanks', function () {
    it('prints the Missouri index block and moves the preparer below the rule for Jackson County only', function () {
        $jackson = liendocRenderFiling(liendocRenderProject('MO', 'Jackson County'), 'mechanics_lien');
        $clay = liendocRenderFiling(liendocRenderProject('MO', 'Clay'), 'mechanics_lien');
        $generator = app(LienDocumentGenerator::class);

        $jacksonText = liendocText($generator->render($jackson));
        $clayText = liendocText($generator->render($clay));

        expect($jacksonText)
            ->toContain('Grantor (mailing address) Mike Stuntz, 9025 Baywood Park Dr, Seminole, FL 33777')
            ->toContain('Grantee (mailing address) S G Roser Construction LLC, 4200 Lakeland Hwy');
        expect(strpos($jacksonText, 'Space above this line'))->toBeLessThan(strpos($jacksonText, 'Prepared by, recording requested by'));

        expect($clayText)->not->toContain('Grantor (mailing address)');
        expect(strpos($clayText, 'Prepared by, recording requested by'))->toBeLessThan(strpos($clayText, 'Space above this line'));
    });

    it('prints ruled blanks, never N/A or a dash, when the application is thin', function () {
        $business = Business::factory()->create(['business_address' => null, 'responsible_people' => null]);
        $project = LienProject::factory()->forBusiness($business)->create([
            'jobsite_state' => 'GA', 'jobsite_county' => null, 'jobsite_county_google' => null,
            'legal_description' => null, 'apn' => null, 'hired_by' => null, 'claimant_type' => ClaimantType::Other,
            'first_furnish_date' => null, 'last_furnish_date' => null,
            'base_contract_amount_cents' => null,
        ]);
        liendocRenderParty($project, PartyRole::Claimant, 'Thin Co', 'Thin Co', ['address1' => null, 'city' => null, 'state' => null, 'zip' => null]);
        $filing = liendocRenderFiling($project, 'mechanics_lien', ['amount_claimed_cents' => null, 'description_of_work' => null, 'jurisdiction_county' => null]);

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = liendocText($pdf);

        expect($text)->not->toContain('N/A')->not->toContain('—');
        expect($pdf->getHtml())->toContain('class="fill fill-wide"');
        expect($text)->toContain('COUNTY OF');

        $package = LienDocumentPackage::forFiling($filing);

        expect($package->isAvailable())->toBeTrue();
        expect($package->warnings)
            ->toContain('No county is set on the filing or project; the caption and filing office cannot be filled in.')
            ->toContain('No legal description on the project; recorders reject instruments without one.')
            ->toContain('The claimant has no mailing address on the party or the business.')
            ->toContain('No owner party on the project.')
            ->toContain('No signer: add a responsible person who can sign liens under the business, or set one in Document details.')
            ->toContain('No amount claimed on the filing and no balance due on the project.')
            ->toContain('No last furnishing date on the project.');
    });
});

describe('package warnings', function () {
    it('flags pasted look-alike characters, citation markers and an amount that does not add up', function () {
        $project = liendocRenderProject('FL', 'Pinellas', [
            'legal_description' => "BAYWOOD PARK LO\u{0422} 25 [1, 2]",
            'payments_received_cents' => 100,
        ]);
        $filing = liendocRenderFiling($project, 'mechanics_lien', ['description_of_work' => 'Drywall [3]']);

        $warnings = LienDocumentPackage::forFiling($filing)->warnings;

        expect($warnings)
            ->toContain('The legal description contains non-Latin look-alike characters (pasted from a PDF?); retype them.')
            ->toContain('The legal description contains citation markers like "[1, 2]"; remove them.')
            ->toContain('The description of work contains citation markers like "[1, 2]"; remove them.')
            ->toContain('The amount claimed ($4,213.75) does not equal contract + change orders − credits − payments − uncompleted work ($4,212.75).');
    });

    it('flags a missing service party, a missing notice date and a missing original lien', function () {
        $project = liendocRenderProject('TX', 'Bexar', ['prelim_notice_sent_at' => null]);
        $project->parties()->where('role', PartyRole::Gc->value)->delete();
        $lien = liendocRenderFiling($project, 'mechanics_lien');
        $release = liendocRenderFiling($project, 'lien_release');

        expect(LienDocumentPackage::forFiling($lien)->warnings)
            ->toContain('Texas serves the general contractor; the project has no general contractor party.')
            ->toContain('The claimant did not contract with the owner and no preliminary notice service date is recorded (project or Document details).');

        expect(LienDocumentPackage::forFiling($release)->warnings)
            ->toContain('No recording reference or date for the lien being released: set the original lien in Document details.');
    });

    it('explains why nothing can be generated', function () {
        $hawaii = liendocRenderFiling(liendocRenderProject('HI', 'Honolulu'), 'mechanics_lien');
        $prelim = liendocRenderFiling(liendocRenderProject('FL', 'Pinellas'), 'prelim_notice');

        $hawaiiPackage = LienDocumentPackage::forFiling($hawaii);
        expect($hawaiiPackage->isAvailable())->toBeFalse();
        expect($hawaiiPackage->form)->toBeNull();
        expect($hawaiiPackage->unavailableReason)->toContain('attorney');

        $prelimPackage = LienDocumentPackage::forFiling($prelim);
        expect($prelimPackage->isAvailable())->toBeTrue();
        expect($prelimPackage->form?->title)->toBe('Notice to Owner');
        expect($prelimPackage->items[0]['label'])->toBe('Notice to Owner');
        expect(collect($prelimPackage->rules())->pluck('value', 'label')['Serve'])
            ->toBe('The owner, the general contractor and the construction lender by certified mail, return receipt requested within 45 days after first furnishing.');
    });

    it('describes the rules in plain words', function () {
        $filing = liendocRenderFiling(liendocRenderProject('FL', 'Pinellas'), 'mechanics_lien');

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['File with'])->toBe('Clerk of the Circuit Court, Official Records, e-recording or mail');
        expect($rules['Fee'])->toContain('$10 for the first page');
        expect($rules['Signing'])->toBe('Sworn to and signed before a notary (jurat)');
        expect($rules['Serve'])->toBe('The owner by certified mail, return receipt requested within 15 days after recording.');
    });
});

describe('notice letters', function () {
    it('renders the Florida Notice to Owner with the statutory warning, sentence and protection paragraphs', function () {
        $filing = liendocRenderFiling(liendocRenderProject('FL', 'Pinellas'), 'prelim_notice');

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = liendocText($pdf);

        foreach ([
            'S G Roser Construction LLC 4200 Lakeland Hwy Lakeland, FL 33801 863-555-0100',
            'Via certified mail, return receipt requested',
            'To: Owner or reputed owner Mike Stuntz',
            'To: Original (direct) contractor Ken Walker Builders',
            'Re: Notice to Owner',
            'WARNING! FLORIDA\'S CONSTRUCTION LIEN LAW ALLOWS SOME UNPAID CONTRACTORS, SUBCONTRACTORS, AND MATERIAL SUPPLIERS TO FILE LIENS AGAINST YOUR PROPERTY EVEN IF YOU HAVE MADE PAYMENT IN FULL.',
            'NOTICE TO OWNER',
            'Fla. Stat. § 713.06',
            'The undersigned hereby informs you that he or she has furnished or is furnishing services or materials as follows:',
            'Removal of drywall, replacement of drywall, and damage repair throughout the home for the improvement of the real property identified as 9025 Baywood Park Dr, Seminole, FL 33777; BAYWOOD PARK LOT 25; Parcel ID 35-30-15-05699-000-0250 under an order given by Ken Walker Builders.',
            'Florida law prescribes the serving of this notice and restricts your right to make payments under your contract in accordance with Section 713.06, Florida Statutes.',
            'IMPORTANT INFORMATION FOR YOUR PROTECTION',
            'PROTECT YOURSELF:',
            '—RECOGNIZE that this Notice to Owner may result in a lien against your property unless all those supplying a Notice to Owner have been paid.',
            'CLAIMANT: S G Roser Construction LLC',
            'Steven Roser',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        expect($text)->not->toContain('My commission expires')->not->toContain('N/A');
        expect($pdf->getHtml())->toContain('<strong>IMPORTANT INFORMATION FOR YOUR PROTECTION</strong>');
    });

    it('renders the California preliminary notice with the § 8202 statement and the estimate', function () {
        $filing = liendocRenderFiling(liendocRenderProject('CA', 'Los Angeles'), 'prelim_notice');

        $text = liendocText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('CALIFORNIA PRELIMINARY NOTICE')
            ->toContain('NOTICE TO PROPERTY OWNER EVEN THOUGH YOU HAVE PAID YOUR CONTRACTOR IN FULL, if the person or firm that has given you this notice is not paid in full')
            ->toContain('You are not required to send the notice if you are a residential homeowner of a dwelling containing four or fewer units.')
            ->toContain('THIS IS NOT A LIEN.')
            ->toContain('Direct contractor Ken Walker Builders')
            ->toContain('Construction lender, if any')
            ->toContain('Relationship to the parties: subcontractor')
            ->toContain('Estimate of the total price of the work provided and to be provided $4,213.75');
    });

    it('renders the Arizona twenty day notice in the statutory order with the receipt', function () {
        $filing = liendocRenderFiling(liendocRenderProject('AZ', 'Maricopa'), 'prelim_notice');

        $text = liendocText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            'ARIZONA PRELIMINARY TWENTY DAY LIEN NOTICE',
            'In accordance with Arizona Revised Statutes section 33-992.01, this is not a lien.',
            'The name and address of the owner or reputed owner are: Mike Stuntz',
            'This preliminary lien notice has been completed by (name and address of claimant): S G Roser Construction LLC',
            'And situated on that certain lot(s) or parcel(s) of land in Maricopa County, Arizona, described as follows: BAYWOOD PARK LOT 25',
            'An estimate of the total price of the labor, professional services, materials, machinery, fixtures or tools furnished or to be furnished is: $4,213.75',
            'Notice to Property Owner If bills are not paid in full',
            '3. Using any other method or device that is appropriate under the circumstances.',
            'Within ten days after the receipt of this preliminary twenty day notice the owner or other interested party is required to furnish all information necessary to correct any inaccuracies',
            'Acknowledgment of receipt of preliminary twenty day notice',
            'Signature of person acknowledging receipt, with title if acknowledgment is made on behalf of another person',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // The statutory order: parties and estimate, then the notice, then the ten-day paragraphs, then the signature and the receipt.
        $positions = array_map(fn ($needle) => strpos($text, $needle), ['An estimate of the total price', 'Notice to Property Owner If bills', 'Within ten days after the receipt', 'By (signature)', 'Acknowledgment of receipt']);
        $sorted = $positions;
        sort($sorted);
        expect($positions)->not->toContain(false)->toBe($sorted);
    });

    it('renders the Texas notice of claim as the § 53.056(a-2) form', function () {
        $filing = liendocRenderFiling(liendocRenderProject('TX', 'Bexar'), 'prelim_notice');

        $text = liendocText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('NOTICE OF CLAIM FOR UNPAID LABOR OR MATERIALS')
            ->toContain('Project description and/or address: Baywood Park drywall / 9025 Baywood Park Dr, Seminole, TX 33777')
            ->toContain('Claimant\'s name: S G Roser Construction LLC')
            ->toContain('Month(s) in which the labor or materials were provided:')
            ->toContain('Original contractor\'s name: Ken Walker Builders')
            ->toContain('Party with whom claimant contracted if different from original contractor: Same as the original contractor')
            ->toContain('Claim amount: $4,213.75')
            ->toContain('Claimant\'s contact person: Steven Roser, 863-555-0100')
            ->toContain('Claimant\'s address: 4200 Lakeland Hwy, Lakeland, FL 33801');
    });

    it('renders the Georgia notice to contractor, the North Carolina notice to lien agent and the generic notices', function () {
        $generator = app(LienDocumentGenerator::class);

        $georgia = liendocText($generator->render(liendocRenderFiling(liendocRenderProject('GA', 'Cherokee'), 'prelim_notice')));
        expect($georgia)
            ->toContain('NOTICE TO CONTRACTOR')
            ->toContain('O.C.G.A. § 44-14-361.5')
            ->toContain('Name and location of the project (as set forth in the Notice of Commencement) Baywood Park drywall')
            ->toContain('Contract price or anticipated value of the labor, services or materials to be furnished, or the amount claimed to be due $4,213.75');

        $carolina = liendocText($generator->render(liendocRenderFiling(liendocRenderProject('NC', 'Mecklenburg'), 'prelim_notice')));
        expect($carolina)
            ->toContain('NOTICE TO LIEN AGENT')
            ->toContain('(1) Potential lien claimant\'s name, mailing address, telephone number, fax number (if available), and email address (if available): S G Roser Construction LLC')
            ->toContain('(4) I give notice of my right subsequently to pursue a claim of lien for improvements to the real property described in this notice.')
            ->toContain('Lien agent (as designated on the Appointment of Lien Agent or the building permit):');

        $ohio = liendocText($generator->render(liendocRenderFiling(liendocRenderProject('OH', 'Franklin'), 'prelim_notice')));
        expect($ohio)
            ->toContain('PRELIMINARY NOTICE')
            ->toContain('THIS IS NOT A LIEN.')
            ->toContain('Relationship to the project Subcontractor')
            ->toContain('Estimated total price $4,213.75')
            ->toContain('the claimant may claim a lien against the property');

        $intent = liendocText($generator->render(liendocRenderFiling(liendocRenderProject('GA', 'Cherokee'), 'noi')));
        expect($intent)
            ->toContain('NOTICE OF INTENT TO FILE A CLAIM OF LIEN')
            ->toContain('under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid')
            ->toContain('within 10 days after the date of this notice, Claimant intends to record a Claim of Lien against the property');
    });
});

describe('generator', function () {
    it('names the file after the claimant, the title and the date', function () {
        $filing = liendocRenderFiling(liendocRenderProject('TX', 'Bexar'), 'mechanics_lien');
        $generator = app(LienDocumentGenerator::class);

        $date = now()->eastern()->format('Y-m-d');

        expect($generator->filename($filing, $generator->resolve($filing)))
            ->toBe("S G Roser Construction LLC Affidavit Claiming a Mechanics Lien {$date}.pdf");
    });

    it('produces a real PDF through DOMPDF with the template version in its metadata', function () {
        $filing = liendocRenderFiling(liendocRenderProject('GA', 'Cherokee'), 'mechanics_lien');

        $bytes = base64_decode(app(LienDocumentGenerator::class)->render($filing)->base64());

        expect(substr($bytes, 0, 4))->toBe('%PDF');
        expect($bytes)->toContain('/Keywords');
        // DOMPDF writes info strings as UTF-16BE with a byte-order mark.
        expect(str_contains($bytes, 'template v1') || str_contains($bytes, mb_convert_encoding('template v1', 'UTF-16BE', 'UTF-8')))->toBeTrue();
    });

    it('spells out dollar amounts', function (int $cents, string $words) {
        expect(MoneyWords::dollars($cents))->toBe($words);
    })->with([
        [421375, 'Four Thousand Two Hundred Thirteen Dollars and Seventy-Five Cents'],
        [100000, 'One Thousand Dollars and No Cents'],
        [235358490, 'Two Million Three Hundred Fifty-Three Thousand Five Hundred Eighty-Four Dollars and Ninety Cents'],
        [5, 'Zero Dollars and Five Cents'],
        [1900, 'Nineteen Dollars and No Cents'],
        [10000000000, 'One Hundred Million Dollars and No Cents'],
    ]);
});
