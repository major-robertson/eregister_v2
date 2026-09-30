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

describe('Colorado notice of intent to file lien statement', function () {
    it('renders the § 38-22-109(3) notice to the owner and the principal contractor', function () {
        $filing = lienFixtureFiling(lienFixtureProject('CO', 'Denver'), 'noi');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('NOTICE OF INTENT TO FILE LIEN STATEMENT')
            ->toContain('C.R.S. § 38-22-109(3)')
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('To: Owner or reputed owner Mike Stuntz')
            ->toContain('To: Original (direct) contractor Ken Walker Builders')
            ->toContain('under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid.')
            ->toContain('9025 Baywood Park Dr, Seminole, CO 33777 (Denver County, Colorado)')
            ->toContain('Last furnished: July 10, 2026')
            ->toContain('Claimant gives notice that it intends to file a lien statement against the property described above with the county clerk and recorder of the county where the property is located. Unless the amount stated above is paid, Claimant will file it no sooner than ten days after this notice is served.')
            ->not->toContain('Unless payment in full')
            ->not->toContain('My commission expires');
    });

    it('is proved by a notarized affidavit of service while the notice itself needs no notary', function () {
        $filing = lienFixtureFiling(lienFixtureProject('CO', 'Denver'), 'noi');

        $doc = app(LienDocumentResolver::class)->resolve($filing);

        expect($doc->body)->toBe('documents.lien.letters.bodies.generic-noi')
            ->and($doc->service)->toMatchArray(['recipients' => ['owner', 'gc'], 'days_after' => null, 'method' => 'certified_mail', 'proof' => 'affidavit'])
            ->and($doc->recipientRoles())->toBe(['owner', 'gc'])
            ->and($doc->notaryRequired())->toBeFalse()
            ->and($doc->section('demand_days'))->toBe(10);

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['Signing'])->toBe('Signed by the claimant; no notary')
            ->and($rules['Serve'])->toBe('The owner and the general contractor by certified mail, return receipt requested.');
    });
});

describe('Colorado statement of lien', function () {
    it('renders the sworn § 38-22-109 statement for Weld County with the Colorado affirmations', function () {
        $filing = lienFixtureFiling(lienFixtureProject('CO', 'Weld'), 'mechanics_lien');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('STATEMENT OF LIEN')
            ->toContain('C.R.S. § 38-22-109')
            ->toContain('STATE OF COLORADO')
            ->toContain('COUNTY OF WELD')
            ->toContain('Parcel Number 35-30-15-05699-000-0250')
            ->toContain('S G Roser Construction LLC ("Claimant") claims a lien under C.R.S. § 38-22-109 upon the real property')
            ->toContain('Owner or reputed owner of the property. Mike Stuntz')
            ->toContain('Person who contracted with Claimant. Ken Walker Builders')
            ->toContain('Property subject to the lien. 9025 Baywood Park Dr, Seminole, CO 33777 (Weld County, Colorado)')
            ->toContain('Last furnished: July 10, 2026')
            ->toContain('After deducting all just credits and offsets, the amount claimed is $4,213.75.')
            ->toContain('Claimant, named above, is the person who furnished the laborers or materials, or performed the labor, for which this lien is claimed (C.R.S. § 38-22-109(1)(b)).')
            ->toContain('If Claimant is a subcontractor, the name of the contractor is stated above (C.R.S. § 38-22-109(1)(b)).')
            ->toContain('The amount claimed above is due and owing to Claimant (C.R.S. § 38-22-109(1)(d)).')
            ->toContain('Claimant served a notice of intent to file a lien statement at least ten days before filing this statement. The affidavit of that service is recorded with this statement (C.R.S. § 38-22-109(3)).')
            ->toContain('This statement is filed within four months after Claimant last furnished labor, laborers or materials, or within two months after the improvement was completed for labor alone by the day or piece (C.R.S. § 38-22-109(4), (5)).')
            ->toContain('being first duly sworn')
            ->toContain('authorized to make this Statement of Lien on its behalf')
            ->toContain('Subscribed and sworn to (or affirmed) before me')
            ->not->toContain('Prior notice.')
            ->not->toContain('personally appeared');

        // The affirmations follow the amount and come before the oath.
        expect(strpos($text, 'the amount claimed is $4,213.75.'))->toBeLessThan(strpos($text, 'is the person who furnished the laborers'))
            ->and(strpos($text, 'for labor alone by the day or piece'))->toBeLessThan(strpos($text, 'being first duly sworn'));
    });

    it('attaches the recorded affidavit of service of the notice of intent and swears the statement before a notary', function () {
        $filing = lienFixtureFiling(lienFixtureProject('CO', 'Weld'), 'mechanics_lien');
        $attachment = 'Affidavit of service of the notice of intent to file a lien statement, recorded with this statement (C.R.S. § 38-22-109(3)).';

        $doc = app(LienDocumentResolver::class)->resolve($filing);

        expect($doc->attachments)->toBe([$attachment])
            ->and($doc->execution)->toMatchArray(['verification' => 'sworn', 'notary' => true, 'notary_form' => 'jurat'])
            ->and($doc->sections)->toMatchArray(['amount' => 'breakdown', 'gc' => true, 'hiring_party' => true, 'prior_notice' => false])
            ->and($doc->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail']);

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['Attach'])->toBe($attachment)
            ->and($rules['Signing'])->toBe('Sworn to and signed before a notary (jurat)')
            ->and($rules['Serve'])->toBe('The owner by certified mail, return receipt requested.')
            ->and($rules['File with'])->toBe('Weld County Clerk and Recorder, e-recording (Recording Department, 1250 H Street, Greeley, CO 80631)')
            ->and($rules['Fee'])->toStartWith('$43 per document');
    });
});

