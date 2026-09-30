<?php

use App\Domains\Lien\Documents\LienDocumentGenerator;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Documents\LienDocumentResolver;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');
    LienDocumentRegistry::flush();
});

afterEach(fn () => LienDocumentRegistry::flush());

describe('South Carolina notice and certificate of mechanic\'s lien', function () {
    it('renders the sworn § 29-5-90 statement with the license number and the affirmations', function () {
        $filing = lienFixtureFiling(lienFixtureProject('SC', 'Berkeley'), 'mechanics_lien');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain("NOTICE AND CERTIFICATE OF MECHANIC'S LIEN")
            ->toContain('S.C. Code Ann. § 29-5-90')
            ->toContain('STATE OF SOUTH CAROLINA')
            ->toContain('COUNTY OF BERKELEY')
            ->toContain('Claimant / Lienor S G Roser Construction LLC Owner Mike Stuntz Amount claimed $4,213.75 TMS Number 35-30-15-05699-000-0250')
            ->toContain('S G Roser Construction LLC ("Claimant") claims a lien under S.C. Code Ann. § 29-5-90 upon the real property')
            ->toContain("Contractor's license number: CGC-123456")
            ->toContain('Owner or reputed owner of the property. Mike Stuntz')
            ->toContain('Person who contracted with Claimant. Ken Walker Builders')
            ->toContain('Property subject to the lien. 9025 Baywood Park Dr, Seminole, SC 33777 (Berkeley County, South Carolina) Legal description: BAYWOOD PARK LOT 25 TMS Number: 35-30-15-05699-000-0250')
            ->toContain('Last furnished: July 10, 2026')
            ->toContain('After deducting all just credits and offsets, the amount claimed is $4,213.75.')
            ->toContain('Prior notice. Claimant served its Notice of Furnishing Labor or Materials on August 7, 2026')
            ->toContain('This statement is a just and true account of the amount due to Claimant, with all just credits given.')
            ->toContain('Claimant files this statement within ninety days after it last furnished labor or materials for the improvement of the property.')
            ->toContain('Claimant is serving a copy of this statement on the owner, or on the person in possession if the owner cannot be found, as S.C. Code Ann. § 29-5-90 requires.')
            ->toContain('being first duly sworn')
            ->toContain('Subscribed and sworn to (or affirmed) before me')
            ->not->toContain('N/A')
            ->not->toContain('—');

        // The GC is the hiring party here, so it is not listed twice.
        expect(substr_count($text, 'Ken Walker Builders'))->toBe(1);
    });

    it('adds the sworn Verified Statement of Account page with its own jurat after the lien', function () {
        $filing = lienFixtureFiling(lienFixtureProject('SC', 'Berkeley'), 'mechanics_lien');

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        expect($text)
            ->toContain('VERIFIED STATEMENT OF ACCOUNT CLERK OF COURT/REGISTER OF DEEDS STATE OF SOUTH CAROLINA COUNTY OF BERKELEY Claimant S G Roser Construction LLC Owner Mike Stuntz')
            ->toContain('Account balance as of July 10, 2026: $4,213.75')
            ->toContain('Payments received: $0.00')
            ->toContain('Balance due as of '.now()->eastern()->format('F j, Y').': $4,213.75 *')
            ->toContain("* Plus interest, attorney's fees and costs.")
            ->toContain("I HEREBY CERTIFY that the foregoing is a true and correct statement of account due to Petitioner in connection with this Mechanics' Lien.")
            ->toContain('CLAIMANT: S G Roser Construction LLC By (signature) Steven Roser Printed name President Title');

        // Two sworn signatures, two jurats: the lien's and the statement's.
        expect(substr_count($text, 'My commission expires'))->toBe(2);
        expect(substr_count($text, 'Subscribed and sworn to (or affirmed) before me on this'))->toBe(2);

        // The statement starts on its own page, after the lien's execution block.
        expect(strpos($text, 'being first duly sworn'))->toBeLessThan(strpos($text, 'VERIFIED STATEMENT OF ACCOUNT'));
        expect($pdf->getHtml())->toContain('<p class="doc-title" style="page-break-before: always;">VERIFIED STATEMENT OF ACCOUNT</p>');
    });

    it('merges the Berkeley and Charleston register facts and leaves other counties to the state', function () {
        $resolver = app(LienDocumentResolver::class);

        $berkeley = $resolver->resolve(lienFixtureFiling(lienFixtureProject('SC', 'Berkeley'), 'mechanics_lien'));
        $charleston = $resolver->resolve(lienFixtureFiling(lienFixtureProject('SC', 'Charleston County'), 'mechanics_lien'));
        $greenville = $resolver->resolve(lienFixtureFiling(lienFixtureProject('SC', 'Greenville'), 'mechanics_lien'));

        expect($berkeley->countyKey)->toBe('berkeley');
        expect($berkeley->recording['adds_cover_page'])->toBeTrue();
        expect($berkeley->recording['filing_office'])->toMatchArray(['label' => 'Berkeley County Register of Deeds', 'method' => 'erecord']);
        expect($berkeley->notes)->toContain('The Register of Deeds adds its own cover page, which becomes page 1 of the recorded instrument.');

        expect($charleston->countyKey)->toBe('charleston');
        expect($charleston->recording['adds_cover_page'])->toBeTrue();
        expect($charleston->recording['filing_office'])->toMatchArray(['label' => 'Charleston County Register of Deeds', 'method' => 'erecord']);

        expect($greenville->countyKey)->toBe('greenville');
        expect($greenville->recording['adds_cover_page'])->toBeFalse();
        expect($greenville->recording['filing_office'])->toMatchArray(['label' => 'Register of Deeds or Clerk of Court (county where the property is located)', 'method' => 'either']);

        // § 8-21-310 sets one fee statewide, so every county shows the state's note.
        foreach ([$berkeley, $charleston, $greenville] as $doc) {
            expect($doc->recording['parcel_label'])->toBe('TMS Number');
            expect($doc->recording['fee_note'])->toContain('$25 to file a notice of mechanic\'s lien and $10 to record a release');
        }
    });

    it('serves every document by certified mail, not the seeded "any"', function () {
        $sc = LienDocumentRegistry::for('SC');
        $lien = $sc['kinds']['mechanics_lien'];

        expect($lien['execution'])->toMatchArray(['verification' => 'sworn', 'notary' => true, 'notary_form' => 'jurat']);
        expect($lien['sections'])->toMatchArray(['amount' => 'breakdown', 'gc' => true, 'hiring_party' => true, 'prior_notice' => true, 'license' => true]);
        expect($lien['service'])->toMatchArray(['recipients' => ['owner'], 'days_after' => 0, 'method' => 'certified_mail']);
        expect($lien['clauses']['after_execution'])->toBe(['documents.lien.instruments.clauses.sc-verified-statement-of-account']);

        expect($sc['kinds']['prelim_notice']['service'])->toMatchArray(['recipients' => ['owner', 'gc'], 'days_after' => null, 'method' => 'certified_mail']);
        expect($sc['kinds']['noi']['service'])->toMatchArray(['recipients' => ['owner', 'gc'], 'method' => 'certified_mail']);
        expect($sc['kinds']['lien_release']['service']['method'])->toBe('certified_mail');
        expect($sc['kinds']['lien_release']['execution'])->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment']);
    });
});

