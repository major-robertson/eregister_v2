<?php

use App\Domains\Lien\Documents\LienDocumentGenerator;
use App\Domains\Lien\Documents\LienDocumentPackage;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Documents\LienDocumentResolver;
use App\Domains\Lien\Enums\PartyRole;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');
    LienDocumentRegistry::flush();
});

afterEach(fn () => LienDocumentRegistry::flush());

describe('Washington claim of lien', function () {
    it('renders the RCW 60.04.091 form in its order with the fixture values, the sworn statement and the jurat', function () {
        $filing = lienFixtureFiling(lienFixtureProject('WA', 'Pierce'), 'mechanics_lien');

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        foreach ([
            'Prepared by, recording requested by and return to: eRegister',
            'Space above this line for recorder\'s use only',
            'CLAIM OF LIEN',
            'RCW 60.04.091',
            'STATE OF WASHINGTON',
            'COUNTY OF PIERCE',
            'Claimant / Lienor S G Roser Construction LLC Owner Mike Stuntz Amount claimed $4,213.75 Parcel Number 35-30-15-05699-000-0250',
            'Abbreviated legal description: BAYWOOD PARK LOT 25 (complete legal description in item 4)',
            'S G Roser Construction LLC, claimant, vs Ken Walker Builders, name of person indebted to claimant:',
            'Notice is hereby given that the person named below claims a lien pursuant to chapter 60.04 RCW. In support of this lien the following information is submitted:',
            '1. NAME OF LIEN CLAIMANT: S G Roser Construction LLC TELEPHONE NUMBER: 863-555-0100 ADDRESS: 4200 Lakeland Hwy, Lakeland, FL 33801',
            '2. DATE ON WHICH THE CLAIMANT BEGAN TO PERFORM LABOR, PROVIDE PROFESSIONAL SERVICES, SUPPLY MATERIAL OR EQUIPMENT OR THE DATE ON WHICH EMPLOYEE BENEFIT CONTRIBUTIONS BECAME DUE: June 30, 2026',
            '3. NAME OF PERSON INDEBTED TO THE CLAIMANT: Ken Walker Builders',
            '4. DESCRIPTION OF THE PROPERTY AGAINST WHICH A LIEN IS CLAIMED (Street address, legal description or other information that will reasonably describe the property): 9025 Baywood Park Dr, Seminole, WA 33777 (Pierce County, Washington) Legal description: BAYWOOD PARK LOT 25 Parcel Number: 35-30-15-05699-000-0250',
            '5. NAME OF THE OWNER OR REPUTED OWNER (If not known state "unknown"): Mike Stuntz',
            '6. THE LAST DATE ON WHICH LABOR WAS PERFORMED; PROFESSIONAL SERVICES WERE FURNISHED; CONTRIBUTIONS TO AN EMPLOYEE BENEFIT PLAN WERE DUE; OR MATERIAL, OR EQUIPMENT WAS FURNISHED: July 10, 2026',
            '7. PRINCIPAL AMOUNT FOR WHICH THE LIEN IS CLAIMED IS: $4,213.75',
            '8. IF THE CLAIMANT IS THE ASSIGNEE OF THIS CLAIM SO STATE HERE:',
            'Steven Roser, President of S G Roser Construction LLC, being sworn, says: I am the claimant (or attorney of the claimant, or administrator, representative, or agent of the trustees of an employee benefit plan) above named; I have read or heard the foregoing claim, read and know the contents thereof, and believe the same to be true and correct and that the claim of lien is not frivolous and is made with reasonable cause, and is not clearly excessive under penalty of perjury.',
            'CLAIMANT: S G Roser Construction LLC',
            'Subscribed and sworn to (or affirmed) before me on this',
            'by Steven Roser, President of S G Roser Construction LLC, who is personally known to me',
            'My commission expires',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // The statute's order: the form's items, then the sworn statement, the signature and the jurat.
        $needles = [
            'CLAIM OF LIEN', 'STATE OF WASHINGTON', 'Abbreviated legal description', ', claimant, vs', 'Notice is hereby given',
            '1. NAME OF LIEN CLAIMANT', '2. DATE ON WHICH', '3. NAME OF PERSON INDEBTED', '4. DESCRIPTION OF THE PROPERTY',
            '5. NAME OF THE OWNER', '6. THE LAST DATE', '7. PRINCIPAL AMOUNT', '8. IF THE CLAIMANT',
            'being sworn, says', 'By (signature)', 'Subscribed and sworn to (or affirmed)',
        ];
        $positions = array_map(fn (string $needle) => strpos($text, $needle), $needles);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false);
        expect($positions)->toBe($sorted);

        // The form's sworn statement replaces the generic one, and the form states the amount itself.
        expect($text)
            ->not->toContain('being first duly sworn')
            ->not->toContain('true of his or her own knowledge')
            ->not->toContain('chapter 64.04')
            ->not->toContain('Contract amount')
            ->not->toContain('Prior notice')
            ->not->toContain('Original (general) contractor')
            ->not->toContain('N/A')
            ->not->toContain('—');

        // RCW 65.04.045: 1-inch margins and a 3-inch top on page 1 (a 2-inch space inside the margin).
        expect($pdf->getHtml())
            ->toContain('@page { margin: 1in; }')
            ->toContain('height: 2in')
            ->toContain('page-break-after: avoid;');
    });

    it('states "unknown" when the project has no owner party and leaves the signer as ruled blanks', function () {
        $project = lienFixtureProject('WA', 'Pierce', [], ['responsible_people' => null]);
        $project->parties()->where('role', PartyRole::Owner->value)->delete();
        $filing = lienFixtureFiling($project, 'mechanics_lien');

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        expect($text)
            ->toContain('5. NAME OF THE OWNER OR REPUTED OWNER (If not known state "unknown"): unknown')
            ->toContain(', of S G Roser Construction LLC, being sworn, says: I am the claimant')
            ->not->toContain('Mike Stuntz');
        expect($pdf->getHtml())->toContain('class="fill fill-wide"');
    });

    it('files with the Pierce County Auditor in Pierce and the County Auditor elsewhere, and serves the owner within 14 days', function () {
        $pierce = lienFixtureFiling(lienFixtureProject('WA', 'Pierce County'), 'mechanics_lien');
        $king = lienFixtureFiling(lienFixtureProject('WA', 'King'), 'mechanics_lien');
        $resolver = app(LienDocumentResolver::class);

        $pierceDoc = $resolver->resolve($pierce);
        $kingDoc = $resolver->resolve($king);

        expect($pierceDoc->title)->toBe('Claim of Lien');
        expect($pierceDoc->body)->toBe('documents.lien.instruments.bodies.wa-claim-of-lien');
        expect($pierceDoc->countyKey)->toBe('pierce');
        expect($pierceDoc->recording['filing_office'])->toBe([
            'label' => 'Pierce County Auditor',
            'method' => 'erecord',
            'address_lines' => ['2401 S. 35th St., Room 200', 'Tacoma, WA 98409'],
            'vendor' => null,
        ]);
        expect($pierceDoc->recording['fee_note'])->toStartWith('$303.50 for the first page and $1.00 per additional page');
        expect($pierceDoc->notes)->toContain('E-recording goes through the vendors under contract with the Auditor (CSC Erecording Solutions, eRecording Partners Network or Simplifile); the vendor collects the recording fee.');

        expect($kingDoc->countyKey)->toBe('king');
        expect($kingDoc->recording['filing_office'])->toMatchArray(['label' => 'County Auditor', 'method' => 'erecord', 'address_lines' => []]);
        expect($kingDoc->recording['fee_note'])->toBeNull();
        expect($kingDoc->recording['parcel_label'])->toBe('Parcel Number');
        expect($kingDoc->recorderSpaceInches())->toBe(2.0);
        expect($kingDoc->notes)->not->toContain('E-recording goes through the vendors under contract with the Auditor (CSC Erecording Solutions, eRecording Partners Network or Simplifile); the vendor collects the recording fee.');

        foreach ([$pierceDoc, $kingDoc] as $doc) {
            expect($doc->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => 14, 'method' => 'certified_mail']);
            expect($doc->execution)->toMatchArray(['verification' => 'sworn', 'notary' => true, 'notary_form' => 'jurat', 'statement' => false]);
            expect($doc->sections)->toMatchArray(['amount' => 'single', 'gc' => false, 'prior_notice' => false]);
        }

        $rules = collect(LienDocumentPackage::forFiling($pierce)->rules())->pluck('value', 'label');

        expect($rules['File with'])->toBe('Pierce County Auditor, e-recording (2401 S. 35th St., Room 200, Tacoma, WA 98409)');
        expect($rules['Fee'])->toContain('$303.50 for the first page');
        expect($rules['Signing'])->toBe('Sworn to and signed before a notary (jurat)');
        expect($rules['Serve'])->toBe('The owner by certified mail, return receipt requested within 14 days after recording.');
    });
});

