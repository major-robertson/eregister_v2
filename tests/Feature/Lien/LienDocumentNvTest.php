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

describe('Nevada notice of lien', function () {
    it('renders the NRS 108.226(5) form with the fixture values, its own verification and the jurat', function () {
        $filing = lienFixtureFiling(lienFixtureProject('NV', 'Clark'), 'mechanics_lien');

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        foreach ([
            "Assessor's Parcel Numbers: 35-30-15-05699-000-0250",
            'NOTICE OF LIEN',
            'NRS 108.226',
            'STATE OF NEVADA',
            'COUNTY OF CLARK',
            "Assessor's Parcel Number 35-30-15-05699-000-0250",
            'The undersigned claims a lien upon the property described in this notice for work, materials or equipment furnished or to be furnished for the improvement of the property:',
            '1. The amount of the original contract is: $4,213.75',
            '2. The total amount of all additional or changed work, materials and equipment, if any, is: $0.00',
            '3. The total amount of all payments received to date is: $0.00',
            '4. The amount of the lien, after deducting all just credits and offsets, is: $4,213.75',
            '5. The name of the owner, if known, of the property is: Mike Stuntz',
            '6. The name of the person by whom the lien claimant was employed or to whom the lien claimant furnished or agreed to furnish work, materials or equipment is: Ken Walker Builders',
            "7. A brief statement of the terms of payment of the lien claimant's contract is:",
            '8. A description of the property to be charged with the lien is: 9025 Baywood Park Dr, Seminole, NV 33777 (Clark County, Nevada) Legal description: BAYWOOD PARK LOT 25',
            'Lien claimant: S G Roser Construction LLC, whose mailing address is 4200 Lakeland Hwy, Lakeland, FL 33801.',
            'Steven Roser, being first duly sworn on oath according to law, deposes and says: I have read the foregoing Notice of Lien, know the contents thereof and state that the same is true of my own personal knowledge, except those matters stated upon information and belief, and, as to those matters, I believe them to be true.',
            'CLAIMANT: S G Roser Construction LLC',
            'Subscribed and sworn to (or affirmed) before me on this',
            'by Steven Roser, President of S G Roser Construction LLC, who is personally known to me',
            'My commission expires',
            'Prepared by, recording requested by and return to: eRegister',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // The form's verification replaces the generic sworn paragraph; the form
        // carries no penalty-of-perjury declaration and NRS 239B.030 needs no
        // affirmation unless a recorder asks for one.
        expect($text)
            ->not->toContain('being first duly sworn, states that he or she is')
            ->not->toContain('penalty of perjury')
            ->not->toContain('Social Security')
            ->not->toContain('N/A');

        // The parcel number line sits under the recorder's space and above the
        // title, as the first line of the form; then the items, the claimant,
        // the verification, the signature and the jurat, in that order.
        $needles = ['Space above this line', "Assessor's Parcel Numbers:", 'NOTICE OF LIEN', '1. The amount of the original contract', '8. A description of the property', 'Lien claimant:', 'being first duly sworn on oath', 'CLAIMANT:', 'Subscribed and sworn to'];
        $positions = array_map(fn (string $needle) => strpos($text, $needle), $needles);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false);
        expect($positions)->toBe($sorted);

        // NRS 247.110(3): a 3-inch first-page space (2in below the 1in margin), 1in
        // margins, and no page number printed in the bottom margin.
        expect($pdf->getHtml())
            ->toContain('@page { margin: 1in; }')
            ->toContain('height: 2in')
            ->not->toContain('class="page-number"');
        expect($text)->not->toContain('Notice of Lien - Page');
    });

    it('states the changed work and the payments from the project', function () {
        $project = lienFixtureProject('NV', 'Clark', ['change_orders_cents' => 50000, 'payments_received_cents' => 100000]);
        $filing = lienFixtureFiling($project, 'mechanics_lien', ['amount_claimed_cents' => 371375]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('1. The amount of the original contract is: $4,213.75')
            ->toContain('2. The total amount of all additional or changed work, materials and equipment, if any, is: $500.00')
            ->toContain('3. The total amount of all payments received to date is: $1,000.00')
            ->toContain('4. The amount of the lien, after deducting all just credits and offsets, is: $3,713.75');

        // The figures reconcile, so the package raises no amount warning.
        $amountWarnings = array_filter(LienDocumentPackage::forFiling($filing)->warnings, fn (string $warning) => str_starts_with($warning, 'The amount claimed'));

        expect($amountWarnings)->toBe([]);
    });

    it('warns before recording when the lien amount does not reconcile with the project', function () {
        $filing = lienFixtureFiling(lienFixtureProject('NV', 'Clark'), 'mechanics_lien', ['amount_claimed_cents' => 500000]);

        expect(LienDocumentPackage::forFiling($filing)->warnings)
            ->toContain('The amount claimed ($5,000.00) does not equal contract + change orders − credits − payments − uncompleted work ($4,213.75).');
    });

    it('prints ruled blanks for missing figures and a missing signer', function () {
        $project = lienFixtureProject('NV', 'Clark', [
            'base_contract_amount_cents' => null,
            'change_orders_cents' => null,
            'payments_received_cents' => null,
            'apn' => null,
        ], ['responsible_people' => null]);
        $filing = lienFixtureFiling($project, 'mechanics_lien', ['amount_claimed_cents' => null]);

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        expect($pdf->getHtml())
            ->toContain('The amount of the original contract is: $<span class="fill fill-mid">&nbsp;</span>')
            ->toContain('The amount of the lien, after deducting all just credits and offsets, is: $<span class="fill fill-mid">&nbsp;</span>')
            ->toContain('<strong>Assessor\'s Parcel Numbers:</strong> <span class="fill fill-wide">&nbsp;</span>');
        expect($text)
            ->toContain('(print name), being first duly sworn on oath according to law, deposes and says:')
            ->not->toContain('N/A');
    });

    it('resolves the Clark County recorder, serves the owner within 30 days and keeps the state office elsewhere', function () {
        $clark = lienFixtureFiling(lienFixtureProject('NV', 'Clark'), 'mechanics_lien');
        $washoe = lienFixtureFiling(lienFixtureProject('NV', 'Washoe'), 'mechanics_lien');

        $form = app(LienDocumentResolver::class)->resolve($clark);

        expect($form->title)->toBe('Notice of Lien');
        expect($form->body)->toBe('documents.lien.instruments.bodies.nv-notice-of-lien');
        expect($form->execution)->toMatchArray(['verification' => 'sworn', 'notary' => true, 'notary_form' => 'jurat', 'statement' => false]);
        expect($form->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => 30, 'method' => 'certified_mail']);
        expect($form->recording['filing_office']['label'])->toBe('Clark County Recorder');
        expect($form->recording['filing_office']['address_lines'])->toBe(['500 S. Grand Central Pkwy, 2nd Floor', 'Box 551510', 'Las Vegas, NV 89155-1510']);
        expect($form->recording['parcel_label'])->toBe('Assessor\'s Parcel Number');
        expect($form->notes)->toContain('Clark County wants the 11-digit parcel number, as the Assessor numbers it, at the top left corner of page 1.');

        $rules = collect(LienDocumentPackage::forFiling($clark)->rules())->pluck('value', 'label');

        expect($rules['File with'])->toBe('Clark County Recorder, e-recording (500 S. Grand Central Pkwy, 2nd Floor, Box 551510, Las Vegas, NV 89155-1510)');
        expect($rules['Fee'])->toContain('$42 per document');
        expect($rules['Signing'])->toBe('Sworn to and signed before a notary (jurat)');
        expect($rules['Serve'])->toBe('The owner by certified mail, return receipt requested within 30 days after recording.');

        $other = app(LienDocumentResolver::class)->resolve($washoe);

        expect($other->countyKey)->toBe('washoe');
        expect($other->recording['filing_office']['label'])->toBe('County Recorder');
        expect($other->recording['filing_office']['address_lines'])->toBe([]);
        expect($other->recording['fee_note'])->toBe('$32 to $43 per document, whatever its length: $25, a $7 add-on and up to $11 in local add-ons (NRS 247.305).');
        expect($other->notes)->not->toContain('Clark County wants the 11-digit parcel number, as the Assessor numbers it, at the top left corner of page 1.');
        expect(collect(LienDocumentPackage::forFiling($washoe)->rules())->pluck('value', 'label')['File with'])->toBe('County Recorder, e-recording');
    });
});

describe('Nevada discharge or release of notice of lien', function () {
    it('renders the NRS 108.2437 form from Document details with the acknowledgment', function () {
        $filing = lienFixtureFiling(lienFixtureProject('NV', 'Clark'), 'lien_release', [
            'document_details_json' => ['original_lien' => ['recording_reference' => '0001234', 'book' => '20260625', 'recorded_at' => '2026-06-25', 'county' => 'Clark']],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            "Assessor's Parcel Numbers: 35-30-15-05699-000-0250",
            'DISCHARGE OR RELEASE OF NOTICE OF LIEN',
            'NRS 108.2437',
            'STATE OF NEVADA',
            'COUNTY OF CLARK',
            'Releases Notice of Lien recorded June 25, 2026 as 0001234',
            'NOTICE IS HEREBY GIVEN THAT:',
            'The undersigned did, on the 25th day of the month of June of the year 2026, record in Book 20260625, as Document No. 0001234, in the office of the county recorder of Clark County, Nevada, its Notice of Lien, or has otherwise given notice of his or her intention to hold a lien upon the following described property or improvements, owned or purportedly owned by Mike Stuntz, located in the County of Clark, State of Nevada, to wit:',
            '9025 Baywood Park Dr, Seminole, NV 33777 (Clark County, Nevada) Legal description: BAYWOOD PARK LOT 25',
            'NOW, THEREFORE, for valuable consideration the undersigned does release, satisfy and discharge this notice of lien on the property or improvements described above by reason of this Notice of Lien.',
            'CLAIMANT: S G Roser Construction LLC',
            'before me, the undersigned notary public, personally appeared Steven Roser, President of S G Roser Construction LLC',
            'and acknowledged that he or she executed it in that capacity on behalf of S G Roser Construction LLC',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        expect($text)
            ->not->toContain('KNOW ALL PERSONS BY THESE PRESENTS')
            ->not->toContain('Subscribed and sworn to')
            ->not->toContain('Discharge or Release of Notice of Lien - Page')
            ->not->toContain('N/A');

        expect(strpos($text, "Assessor's Parcel Numbers:"))->toBeLessThan(strpos($text, 'DISCHARGE OR RELEASE OF NOTICE OF LIEN'));
        expect(strpos($text, 'NOW, THEREFORE'))->toBeLessThan(strpos($text, 'CLAIMANT:'));

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['Signing'])->toBe('Signed and acknowledged before a notary');
        expect($rules['Serve'])->toBe('The owner by certified mail, return receipt requested.');
    });

    it('leaves out the book when the recorder numbers documents without one', function () {
        $filing = lienFixtureFiling(lienFixtureProject('NV', 'Washoe'), 'lien_release', [
            'document_details_json' => ['original_lien' => ['recording_reference' => '5512345', 'recorded_at' => '2026-06-25']],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('record as Document No. 5512345, in the office of the county recorder of Washoe County, Nevada, its Notice of Lien')
            ->toContain('located in the County of Washoe, State of Nevada, to wit:')
            ->not->toContain('in Book');
    });
});

describe('Nevada notice of right to lien', function () {
    it('renders the NRS 108.245 form to the owner with the prime contractor copy', function () {
        $filing = lienFixtureFiling(lienFixtureProject('NV', 'Clark'), 'prelim_notice');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            'Via certified mail, return receipt requested',
            'To: Owner or reputed owner Mike Stuntz',
            'To: Original (direct) contractor Ken Walker Builders',
            'NOTICE OF RIGHT TO LIEN',
            'NRS 108.245',
            "The undersigned notifies you that he or she has supplied materials or equipment or performed work or services as follows: Removal of drywall, replacement of drywall, and damage repair throughout the home for improvement of property identified as 9025 Baywood Park Dr, Seminole, NV 33777; BAYWOOD PARK LOT 25; Assessor's Parcel Number 35-30-15-05699-000-0250 under contract with Ken Walker Builders.",
            'This is not a notice that the undersigned has not been or does not expect to be paid, but a notice required by law that the undersigned may, at a future date, record a notice of lien as provided by law against the property if the undersigned is not paid.',
            'CLAIMANT: S G Roser Construction LLC',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // NRS 108.245(4): the notice need not be verified, sworn to or acknowledged.
        expect($text)
            ->not->toContain('My commission expires')
            ->not->toContain('THIS IS NOT A LIEN.')
            ->not->toContain('N/A');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->notaryRequired())->toBeFalse();
        expect($form->service)->toMatchArray(['recipients' => ['owner', 'gc'], 'days_after' => null, 'method' => 'certified_mail']);
    });
});

describe('Nevada notice of intent to lien', function () {
    it('renders the NRS 108.226(6) fifteen-day notice with the notice of lien information', function () {
        $filing = lienFixtureFiling(lienFixtureProject('NV', 'Clark'), 'noi');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            'To: Owner or reputed owner Mike Stuntz',
            'To: Original (direct) contractor Ken Walker Builders',
            'NOTICE OF INTENT TO LIEN',
            'NRS 108.226(6)',
            'under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid',
            'Legal description: BAYWOOD PARK LOT 25',
            'Amount claimed, after deducting all just credits and offsets $ 4,213.75',
            'The name of the owner, if known, of the property is: Mike Stuntz',
            "A brief statement of the terms of payment of the lien claimant's contract is:",
            'This is a 15-day notice of intent to lien under NRS 108.226(6). Unless the amount stated above is paid in full, Claimant intends to record a notice of lien against the property described above with the county recorder of the county where the property is located no sooner than 15 days after this notice is served.',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        expect($text)->not->toContain('within 10 days after the date of this notice');
        expect(strpos($text, 'The name of the owner, if known'))->toBeLessThan(strpos($text, 'This is a 15-day notice'));

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->section('demand_days'))->toBe(15);
        expect($form->service)->toMatchArray(['recipients' => ['owner', 'gc'], 'days_after' => null, 'method' => 'certified_mail']);
    });
});