describe('South Carolina notice of furnishing', function () {
    it('renders the § 29-5-20(B) contents in the statutory order for the owner and the contractor', function () {
        $filing = lienFixtureFiling(lienFixtureProject('SC', 'Berkeley'), 'prelim_notice');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('To: Owner or reputed owner Mike Stuntz 9025 Baywood Park Dr Seminole, SC 33777')
            ->toContain('To: Original (direct) contractor Ken Walker Builders')
            ->toContain('Re: Notice of Furnishing Labor or Materials')
            ->toContain('NOTICE OF FURNISHING LABOR OR MATERIALS')
            ->toContain('S.C. Code Ann. §§ 29-5-20(B) and 29-5-40')
            ->toContain('The claimant named below gives notice that it has furnished or will furnish labor, services or materials for the improvement of the real estate described below, in the amount or value stated below.')
            ->toContain('Claimant (person giving notice and claiming payment) S G Roser Construction LLC Steven Roser 4200 Lakeland Hwy Lakeland, FL 33801 Telephone: 863-555-0100')
            ->toContain('Person with whom the claimant contracted or by whom it was employed Ken Walker Builders')
            ->toContain('Labor, services or materials furnished Removal of drywall, replacement of drywall, and damage repair throughout the home')
            ->toContain('Contract price or value of the labor, services or materials $4,213.75')
            ->toContain('Project where the labor, services or materials are used Baywood Park drywall 9025 Baywood Park Dr, Seminole, SC 33777 (Berkeley County, South Carolina) Legal description: BAYWOOD PARK LOT 25 TMS Number: 35-30-15-05699-000-0250')
            ->toContain('First furnished (or scheduled to be furnished) June 30, 2026')
            ->toContain('Last furnished (or scheduled to be furnished) July 10, 2026')
            ->toContain('Amount claimed to be due, if any $4,213.75')
            ->toContain('This notice is given under S.C. Code Ann. §§ 29-5-20(B) and 29-5-40. It is not a lien.')
            ->toContain('CLAIMANT: S G Roser Construction LLC')
            ->not->toContain('My commission expires')
            ->not->toContain('N/A');

        // Items (1) to (6) of § 29-5-20(B), in order.
        $needles = ['Claimant (person giving notice', 'Person with whom the claimant contracted', 'Labor, services or materials furnished', 'Contract price or value', 'Project where the labor', 'First furnished', 'Last furnished', 'Amount claimed to be due'];
        $positions = array_map(fn (string $needle) => strpos($text, $needle), $needles);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false);
        expect($positions)->toBe($sorted);
    });
});

