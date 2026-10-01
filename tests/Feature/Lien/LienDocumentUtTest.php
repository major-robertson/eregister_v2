<?php

use App\Domains\Lien\Documents\LienDocumentGenerator;
use App\Domains\Lien\Documents\LienDocumentPackage;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Documents\LienDocumentResolver;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');
    LienDocumentRegistry::flush();
});

afterEach(fn () => LienDocumentRegistry::flush());

describe('Utah notice of construction lien', function () {
    it('renders the § 38-1a-502 notice for Utah County, acknowledged, with the owner-occupied residence statement', function () {
        $filing = lienFixtureFiling(lienFixtureProject('UT', 'Utah'), 'mechanics_lien');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            'NOTICE OF CONSTRUCTION LIEN',
            'Utah Code § 38-1a-502',
            'STATE OF UTAH',
            'COUNTY OF UTAH',
            'Claimant / Lienor S G Roser Construction LLC Owner Mike Stuntz Amount claimed $4,213.75 Parcel Number 35-30-15-05699-000-0250',
            'S G Roser Construction LLC ("Claimant") claims a lien under Utah Code § 38-1a-502 upon the real property and improvements described below',
            'Claimant. S G Roser Construction LLC Steven Roser 4200 Lakeland Hwy Lakeland, FL 33801 Telephone: 863-555-0100',
            'Owner or reputed owner of the property. Mike Stuntz 9025 Baywood Park Dr Seminole, UT 33777',
            'Person who contracted with Claimant. Ken Walker Builders',
            'Property subject to the lien. 9025 Baywood Park Dr, Seminole, UT 33777 (Utah County, Utah) Legal description: BAYWOOD PARK LOT 25 Parcel Number: 35-30-15-05699-000-0250',
            'First furnished: June 30, 2026 Last furnished: July 10, 2026',
            'After deducting all just credits and offsets, the amount claimed is $4,213.75 (Four Thousand Two Hundred Thirteen Dollars and Seventy-Five Cents).',
            'Amount claimed in words: Four Thousand Two Hundred Thirteen Dollars and Seventy-Five Cents ($4,213.75).',
            'Claimant\'s current address and current telephone number are stated above (Utah Code § 38-1a-502(2)(e)).',
            'This notice is submitted for recording within the time required by Utah Code § 38-1a-502(1).',
            'The following notice applies only if the property described above is an owner-occupied residence (Utah Code § 38-1a-502(2)(i); Utah Admin. Code R156-38a-108).',
            'PROTECTION AGAINST LIENS AND CIVIL ACTION. Notice is hereby provided in accordance with Section 38-11-108 of the Utah Code that under Utah law an "owner" may be protected against liens being maintained against an "owner-occupied residence" and from other civil action being maintained to recover monies owed for "qualified services" performed or provided by suppliers and subcontractors as a part of this contract, if either section (1) or (2) is met:',
            '(1)(a) the owner entered into a written contract with an original contractor, a factory built housing retailer, or a real estate developer; (b) the original contractor was properly licensed or exempt from licensure under Title 58, Chapter 55, Utah Construction Trades Licensing Act at the time the contract was executed; and (c) the owner paid in full the contracting entity in accordance with the written contract and any written or oral amendments to the contract; or (2) the amount of the general contract between the owner and the original contractor totals no more than $5,000.',
            '(3) An owner who can establish compliance with either section (1) or (2) may perfect the owner\'s protection by applying for a Certificate of Compliance with the Division of Occupational and Professional Licensing. The application is available at www.dopl.utah.gov/rlrf.',
            'CLAIMANT: S G Roser Construction LLC',
            'before me, the undersigned notary public, personally appeared Steven Roser, President of S G Roser Construction LLC, personally known to me or proved to me on the basis of satisfactory evidence to be the person whose name is subscribed to the foregoing instrument, and acknowledged that he or she executed it in that capacity on behalf of S G Roser Construction LLC for the purposes stated in it.',
            'My commission expires',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // Signed and acknowledged (§ 38-1a-502(2)(h)), not sworn or verified; the amount is
        // stated without "plus interest ..." language; the registry notice is not recited.
        expect($text)
            ->not->toContain('being first duly sworn')
            ->not->toContain('Subscribed and sworn')
            ->not->toContain('under penalty of perjury')
            ->not->toContain('plus interest')
            ->not->toContain('Prior notice.')
            ->not->toContain('N/A')
            ->not->toContain('—');

        // The preparer block prints below the rule, clear of the upper right corner (§ 17-71-402(4)(a)(iii)).
        expect(strpos($text, 'Space above this line for recorder\'s use only'))
            ->toBeLessThan(strpos($text, 'Prepared by, recording requested by and return to: eRegister'));

        // The statement follows the numbered items and comes before the signature and the acknowledgment.
        $positions = array_map(fn (string $needle) => strpos($text, $needle), [
            'Amount claimed in words:',
            'This notice is submitted for recording',
            'The following notice applies only if',
            '(3) An owner who can establish compliance',
            'CLAIMANT: S G Roser Construction LLC',
            'personally appeared Steven Roser',
        ]);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false);
        expect($positions)->toBe($sorted);
    });

    it('merges the Utah County recorder facts and leaves other counties on the state values', function () {
        $utah = app(LienDocumentResolver::class)->resolve(lienFixtureFiling(lienFixtureProject('UT', 'Utah County'), 'mechanics_lien'));

        expect($utah->countyKey)->toBe('utah');
        expect($utah->countyName)->toBe('Utah');
        expect($utah->recording['filing_office'])->toMatchArray([
            'label' => 'Utah County Recorder',
            'method' => 'erecord',
            'address_lines' => ['100 East Center St, Suite 1300', 'Provo, UT 84606'],
        ]);
        expect($utah->recording['fee_note'])->toBe('$40 per document, any number of pages, plus $2 for each description over ten (2026).');
        expect($utah->recording['parcel_label'])->toBe('Parcel Number');
        expect($utah->recording['preparer_in_space'])->toBeFalse();
        expect($utah->notes)
            ->toContain('The county calls the parcel number a serial number and finds recorded documents by entry number.')
            ->toContain('There is no lien without a preliminary notice filed in the State Construction Registry (Utah Code § 38-1a-501(1)(e)); keep the SCR entry number with the filing. An owner can petition to nullify a lien filed without one (Utah Code § 38-1a-805).');
        expect($utah->execution)->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment', 'notary_variant' => null]);
        expect($utah->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => 30, 'method' => 'certified_mail']);
        expect(LienDocumentPackage::executionLabel($utah->execution))->toBe('Signed and acknowledged before a notary');
        expect(LienDocumentPackage::serviceLabel($utah))->toBe('The owner by certified mail, return receipt requested within 30 days after recording.');

        $saltLake = app(LienDocumentResolver::class)->resolve(lienFixtureFiling(lienFixtureProject('UT', 'Salt Lake'), 'mechanics_lien'));

        expect($saltLake->countyKey)->toBe('salt-lake');
        expect($saltLake->recording['filing_office']['label'])->toBe('County Recorder');
        expect($saltLake->recording['filing_office']['address_lines'])->toBe([]);
        expect($saltLake->recording['fee_note'])->toBe('$40 per instrument, whatever the page count, plus $2 for each legal description over ten; a county of the second to sixth class may add $5 (Utah Code § 17-71-407).');
        expect($saltLake->notes)->not->toContain('The county calls the parcel number a serial number and finds recorded documents by entry number.');
        expect($saltLake->service['days_after'])->toBe(30);
    });

    it('overrides the seeded verified execution and sets the service method on every kind', function () {
        $ut = LienDocumentRegistry::for('UT');
        $lien = $ut['kinds']['mechanics_lien'];

        expect($lien['title'])->toBe('Notice of Construction Lien');
        expect($lien['body'])->toBe('documents.lien.instruments.bodies.generic-lien');
        expect($lien['sections'])->toMatchArray(['amount' => 'breakdown', 'amount_in_words' => true, 'gc' => true, 'hiring_party' => true, 'prior_notice' => false]);
        expect($lien['clauses']['before_signature'])->toBe(['documents.lien.instruments.clauses.ut-owner-occupied-residence-statement']);
        expect(view()->exists('documents.lien.instruments.clauses.ut-owner-occupied-residence-statement'))->toBeTrue();
        expect($lien['execution'])->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment']);

        foreach (LienDocumentRegistry::KINDS as $kind) {
            expect($ut['kinds'][$kind]['service']['method'])->toBe('certified_mail');
            expect($ut['kinds'][$kind]['service']['recipients'])->toBe(['owner']);
        }

        expect($ut['kinds']['lien_release']['service']['days_after'])->toBeNull();
        expect($ut['recording']['filing_office'])->toMatchArray(['label' => 'County Recorder', 'method' => 'erecord']);
    });
});

