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

describe('New Jersey construction lien claim', function () {
    it('prints the N.J.S.A. 2A:44A-8 form with the fixture values, the verification, the jurat and the form\'s notarial', function () {
        $filing = lienFixtureFiling(lienFixtureProject('NJ', 'Bergen'), 'mechanics_lien', [
            'document_details_json' => ['block' => '1506', 'lot' => '12', 'contract_date' => '2026-01-16', 'contract_type' => 'written', 'owner_interest' => 'fee simple'],
        ]);

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        foreach ([
            'CONSTRUCTION LIEN CLAIM',
            'N.J.S.A. 2A:44A-8',
            'STATE OF NEW JERSEY',
            'COUNTY OF BERGEN',
            'TO THE CLERK, COUNTY OF BERGEN:',
            'In accordance with the "Construction Lien Law," P.L.1993, c.318 (C.2A:44A-1 et al.), notice is hereby given that (only complete those sections that apply):',
            'I, Steven Roser, an officer/member of the claimant known as S G Roser Construction LLC, located at 4200 Lakeland Hwy, Lakeland, FL 33801, claim a construction lien against the real property of Mike Stuntz, in that certain tract or parcel of land and premises described as Block 1506, Lot 12, on the tax map of the municipality of Seminole, County of Bergen, State of New Jersey, in the amount of $4,213.75, as calculated below for the value of the work, services, material or equipment provided.',
            'The lien is claimed against the interest of the owner (fee simple).',
            'Street address: 9025 Baywood Park Dr, Seminole, NJ 33777 Legal description: BAYWOOD PARK LOT 25 Parcel ID: 35-30-15-05699-000-0250',
            '2. In accordance with a written contract for improvement of the above property, dated January 16, 2026, with the contractor, named or known as Ken Walker Builders, and located at 13700 58th St N Ste 204, Clearwater, FL 33760, this claimant performed the following work or provided the following services, material or equipment: Removal of drywall, replacement of drywall, and damage repair throughout the home',
            '3. The date of the provision of the last work, services, material or equipment for which payment is claimed is July 10, 2026.',
            '4. The amount due for work, services, material or equipment delivery provided by claimant in connection with the improvement of the real property, and upon which this lien claim is based, is calculated as follows:',
            'A. Initial Contract Price: $4,213.75',
            'B. Executed Amendments to Contract Price/Change Orders: $0.00',
            'C. Total Contract Price (A + B) = $4,213.75',
            'D. If Contract Not Completed, Value Determined in Accordance with the Contract of Work Completed or Services, Material, Equipment Provided:',
            'E. Total from C or D (whichever is applicable): $4,213.75',
            'F. Agreed upon Credits: $0.00',
            'G. Amount Paid to Date: $0.00',
            'TOTAL LIEN CLAIM AMOUNT E - [F + G] = $4,213.75',
            'NOTICE OF UNPAID BALANCE AND ARBITRATION AWARD',
            'This claim (check one) does does not arise from a Residential Construction Contract. If it does, complete 5 and 6 below; if not residential, complete 5 below, only if applicable. If not residential and 5 is not applicable, skip to Claimant\'s Representation and Verification.',
            '5. A Notice of Unpaid Balance and Right to File Lien (if any) was previously filed with the County Clerk of Bergen County on',
            '6. An award of the arbitrator (if residential) was issued on',
            'CLAIMANT\'S REPRESENTATION AND VERIFICATION Claimant represents and verifies under oath that:',
            '1. I have authority to file this claim.',
            '2. The claimant is entitled to the amount claimed at the date of lodging for record of the claim, pursuant to claimant\'s contract described above.',
            '3. The work, services, material or equipment for which this lien claim is filed was provided exclusively in connection with the improvement of the real property which is the subject of this claim.',
            '4. This claim form has been lodged for record with the County Clerk where the property is located within 90 or, if residential construction, 120 days from the last date upon which the work, services, material or equipment for which payment is claimed was provided.',
            '5. This claim form has been completed in its entirety to the best of my ability and I understand that if I do not complete this form in its entirety, the form may be deemed invalid by a court of law.',
            '6. This claim form will be served as required by statute upon the owner or community association, and upon the contractor or subcontractor against whom this claim has been asserted, if any.',
            '7. The foregoing statements made by me in this claim form are true, to the best of my knowledge. I am aware that if any of the foregoing statements made by me in this claim form are willfully false, this construction lien claim will be void and that I will be liable for damages to the owner or any other person injured as a consequence of the filing of this lien claim.',
            'CLAIMANT: S G Roser Construction LLC',
            'Subscribed and sworn to (or affirmed) before me on this',
            'by Steven Roser, President of S G Roser Construction LLC, who is personally known to me',
            'SUGGESTED NOTARIAL FOR CORPORATE OR LIMITED LIABILITY CLAIMANT:',
            'before me, the subscriber, personally appeared Steven Roser who, I am satisfied is the Secretary (or other officer/manager/agent) of the Corporation (partnership or limited liability company) named herein and who by me duly sworn/affirmed, asserted authority to act on behalf of the Corporation (partnership or limited liability company) and who, by virtue of its Bylaws, or Resolution of its Board of Directors (or partnership or operating agreement) executed the within instrument on its behalf, and thereupon acknowledged that claimant signed, sealed and delivered same as claimant\'s act and deed, for the purposes herein expressed.',
            'NOTARY PUBLIC My commission expires',
            'NOTICE TO OWNER OF REAL PROPERTY NOTICE TO CONTRACTOR OR SUBCONTRACTOR, IF APPLICABLE',
            'The owner\'s real estate may be subject to sale to satisfy the amount asserted by this claim. However, the owner\'s real estate cannot be sold until the facts and issues which form the basis of this claim are decided in a legal proceeding before a court of law. The lien claimant is required by law to commence suit to enforce this claim.',
            '1. Within one year of the date of the last provision of work, services, material or equipment, payment for which the lien claim was filed; or',
            '2. Within 30 days following receipt of written notice, by personal service or certified mail, return receipt requested, from the owner or community association, contractor, or subcontractor against whom a lien claim is filed, as appropriate, requiring the claimant to commence an action to establish the lien claim.',
            '2. causing the lien claim to be discharged by filing a surety bond or making a deposit of funds as provided for in section 31 of P.L.1993, c.318 (C.2A:44A-31), by which the owner will retain the right to challenge this lien claim in a legal proceeding before a court of law.',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // The form carries its own verification, so no generic sworn paragraph; the entity
        // claimant gets only the corporate notarial; no interest or fees are added to the claim.
        expect($text)
            ->not->toContain('being first duly sworn')
            ->not->toContain('SUGGESTED NOTARIAL FOR INDIVIDUAL CLAIMANT')
            ->not->toContain('attorney\'s fees')
            ->not->toContain('N/A');

        // The form's order: items 1 to 4, the residential section, the verification, the
        // signature and jurat, the form's notarial, then the Notice to Owner.
        $needles = ['TO THE CLERK, COUNTY OF BERGEN:', 'I, Steven Roser', '2. In accordance with a written contract', '3. The date of the provision', 'TOTAL LIEN CLAIM AMOUNT', 'NOTICE OF UNPAID BALANCE AND ARBITRATION AWARD', 'Claimant represents and verifies under oath that:', 'CLAIMANT: S G Roser Construction LLC', 'Subscribed and sworn to (or affirmed)', 'SUGGESTED NOTARIAL FOR CORPORATE', 'NOTICE TO OWNER OF REAL PROPERTY'];
        $positions = array_map(fn (string $needle) => strpos($text, $needle), $needles);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false);
        expect($positions)->toBe($sorted);
    });

    it('answers the residential question from the property type and fills item D when work was left uncompleted', function () {
        $residential = lienFixtureText(app(LienDocumentGenerator::class)->render(
            lienFixtureFiling(lienFixtureProject('NJ', 'Bergen', ['property_class' => 'residential']), 'mechanics_lien')
        ));

        expect($residential)
            ->toContain('This claim does arise from a Residential Construction Contract.')
            ->toContain('5. A Notice of Unpaid Balance and Right to File Lien (if any) was previously filed with the County Clerk of Bergen County on')
            ->toContain('6. An award of the arbitrator (if residential) was issued on')
            ->not->toContain('(check one)');

        $project = lienFixtureProject('NJ', 'Bergen', [
            'property_class' => 'commercial',
            'change_orders_cents' => 50000,
            'payments_received_cents' => 100000,
            'uncompleted_work_cents' => 20000,
        ]);
        $commercial = lienFixtureText(app(LienDocumentGenerator::class)->render(
            lienFixtureFiling($project, 'mechanics_lien', ['amount_claimed_cents' => 351375])
        ));

        expect($commercial)
            ->toContain('This claim does not arise from a Residential Construction Contract.')
            ->toContain('5. A Notice of Unpaid Balance and Right to File Lien (if any) was previously filed')
            ->toContain('B. Executed Amendments to Contract Price/Change Orders: $500.00')
            ->toContain('C. Total Contract Price (A + B) = $4,713.75')
            ->toContain('Services, Material, Equipment Provided: $4,513.75')
            ->toContain('E. Total from C or D (whichever is applicable): $4,513.75')
            ->toContain('G. Amount Paid to Date: $1,000.00')
            ->toContain('TOTAL LIEN CLAIM AMOUNT E - [F + G] = $3,513.75')
            ->not->toContain('An award of the arbitrator');
    });

    it('prints ruled blanks for a missing block, lot and contract date', function () {
        $pdf = app(LienDocumentGenerator::class)->render(lienFixtureFiling(lienFixtureProject('NJ', 'Bergen'), 'mechanics_lien'));
        $text = lienFixtureText($pdf);

        expect($text)
            ->toContain('described as Block , Lot , on the tax map of the municipality of Seminole, County of Bergen, State of New Jersey')
            ->toContain('dated , with the contractor, named or known as Ken Walker Builders')
            ->toContain('The lien is claimed against the interest of the owner.')
            ->not->toContain('N/A');
        expect($pdf->getHtml())->toContain('class="fill fill-short"');
    });

    it('resolves the county clerk, the jurat and the 10-day service on the owner and the hiring party', function () {
        $filing = lienFixtureFiling(lienFixtureProject('NJ', 'Bergen'), 'mechanics_lien');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->title)->toBe('Construction Lien Claim');
        expect($form->statute)->toBe('N.J.S.A. 2A:44A-8');
        expect($form->body)->toBe('documents.lien.instruments.bodies.nj-construction-lien-claim');
        expect($form->countyKey)->toBe('bergen');
        expect($form->sections)->toMatchArray(['amount' => 'breakdown', 'gc' => false, 'block_lot' => true, 'prior_notice' => false]);
        expect($form->execution)->toMatchArray(['verification' => 'sworn', 'notary' => true, 'notary_form' => 'jurat', 'statement' => false]);
        expect($form->clauses['after_execution'])->toBe([
            'documents.lien.instruments.clauses.nj-lien-claim-notarial',
            'documents.lien.instruments.clauses.nj-notice-to-owner',
        ]);
        expect($form->service)->toMatchArray(['recipients' => ['owner', 'customer'], 'days_after' => 10, 'method' => 'certified_mail', 'proof' => 'declaration']);
        expect($form->recording['filing_office'])->toMatchArray(['label' => 'County Clerk', 'method' => 'either']);
        expect($form->recording['parcel_label'])->toBe('Parcel ID');
        expect($form->notes)->toContain('Undecided: whether the form\'s notarial alone, without the separate jurat, satisfies "verified by oath"; until counsel says otherwise, the notary completes both.');

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['File with'])->toBe('County Clerk, e-recording or mail');
        expect($rules['Fee'])->toContain('construction lien $15');
        expect($rules['Signing'])->toBe('Sworn to and signed before a notary (jurat)');
        expect($rules['Serve'])->toBe('The owner and the hiring party by certified mail, return receipt requested within 10 days after recording.');
    });
});

