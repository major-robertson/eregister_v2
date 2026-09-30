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

describe('Oregon claim of lien', function () {
    it('renders the ORS 87.035 claim with the affirmations and the oath, and no prior notice item', function () {
        $filing = lienFixtureFiling(lienFixtureProject('OR', 'Multnomah'), 'mechanics_lien', [
            'document_details_json' => ['notice_served_at' => '2026-07-02', 'notice_served_method' => 'certified_mail'],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            'CLAIM OF LIEN',
            'ORS 87.035',
            'STATE OF OREGON',
            'COUNTY OF MULTNOMAH',
            'Claimant / Lienor S G Roser Construction LLC',
            'Owner Mike Stuntz',
            'Amount claimed $4,213.75',
            'Parcel Number 35-30-15-05699-000-0250',
            'S G Roser Construction LLC ("Claimant") claims a lien under ORS 87.035 upon the real property',
            'Owner or reputed owner of the property. Mike Stuntz',
            'Person who contracted with Claimant. Ken Walker Builders',
            'Property subject to the lien. 9025 Baywood Park Dr, Seminole, OR 33777 (Multnomah County, Oregon)',
            'Parcel Number: 35-30-15-05699-000-0250',
            'After deducting all just credits and offsets, the amount claimed is $4,213.75.',
            'The amount claimed above is a true statement of Claimant\'s demand, after deducting all just credits and offsets (ORS 87.035(3)(a)).',
            'This claim of lien is filed not later than 75 days after Claimant ceased to provide labor, rent equipment or furnish materials, and not later than 75 days after completion of construction (ORS 87.035(1)).',
            'If Claimant did not contract with the owner, Claimant gave the owner any notice of right to a lien that ORS 87.021 requires.',
            'The undersigned, being first duly sworn, states that he or she is the President of S G Roser Construction LLC',
            'and that the statements in it are true of his or her own knowledge.',
            'Subscribed and sworn to (or affirmed) before me',
            'by Steven Roser, President of S G Roser Construction LLC',
            'My commission expires',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // ORS 87.035(4) wants an oath to the truth of the statements, not the archive draft's hedge.
        expect($text)->not->toContain('best of my knowledge')->not->toContain('N/A');
        // ORS 87.021(3)(b) excuses some claimants from the notice, so even a recorded notice date prints no item.
        expect($text)->not->toContain('Prior notice.')->not->toContain('July 2, 2026');
        // Multnomah rejected a claim whose amount started on page 2: the index line carries it above the body.
        expect(strpos($text, 'Amount claimed $4,213.75'))->toBeLessThan(strpos($text, '("Claimant") claims a lien'));
    });

    it('drops the prior notice item for a claimant who contracted with the owner', function () {
        $project = lienFixtureProject('OR', 'Multnomah', [
            'claimant_type' => ClaimantType::Gc,
            'hired_by' => 'owner',
            'prelim_notice_sent_at' => null,
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render(lienFixtureFiling($project, 'mechanics_lien')));

        expect($text)
            ->toContain('as a direct contractor in contract with the owner.')
            ->not->toContain('Prior notice.');
    });

    it('merges the Multnomah County office and fee, and another county keeps the state facts', function () {
        $multnomah = app(LienDocumentResolver::class)->resolve(lienFixtureFiling(lienFixtureProject('OR', 'Multnomah County'), 'mechanics_lien'));

        expect($multnomah->countyKey)->toBe('multnomah');
        expect($multnomah->recording['filing_office'])->toMatchArray([
            'label' => 'Multnomah County Clerk',
            'method' => 'erecord',
            'address_lines' => ['501 SE Hawthorne Blvd, Suite 175', 'Portland, OR 97214'],
        ]);
        expect($multnomah->recording['fee_note'])->toBe('$76 for the first page and $5 for each additional page; a three-page claim of lien cost $86 (2026).');
        expect($multnomah->recording['parcel_label'])->toBe('Parcel Number');
        expect($multnomah->notes)->toContain('Multnomah rejected a 2026 claim of lien for an "incomplete notary acknowledgement" (the draft\'s jurat did not name the person who signed, which ORS 194.280(1)(d) requires), because its amount started on page 2, and for an oversize phone scan of the signed copy.');

        $washington = app(LienDocumentResolver::class)->resolve(lienFixtureFiling(lienFixtureProject('OR', 'Washington'), 'mechanics_lien'));

        expect($washington->countyKey)->toBe('washington');
        expect($washington->recording['filing_office'])->toMatchArray(['label' => 'County Clerk', 'method' => 'erecord', 'address_lines' => []]);
        expect($washington->recording['fee_note'])->toBe('$5 per page plus $71 in state fees (ORS 205.320, 205.323), so $76 for the first page; check the county\'s schedule (2026).');
        expect(collect($washington->notes)->filter(fn (string $note) => str_contains($note, 'Multnomah')))->toBeEmpty();
    });

    it('serves the owner and the mortgagee within 20 days after filing', function () {
        $filing = lienFixtureFiling(lienFixtureProject('OR', 'Multnomah'), 'mechanics_lien');
        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->recipientRoles())->toBe(['owner', 'lender']);
        expect($form->service)->toMatchArray(['days_after' => 20, 'method' => 'certified_mail']);
        expect($form->execution)->toMatchArray(['verification' => 'sworn', 'notary' => true, 'notary_form' => 'jurat']);
        expect($form->sections)->toMatchArray(['amount' => 'breakdown', 'gc' => true, 'hiring_party' => true, 'prior_notice' => false]);

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['File with'])->toBe('Multnomah County Clerk, e-recording (501 SE Hawthorne Blvd, Suite 175, Portland, OR 97214)');
        expect($rules['Signing'])->toBe('Sworn to and signed before a notary (jurat)');
        expect($rules['Serve'])->toBe('The owner and the construction lender by certified mail, return receipt requested within 20 days after recording.');
    });
});

describe('Oregon notice of right to a lien', function () {
    it('renders the ORS 87.023 form verbatim, with the reverse side on its own page after the signature', function () {
        $filing = lienFixtureFiling(lienFixtureProject('OR', 'Multnomah'), 'prelim_notice');

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);
        $html = $pdf->getHtml();

        foreach ([
            'Via certified mail, return receipt requested',
            'To: Owner or reputed owner Mike Stuntz',
            'Re: Notice of Right to a Lien',
            'NOTICE OF RIGHT TO A LIEN',
            'ORS 87.021 and 87.023',
            'WARNING: READ THIS NOTICE. PROTECT YOURSELF FROM PAYING ANY CONTRACTOR OR SUPPLIER TWICE FOR THE SAME SERVICE.',
            'To: Mike Stuntz (Owner) Date of mailing:',
            '9025 Baywood Park Dr, Seminole, OR 33777 (Owner\'s address)',
            'This is to inform you that S G Roser Construction LLC has begun to provide Removal of drywall, replacement of drywall, and damage repair throughout the home (description of materials, equipment, labor or services) ordered by Ken Walker Builders for improvements to property you own. The property is located at 9025 Baywood Park Dr, Seminole, OR 33777.',
            'A lien may be claimed for all materials, equipment, labor and services furnished after a date that is eight days, not including Saturdays, Sundays and other holidays, as defined in ORS 187.010, before this notice was mailed to you.',
            'Even if you or your mortgage lender have made full payment to the contractor who ordered these materials or services, your property may still be subject to a lien unless the supplier providing this notice is paid.',
            'THIS IS NOT A LIEN. It is a notice sent to you for your protection in compliance with the construction lien laws of the State of Oregon.',
            'This notice has been sent to you by: NAME: S G Roser Construction LLC ADDRESS: 4200 Lakeland Hwy, Lakeland, FL 33801 TELEPHONE: 863-555-0100',
            'IF YOU HAVE ANY QUESTIONS ABOUT THIS NOTICE, FEEL FREE TO CALL US.',
            'CLAIMANT: S G Roser Construction LLC',
            'IMPORTANT INFORMATION ON REVERSE SIDE',
            'IMPORTANT INFORMATION FOR YOUR PROTECTION',
            'Under Oregon\'s laws, those who work on your property or provide labor, equipment, services or materials and are not paid have a right to enforce their claim for payment against your property. This claim is known as a construction lien.',
            'If your contractor fails to pay subcontractors, material suppliers, rental equipment suppliers, service providers or laborers or neglects to make other legally required payments, the people who are owed money can look to your property for payment, even if you have paid your contractor in full.',
            'The law states that all people hired by a contractor to provide you with materials, equipment, labor or services must give you a notice of right to a lien to let you know what they have provided.',
            'WAYS TO PROTECT YOURSELF ARE:',
            '- RECOGNIZE that this notice of right to a lien may result in a lien against your property unless all those supplying a notice of right to a lien have been paid.',
            '- LEARN more about the lien laws and the meaning of this notice by contacting the Construction Contractors Board, an attorney or the firm sending this notice.',
            '- ASK for a statement of the labor, equipment, services or materials provided to your property from each party that sends you a notice of right to a lien.',
            '- WHEN PAYING your contractor for materials, equipment, labor or services, you may make checks payable jointly to the contractor and the firm furnishing materials, equipment, labor or services for which you have received a notice of right to a lien.',
            '- OR use one of the methods suggested by the "Information Notice to Owners." If you have not received such a notice, contact the Construction Contractors Board.',
            '- GET EVIDENCE that all firms from whom you have received a notice of right to a lien have been paid or have waived the right to claim a lien against your property.',
            '- CONSULT an attorney, a professional escrow company or your mortgage lender.',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        expect($text)->not->toContain('My commission expires')->not->toContain('N/A');

        // The published form's underlines, and the reverse side starting a page of its own.
        expect($html)
            ->toContain('<u>WARNING</u>')
            ->toContain('<u>even if you have paid your contractor in full</u>')
            ->toContain('<u>jointly</u>')
            ->toContain('<u>waived</u>')
            ->toContain('page-break-before: always');

        $positions = array_map(fn (string $needle) => strpos($text, $needle), [
            'WARNING: READ THIS NOTICE.',
            'This is to inform you',
            'FEEL FREE TO CALL US.',
            'By (signature)',
            'IMPORTANT INFORMATION ON REVERSE SIDE',
            'IMPORTANT INFORMATION FOR YOUR PROTECTION',
            '- CONSULT an attorney',
        ]);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false)->toBe($sorted);
    });

    it('prints the date of mailing once the notice has been mailed', function () {
        $filing = lienFixtureFiling(lienFixtureProject('OR', 'Multnomah'), 'prelim_notice', ['mailed_at' => '2026-09-01 15:00:00']);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)->toContain('To: Mike Stuntz (Owner) Date of mailing: September 1, 2026');
    });

    it('goes to the owner by certified mail, signed without a notary', function () {
        $filing = lienFixtureFiling(lienFixtureProject('OR', 'Multnomah'), 'prelim_notice');
        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->title)->toBe('Notice of Right to a Lien');
        expect($form->body)->toBe('documents.lien.letters.bodies.or-notice-of-right-to-lien');
        expect($form->clauses['after_execution'])->toBe(['documents.lien.letters.clauses.or-notice-of-right-to-lien-reverse']);
        expect($form->recipientRoles())->toBe(['owner']);
        expect($form->service)->toMatchArray(['days_after' => null, 'method' => 'certified_mail']);
        expect($form->notaryRequired())->toBeFalse();

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['Signing'])->toBe('Signed by the claimant; no notary');
        expect($rules['Serve'])->toBe('The owner by certified mail, return receipt requested.');
    });
});

