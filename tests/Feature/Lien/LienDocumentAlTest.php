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

describe('Alabama verified statement of lien', function () {
    it('renders the § 35-11-213 form with the fixture values, the claimant\'s signature, then the form\'s affidavit and jurat', function () {
        $filing = lienFixtureFiling(lienFixtureProject('AL', 'Baldwin'), 'mechanics_lien');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            'VERIFIED STATEMENT OF LIEN',
            'Ala. Code § 35-11-213',
            'STATE OF ALABAMA',
            'COUNTY OF BALDWIN',
            'Claimant / Lienor S G Roser Construction LLC Owner Mike Stuntz Amount claimed $4,213.75 Parcel Number 35-30-15-05699-000-0250',
            'S G Roser Construction LLC files this statement in writing, verified by the oath of Steven Roser, its President, who has personal knowledge of the facts herein set forth:',
            'That said S G Roser Construction LLC claims a lien upon the following property, situated in Baldwin county, Alabama, to wit: 9025 Baywood Park Dr, Seminole, AL 33777 (Baldwin County, Alabama) Legal description: BAYWOOD PARK LOT 25 Parcel Number: 35-30-15-05699-000-0250',
            'This lien is claimed, separately and severally, as to both the buildings and improvements thereon, and the said land.',
            'That said lien is claimed to secure an indebtedness of $4,213.75 (Four Thousand Two Hundred Thirteen Dollars and Seventy-Five Cents) with interest, from to wit the 10th day of July, 2026, for Removal of drywall, replacement of drywall, and damage repair throughout the home.',
            'The name of the owner or proprietor of the said property is Mike Stuntz.',
            'CLAIMANT: S G Roser Construction LLC By (signature) Date Steven Roser Printed name President Title',
            'Before me, , a notary public in and for the county of , State of , personally appeared Steven Roser, who being duly sworn, doth depose and say: That he has personal knowledge of the facts set forth in the foregoing statement of lien, and that the same are true and correct to the best of his knowledge and belief.',
            'Affiant Steven Roser Printed name',
            'Subscribed and sworn to before me on this the day of , 20 , by said affiant.',
            'Notary Public My commission expires',
            'Prepared by, recording requested by and return to: eRegister',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // The form's own affidavit and jurat are the oath: no shared sworn
        // statement, no shared certificate, and no prior-notice line.
        expect($text)
            ->not->toContain('being first duly sworn')
            ->not->toContain('Subscribed and sworn to (or affirmed)')
            ->not->toContain('Prior notice.')
            ->not->toContain('N/A');
        expect(substr_count($text, 'My commission expires'))->toBe(1);

        // The form's order: the statement, the claimant's signature, then the affidavit and the jurat.
        $needles = ['files this statement in writing', 'That said S G Roser', 'This lien is claimed, separately and severally', 'That said lien is claimed to secure', 'The name of the owner or proprietor', 'By (signature)', 'doth depose and say', 'Affiant', 'Subscribed and sworn to before me', 'My commission expires'];
        $positions = array_map(fn (string $needle) => strpos($text, $needle), $needles);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false);
        expect($positions)->toBe($sorted);
    });

    it('prints ruled blanks for a missing signer, interest date, amount and work', function () {
        $project = lienFixtureProject('AL', 'Baldwin County', [
            'last_furnish_date' => null,
            'base_contract_amount_cents' => null,
        ], ['responsible_people' => null]);
        $filing = lienFixtureFiling($project, 'mechanics_lien', ['amount_claimed_cents' => null, 'description_of_work' => null]);

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        expect($text)
            ->toContain('COUNTY OF BALDWIN')
            ->toContain('S G Roser Construction LLC files this statement in writing, verified by the oath of , who has personal knowledge of the facts herein set forth:')
            ->toContain('That said lien is claimed to secure an indebtedness of $ with interest, from to wit the day of , , for .')
            ->toContain('personally appeared , who being duly sworn, doth depose and say:')
            ->not->toContain('N/A');
        expect($pdf->getHtml())->toContain('class="fill fill-short"');
    });

    it('files with the judge of probate, keeps the shared notary certificate off and mails the owner a copy', function () {
        $filing = lienFixtureFiling(lienFixtureProject('AL', 'Baldwin'), 'mechanics_lien');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->title)->toBe('Verified Statement of Lien');
        expect($form->statute)->toBe('Ala. Code § 35-11-213');
        expect($form->body)->toBe('documents.lien.instruments.bodies.al-verified-statement-of-lien');
        expect($form->countyKey)->toBe('baldwin');
        expect($form->sections)->toMatchArray(['amount' => 'single', 'amount_in_words' => true, 'gc' => true, 'prior_notice' => false]);
        expect($form->clauses['after_execution'])->toBe(['documents.lien.instruments.clauses.al-affidavit']);
        expect($form->execution)->toMatchArray(['verification' => 'sworn', 'notary' => false, 'notary_form' => null, 'statement' => false]);
        expect($form->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail']);
        expect($form->recording['filing_office'])->toMatchArray(['label' => 'Judge of Probate', 'method' => 'either']);
        expect($form->recording['parcel_label'])->toBe('Parcel Number');
        expect($form->recording['fee_note'])->toBeNull();

        $notes = implode(' ', $form->notes);

        expect($notes)
            ->toContain('six months after the last item of work or material for an original contractor, 30 days for a journeyman or day laborer, and four months for everyone else (Ala. Code § 35-11-215)')
            ->toContain('(Ala. Code § 35-11-218)')
            ->toContain('within six months after the entire debt matures (Ala. Code § 35-11-221)')
            ->toContain('Undecided: whether interest should run from the last furnishing date')
            ->toContain('Undecided: whether the preparer block');

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['File with'])->toBe('Judge of Probate, e-recording or mail');
        expect($rules['Serve'])->toBe('The owner by certified mail, return receipt requested.');
    });
});

