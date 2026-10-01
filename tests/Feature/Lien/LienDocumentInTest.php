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

describe('Indiana sworn statement and notice of intention to hold mechanic\'s lien', function () {
    it('renders the IC 32-28-3-3 statement for Marion County with the IC 36-2-11-15 statements after the notary block', function () {
        $filing = lienFixtureFiling(lienFixtureProject('IN', 'Marion'), 'mechanics_lien', [
            'document_details_json' => ['license_number' => 'GL2400093'],
        ]);

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        foreach ([
            'SWORN STATEMENT AND NOTICE OF INTENTION TO HOLD MECHANIC\'S LIEN',
            'IC 32-28-3-3',
            'STATE OF INDIANA',
            'COUNTY OF MARION',
            'Parcel Number 35-30-15-05699-000-0250',
            'S G Roser Construction LLC ("Claimant") claims a lien under IC 32-28-3-3 upon the real property and improvements described below',
            'Contractor\'s license number: GL2400093',
            'Owner or reputed owner of the property. Mike Stuntz 9025 Baywood Park Dr Seminole, IN 33777',
            'Person who contracted with Claimant. Ken Walker Builders',
            'Property subject to the lien. 9025 Baywood Park Dr, Seminole, IN 33777 (Marion County, Indiana) Legal description: BAYWOOD PARK LOT 25 Parcel Number: 35-30-15-05699-000-0250',
            'First furnished: June 30, 2026 Last furnished: July 10, 2026',
            'After deducting all just credits and offsets, the amount claimed is $4,213.75.',
            'Prior notice. Claimant served its Notice of Delivery or Work and of the Existence of Lien Rights on August 7, 2026',
            // House wording that tracks IC 32-28-3-3(a)-(c).
            'Claimant gives notice of its intention to hold a lien upon the real estate and improvements described above for the amount of its claim stated above.',
            'The owner\'s name and address stated above are the owner\'s name and latest address as shown on the property tax records of the county.',
            'Claimant files this statement and notice in the recorder\'s office of the county within the time IC 32-28-3-3 allows. That is not later than sixty (60) days after performing labor or furnishing materials or machinery for a Class 2 structure (as defined in IC 22-12-1-5) or an improvement on the same real estate auxiliary to a Class 2 structure, and not later than ninety (90) days after in any other case.',
            'The undersigned, being first duly sworn, states that he or she is the President of S G Roser Construction LLC',
            'Subscribed and sworn to (or affirmed) before me on this',
            'by Steven Roser, President of S G Roser Construction LLC',
            'My commission expires',
            // IC 33-42-9-12(a)(5)(B).
            'Notary\'s county of commission',
            // IC 36-2-11-15(d) and (c), verbatim; the preparer's name is a blank until config names one.
            'I affirm, under the penalties for perjury, that I have taken reasonable care to redact each Social Security number in this document, unless required by law (',
            'This instrument was prepared by , eRegister.',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        expect($text)->not->toContain('N/A')->not->toContain('—');

        // The statements sit at the conclusion of the instrument, affirmation first.
        $affirmation = strpos($text, 'I affirm, under the penalties for perjury');
        expect($affirmation)->toBeGreaterThan(strpos($text, 'My commission expires'));
        expect(strpos($text, 'This instrument was prepared by'))->toBeGreaterThan($affirmation);
        expect($pdf->getHtml())->toContain('This instrument was prepared by <span class="fill fill-mid">&nbsp;</span>, eRegister.');

        // The shared execution partial keeps the signature and the whole certificate together:
        // its keep div opens at the signature table and runs past the county-of-commission line.
        $html = $pdf->getHtml();
        $keep = strpos($html, '<div class="keep">');
        expect(substr($html, $keep, strpos($html, 'I affirm, under the penalties for perjury') - $keep))
            ->toMatch('/^<div class="keep">\s*<table class="sig-table">/')
            ->toContain('Notary\'s county of commission');

        // IC 36-2-11-16.5(b)(2): 2in top and bottom, 1in sides, no page number in the margin,
        // and a 1in recorder space under the 2in margin so the rule still sits 3in down page 1.
        expect($pdf->getHtml())
            ->toContain('@page { margin: 2in 1in; }')
            ->toContain('<div class="recorder-space" style="height: 1in;">')
            ->not->toContain('class="page-number"');

        // The whole space stays clear: the preparer block prints below the rule.
        expect(strpos($text, 'Space above this line for recorder\'s use only'))
            ->toBeLessThan(strpos($text, 'Prepared by, recording requested by and return to: eRegister'));
    });

    it('prints the business license number when Document details has none', function () {
        $filing = lienFixtureFiling(lienFixtureProject('IN', 'Marion'), 'mechanics_lien');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)->toContain('Contractor\'s license number: CGC-123456');
    });

    it('merges the Marion County recorder facts and leaves other counties on the state rules', function () {
        $resolver = app(LienDocumentResolver::class);
        $marion = $resolver->resolve(lienFixtureFiling(lienFixtureProject('IN', 'Marion County'), 'mechanics_lien'));
        $hamilton = $resolver->resolve(lienFixtureFiling(lienFixtureProject('IN', 'Hamilton'), 'mechanics_lien'));

        expect($marion->countyKey)->toBe('marion');
        expect($marion->recording['fee_note'])->toBe('$35 flat per instrument (September 2026).');
        expect($marion->recording['filing_office'])->toMatchArray(['label' => 'Marion County Recorder', 'method' => 'erecord']);
        expect($marion->notes)->toContain('The recorder\'s stamp goes in the top-right of the first-page space.');

        expect($hamilton->countyKey)->toBe('hamilton');
        expect($hamilton->recording['fee_note'])->toBe('$25 per instrument plus any fee the county adds by ordinance (IC 36-2-7-10).');
        expect($hamilton->recording['filing_office'])->toMatchArray(['label' => 'County Recorder', 'method' => 'erecord']);
        expect($hamilton->notes)->not->toContain('The recorder\'s stamp goes in the top-right of the first-page space.');
    });

    it('keeps the Indiana execution, service and recording rules', function () {
        $in = LienDocumentRegistry::for('IN');
        $lien = $in['kinds']['mechanics_lien'];
        $affirmationView = 'documents.lien.instruments.clauses.in-recording-affirmation';

        expect($lien['title'])->toBe('Sworn Statement and Notice of Intention to Hold Mechanic\'s Lien');
        expect($lien['body'])->toBe('documents.lien.instruments.bodies.generic-lien');
        expect($lien['sections'])->toMatchArray(['amount' => 'breakdown', 'license' => true, 'gc' => true, 'hiring_party' => true, 'prior_notice' => true]);
        expect($lien['clauses']['affirmations'])->toHaveCount(3);
        expect($lien['clauses']['after_execution'])->toBe([$affirmationView]);
        expect($lien['execution'])->toMatchArray(['verification' => 'sworn', 'notary' => true, 'notary_form' => 'jurat', 'notary_county_line' => true]);
        expect($lien['service'])->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail']);

        expect($in['kinds']['lien_release']['clauses']['after_execution'])->toBe([$affirmationView]);
        expect($in['kinds']['lien_release']['execution'])->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment', 'notary_county_line' => true]);

        expect($in['recording'])->toMatchArray([
            'parcel_label' => 'Parcel Number',
            'preparer_in_space' => false,
            'top_margin_in' => 3.0,
            'other_margin_in' => 2.0,
            'side_margin_in' => 1.0,
            'page_numbers' => false,
            'min_font_pt' => 10,
        ]);
        expect($in['recording']['filing_office'])->toMatchArray(['label' => 'County Recorder', 'method' => 'erecord']);

        expect($in['kinds']['noi']['body'])->toBe('documents.lien.letters.bodies.generic-noi');
        expect($in['kinds']['noi']['sections']['demand_days'])->toBe(10);
    });
});

describe('Indiana owner-occupied residence notice', function () {
    it('renders the IC 32-28-3-1(h), (i) notice of the delivery or work and of the existence of lien rights', function () {
        $filing = lienFixtureFiling(lienFixtureProject('IN', 'Marion'), 'prelim_notice');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            'NOTICE OF DELIVERY OR WORK AND OF THE EXISTENCE OF LIEN RIGHTS',
            'IC 32-28-3-1(h), (i)',
            'Via certified mail, return receipt requested',
            'To: Owner or reputed owner Mike Stuntz 9025 Baywood Park Dr Seminole, IN 33777',
            'Claimant (person giving notice) S G Roser Construction LLC Steven Roser 4200 Lakeland Hwy Lakeland, FL 33801',
            'Person who contracted with the claimant Ken Walker Builders',
            'Property (jobsite) 9025 Baywood Park Dr, Seminole, IN 33777 (Marion County, Indiana) Legal description: BAYWOOD PARK LOT 25',
            'First furnished June 30, 2026',
            'Estimated total price $4,213.75',
            // House wording that tracks IC 32-28-3-1(h), (i).
            'This is the written notice of the delivery or work described above, and of the existence of lien rights, that IC 32-28-3-1 requires.',
            'If the claimant is not paid, it may hold a lien on the dwelling and on the owner\'s interest in the land, to the extent of the value of the materials, labor or machinery it furnished, by filing a sworn statement and notice of intention to hold a lien in the county recorder\'s office (IC 32-28-3-3).',
            'CLAIMANT: S G Roser Construction LLC',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        expect($text)->not->toContain('My commission expires')->not->toContain('To: Original (direct) contractor');

        expect(collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label')['Serve'])
            ->toBe('The owner by certified mail, return receipt requested within 30 days after first furnishing.');
    });
});

describe('Indiana release of mechanic\'s lien', function () {
    it('renders the release with the acknowledgment and the IC 36-2-11-15 statements', function () {
        $release = lienFixtureFiling(lienFixtureProject('IN', 'Marion'), 'lien_release', [
            'document_details_json' => ['original_lien' => [
                'recording_reference' => 'Instrument No. 2026-012345',
                'recorded_at' => '2026-09-15',
                'county' => 'Marion',
            ]],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($release));

        expect($text)
            ->toContain('RELEASE OF MECHANIC\'S LIEN')
            ->toContain('IC 32-28-1-1')
            ->toContain('KNOW ALL PERSONS BY THESE PRESENTS that S G Roser Construction LLC ("Claimant"), whose address is 4200 Lakeland Hwy, Lakeland, FL 33801, is the claimant under that certain Sworn Statement and Notice of Intention to Hold Mechanic\'s Lien recorded on September 15, 2026 as Instrument No. 2026-012345 in the official records of Marion County, Indiana')
            ->toContain('authorizes and directs the Marion County Recorder to cancel it of record.')
            ->toContain('before me, the undersigned notary public, personally appeared Steven Roser, President of S G Roser Construction LLC, personally known to me or proved to me on the basis of satisfactory evidence to be the person whose name is subscribed to the foregoing instrument, and acknowledged that he or she executed it in that capacity on behalf of S G Roser Construction LLC for the purposes stated in it.')
            ->toContain('Notary\'s county of commission')
            ->toContain('I affirm, under the penalties for perjury, that I have taken reasonable care to redact each Social Security number in this document, unless required by law (')
            ->toContain('This instrument was prepared by , eRegister.')
            ->not->toContain('being first duly sworn')
            ->not->toContain('Amount claimed');

        expect(strpos($text, 'I affirm, under the penalties for perjury'))->toBeGreaterThan(strpos($text, 'My commission expires'));
    });
});

describe('Indiana notice of intent', function () {
    it('keeps the generic notice of intent as a demand courtesy', function () {
        $filing = lienFixtureFiling(lienFixtureProject('IN', 'Marion'), 'noi');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('NOTICE OF INTENT TO FILE A MECHANIC\'S LIEN')
            ->toContain('under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid')
            ->toContain('within 10 days after the date of this notice, Claimant intends to record a Sworn Statement and Notice of Intention to Hold Mechanic\'s Lien against the property');
    });
});

describe('defaults the Indiana settings leave alone', function () {
    it('keeps 1-inch margins, page numbers and no county-of-commission line in Ohio', function () {
        $pdf = app(LienDocumentGenerator::class)->render(lienFixtureFiling(lienFixtureProject('OH', 'Franklin'), 'mechanics_lien'));

        // With side_margin_in unset the shell still emits the one-value rule.
        expect($pdf->getHtml())
            ->toContain('@page { margin: 1in; }')
            ->toContain('<div class="recorder-space" style="height: 2in;">')
            ->toContain('class="page-number"');

        expect(lienFixtureText($pdf))
            ->toContain('My commission expires')
            ->not->toContain('Notary\'s county of commission');

        $ohio = LienDocumentRegistry::for('OH');
        expect($ohio['recording']['side_margin_in'])->toBeNull();
        expect($ohio['kinds']['mechanics_lien']['execution']['notary_county_line'])->toBeFalse();
        expect($ohio['kinds']['lien_release']['execution']['notary_county_line'])->toBeFalse();
    });
});
