<?php

use App\Domains\Lien\Documents\LienDocumentGenerator;
use App\Domains\Lien\Documents\LienDocumentPackage;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Documents\LienDocumentResolver;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\PartyRole;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');
    LienDocumentRegistry::flush();
});

afterEach(fn () => LienDocumentRegistry::flush());

describe('Tennessee notice of lien', function () {
    it('renders the § 66-11-112(d) sworn statement for Sumner County with the jurat and the certificate of authenticity page', function () {
        $filing = lienFixtureFiling(lienFixtureProject('TN', 'Sumner'), 'mechanics_lien');

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        foreach ([
            'NOTICE OF LIEN',
            'Tenn. Code Ann. § 66-11-112',
            'STATE OF TENNESSEE',
            'COUNTY OF SUMNER',
            'Claimant / Lienor S G Roser Construction LLC',
            'Owner Mike Stuntz',
            'Amount claimed $4,213.75',
            'Map and Parcel 35-30-15-05699-000-0250',
            'Steven Roser being first duly sworn, says that S G Roser Construction LLC, the Lien Claimant, furnished certain material or performed certain work or labor in furtherance of improvements to the real property hereinafter described, in pursuance of a certain contract, with Ken Walker Builders, the prime contractor.',
            'The first of the work or labor was performed or the first of the material, services, equipment, or machinery was furnished on the 30th day of June, 2026.',
            'The last of the work or labor was performed or the last of the material, services, equipment, or machinery was furnished on the 10th day of July, 2026, and there is justly and truly due Lien Claimant therefor from Ken Walker Builders, the prime contractor over and above all legal setoffs, the sum of Four Thousand Two Hundred Thirteen Dollars and Seventy-Five Cents ($4,213.75), for which amount Lien Claimant claims a lien under T.C.A. §§ 66-11-101, et seq. on the real property, of which Mike Stuntz is or was the owner, which is described as follows:',
            '9025 Baywood Park Dr, Seminole, TN 33777 (Sumner County, Tennessee)',
            'Legal description: BAYWOOD PARK LOT 25',
            'Map and Parcel: 35-30-15-05699-000-0250',
            'Description of work: Removal of drywall, replacement of drywall, and damage repair throughout the home',
            'Subscribed and sworn to (or affirmed) before me',
            'by Steven Roser, President of S G Roser Construction LLC',
            'CERTIFICATE OF AUTHENTICITY',
            'Tenn. Code Ann. § 66-24-101(d)(3)',
            'do hereby make oath that I am a licensed attorney and/or the custodian of the original version of the electronic document tendered for registration herewith and that this electronic document is a true and exact copy of the original document executed and authenticated according to law on',
            '(date of document).',
            'Affiant Signature',
            'Sworn to and subscribed before me this',
            "Notary's Signature",
            'MY COMMISSION EXPIRES:',
            "Notary's Seal (if on paper)",
            "Space above this line for recorder's use only",
            'Prepared by, recording requested by and return to: eRegister',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // The body is the sworn statement: no generic sworn preface, no amounts table, no prior-notice item.
        expect(substr_count($text, 'being first duly sworn'))->toBe(1);
        expect($text)
            ->not->toContain('The undersigned, being first duly sworn')
            ->not->toContain('Contract amount')
            ->not->toContain('Prior notice.')
            ->not->toContain('N/A');

        // Two notary blocks: the jurat on the notice, then the certificate's own statutory jurat.
        expect(substr_count(mb_strtolower($text), 'my commission expires'))->toBe(2);

        // The form, the jurat, then the certificate on its own page.
        $positions = array_map(fn (string $needle) => strpos($text, $needle), [
            'being first duly sworn, says that',
            'Description of work:',
            'Subscribed and sworn to (or affirmed) before me',
            'CERTIFICATE OF AUTHENTICITY',
            'MY COMMISSION EXPIRES:',
        ]);
        $sorted = $positions;
        sort($sorted);
        expect($positions)->not->toContain(false)->toBe($sorted);

        expect($pdf->getHtml())
            ->toContain('<div class="keep" style="page-break-before: always;">')
            ->toContain('height: 2in');
    });

    it('names the hiring party for a remote contractor, serves the owner under § 66-11-115 and names the prime contractor for a sub-subcontractor', function () {
        $generator = app(LienDocumentGenerator::class);

        $subcontractor = lienFixtureText($generator->render(lienFixtureFiling(lienFixtureProject('TN', 'Sumner'), 'mechanics_lien')));

        expect($subcontractor)
            ->toContain('in pursuance of a certain contract, with Ken Walker Builders, the prime contractor.')
            ->toContain('there is justly and truly due Lien Claimant therefor from Ken Walker Builders, the prime contractor over and above all legal setoffs')
            ->toContain('of which Mike Stuntz is or was the owner')
            ->toContain('Lien Claimant is a remote contractor and serves this notice of lien on the owner under Tenn. Code Ann. § 66-11-115.')
            ->not->toContain('Prime contractor:');

        $project = lienFixtureProject('TN', 'Sumner', ['claimant_type' => ClaimantType::SubSubContractor, 'hired_by' => 'subcontractor']);
        $project->parties()->where('role', PartyRole::Customer->value)->update([
            'name' => 'Dana Hill',
            'company_name' => 'Hill Drywall Co',
            'address1' => '12 Main St',
            'city' => 'Gallatin',
            'state' => 'TN',
            'zip' => '37066',
        ]);

        $subSubcontractor = lienFixtureText($generator->render(lienFixtureFiling($project, 'mechanics_lien')));

        expect($subSubcontractor)
            ->toContain('in pursuance of a certain contract, with Hill Drywall Co, a remote contractor.')
            ->toContain('there is justly and truly due Lien Claimant therefor from Hill Drywall Co, a remote contractor over and above all legal setoffs')
            ->toContain('Lien Claimant is a remote contractor and serves this notice of lien on the owner under Tenn. Code Ann. § 66-11-115.')
            ->toContain('Prime contractor: Ken Walker Builders, 13700 58th St N Ste 204, Clearwater, FL 33760.');
    });

    it('names the owner as the contracting party for a prime contractor', function () {
        $project = lienFixtureProject('TN', 'Sumner', ['claimant_type' => ClaimantType::Gc, 'hired_by' => 'owner']);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render(lienFixtureFiling($project, 'mechanics_lien')));

        expect($text)
            ->toContain('in pursuance of a certain contract, with Mike Stuntz, the owner.')
            ->toContain('there is justly and truly due Lien Claimant therefor from Mike Stuntz, the owner over and above all legal setoffs')
            ->toContain('of which Mike Stuntz is or was the owner')
            ->not->toContain('remote contractor')
            ->not->toContain('Ken Walker Builders');
    });
});

describe('Tennessee notice of nonpayment', function () {
    it('renders the § 66-11-145(d) form to the owner and the prime contractor with every item (a) requires', function () {
        $filing = lienFixtureFiling(lienFixtureProject('TN', 'Sumner'), 'prelim_notice');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            'S G Roser Construction LLC 4200 Lakeland Hwy Lakeland, FL 33801 863-555-0100',
            'Via certified mail, return receipt requested',
            'To: Owner or reputed owner Mike Stuntz',
            'To: Original (direct) contractor Ken Walker Builders',
            'NOTICE OF NONPAYMENT',
            'Tenn. Code Ann. § 66-11-145',
            // (a)(1) the remote contractor and (a)(2) what it provided, in the form's words.
            'Pursuant to Tennessee Code Annotated, § 66-11-145, notice is hereby given that S G Roser Construction LLC has not been paid for certain labor, materials, services, equipment, or machinery it supplied in the Removal of drywall, replacement of drywall, and damage repair throughout the home of the Baywood Park drywall, located at:',
            // (a)(5) the property.
            '9025 Baywood Park Dr, Seminole, TN 33777 (Sumner County, Tennessee) Legal description: BAYWOOD PARK LOT 25 Map and Parcel: 35-30-15-05699-000-0250',
            // (a)(3) the amount owed and (a)(4) the last date of furnishing.
            'The amount presently due and owing is $4,213.75.',
            'The last date labor, materials, services, equipment, or machinery were provided in connection with the improvements was July 10, 2026.',
            // (a)(1) the address for communications.
            'You may send any communications regarding this matter to the following name and address: S G Roser Construction LLC Steven Roser 4200 Lakeland Hwy Lakeland, FL 33801 Telephone: 863-555-0100',
            'CLAIMANT: S G Roser Construction LLC',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        expect($text)
            ->not->toContain('THIS IS NOT A LIEN')
            ->not->toContain('My commission expires')
            ->not->toContain('N/A');

        $positions = array_map(fn (string $needle) => strpos($text, $needle), [
            'Pursuant to Tennessee Code Annotated',
            'The amount presently due and owing',
            'You may send any communications',
            'By (signature)',
        ]);
        $sorted = $positions;
        sort($sorted);
        expect($positions)->not->toContain(false)->toBe($sorted);
    });
});

describe('Tennessee release and notice of intent', function () {
    it('releases the recorded notice of lien by book and page before a notary', function () {
        $release = lienFixtureFiling(lienFixtureProject('TN', 'Sumner'), 'lien_release', [
            'document_details_json' => ['original_lien' => ['recording_reference' => 'Book 5432 Page 610', 'recorded_at' => '2026-07-20', 'county' => 'Sumner']],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($release));

        expect($text)
            ->toContain('RELEASE OF LIEN')
            ->toContain('Tenn. Code Ann. § 66-11-135')
            ->toContain('Releases Notice of Lien recorded July 20, 2026 as Book 5432 Page 610')
            ->toContain('KNOW ALL PERSONS BY THESE PRESENTS that S G Roser Construction LLC ("Claimant"), whose address is 4200 Lakeland Hwy, Lakeland, FL 33801, is the claimant under that certain Notice of Lien recorded on July 20, 2026 as Book 5432 Page 610 in the official records of Sumner County, Tennessee')
            ->toContain('hereby releases, discharges and cancels the lien and the Notice of Lien of record, and authorizes and directs the Sumner County Register of Deeds to cancel it of record.')
            ->toContain('before me, the undersigned notary public, personally appeared Steven Roser, President of S G Roser Construction LLC')
            ->toContain('and acknowledged that he or she executed it in that capacity on behalf of S G Roser Construction LLC for the purposes stated in it.')
            ->toContain('CERTIFICATE OF AUTHENTICITY')
            ->not->toContain('Subscribed and sworn to (or affirmed) before me')
            ->not->toContain('Amount claimed');
    });

    it('sends the generic ten-day demand for a notice of lien by certified mail', function () {
        $filing = lienFixtureFiling(lienFixtureProject('TN', 'Sumner'), 'noi');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('NOTICE OF INTENT TO RECORD A LIEN')
            ->toContain('Tenn. Code Ann. § 66-11-101 et seq.')
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('within 10 days after the date of this notice, Claimant intends to record a Notice of Lien against the property')
            ->not->toContain('Via any');
    });
});

describe('Tennessee resolver and rules', function () {
    it('merges the Sumner County register over the state office and leaves Davidson County on the state rules', function () {
        $resolver = app(LienDocumentResolver::class);

        $sumner = $resolver->resolve(lienFixtureFiling(lienFixtureProject('TN', 'Sumner'), 'mechanics_lien'));

        expect($sumner->countyKey)->toBe('sumner');
        expect($sumner->recording['filing_office'])->toMatchArray([
            'label' => 'Sumner County Register of Deeds',
            'method' => 'mail',
            'address_lines' => ['355 N. Belvedere Dr., Suite 201', 'Gallatin, TN 37066'],
        ]);
        expect($sumner->recording['fee_note'])->toBe('$12 for up to two pages plus $5 for each additional page (2026).');
        expect($sumner->recording['parcel_label'])->toBe('Map and Parcel');
        expect($sumner->notes)->toContain('An instrument notarized online goes in with the certificate of authenticity page; the register recorded the June 2026 notice of lien that way.');

        $davidson = $resolver->resolve(lienFixtureFiling(lienFixtureProject('TN', 'Davidson'), 'mechanics_lien'));

        expect($davidson->countyKey)->toBe('davidson');
        expect($davidson->recording['filing_office']['label'])->toBe('Register of Deeds');
        expect($davidson->recording['filing_office']['method'])->toBe('mail');
        expect($davidson->recording['filing_office']['address_lines'])->toBe([]);
        expect($davidson->recording['fee_note'])->toBe('$10 for up to two pages plus $5 for each additional page, and $2 for each instrument (Tenn. Code Ann. § 8-21-1001).');
        expect($davidson->notes)->not->toContain('An instrument notarized online goes in with the certificate of authenticity page; the register recorded the June 2026 notice of lien that way.');
    });

    it('swears the notice with a jurat, acknowledges the release and serves every kind by certified mail', function () {
        $tn = LienDocumentRegistry::for('TN');

        expect($tn['kinds']['mechanics_lien']['execution'])->toMatchArray(['verification' => 'sworn', 'notary' => true, 'notary_form' => 'jurat', 'statement' => false]);
        expect($tn['kinds']['mechanics_lien']['clauses']['after_execution'])->toBe(['documents.lien.instruments.clauses.tn-certificate-of-authenticity']);
        expect($tn['kinds']['lien_release']['execution'])->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment']);
        expect($tn['kinds']['prelim_notice']['service']['recipients'])->toBe(['owner', 'gc']);

        foreach ($tn['kinds'] as $kind => $entry) {
            expect($entry['service']['method'])->toBe('certified_mail', "{$kind} is served by certified mail");
        }

        $rules = collect(LienDocumentPackage::forFiling(lienFixtureFiling(lienFixtureProject('TN', 'Sumner'), 'mechanics_lien'))->rules())->pluck('value', 'label');

        expect($rules['File with'])->toBe('Sumner County Register of Deeds, by mail (355 N. Belvedere Dr., Suite 201, Gallatin, TN 37066)');
        expect($rules['Signing'])->toBe('Sworn to and signed before a notary (jurat)');
        expect($rules['Serve'])->toBe('The owner by certified mail, return receipt requested at recording.');
    });
});
