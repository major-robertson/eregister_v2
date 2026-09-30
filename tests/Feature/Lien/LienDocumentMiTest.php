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

describe('Michigan claim of lien', function () {
    it('renders the MCL 570.1111(2) form with the fixture values and the shared jurat', function () {
        $filing = lienFixtureFiling(lienFixtureProject('MI', 'Saginaw'), 'mechanics_lien');

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        foreach ([
            'CLAIM OF LIEN',
            'MCL 570.1111',
            'STATE OF MICHIGAN',
            'COUNTY OF SAGINAW',
            'Parcel Number 35-30-15-05699-000-0250',
            'Notice is hereby given that on the 30th day of June, 2026, S G Roser Construction LLC, 4200 Lakeland Hwy, Lakeland, FL 33801, first provided labor or material for an improvement to:',
            '9025 Baywood Park Dr, Seminole, MI 33777 (Saginaw County, Michigan) Legal description: BAYWOOD PARK LOT 25 Parcel Number: 35-30-15-05699-000-0250 the owner of which property is Mike Stuntz.',
            'The last day of providing the labor or material was the 10th day of July, 2026.',
            'TO BE COMPLETED BY A LIEN CLAIMANT WHO IS A CONTRACTOR, SUBCONTRACTOR, OR SUPPLIER:',
            "The lien claimant's contract amount, including extras, is $4,213.75. The lien claimant has received payment thereon in the total amount of $0.00, and therefor claims a construction lien upon the above-described real property in the amount of $4,213.75.",
            'Address of party signing claim of lien: 4200 Lakeland Hwy, Lakeland, FL 33801 CLAIMANT: S G Roser Construction LLC',
            'Subscribed and sworn to (or affirmed) before me',
            'by Steven Roser, President of S G Roser Construction LLC, who is personally known to me',
            'My commission expires',
            "Notary's printed or typed name",
            'Prepared by, recording requested by and return to: eRegister',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // The form has no sworn paragraph of its own, the claimant is not a laborer,
        // and Michigan instruments carry no page number (MCL 565.201(1)(f)(i) margins).
        expect($text)
            ->not->toContain('being first duly sworn')
            ->not->toContain('TO BE COMPLETED BY A LIEN CLAIMANT WHO IS A LABORER')
            ->not->toContain('Claim of Lien - Page')
            ->not->toContain('N/A');

        // The form's order, and nothing printed in the page-1 recorder space: the
        // preparer block follows the rule (MCL 565.201(1)(f)(i)).
        $needles = ['Space above this line', 'Prepared by, recording requested by', 'CLAIM OF LIEN', 'Notice is hereby given', 'The last day of providing', 'TO BE COMPLETED BY', 'Address of party signing', 'CLAIMANT:', 'Subscribed and sworn to'];
        $positions = array_map(fn (string $needle) => strpos($text, $needle), $needles);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false);
        expect($positions)->toBe($sorted);

        expect($pdf->getHtml())
            ->toContain('height: 2in')
            ->toContain('class="preparer-below"')
            ->not->toContain('class="page-number"');
    });

    it('states the contract amount with its extras and the payments received', function () {
        $project = lienFixtureProject('MI', 'Kent', ['change_orders_cents' => 50000, 'payments_received_cents' => 100000]);
        $filing = lienFixtureFiling($project, 'mechanics_lien', ['amount_claimed_cents' => 371375]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('COUNTY OF KENT')
            ->toContain("The lien claimant's contract amount, including extras, is $4,713.75. The lien claimant has received payment thereon in the total amount of $1,000.00, and therefor claims a construction lien upon the above-described real property in the amount of $3,713.75.");
    });

    it('prints ruled blanks for missing dates and amounts', function () {
        $project = lienFixtureProject('MI', 'Saginaw', [
            'first_furnish_date' => null,
            'last_furnish_date' => null,
            'base_contract_amount_cents' => null,
            'payments_received_cents' => null,
        ]);
        $filing = lienFixtureFiling($project, 'mechanics_lien', ['amount_claimed_cents' => null]);

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        expect($text)
            ->toContain('Notice is hereby given that on the day of , , S G Roser Construction LLC')
            ->toContain('The last day of providing the labor or material was the day of , .')
            ->toContain("The lien claimant's contract amount, including extras, is $ . The lien claimant has received payment thereon in the total amount of $ , and therefor claims a construction lien upon the above-described real property in the amount of $ .")
            ->not->toContain('N/A');
        expect($pdf->getHtml())->toContain('class="fill fill-short"');
    });

    it('resolves the register of deeds, the 15-day service with a sworn proof and the notice-of-furnishing attachment', function () {
        $filing = lienFixtureFiling(lienFixtureProject('MI', 'Saginaw'), 'mechanics_lien');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->title)->toBe('Claim of Lien');
        expect($form->statute)->toBe('MCL 570.1111');
        expect($form->body)->toBe('documents.lien.instruments.bodies.mi-claim-of-lien');
        expect($form->countyKey)->toBe('saginaw');
        expect($form->sections)->toMatchArray(['amount' => 'single', 'gc' => false, 'prior_notice' => false]);
        expect($form->execution)->toMatchArray(['verification' => 'sworn', 'notary' => true, 'notary_form' => 'jurat', 'statement' => false]);
        expect($form->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => 15, 'method' => 'certified_mail', 'proof' => 'affidavit']);
        expect($form->recording['filing_office'])->toMatchArray(['label' => 'Register of Deeds', 'method' => 'erecord']);
        expect($form->recording)->toMatchArray(['parcel_label' => 'Parcel Number', 'preparer_in_space' => false, 'page_numbers' => false]);
        expect($form->recorderSpaceInches())->toBe(2.0);
        expect($form->attachments)->toBe(['Proof of service of the notice of furnishing, for a subcontractor, supplier or laborer (MCL 570.1111(4)).']);

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['File with'])->toBe('Register of Deeds, e-recording');
        expect($rules['Fee'])->toContain('$30 per document');
        expect($rules['Signing'])->toBe('Sworn to and signed before a notary (jurat)');
        expect($rules['Serve'])->toBe('The owner by certified mail, return receipt requested within 15 days after recording.');
        expect($rules['Attach'])->toBe('Proof of service of the notice of furnishing, for a subcontractor, supplier or laborer (MCL 570.1111(4)).');
    });
});