describe('Utah preliminary notice, notice of intent and release', function () {
    it('renders the preliminary notice as the registry record and a courtesy copy for the owner', function () {
        $filing = lienFixtureFiling(lienFixtureProject('UT', 'Utah'), 'prelim_notice');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            'PRELIMINARY NOTICE (STATE CONSTRUCTION REGISTRY)',
            'Utah Code § 38-1a-501',
            'Via certified mail, return receipt requested',
            'To: Owner or reputed owner Mike Stuntz',
            'Claimant (person giving notice) S G Roser Construction LLC Steven Roser 4200 Lakeland Hwy Lakeland, FL 33801 Telephone: 863-555-0100',
            'Person who contracted with the claimant Ken Walker Builders Ken Walker 13700 58th St N Ste 204 Clearwater, FL 33760',
            'Owner or reputed owner Mike Stuntz 9025 Baywood Park Dr Seminole, UT 33777',
            'Property (jobsite) 9025 Baywood Park Dr, Seminole, UT 33777 (Utah County, Utah) Legal description: BAYWOOD PARK LOT 25 Parcel Number: 35-30-15-05699-000-0250',
            'First furnished June 30, 2026',
            'Utah law requires this preliminary notice to be filed with the State Construction Registry (Utah Code § 38-1a-501). This copy is sent to the owner for information.',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        expect($text)->not->toContain('Via any')->not->toContain('My commission expires');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->notes)
            ->toContain('The legal step is filing the preliminary notice with the State Construction Registry no later than 20 days after first providing construction work (Utah Code § 38-1a-501(1)(a)); mailing this letter does not replace it.')
            ->toContain('This letter is the record of what was filed and a courtesy copy for the owner; put the SCR entry number in the filing notes.');
        expect($form->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail']);
        expect($form->notaryRequired())->toBeFalse();
        expect(LienDocumentPackage::serviceLabel($form))->toBe('The owner by certified mail, return receipt requested.');
    });

    it('renders the notice of intent as a demand courtesy to the owner', function () {
        $filing = lienFixtureFiling(lienFixtureProject('UT', 'Utah'), 'noi');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('NOTICE OF INTENT TO RECORD A CONSTRUCTION LIEN')
            ->toContain('Utah Code § 38-1a-101 et seq.')
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('To: Owner or reputed owner Mike Stuntz')
            ->toContain('S G Roser Construction LLC ("Claimant") furnished labor, services, equipment or materials for the improvement of the property described below under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid.')
            ->toContain('Unless payment in full of the amount stated above is received within 10 days after the date of this notice, Claimant intends to record a Notice of Construction Lien against the property');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->section('demand_days'))->toBe(10);
        expect($form->service['method'])->toBe('certified_mail');
        expect($form->notes)->toContain('Utah has no notice-of-intent step; the registry preliminary notice and the notice of construction lien are the statutory steps. This is a demand courtesy.');
    });

    it('renders the release as the § 38-1a-803 cancellation, acknowledged before a notary', function () {
        $release = lienFixtureFiling(lienFixtureProject('UT', 'Utah'), 'lien_release', [
            'document_details_json' => ['original_lien' => ['recording_reference' => 'Entry 45678:2026', 'recorded_at' => '2026-07-20', 'county' => 'Utah']],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($release));

        expect($text)
            ->toContain('RELEASE OF CONSTRUCTION LIEN')
            ->toContain('Utah Code § 38-1a-803')
            ->toContain('Releases Notice of Construction Lien recorded July 20, 2026 as Entry 45678:2026')
            ->toContain('KNOW ALL PERSONS BY THESE PRESENTS that S G Roser Construction LLC ("Claimant"), whose address is 4200 Lakeland Hwy, Lakeland, FL 33801, is the claimant under that certain Notice of Construction Lien recorded on July 20, 2026 as Entry 45678:2026 in the official records of Utah County, Utah, against the real property described below, owned by Mike Stuntz:')
            ->toContain('releases, discharges and cancels the lien and the Notice of Construction Lien of record, and authorizes and directs the Utah County Recorder to cancel it of record.')
            ->toContain('Claimant submits this release for recording as the cancellation of the construction lien described above (Utah Code § 38-1a-803).')
            ->toContain('personally appeared Steven Roser, President of S G Roser Construction LLC, personally known to me')
            ->not->toContain('Amount claimed')
            ->not->toContain('being first duly sworn')
            ->not->toContain('Subscribed and sworn')
            ->not->toContain('The following notice applies only if');

        $form = app(LienDocumentResolver::class)->resolve($release);

        expect($form->execution)->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment']);
        expect($form->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail']);
        expect($form->notes)->toContain('Once the full amount owing under the lien, including costs and cancellation fees, is paid, a person interested in the property may ask for a cancellation; the claimant must submit it to each applicable county recorder within 10 days after the request or owe $100 a day or actual damages, whichever is greater (Utah Code § 38-1a-803).');
    });
});
