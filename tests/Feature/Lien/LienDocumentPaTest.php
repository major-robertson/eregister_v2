<?php

use App\Domains\Lien\Documents\LienDocumentGenerator;
use App\Domains\Lien\Documents\LienDocumentPackage;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Documents\LienDocumentResolver;
use App\Domains\Lien\Enums\ClaimantType;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');
    LienDocumentRegistry::flush();
});

afterEach(fn () => LienDocumentRegistry::flush());

describe('Pennsylvania mechanics\' lien claim', function () {
    it('renders the docket caption, the § 1503 items, the formal notice recital, the § 4904 verification and the certificate of compliance', function () {
        $filing = lienFixtureFiling(lienFixtureProject('PA', 'Allegheny', ['completion_date' => '2026-07-10']), 'mechanics_lien', [
            'document_details_json' => ['notice_served_at' => '2026-08-07', 'notice_served_method' => 'certified_mail'],
        ]);

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        foreach ([
            'MECHANICS\' LIEN CLAIM',
            '49 P.S. § 1502',
            'IN THE COURT OF COMMON PLEAS OF ALLEGHENY COUNTY, PENNSYLVANIA',
            'S G Roser Construction LLC, Claimant v. Mike Stuntz, Owner',
            'No. of 20',
            'Property subject to lien: 9025 Baywood Park Dr, Seminole, PA 33777',
            'Parcel ID: 35-30-15-05699-000-0250',
            'Claimant furnished the labor, services, equipment or materials described below as a subcontractor.',
            'Person who contracted with Claimant. Ken Walker Builders',
            'Date of the contract:',
            'Legal description: BAYWOOD PARK LOT 25',
            'Date of completion of the work of improvement: July 10, 2026',
            'After deducting all just credits and offsets, the amount claimed is $4,213.75.',
            'Claimant completed its work on the date of completion stated above, and this claim is filed within six months after that date (49 P.S. § 1502(a)(1)).',
            'A detailed statement of the kind and character of the labor and materials furnished, with the prices charged for each, is attached as Exhibit A (49 P.S. § 1503(6)).',
            'Claimant will serve written notice of the filing of this claim on the owner within one month after filing, giving the court, term and number and date of filing, and will file the affidavit of service within 20 days after service (49 P.S. § 1502(a)(2)).',
            'Claimant files this claim as a subcontractor (49 P.S. § 1503(1)).',
            'Formal notice of Claimant\'s intention to file this claim was served on the owner on August 7, 2026 by certified mail, return receipt requested, at least 30 days before this claim was filed (49 P.S. § 1501(b.1)).',
            'VERIFICATION I, Steven Roser, President of S G Roser Construction LLC, verify that the statements made in the foregoing Mechanics\' Lien Claim are true and correct to the best of my knowledge, information and belief.',
            'I understand that false statements herein are made subject to the penalties of 18 Pa.C.S. § 4904 relating to unsworn falsification to authorities.',
            'CLAIMANT: S G Roser Construction LLC By (signature) Date Steven Roser Printed name President Title',
            'CERTIFICATE OF COMPLIANCE',
            'I certify that this filing complies with the provisions of the Case Records Public Access Policy of the Unified Judicial System of Pennsylvania that require filing confidential information and documents differently than non-confidential information and documents.',
            'SUBMITTED BY: S G Roser Construction LLC, Claimant',
            'Address: 4200 Lakeland Hwy, Lakeland, FL 33801 Telephone: 863-555-0100',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // Verified, not notarized; Pennsylvania has no preliminary notice to recite.
        expect($text)
            ->not->toContain('Notary')
            ->not->toContain('My commission expires')
            ->not->toContain('being first duly sworn')
            ->not->toContain('under penalty of perjury')
            ->not->toContain('Prior notice.')
            ->not->toContain('N/A');

        // Title and caption first, then the body, the § 1503 recitals, the verification,
        // the signature and, last, the certificate of compliance.
        $needles = ['MECHANICS\' LIEN CLAIM', 'IN THE COURT OF COMMON PLEAS', '("Claimant") claims a lien under 49 P.S. § 1502', 'Claimant files this claim as a subcontractor', 'Formal notice of Claimant', 'VERIFICATION', 'CLAIMANT: S G Roser Construction LLC', 'CERTIFICATE OF COMPLIANCE', 'I certify that this filing complies'];
        $positions = array_map(fn (string $needle) => strpos($text, $needle), $needles);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false);
        expect($positions)->toBe($sorted);

        // The ordinary 1" margins, no page-1 recorder space, and the certificate on its own page.
        expect($pdf->getHtml())
            ->toContain('@page { margin: 1in; }')
            ->toContain('.recorder-space { height: 0in; overflow: hidden; }')
            ->toContain('page-break-before: always;')
            ->not->toContain('class="notary"');
    });

    it('resolves the docket caption, the Allegheny e-filing office and the Pennsylvania service rules', function () {
        $filing = lienFixtureFiling(lienFixtureProject('PA', 'Allegheny'), 'mechanics_lien');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->title)->toBe('Mechanics\' Lien Claim');
        expect($form->statute)->toBe('49 P.S. § 1502');
        expect($form->body)->toBe('documents.lien.instruments.bodies.generic-lien');
        expect($form->recording['caption'])->toBe('docket');
        expect($form->recording['top_margin_in'])->toBe(1.0);
        expect($form->recorderSpaceInches())->toBe(0.0);
        expect($form->recording['index_line'])->toBeFalse();
        expect($form->recording['preparer_in_space'])->toBeFalse();
        expect($form->recording['page_numbers'])->toBeTrue();
        expect($form->recording['filing_office'])->toMatchArray([
            'label' => 'Allegheny County Department of Court Records, Civil/Family Division',
            'method' => 'erecord',
            'address_lines' => ['City-County Building, First Floor', '414 Grant Street', 'Pittsburgh, PA 15219-2469'],
        ]);
        expect($form->recording['fee_note'])->toBe('$102.75 to file a mechanics\' lien claim (county fee schedule, 2026).');
        expect($form->execution)->toMatchArray(['verification' => 'verified', 'notary' => false, 'notary_form' => null, 'statement' => false]);
        expect($form->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => 30, 'method' => 'personal_delivery', 'proof' => 'affidavit', 'perjury_state' => 'PA']);
        expect($form->sections)->toMatchArray(['amount' => 'itemized', 'completion_date' => true, 'contract_date' => true, 'prior_notice' => false]);
        expect($form->attachments)->toHaveCount(2);
        expect($form->attachments[0])->toStartWith('Exhibit A: detailed statement of the kind and character of the labor and materials furnished');
        expect($form->notes)->toContain('After the notice of filing is served, file the affidavit of service in the same case within 20 days after service.');

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['File with'])->toBe('Allegheny County Department of Court Records, Civil/Family Division, e-recording via the county e-filing portal (City-County Building, First Floor, 414 Grant Street, Pittsburgh, PA 15219-2469)');
        expect($rules['Signing'])->toBe('Signed and verified under penalty of perjury; no notary');
        expect($rules['Serve'])->toBe('The owner by personal delivery within 30 days after recording.');
        expect($rules['Attach'])->toBe('Exhibit A: detailed statement of the kind and character of the labor and materials furnished and the prices charged for each (49 P.S. § 1503(6)). Certificate of compliance with the Case Records Public Access Policy of the Unified Judicial System (prints as the last page).');
    });

    it('files with the Butler County Prothonotary by mail and leaves other counties with the state values', function () {
        $butler = lienFixtureFiling(lienFixtureProject('PA', 'Butler County'), 'mechanics_lien');
        $philadelphia = lienFixtureFiling(lienFixtureProject('PA', 'Philadelphia'), 'mechanics_lien');
        $resolver = app(LienDocumentResolver::class);

        $butlerForm = $resolver->resolve($butler);

        expect($butlerForm->countyKey)->toBe('butler');
        expect($butlerForm->recording['filing_office'])->toMatchArray([
            'label' => 'Butler County Prothonotary',
            'method' => 'mail',
            'address_lines' => ['P.O. Box 1208', 'Butler, PA 16003-1208'],
        ]);
        expect($butlerForm->recording['caption'])->toBe('docket');
        expect($butlerForm->recording['fee_note'])->toBe('$27.00 to file a mechanics\' lien claim (fee sheet effective January 6, 2026).');

        $philadelphiaForm = $resolver->resolve($philadelphia);

        expect($philadelphiaForm->recording['filing_office'])->toMatchArray(['label' => 'Prothonotary', 'method' => 'either', 'address_lines' => []]);
        expect($philadelphiaForm->recording['fee_note'])->toBeNull();
        expect($philadelphiaForm->recording['caption'])->toBe('docket');

        // Without a formal notice date in Document details the recital prints blanks; it
        // never borrows the project's preliminary notice date (August 7, 2026 in the fixture).
        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($butler));

        expect($text)
            ->toContain('IN THE COURT OF COMMON PLEAS OF BUTLER COUNTY, PENNSYLVANIA')
            ->toContain('Formal notice of Claimant\'s intention to file this claim was served on the owner on by , at least 30 days before this claim was filed')
            ->not->toContain('August 7, 2026');
    });

    it('states a contractor claim and recites no formal notice when the claimant contracted with the owner', function () {
        $filing = lienFixtureFiling(lienFixtureProject('PA', 'Allegheny', ['claimant_type' => ClaimantType::Gc, 'hired_by' => 'owner']), 'mechanics_lien');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('as a direct contractor in contract with the owner.')
            ->toContain('Claimant files this claim as a contractor (49 P.S. § 1503(1)).')
            ->toContain('18 Pa.C.S. § 4904')
            ->toContain('CERTIFICATE OF COMPLIANCE')
            ->not->toContain('Formal notice of Claimant')
            ->not->toContain('files this claim as a subcontractor');
    });
});