describe('Michigan notice of furnishing', function () {
    it('renders the MCL 570.1109(4) form with the warning to owner and no notary', function () {
        $filing = lienFixtureFiling(lienFixtureProject('MI', 'Saginaw'), 'prelim_notice');

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        foreach ([
            'Via certified mail, return receipt requested',
            'To: Owner or reputed owner Mike Stuntz',
            'To: Original (direct) contractor Ken Walker Builders',
            'NOTICE OF FURNISHING',
            'MCL 570.1109',
            'To: Mike Stuntz 9025 Baywood Park Dr Seminole, MI 33777 Please take notice that the undersigned is furnishing to Ken Walker Builders Ken Walker 13700 58th St N Ste 204 Clearwater, FL 33760 certain labor or material for Removal of drywall, replacement of drywall, and damage repair throughout the home,',
            'in connection with the improvements to the real property described in the notice of commencement recorded in liber , on page , Saginaw County records,',
            'Legal description: BAYWOOD PARK LOT 25 Parcel Number: 35-30-15-05699-000-0250 or (a copy of which is attached to this notice)',
            'WARNING TO OWNER: THIS NOTICE IS REQUIRED BY THE MICHIGAN CONSTRUCTION LIEN ACT. IF YOU HAVE QUESTIONS ABOUT YOUR RIGHTS AND DUTIES UNDER THIS ACT, YOU SHOULD CONTACT AN ATTORNEY TO PROTECT YOU FROM THE POSSIBILITY OF PAYING TWICE FOR THE IMPROVEMENTS TO YOUR PROPERTY.',
            'S G Roser Construction LLC, 4200 Lakeland Hwy, Lakeland, FL 33801 (name and address of lien claimant)',
            'by Steven Roser, President (name and capacity of party signing for lien claimant)',
            '4200 Lakeland Hwy, Lakeland, FL 33801 (address of party signing)',
            'CLAIMANT: S G Roser Construction LLC',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        expect($text)
            ->not->toContain('My commission expires')
            ->not->toContain('Subscribed and sworn')
            ->not->toContain('N/A');
        expect($pdf->getHtml())->toContain('<div class="notice-box">WARNING TO OWNER:');

        $positions = array_map(fn (string $needle) => strpos($text, $needle), ['NOTICE OF FURNISHING', 'Please take notice', 'or (a copy of which', 'WARNING TO OWNER', '(address of party signing)', 'By (signature)']);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false);
        expect($positions)->toBe($sorted);
    });

    it('serves the designee or owner and the general contractor with a sworn proof, and attaches the notice of commencement', function () {
        $filing = lienFixtureFiling(lienFixtureProject('MI', 'Saginaw'), 'prelim_notice');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->title)->toBe('Notice of Furnishing');
        expect($form->statute)->toBe('MCL 570.1109');
        expect($form->body)->toBe('documents.lien.letters.bodies.mi-notice-of-furnishing');
        expect($form->notaryRequired())->toBeFalse();
        expect($form->service)->toMatchArray(['recipients' => ['owner', 'gc'], 'days_after' => null, 'method' => 'certified_mail', 'proof' => 'affidavit']);

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['Signing'])->toBe('Signed by the claimant; no notary');
        expect($rules['Serve'])->toBe('The owner and the general contractor by certified mail, return receipt requested.');
        expect($rules['Attach'])->toBe('A copy of the notice of commencement, unless the liber and page where it was recorded are filled in (MCL 570.1109(4)).');
    });
});