describe('Washington notice to owner', function () {
    it('renders the RCW 60.04.031(4) form verbatim, front and reverse side, with the fixture values and no notary', function () {
        $filing = lienFixtureFiling(lienFixtureProject('WA', 'King'), 'prelim_notice');

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        foreach ([
            'S G Roser Construction LLC 4200 Lakeland Hwy Lakeland, FL 33801 863-555-0100',
            'Via certified mail, return receipt requested',
            'To: Owner or reputed owner Mike Stuntz',
            'To: Original (direct) contractor Ken Walker Builders',
            'Re: Notice to Owner',
            'NOTICE TO OWNER RCW 60.04.031 IMPORTANT: READ BOTH SIDES OF THIS NOTICE CAREFULLY. PROTECT YOURSELF FROM PAYING TWICE To: Mike Stuntz Date:',
            'Re: 9025 Baywood Park Dr, Seminole, WA 33777 (King County, Washington) From: S G Roser Construction LLC AT THE REQUEST OF: Ken Walker Builders',
            'THIS IS NOT A LIEN: This notice is sent to you to tell you who is providing professional services, materials, or equipment for the improvement of your property and to advise you of the rights of these persons and your responsibilities. Also take note that laborers on your project may claim a lien without sending you a notice.',
            'OWNER/OCCUPIER OF EXISTING RESIDENTIAL PROPERTY Under Washington law, those who furnish labor, professional services, materials, or equipment for the repair, remodel, or alteration of your owner-occupied principal residence and who are not paid, have a right to enforce their claim for payment against your property. This claim is known as a construction lien.',
            'The law limits the amount that a lien claimant can claim against your property. Claims may only be made against that portion of the contract price you have not yet paid to your prime contractor as of the time this notice was given to you or three days after this notice was mailed to you. Review the back of this notice for more information and ways to avoid lien claims.',
            'COMMERCIAL AND/OR NEW RESIDENTIAL PROPERTY We have or will be providing professional services, materials, or equipment for the improvement of your commercial or new residential project. In the event you or your contractor fail to pay us, we may file a lien against your property. A lien may be claimed for all professional services, materials, or equipment furnished after a date that is sixty days before this notice was given to you or mailed to you, unless the improvement to your property is the construction of a new single-family residence, then ten days before this notice was given to you or mailed to you.',
            'Sender: S G Roser Construction LLC Address: 4200 Lakeland Hwy, Lakeland, FL 33801 Telephone: 863-555-0100',
            'Brief description of professional services, materials, or equipment provided or to be provided: Removal of drywall, replacement of drywall, and damage repair throughout the home',
            'IMPORTANT INFORMATION ON REVERSE SIDE',
            'First furnished June 30, 2026 Estimated total price $4,213.75',
            'IMPORTANT INFORMATION FOR YOUR PROTECTION This notice is sent to inform you that we have or will provide professional services, materials, or equipment for the improvement of your property. We expect to be paid by the person who ordered our services, but if we are not paid, we have the right to enforce our claim by filing a construction lien against your property.',
            'LEARN more about the lien laws and the meaning of this notice by discussing them with your contractor, suppliers, Department of Labor and Industries, the firm sending you this notice, your lender, or your attorney.',
            'COMMON METHODS TO AVOID CONSTRUCTION LIENS: There are several methods available to protect your property from construction liens. The following are two of the more commonly used methods.',
            'DUAL PAYCHECKS (Joint Checks): When paying your contractor for services or materials, you may make checks payable jointly to the contractor and the firms furnishing you this notice.',
            'LIEN RELEASES: You may require your contractor to provide lien releases signed by all the suppliers and subcontractors from whom you have received this notice. If they cannot obtain lien releases because you have not paid them, you may use the dual payee check method to protect yourself.',
            'YOU SHOULD TAKE APPROPRIATE STEPS TO PROTECT YOUR PROPERTY FROM LIENS.',
            'YOUR PRIME CONTRACTOR AND YOUR CONSTRUCTION LENDER ARE REQUIRED BY LAW TO GIVE YOU WRITTEN INFORMATION ABOUT LIEN CLAIMS. IF YOU HAVE NOT RECEIVED IT, ASK THEM FOR IT.',
            'CLAIMANT: S G Roser Construction LLC',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // The form's front, the added facts, the reverse side, then the signature.
        $needles = ['IMPORTANT: READ BOTH SIDES', 'THIS IS NOT A LIEN', 'OWNER/OCCUPIER OF EXISTING', 'COMMERCIAL AND/OR NEW', 'Sender:', 'IMPORTANT INFORMATION ON REVERSE SIDE', 'First furnished', 'IMPORTANT INFORMATION FOR YOUR PROTECTION', 'ASK THEM FOR IT.', 'By (signature)'];
        $positions = array_map(fn (string $needle) => strpos($text, $needle), $needles);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false);
        expect($positions)->toBe($sorted);

        // A written notice: no oath, declaration or notary, and none of the archive's paraphrase.
        expect($text)
            ->not->toContain('My commission expires')
            ->not->toContain('penalty of perjury')
            ->not->toContain('PAYING ANY CONTRACTOR OR SUPPLIER TWICE')
            ->not->toContain('N/A');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->title)->toBe('Notice to Owner');
        expect($form->body)->toBe('documents.lien.letters.bodies.wa-notice-to-owner');
        expect($form->service)->toMatchArray(['recipients' => ['owner', 'gc'], 'days_after' => null, 'method' => 'certified_mail']);
        expect($form->notaryRequired())->toBeFalse();
        expect(collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label')['Serve'])
            ->toBe('The owner and the general contractor by certified mail, return receipt requested.');
    });
});

describe('Washington release and notice of intent', function () {
    it('renders the release of the recorded claim, directed to the Pierce County Auditor, with an acknowledgment', function () {
        $release = lienFixtureFiling(lienFixtureProject('WA', 'Pierce'), 'lien_release', [
            'document_details_json' => ['original_lien' => ['recording_reference' => 'Instrument 202603310123', 'recorded_at' => '2026-03-31', 'county' => 'Pierce']],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($release));

        expect($text)
            ->toContain('RELEASE OF CLAIM OF LIEN')
            ->toContain('RCW 60.04.071')
            ->toContain('Releases Claim of Lien recorded March 31, 2026 as Instrument 202603310123')
            ->toContain('KNOW ALL PERSONS BY THESE PRESENTS that S G Roser Construction LLC')
            ->toContain('is the claimant under that certain Claim of Lien recorded on March 31, 2026 as Instrument 202603310123 in the official records of Pierce County, Washington')
            ->toContain('releases, discharges and cancels the lien and the Claim of Lien of record, and authorizes and directs the Pierce County Auditor to cancel it of record.')
            ->toContain('before me, the undersigned notary public, personally appeared Steven Roser, President of S G Roser Construction LLC')
            ->not->toContain('being sworn, says')
            ->not->toContain('Subscribed and sworn');

        $form = app(LienDocumentResolver::class)->resolve($release);

        expect($form->execution)->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment']);
        expect($form->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail']);
    });

    it('renders the courtesy notice of intent with the 10-day demand for the principal owed', function () {
        $filing = lienFixtureFiling(lienFixtureProject('WA', 'Pierce'), 'noi');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('NOTICE OF INTENT TO RECORD A CLAIM OF LIEN')
            ->toContain('Chapter 60.04 RCW')
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('To: Owner or reputed owner Mike Stuntz')
            ->toContain('To: Original (direct) contractor Ken Walker Builders')
            ->toContain('under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid')
            ->toContain('within 10 days after the date of this notice, Claimant intends to record a Claim of Lien against the property')
            ->not->toContain('My commission expires');

        expect(app(LienDocumentResolver::class)->resolve($filing)->section('demand_days'))->toBe(10);
    });
});