describe('South Carolina release and notice of intent', function () {
    it('renders the § 29-5-430 release with an acknowledgment and no statement of account', function () {
        $filing = lienFixtureFiling(lienFixtureProject('SC', 'Berkeley'), 'lien_release', [
            'document_details_json' => ['original_lien' => ['recording_reference' => 'Book 1234 Page 567', 'recorded_at' => '2026-08-20', 'county' => 'Berkeley']],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain("RELEASE AND SATISFACTION OF MECHANIC'S LIEN")
            ->toContain('S.C. Code Ann. § 29-5-430')
            ->toContain('STATE OF SOUTH CAROLINA')
            ->toContain('COUNTY OF BERKELEY')
            ->toContain("Releases Notice and Certificate of Mechanic's Lien recorded August 20, 2026 as Book 1234 Page 567")
            ->toContain("is the claimant under that certain Notice and Certificate of Mechanic's Lien recorded on August 20, 2026 as Book 1234 Page 567 in the official records of Berkeley County, South Carolina")
            ->toContain('authorizes and directs the Berkeley County Register of Deeds to cancel it of record.')
            ->toContain('The debt secured by the lien described above has been fully paid. This release may be recorded where the statement of the lien is recorded (S.C. Code Ann. § 29-5-430).')
            ->toContain('before me, the undersigned notary public, personally appeared Steven Roser, President of S G Roser Construction LLC, personally known to me or proved to me on the basis of satisfactory evidence')
            ->toContain('and acknowledged that he or she executed it in that capacity on behalf of S G Roser Construction LLC for the purposes stated in it.')
            ->not->toContain('being first duly sworn')
            ->not->toContain('VERIFIED STATEMENT OF ACCOUNT');
    });

    it('renders the generic notice of intent by certified mail', function () {
        $filing = lienFixtureFiling(lienFixtureProject('SC', 'Berkeley'), 'noi');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain("NOTICE OF INTENT TO FILE A MECHANIC'S LIEN")
            ->toContain('S.C. Code Ann. § 29-5-10 et seq.')
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid')
            ->toContain("within 10 days after the date of this notice, Claimant intends to record a Notice and Certificate of Mechanic's Lien against the property");
    });
});