describe('Michigan discharge and notice of intent', function () {
    it('renders the MCL 570.1127 certificate of discharge with an acknowledgment', function () {
        $filing = lienFixtureFiling(lienFixtureProject('MI', 'Saginaw'), 'lien_release', [
            'document_details_json' => ['original_lien' => ['recording_reference' => 'Liber 2641 Page 118', 'recorded_at' => '2026-07-20', 'county' => 'Saginaw']],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('CERTIFICATE OF DISCHARGE OF LIEN')
            ->toContain('MCL 570.1127')
            ->toContain('STATE OF MICHIGAN')
            ->toContain('COUNTY OF SAGINAW')
            ->toContain('Releases Claim of Lien recorded July 20, 2026 as Liber 2641 Page 118')
            ->toContain('KNOW ALL PERSONS BY THESE PRESENTS that S G Roser Construction LLC ("Claimant"), whose address is 4200 Lakeland Hwy, Lakeland, FL 33801, is the claimant under that certain Claim of Lien recorded on July 20, 2026 as Liber 2641 Page 118 in the official records of Saginaw County, Michigan')
            ->toContain('releases, discharges and cancels the lien and the Claim of Lien of record, and authorizes and directs the Register of Deeds to cancel it of record.')
            ->toContain('The undersigned lien claimant certifies that the claim of lien described above has been fully paid and is now discharged.')
            ->toContain('before me, the undersigned notary public, personally appeared Steven Roser, President of S G Roser Construction LLC, personally known to me')
            ->toContain('acknowledged that he or she executed it in that capacity on behalf of S G Roser Construction LLC')
            ->not->toContain('Subscribed and sworn')
            ->not->toContain('Amount claimed')
            ->not->toContain('Witness (signature)');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->execution)->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment', 'witness' => false]);
        expect($form->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail', 'proof' => 'declaration']);

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['Signing'])->toBe('Signed and acknowledged before a notary');
        expect($rules['Serve'])->toBe('The owner by certified mail, return receipt requested.');
    });

    it('sends the courtesy notice of intent to record a claim of lien', function () {
        $filing = lienFixtureFiling(lienFixtureProject('MI', 'Saginaw'), 'noi');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('NOTICE OF INTENT TO RECORD A CLAIM OF LIEN')
            ->toContain('MCL 570.1101 et seq.')
            ->toContain('To: Owner or reputed owner Mike Stuntz')
            ->toContain('To: Original (direct) contractor Ken Walker Builders')
            ->toContain('You are hereby notified that S G Roser Construction LLC ("Claimant") furnished labor, services, equipment or materials for the improvement of the property described below under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid.')
            ->toContain('within 10 days after the date of this notice, Claimant intends to record a Claim of Lien against the property')
            ->not->toContain('My commission expires');

        expect(app(LienDocumentResolver::class)->resolve($filing)->service)
            ->toMatchArray(['recipients' => ['owner', 'gc'], 'days_after' => null, 'method' => 'certified_mail']);
    });
});

describe('Michigan staff notes', function () {
    it('keeps the notes plain: statute cites, no names, open items as Undecided, attachments as sentences', function () {
        $mi = LienDocumentRegistry::for('MI');

        $notes = $mi['recording']['notes'];
        $attachments = [];

        foreach ($mi['kinds'] as $entry) {
            $notes = array_merge($notes, $entry['notes']);
            $attachments = array_merge($attachments, $entry['attachments']);
        }

        expect($notes)->not->toBeEmpty();

        foreach ($notes as $note) {
            expect($note)->toBeString()->not->toContain('Major')->not->toContain('N/A');

            if (str_starts_with($note, 'Undecided:')) {
                expect($note)->toContain('until counsel says otherwise');
            }
        }

        foreach ($attachments as $attachment) {
            expect($attachment)->toEndWith('.');
        }

        expect(implode(' ', $mi['kinds']['mechanics_lien']['notes']))
            ->toContain('within 90 days after the claimant\'s last furnishing')
            ->toContain('MCL 570.1111(5)')
            ->toContain('MCL 570.1117(1)');
        expect(implode(' ', $mi['kinds']['prelim_notice']['notes']))
            ->toContain('within 20 days after first furnishing')
            ->toContain('MCL 570.1109(5)-(6)');
    });
});
