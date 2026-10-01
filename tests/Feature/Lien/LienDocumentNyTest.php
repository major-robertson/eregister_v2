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

describe('New York notice of mechanic\'s lien', function () {
    it('renders the § 9 notice for Queens with block and lot, the owner\'s interest and the § 9 verification', function () {
        $filing = lienFixtureFiling(lienFixtureProject('NY', 'Queens'), 'mechanics_lien', [
            'document_details_json' => ['block' => '1506', 'lot' => '12', 'owner_interest' => 'fee simple'],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            "NOTICE OF MECHANIC'S LIEN",
            'N.Y. Lien Law §§ 9 and 10',
            'STATE OF NEW YORK',
            'COUNTY OF QUEENS',
            'S G Roser Construction LLC ("Claimant") claims a lien under N.Y. Lien Law §§ 9 and 10 upon the real property',
            'Owner or reputed owner of the property. Mike Stuntz',
            'Interest of the owner in the property: fee simple',
            'Person who contracted with Claimant. Ken Walker Builders',
            'Property subject to the lien. 9025 Baywood Park Dr, Seminole, NY 33777 (Queens County, New York)',
            'Block: 1506 Lot: 12',
            'The property subject to the lien is is not real property improved or to be improved with a single family dwelling.',
            'First furnished: June 30, 2026',
            'Last furnished: July 10, 2026',
            'Contract amount (agreed price or reasonable value of the labor and materials) $ 4,213.75',
            'Less payments received $ 0.00',
            'Amount claimed, after deducting all just credits and offsets $ 4,213.75',
            'VERIFICATION STATE OF COUNTY OF ss.:',
            'I, Steven Roser, being duly sworn, depose and say that I am the President of S G Roser Construction LLC, the lienor named in the foregoing notice of lien; that I have read the notice of lien and know its contents; and that the statements therein contained are true to my knowledge except as to the matters therein stated to be alleged on information and belief, and that as to those matters I believe it to be true.',
            'Subscribed and sworn to (or affirmed) before me',
            'by Steven Roser, President of S G Roser Construction LLC',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // The § 9 verification replaces the generic sworn statement, New York has
        // no preliminary notice to recite, and the admin notes never print.
        expect($text)
            ->not->toContain('being first duly sworn')
            ->not->toContain('true of his or her own knowledge')
            ->not->toContain('Prior notice.')
            ->not->toContain('Undecided')
            ->not->toContain('N/A');

        // The general contractor hired the claimant, so it is listed once.
        expect(substr_count($text, 'Ken Walker Builders'))->toBe(1);

        // The single family statement sits with the property; the verification
        // precedes the signature and the jurat follows it.
        $needles = ['Block: 1506', 'The property subject to the lien is', 'I, Steven Roser, being duly sworn', 'By (signature)', 'Subscribed and sworn to'];
        $positions = array_map(fn (string $needle) => strpos($text, $needle), $needles);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false);
        expect($positions)->toBe($sorted);
    });

    it('states that a commercial project is not a single family dwelling', function () {
        $filing = lienFixtureFiling(lienFixtureProject('NY', 'Albany', ['property_class' => 'commercial']), 'mechanics_lien');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('COUNTY OF ALBANY')
            ->toContain('The property subject to the lien is not real property improved or to be improved with a single family dwelling.')
            ->not->toContain('lien is is not');
    });

    it('merges the Queens County Clerk over the state filing facts and keeps the state values elsewhere', function () {
        $resolver = app(LienDocumentResolver::class);

        $queens = $resolver->resolve(lienFixtureFiling(lienFixtureProject('NY', 'Queens'), 'mechanics_lien'));

        expect($queens->countyKey)->toBe('queens');
        expect($queens->recording['filing_office'])->toMatchArray([
            'label' => 'Queens County Clerk',
            'method' => 'erecord',
            'address_lines' => ['88-11 Sutphin Blvd', 'Jamaica, NY 11435'],
            'vendor' => 'NYS Courts EDDS',
        ]);
        expect($queens->recording['cover_sheet'])->toBeFalse();
        expect($queens->recording['fee_note'])->toBe('$30 for the notice of lien plus $5 for the affidavit of service, paid by card after the clerk reviews the upload (2026).');
        expect($queens->notes)->toContain('Upload the affidavit of service with the notice of lien. A4 phone scans were accepted in 2026, and no NYSCEF registration is needed.');

        $albany = $resolver->resolve(lienFixtureFiling(lienFixtureProject('NY', 'Albany'), 'mechanics_lien'));

        expect($albany->countyKey)->toBe('albany');
        expect($albany->recording['filing_office'])->toMatchArray(['label' => 'County Clerk', 'method' => 'mail', 'address_lines' => [], 'vendor' => null]);
        expect($albany->recording['cover_sheet'])->toBeTrue();
        expect($albany->recording['fee_note'])->toBeNull();
        expect($albany->service['proof'])->toBe('affidavit');
        expect($albany->service['days_after'])->toBe(30);
        expect($albany->service['recipients'])->toBe(['owner', 'customer']);
        expect($albany->service['method'])->toBe('certified_mail');
        expect($albany->notes)->not->toContain('Upload the affidavit of service with the notice of lien. A4 phone scans were accepted in 2026, and no NYSCEF registration is needed.');
    });

    it('describes the filing office and the § 11 and § 11-b service on the rule strip', function () {
        $queens = LienDocumentPackage::forFiling(lienFixtureFiling(lienFixtureProject('NY', 'Queens'), 'mechanics_lien'));
        $rules = collect($queens->rules())->pluck('value', 'label');

        expect($rules['File with'])->toBe('Queens County Clerk, e-recording via NYS Courts EDDS (88-11 Sutphin Blvd, Jamaica, NY 11435)');
        expect($rules['Fee'])->toBe('$30 for the notice of lien plus $5 for the affidavit of service, paid by card after the clerk reviews the upload (2026).');
        expect($rules['Signing'])->toBe('Sworn to and signed before a notary (jurat)');
        expect($rules['Serve'])->toBe('The owner and the hiring party by certified mail, return receipt requested within 30 days after recording.');
        expect(collect($queens->items)->pluck('label')->all())->not->toContain('Filing cover sheet');
        expect($queens->warnings)->toContain('The hiring party (Ken Walker Builders) is not a recipient yet; add them under Recipients for the proof of service and labels.');

        $albany = LienDocumentPackage::forFiling(lienFixtureFiling(lienFixtureProject('NY', 'Albany'), 'mechanics_lien'));

        expect(collect($albany->rules())->pluck('value', 'label')['File with'])->toBe('County Clerk, by mail');
        expect(collect($albany->items)->pluck('label')->all())->toContain('Filing cover sheet');
    });

    it('keeps the § 9 sections, the clause views and certified mail on every kind', function () {
        $ny = LienDocumentRegistry::for('NY');
        $lien = $ny['kinds']['mechanics_lien'];

        expect($lien['body'])->toBe('documents.lien.instruments.bodies.generic-lien');
        expect($lien['sections'])->toMatchArray([
            'amount' => 'breakdown',
            'gc' => true,
            'hiring_party' => true,
            'block_lot' => true,
            'owner_interest' => true,
            'prior_notice' => false,
            'first_furnish' => true,
            'last_furnish' => true,
        ]);
        expect($lien['execution'])->toMatchArray(['verification' => 'sworn', 'notary' => true, 'notary_form' => 'jurat', 'statement' => false]);
        expect($lien['clauses']['after_property'])->toBe(['documents.lien.instruments.clauses.ny-single-family-dwelling']);
        expect($lien['clauses']['before_signature'])->toBe(['documents.lien.instruments.clauses.ny-verification']);
        expect(view()->exists('documents.lien.instruments.clauses.ny-single-family-dwelling'))->toBeTrue();
        expect(view()->exists('documents.lien.instruments.clauses.ny-verification'))->toBeTrue();

        foreach (LienDocumentRegistry::KINDS as $kind) {
            expect($ny['kinds'][$kind]['service']['method'])->toBe('certified_mail');
        }

        expect($ny['kinds']['prelim_notice']['statute'])->toBeNull();
        expect($ny['kinds']['lien_release']['service']['recipients'])->toBe(['owner']);
        expect($ny['kinds']['lien_release']['service']['days_after'])->toBeNull();
    });
});

describe('New York certificate of discharge', function () {
    it('renders the § 19(1) certificate with the discharge statement and an acknowledgment', function () {
        $release = lienFixtureFiling(lienFixtureProject('NY', 'Queens'), 'lien_release', [
            'document_details_json' => ['original_lien' => ['recording_reference' => 'Index No. 2026-01234', 'recorded_at' => '2026-07-20', 'county' => 'Queens']],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($release));

        expect($text)
            ->toContain("CERTIFICATE OF DISCHARGE OF MECHANIC'S LIEN")
            ->toContain('N.Y. Lien Law § 19(1)')
            ->toContain('KNOW ALL PERSONS BY THESE PRESENTS that S G Roser Construction LLC')
            ->toContain("is the claimant under that certain Notice of Mechanic's Lien recorded on July 20, 2026 as Index No. 2026-01234 in the official records of Queens County, New York")
            ->toContain('authorizes and directs the Queens County Clerk to cancel it of record.')
            ->toContain('The undersigned lienor certifies that the lien is satisfied and released as to the whole of the real property affected thereby and may be discharged in whole (N.Y. Lien Law § 19(1)).')
            ->toContain('before me, the undersigned notary public, personally appeared Steven Roser, President of S G Roser Construction LLC')
            ->toContain('and acknowledged that he or she executed it in that capacity on behalf of S G Roser Construction LLC')
            ->not->toContain('VERIFICATION')
            ->not->toContain('Subscribed and sworn to')
            ->not->toContain('single family dwelling');

        $rules = collect(LienDocumentPackage::forFiling($release)->rules())->pluck('value', 'label');

        expect($rules['Signing'])->toBe('Signed and acknowledged before a notary');
        expect($rules['Serve'])->toBe('The owner by certified mail, return receipt requested.');
    });
});

describe('New York courtesy notices', function () {
    it('renders the courtesy notice of furnishing and the notice of intent by certified mail', function () {
        $generator = app(LienDocumentGenerator::class);

        $furnishing = lienFixtureText($generator->render(lienFixtureFiling(lienFixtureProject('NY', 'Queens'), 'prelim_notice')));

        expect($furnishing)
            ->toContain('NOTICE OF FURNISHING (COURTESY)')
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('To: Owner or reputed owner Mike Stuntz')
            ->toContain('THIS IS NOT A LIEN.')
            ->toContain('Estimated total price $4,213.75')
            ->not->toContain('Via any')
            ->not->toContain('My commission expires');

        $intent = lienFixtureText($generator->render(lienFixtureFiling(lienFixtureProject('NY', 'Queens'), 'noi')));

        expect($intent)
            ->toContain("NOTICE OF INTENT TO FILE A MECHANIC'S LIEN")
            ->toContain('N.Y. Lien Law art. 2')
            ->toContain('To: Owner or reputed owner Mike Stuntz')
            ->toContain('To: Person who contracted with the claimant Ken Walker Builders')
            ->toContain('under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid')
            ->toContain("within 10 days after the date of this notice, Claimant intends to record a Notice of Mechanic's Lien against the property")
            ->not->toContain('Via any');
    });
});