describe('Oregon release and notice of intent', function () {
    it('renders the release of the recorded claim, acknowledged before a notary', function () {
        $filing = lienFixtureFiling(lienFixtureProject('OR', 'Multnomah'), 'lien_release', [
            'document_details_json' => ['original_lien' => ['recording_reference' => 'Instrument No. 2026-045123', 'recorded_at' => '2026-07-15', 'county' => 'Multnomah']],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('RELEASE OF CLAIM OF LIEN')
            ->toContain('ORS 87.001 to 87.093')
            ->toContain('STATE OF OREGON')
            ->toContain('COUNTY OF MULTNOMAH')
            ->toContain('Releases Claim of Lien recorded July 15, 2026 as Instrument No. 2026-045123')
            ->toContain('KNOW ALL PERSONS BY THESE PRESENTS that S G Roser Construction LLC ("Claimant"), whose address is 4200 Lakeland Hwy, Lakeland, FL 33801, is the claimant under that certain Claim of Lien recorded on July 15, 2026 as Instrument No. 2026-045123 in the official records of Multnomah County, Oregon, against the real property described below, owned by Mike Stuntz:')
            ->toContain('releases, discharges and cancels the lien and the Claim of Lien of record, and authorizes and directs the Multnomah County Clerk to cancel it of record.')
            ->toContain('personally appeared Steven Roser, President of S G Roser Construction LLC')
            ->not->toContain('being first duly sworn')
            ->not->toContain('Subscribed and sworn to');

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['Signing'])->toBe('Signed and acknowledged before a notary');
        expect($rules['Serve'])->toBe('The owner by certified mail, return receipt requested.');
    });

    it('renders the courtesy notice of intent with a 10-day demand', function () {
        $filing = lienFixtureFiling(lienFixtureProject('OR', 'Multnomah'), 'noi');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('NOTICE OF INTENT TO FILE A CLAIM OF LIEN')
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('To: Owner or reputed owner Mike Stuntz')
            ->toContain('under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid.')
            ->toContain('Unless payment in full of the amount stated above is received within 10 days after the date of this notice, Claimant intends to record a Claim of Lien against the property')
            ->not->toContain('Via any')
            ->not->toContain('My commission expires');
    });

    it('sets the delivery method on every kind and points at views that exist', function () {
        $or = LienDocumentRegistry::for('OR');

        expect($or['recording']['filing_office'])->toMatchArray(['label' => 'County Clerk', 'method' => 'erecord']);
        expect($or['recording']['parcel_label'])->toBe('Parcel Number');
        expect($or['kinds']['noi']['sections']['demand_days'])->toBe(10);
        expect($or['kinds']['lien_release']['execution'])->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment']);

        foreach ($or['kinds'] as $kind => $entry) {
            expect($entry['service']['method'])->toBe('certified_mail', "{$kind} delivery method");
            expect(view()->exists($entry['body']))->toBeTrue("{$kind} body view");
        }

        expect(view()->exists('documents.lien.letters.clauses.or-notice-of-right-to-lien-reverse'))->toBeTrue();
    });
});