describe('Colorado county files', function () {
    it('merges the Denver, Weld and Boulder recorder facts and leaves other counties to the state', function () {
        $resolve = fn (string $county) => app(LienDocumentResolver::class)->resolve(lienFixtureFiling(lienFixtureProject('CO', $county), 'mechanics_lien'));
        $weldNote = 'Weld takes e-recording through Simplifile, eRecording Partners and CSC (weld.gov, 2026).';

        $denver = $resolve('Denver');
        $weld = $resolve('Weld County');
        $boulder = $resolve('Boulder');
        $adams = $resolve('Adams');

        expect($denver->countyKey)->toBe('denver')
            ->and($denver->recording['filing_office'])->toMatchArray([
                'label' => 'Denver Clerk and Recorder',
                'method' => 'erecord',
                'address_lines' => ['Recording Dept., 200 W. 14th Ave.', 'Denver, CO 80204'],
            ]);

        expect($weld->countyKey)->toBe('weld')
            ->and($weld->countyName)->toBe('Weld')
            ->and($weld->recording['filing_office'])->toMatchArray([
                'label' => 'Weld County Clerk and Recorder',
                'method' => 'erecord',
                'address_lines' => ['Recording Department, 1250 H Street', 'Greeley, CO 80631'],
            ])
            ->and($weld->recording['parcel_label'])->toBe('Parcel Number')
            ->and($weld->notes)->toContain($weldNote);

        expect($boulder->countyKey)->toBe('boulder')
            ->and($boulder->recording['filing_office'])->toMatchArray([
                'label' => 'Boulder County Clerk and Recorder',
                'method' => 'erecord',
                'address_lines' => ['Recording Division, 1750 33rd St., Suite 201', 'Boulder, CO 80301'],
            ]);

        expect($adams->countyKey)->toBe('adams')
            ->and($adams->recording['filing_office'])->toMatchArray(['label' => 'County Clerk and Recorder', 'method' => 'erecord', 'address_lines' => []])
            ->and($adams->notes)->not->toContain($weldNote);
    });
});

describe('Colorado release of lien', function () {
    it('renders the § 38-22-118 release with an acknowledgment and the recorded statement it satisfies', function () {
        $filing = lienFixtureFiling(lienFixtureProject('CO', 'Weld'), 'lien_release', [
            'document_details_json' => ['original_lien' => ['recording_reference' => 'Reception No. 4987123', 'recorded_at' => '2026-07-20', 'county' => 'Weld']],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('RELEASE OF LIEN')
            ->toContain('C.R.S. § 38-22-118')
            ->toContain('STATE OF COLORADO')
            ->toContain('COUNTY OF WELD')
            ->toContain('Releases Statement of Lien recorded July 20, 2026 as Reception No. 4987123')
            ->toContain('is the claimant under that certain Statement of Lien recorded on July 20, 2026 as Reception No. 4987123 in the official records of Weld County, Colorado')
            ->toContain('authorizes and directs the Weld County Clerk and Recorder to cancel it of record.')
            ->toContain("This release is Claimant's acknowledgment of satisfaction of the lien, entered of record under C.R.S. § 38-22-118.")
            ->toContain('before me, the undersigned notary public, personally appeared Steven Roser, President of S G Roser Construction LLC')
            ->toContain('acknowledged that he or she executed it in that capacity on behalf of S G Roser Construction LLC for the purposes stated in it.')
            ->not->toContain('being first duly sworn');
    });
});

describe('Colorado preliminary notice and registry', function () {
    it('keeps the courtesy preliminary notice to the owner and the principal contractor', function () {
        $filing = lienFixtureFiling(lienFixtureProject('CO', 'Boulder'), 'prelim_notice');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('PRELIMINARY NOTICE')
            ->toContain('C.R.S. § 38-22-101 et seq.')
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('To: Owner or reputed owner Mike Stuntz')
            ->toContain('To: Original (direct) contractor Ken Walker Builders')
            ->toContain('Estimated total price $4,213.75')
            ->not->toContain('My commission expires');
    });

    it('files with the county clerk and recorder and names a service method for every kind', function () {
        $co = LienDocumentRegistry::for('CO');

        expect($co['recording']['filing_office'])->toMatchArray(['label' => 'County Clerk and Recorder', 'method' => 'erecord'])
            ->and($co['recording']['parcel_label'])->toBe('Parcel Number')
            ->and($co['recording']['fee_note'])->toStartWith('$43 per document')
            ->and($co['kinds']['noi']['sections']['demand_days'])->toBe(10)
            ->and($co['kinds']['noi']['statute'])->toBe('C.R.S. § 38-22-109(3)')
            ->and($co['kinds']['noi']['execution']['notary'])->toBeFalse()
            ->and($co['kinds']['mechanics_lien']['title'])->toBe('Statement of Lien')
            ->and($co['kinds']['lien_release']['execution'])->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment'])
            ->and($co['kinds']['prelim_notice']['enabled'])->toBeTrue()
            ->and($co['kinds']['prelim_notice']['service'])->toMatchArray(['recipients' => ['owner', 'gc'], 'method' => 'certified_mail']);

        // The seeded rule says "any"; every kind names its own method.
        foreach ($co['kinds'] as $entry) {
            expect($entry['service']['method'])->toBe('certified_mail');
        }
    });
});