describe('Alabama satisfaction of lien', function () {
    it('acknowledges satisfaction of the recorded statement and asks for the margin entry', function () {
        $filing = lienFixtureFiling(lienFixtureProject('AL', 'Baldwin'), 'lien_release', [
            'document_details_json' => ['original_lien' => ['recording_reference' => 'Instrument 2026031245', 'recorded_at' => '2026-07-28', 'county' => 'Baldwin']],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('SATISFACTION OF LIEN')
            ->toContain('Ala. Code § 35-11-231')
            ->toContain('Releases Verified Statement of Lien recorded July 28, 2026 as Instrument 2026031245')
            ->toContain('is the claimant under that certain Verified Statement of Lien recorded on July 28, 2026 as Instrument 2026031245 in the official records of Baldwin County, Alabama, against the real property described below, owned by Mike Stuntz:')
            ->toContain('Claimant acknowledges full satisfaction of the claim secured by that lien, and hereby releases, discharges and cancels the lien and the Verified Statement of Lien of record, and authorizes and directs the Judge of Probate to cancel it of record.')
            ->toContain('Claimant asks the Judge of Probate to note this satisfaction on the margin of the record of the Verified Statement of Lien (Ala. Code § 35-11-231(a)).')
            ->toContain('personally appeared Steven Roser, President of S G Roser Construction LLC, personally known to me or proved to me on the basis of satisfactory evidence')
            ->toContain('acknowledged that he or she executed it in that capacity on behalf of S G Roser Construction LLC')
            ->not->toContain('doth depose and say')
            ->not->toContain('N/A');
        expect(substr_count($text, 'My commission expires'))->toBe(1);

        $release = LienDocumentRegistry::for('AL')['kinds']['lien_release'];

        expect($release['execution'])->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment']);
        expect(LienDocumentPackage::executionLabel($release['execution']))->toBe('Signed and acknowledged before a notary');
        expect($release['service'])->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail']);
        expect(implode(' ', $release['notes']))->toContain('for at least $200 (Ala. Code § 35-11-231)');
    });
});

describe('Alabama notice of claim and intent to file lien', function () {
    it('gives the § 35-11-218 notice to the owner and the general contractor', function () {
        $filing = lienFixtureFiling(lienFixtureProject('AL', 'Baldwin'), 'noi');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('To: Owner or reputed owner Mike Stuntz 9025 Baywood Park Dr Seminole, AL 33777')
            ->toContain('To: Original (direct) contractor Ken Walker Builders')
            ->toContain('Re: Notice of Claim and Intent to File Lien')
            ->toContain('NOTICE OF CLAIM AND INTENT TO FILE LIEN')
            ->toContain('Ala. Code § 35-11-218')
            ->toContain('S G Roser Construction LLC ("Claimant") furnished labor, services, equipment or materials for the improvement of the property described below under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid.')
            ->toContain('Claimant claims a lien on the building or improvement on the property described above for the amount stated above. That amount is owing to Claimant for the labor, services or materials described above, from the person Claimant contracted with.')
            ->toContain('After this notice, any unpaid balance in the hands of the owner or proprietor is held subject to the lien (Ala. Code § 35-11-218).')
            ->toContain('Unless the amount is paid in full within 10 days after the date of this notice, Claimant will file its Verified Statement of Lien in the office of the Judge of Probate of the county where the property is situated.')
            ->not->toContain('Claimant intends to record')
            ->not->toContain('My commission expires');

        $noi = LienDocumentRegistry::for('AL')['kinds']['noi'];

        expect($noi['body'])->toBe('documents.lien.letters.bodies.generic-noi');
        expect($noi['sections']['demand_days'])->toBe(10);
        expect($noi['service'])->toMatchArray(['recipients' => ['owner', 'gc'], 'days_after' => null, 'method' => 'certified_mail', 'proof' => 'declaration']);
    });
});

describe('Alabama notice to owner before furnishing materials', function () {
    it('prints the § 35-11-210 form with the owner, the contractor, the property, the material and its price', function () {
        $project = lienFixtureProject('AL', 'Baldwin', ['claimant_type' => ClaimantType::SupplierToContractor]);
        $filing = lienFixtureFiling($project, 'prelim_notice', ['document_details_json' => ['estimated_price_cents' => 1250000]]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('To: Owner or reputed owner Mike Stuntz')
            ->toContain('NOTICE TO OWNER BEFORE FURNISHING MATERIALS')
            ->toContain('Ala. Code § 35-11-210')
            ->toContain('To Mike Stuntz, owner or proprietor:')
            ->toContain('Take notice, that the undersigned is about to furnish Ken Walker Builders, your contractor or subcontractor, certain material for the construction, or for the repairing, altering, or beautifying of a building or buildings, or improvement or improvements, on the following described property: 9025 Baywood Park Dr, Seminole, AL 33777 (Baldwin County, Alabama) Legal description: BAYWOOD PARK LOT 25 Parcel Number: 35-30-15-05699-000-0250')
            ->toContain('and there will become due to the undersigned on account thereof the price of the material, for the payment of which the undersigned will claim a lien.')
            ->toContain('Material to be furnished Removal of drywall, replacement of drywall, and damage repair throughout the home')
            ->toContain('Price of the material $12,500.00')
            ->toContain('CLAIMANT: S G Roser Construction LLC')
            ->not->toContain('To: Original (direct) contractor')
            ->not->toContain('My commission expires');

        $prelim = LienDocumentRegistry::for('AL')['kinds']['prelim_notice'];

        expect($prelim['body'])->toBe('documents.lien.letters.bodies.al-materialman-notice');
        expect($prelim['service'])->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail']);
    });
});