describe('New Jersey notice of unpaid balance', function () {
    it('prints the N.J.S.A. 2A:44A-20 form to the owner and the hiring party with an acknowledgment', function () {
        $project = lienFixtureProject('NJ', 'Bergen', ['property_class' => 'residential']);
        $filing = lienFixtureFiling($project, 'prelim_notice', [
            'document_details_json' => ['block' => '1506', 'lot' => '12', 'contract_date' => '2026-01-16'],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            'Via certified mail, return receipt requested',
            'To: Owner or reputed owner Mike Stuntz 9025 Baywood Park Dr Seminole, NJ 33777',
            'To: Person who contracted with the claimant Ken Walker Builders',
            'NOTICE OF UNPAID BALANCE AND RIGHT TO FILE LIEN',
            'N.J.S.A. 2A:44A-20',
            'TO THE CLERK, COUNTY OF BERGEN: In accordance with the "Construction Lien Law," P.L.1993, c.318 (C.2A:44A-1 et al.), notice is hereby given that:',
            '1. Steven Roser, an officer/member of the claimant known as S G Roser Construction LLC, located at 4200 Lakeland Hwy, Lakeland, FL 33801, has on',
            'a potential construction lien against the real property of Mike Stuntz, in that certain tract or parcel of land and premises described as Block 1506, Lot 12, on the tax map of the municipality of Seminole, County of Bergen, State of New Jersey, in the amount of ($4,213.75), as calculated below for the value of the work, services, material or equipment provided. The lien is to be claimed against the interest of the owner.',
            '2. The work, services, material or equipment was provided pursuant to the terms of a written contract (or, in the case of a supplier, a delivery or order slip signed by the owner, community association, contractor, or subcontractor having a direct contractual relation with a contractor, or an authorized agent of any of them), dated January 16, 2026, between S G Roser Construction LLC and contractor, named or known as Ken Walker Builders and located at 13700 58th St N Ste 204, Clearwater, FL 33760, in the total contract amount of ($4,213.75) together with (if applicable) amendments to the total contract amount aggregating ($0.00).',
            '4. The date of the provision of the last work, services, material or equipment for which payment is claimed is July 10, 2026.',
            'D. If Contract Not Completed, Value Determined in Accordance with Contract of Work Completed or Services, Material or Equipment Provided:',
            'TOTAL LIEN CLAIM AMOUNT E - [F + G] = $4,213.75',
            '6. The written contract is a residential construction contract as defined in section 2 of P.L.1993, c.318 (C.2A:44A-2).',
            '7. This notification has been lodged for record prior or subsequent to completion of the work, services, material or equipment as described above. The purpose of this notification is to advise the owner or community association and any other person who is attempting to encumber or take transfer of said property described above that a potential construction lien may be lodged for record within the 90-day period, or in the case of a residential construction contract within the 120-day period, following the date of the provision of the last work, services, material or equipment as set forth in paragraph 4 of this notice.',
            'CLAIMANT\'S REPRESENTATION AND VERIFICATION Claimant represents and verifies that:',
            '1. I have authority to file this Notice of Unpaid Balance and Right to File Lien.',
            '4. The Notice of Unpaid Balance and Right to File Lien has been lodged for record within 90 days, or in the case of a residential construction contract within 60 days, from the last date upon which the work, services, material or equipment for which payment is claimed was provided.',
            '5. The foregoing statements made by me are true, to the best of my knowledge.',
            'CLAIMANT: S G Roser Construction LLC',
            'acknowledged that he or she executed it in that capacity on behalf of S G Roser Construction LLC',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // The notice's verification is not under oath: an acknowledgment, no jurat.
        expect($text)
            ->not->toContain('verifies under oath')
            ->not->toContain('Subscribed and sworn to');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->family)->toBe('letter');
        expect($form->body)->toBe('documents.lien.letters.bodies.nj-notice-of-unpaid-balance');
        expect($form->execution)->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment']);
        expect($form->service)->toMatchArray(['recipients' => ['owner', 'customer'], 'days_after' => null, 'method' => 'certified_mail']);
        expect($form->notes)->toContain('Undecided: whether county clerks want a first-page recording format for the notice; until counsel says otherwise, lodge it as generated.');
    });
});

describe('New Jersey discharge and notice of intent', function () {
    it('prints the certificate of discharge with the N.J.S.A. 2A:44A-30(a) particulars and an acknowledgment', function () {
        $filing = lienFixtureFiling(lienFixtureProject('NJ', 'Bergen'), 'lien_release', [
            'document_details_json' => ['original_lien' => ['recording_reference' => 'Instrument 2026-044521', 'book' => '88', 'page' => '214', 'recorded_at' => '2026-07-20', 'county' => 'Bergen']],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('CERTIFICATE OF DISCHARGE OF CONSTRUCTION LIEN CLAIM')
            ->toContain('N.J.S.A. 2A:44A-30')
            ->toContain('is the claimant under that certain Construction Lien Claim recorded on July 20, 2026 as Instrument 2026-044521 in Book 88, Page 214 in the official records of Bergen County, New Jersey')
            ->toContain('authorizes and directs the County Clerk to cancel it of record.')
            ->toContain('The undersigned claimant directs the County Clerk to discharge the lien claim of record.')
            ->toContain('As N.J.S.A. 2A:44A-30(a) requires, this certificate states: Date the lien claim was filed July 20, 2026 Book and page endorsed on the lien claim Book 88, Page 214 Owner named in the lien claim Mike Stuntz Location of the property 9025 Baywood Park Dr, Seminole, NJ 33777 (Bergen County, New Jersey) Person for whom the work, services, equipment or materials was provided Ken Walker Builders')
            ->toContain('acknowledged that he or she executed it in that capacity on behalf of S G Roser Construction LLC')
            ->not->toContain('Subscribed and sworn to');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->execution)->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment']);
        expect($form->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail']);
    });

    it('sends a courtesy notice of intent that names the residential steps', function () {
        $text = lienFixtureText(app(LienDocumentGenerator::class)->render(
            lienFixtureFiling(lienFixtureProject('NJ', 'Bergen'), 'noi')
        ));

        expect($text)
            ->toContain('NOTICE OF INTENT TO FILE A CONSTRUCTION LIEN CLAIM')
            ->toContain('N.J.S.A. 2A:44A-1 et seq.')
            ->toContain('under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid')
            ->toContain('Unless payment in full of the amount stated above is received within 10 days after the date of this notice, Claimant intends to file a Construction Lien Claim against the property under the New Jersey Construction Lien Law, N.J.S.A. 2A:44A-1 et seq. If the work was residential construction, Claimant will first file a Notice of Unpaid Balance and Right to File Lien and demand arbitration, as N.J.S.A. 2A:44A-21 requires.')
            ->not->toContain('My commission expires');
    });
});