describe('Pennsylvania notices', function () {
    it('renders the § 1501 formal notice of intention with every (c) item and the 30-day statement', function () {
        $filing = lienFixtureFiling(lienFixtureProject('PA', 'Allegheny', ['completion_date' => '2026-07-10']), 'noi');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            'Via certified mail, return receipt requested',
            'To: Owner or reputed owner Mike Stuntz',
            'FORMAL NOTICE OF INTENTION TO FILE MECHANICS\' LIEN CLAIM',
            '49 P.S. § 1501(b.1)',
            'You are hereby notified that S G Roser Construction LLC ("Claimant") furnished labor, services, equipment or materials for the improvement of the property described below under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid.',
            '9025 Baywood Park Dr, Seminole, PA 33777 (Allegheny County, Pennsylvania) Legal description: BAYWOOD PARK LOT 25 Parcel ID: 35-30-15-05699-000-0250',
            'Removal of drywall, replacement of drywall, and damage repair throughout the home',
            'Date of completion of the work of improvement: July 10, 2026',
            'Amount unpaid $4,213.75',
            'Claimant gives you formal written notice of its intention to file a mechanics\' lien claim against the property described above for the amount stated above, for the labor and materials described above, which Claimant completed on the date of completion stated above. Unless Claimant is paid in full first, it will file the claim with the prothonotary of the court of common pleas no sooner than 30 days after this notice is served (49 P.S. § 1501(b.1), (c)).',
            'CLAIMANT: S G Roser Construction LLC',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // The statutory paragraph replaces the generic demand; a notice is not a court filing.
        expect($text)
            ->not->toContain('within 30 days after the date of this notice')
            ->not->toContain('CERTIFICATE OF COMPLIANCE')
            ->not->toContain('18 Pa.C.S. § 4904');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->section('demand_days'))->toBe(30);
        expect($form->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail', 'proof' => 'affidavit']);
        expect($form->notaryRequired())->toBeFalse();
    });

    it('renders the preliminary notice as a courtesy notice of furnishing, since § 1501(a) was deleted in 2006', function () {
        $filing = lienFixtureFiling(lienFixtureProject('PA', 'Allegheny'), 'prelim_notice');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('NOTICE OF FURNISHING (COURTESY)')
            ->toContain('THIS IS NOT A LIEN.')
            ->toContain('Relationship to the project Subcontractor')
            ->toContain('Person who contracted with the claimant Ken Walker Builders')
            ->toContain('First furnished June 30, 2026')
            ->toContain('Estimated total price $4,213.75')
            ->not->toContain('49 P.S.')
            ->not->toContain('CERTIFICATE OF COMPLIANCE')
            ->not->toContain('VERIFICATION');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->title)->toBe('Notice of Furnishing (courtesy)');
        expect($form->statute)->toBeNull();
        expect($form->service)->toMatchArray(['recipients' => ['owner'], 'method' => 'certified_mail', 'days_after' => null]);
        expect($form->notes)->toContain('Not required in Pennsylvania: the preliminary notice for alteration and repair work (the former 49 P.S. § 1501(a)) was deleted in 2006 (Act 52). This is a courtesy notice of furnishing.');
    });
});

describe('Pennsylvania satisfaction', function () {
    it('renders the § 1704 satisfaction verified, with the docketed claim, the § 4904 verification and the certificate page', function () {
        $filing = lienFixtureFiling(lienFixtureProject('PA', 'Allegheny'), 'lien_release', [
            'document_details_json' => ['original_lien' => ['recording_reference' => 'MLD-26-000123', 'recorded_at' => '2026-07-20', 'county' => 'Allegheny']],
        ]);

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        foreach ([
            'SATISFACTION OF MECHANICS\' LIEN CLAIM',
            '49 P.S. § 1704',
            'IN THE COURT OF COMMON PLEAS OF ALLEGHENY COUNTY, PENNSYLVANIA',
            'S G Roser Construction LLC, Claimant v. Mike Stuntz, Owner',
            'is the claimant under that certain Mechanics\' Lien Claim recorded on July 20, 2026 as MLD-26-000123 in the official records of Allegheny County, Pennsylvania',
            'The claim described above has been paid, and Claimant directs that satisfaction of the claim be entered on the record (49 P.S. § 1704).',
            'VERIFICATION I, Steven Roser, President of S G Roser Construction LLC, verify that the statements made in the foregoing Satisfaction of Mechanics\' Lien Claim are true and correct to the best of my knowledge, information and belief.',
            'I understand that false statements herein are made subject to the penalties of 18 Pa.C.S. § 4904 relating to unsworn falsification to authorities.',
            'CERTIFICATE OF COMPLIANCE',
            'I certify that this filing complies with the provisions of the Case Records Public Access Policy of the Unified Judicial System of Pennsylvania that require filing confidential information and documents differently than non-confidential information and documents.',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        expect($text)
            ->not->toContain('My commission expires')
            ->not->toContain('personally appeared');

        $needles = ['has been paid', 'VERIFICATION', 'CLAIMANT: S G Roser Construction LLC', 'CERTIFICATE OF COMPLIANCE'];
        $positions = array_map(fn (string $needle) => strpos($text, $needle), $needles);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false);
        expect($positions)->toBe($sorted);
        expect($pdf->getHtml())->toContain('page-break-before: always;');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->execution)->toMatchArray(['verification' => 'verified', 'notary' => false, 'notary_form' => null, 'statement' => false]);
        expect($form->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail', 'proof' => 'declaration']);
        expect($form->clauses['after_execution'])->toBe(['documents.lien.instruments.clauses.pa-certificate-of-compliance']);
    });
});
