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

describe('Illinois claim for lien', function () {
    it('renders the Clinton County claim with the brief statement of the contract, the 90-day notice line, the affirmations and the jurat', function () {
        $filing = lienFixtureFiling(lienFixtureProject('IL', 'Clinton'), 'mechanics_lien', [
            'document_details_json' => [
                'contract_date' => '2026-01-16',
                'contract_type' => 'written',
                'notice_served_at' => '2026-08-07',
                'notice_served_method' => 'certified_mail',
            ],
        ]);

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        foreach ([
            'CLAIM FOR LIEN',
            '770 ILCS 60/7 and 60/28',
            'STATE OF ILLINOIS',
            'COUNTY OF CLINTON',
            'Claimant / Lienor S G Roser Construction LLC',
            'Amount claimed $4,213.75',
            'PIN 35-30-15-05699-000-0250',
            'S G Roser Construction LLC ("Claimant") claims a lien under 770 ILCS 60/7 and 60/28 upon the real property and improvements described below',
            'Claimant furnished the labor, services, equipment or materials described below as a subcontractor.',
            // The 770 ILCS 60/7(a) "brief statement of the claimant's contract": with whom, written or oral, when, for what, at what price.
            'Person who contracted with Claimant. Ken Walker Builders',
            'The contract was written; a copy is attached.',
            'Date of the contract: January 16, 2026',
            'Property subject to the lien. 9025 Baywood Park Dr, Seminole, IL 33777 (Clinton County, Illinois)',
            'Legal description: BAYWOOD PARK LOT 25',
            'PIN: 35-30-15-05699-000-0250',
            'Removal of drywall, replacement of drywall, and damage repair throughout the home',
            'Contract amount (agreed price or reasonable value of the labor and materials) $ 4,213.75',
            'After deducting all just credits and offsets, the amount claimed is $4,213.75.',
            "Prior notice. Claimant served its Subcontractor's Notice of Claim (90-Day Notice) on August 7, 2026 by certified mail, return receipt requested.",
            "This claim for lien is filed within four months after the completion of Claimant's contract, or of the extra or additional work or materials furnished under it (770 ILCS 60/7(a), 60/28).",
            'The amount claimed is the balance due to Claimant after allowing all credits (770 ILCS 60/7(a)).',
            'Claimant also claims interest on the amount claimed at the rate of 10% per annum from the date it became due (770 ILCS 60/1(a), 60/21(a)).',
            'The lien extends to every estate, right of redemption or other interest the owner had in the land when the contract was made or acquires later, and it attaches as of the date of the contract (770 ILCS 60/1(a)).',
            'The undersigned, being first duly sworn, states that he or she is the President of S G Roser Construction LLC, the claimant named above; that he or she is authorized to make this Claim for Lien on its behalf',
            'and that the statements in it are true of his or her own knowledge.',
            'Subscribed and sworn to (or affirmed) before me on this',
            'by Steven Roser, President of S G Roser Construction LLC, who is personally known to me',
            'My commission expires',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // "Verified by the affidavit" (770 ILCS 60/7(a)): no acknowledgment, no best-knowledge formula.
        expect($text)->not->toContain('best of')->not->toContain('acknowledged before me')->not->toContain('N/A');

        // Clinton County wants 3 inches wide by 4 high in the top right corner: page 1 is blank
        // across its top four inches, and the preparer block prints below the rule.
        expect($pdf->getHtml())->toContain('height: 3in');
        expect(strpos($text, 'Space above this line'))->toBeLessThan(strpos($text, 'Prepared by, recording requested by and return to'))
            ->and(strpos($text, 'Prepared by, recording requested by and return to'))->toBeLessThan(strpos($text, 'CLAIM FOR LIEN'));
    });

    it('merges the Clinton County recording facts, leaves other counties on the state rules and serves the owner within 10 days', function () {
        $clintonFiling = lienFixtureFiling(lienFixtureProject('IL', 'Clinton'), 'mechanics_lien');
        $sangamonFiling = lienFixtureFiling(lienFixtureProject('IL', 'Sangamon'), 'mechanics_lien');

        $clinton = app(LienDocumentResolver::class)->resolve($clintonFiling);
        $sangamon = app(LienDocumentResolver::class)->resolve($sangamonFiling);

        expect($clinton->countyKey)->toBe('clinton');
        expect($clinton->recording['filing_office'])->toMatchArray([
            'label' => 'Clinton County Clerk and Recorder',
            'method' => 'erecord',
            'address_lines' => ['850 Fairfax Street', 'Carlyle, IL 62231'],
            'vendor' => 'CSC',
        ]);
        expect($clinton->recording['top_margin_in'])->toBe(4.0);
        expect($clinton->recorderSpaceInches())->toBe(3.0);
        expect($clinton->recording['preparer_in_space'])->toBeFalse();
        expect($clinton->recording['fee_note'])->toBe('$70 per lien or release as a standard land document; $85 if non-standard (effective February 1, 2025).');
        expect($clinton->notes)->toContain("Clinton County rejects documents with blank lines: before recording, fill in every line or line through any that does not apply, including the notary's identification line.");
        expect($clinton->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => 10, 'method' => 'certified_mail']);

        expect($sangamon->countyKey)->toBe('sangamon');
        expect($sangamon->recording['filing_office'])->toMatchArray([
            'label' => 'County Recorder',
            'method' => 'erecord',
            'address_lines' => [],
            'vendor' => null,
        ]);
        expect($sangamon->recording['top_margin_in'])->toBe(3.0);
        expect($sangamon->recorderSpaceInches())->toBe(2.0);
        expect($sangamon->recording['preparer_in_space'])->toBeFalse();
        expect($sangamon->recording['fee_note'])->toBeNull();
        expect($sangamon->notes)->not->toContain("Clinton County rejects documents with blank lines: before recording, fill in every line or line through any that does not apply, including the notary's identification line.");

        $clintonRules = collect(LienDocumentPackage::forFiling($clintonFiling)->rules())->pluck('value', 'label');
        $sangamonRules = collect(LienDocumentPackage::forFiling($sangamonFiling)->rules())->pluck('value', 'label');

        expect($clintonRules['File with'])->toBe('Clinton County Clerk and Recorder, e-recording via CSC (850 Fairfax Street, Carlyle, IL 62231)');
        expect($clintonRules['Fee'])->toBe('$70 per lien or release as a standard land document; $85 if non-standard (effective February 1, 2025).');
        expect($clintonRules['Signing'])->toBe('Sworn to and signed before a notary (jurat)');
        expect($clintonRules['Serve'])->toBe('The owner by certified mail, return receipt requested within 10 days after recording.');
        expect($clintonRules['Attach'])->toBe('A copy of the written contract, when the claim says the contract was written.');
        expect($sangamonRules['File with'])->toBe('County Recorder, e-recording');
        expect($sangamonRules->has('Fee'))->toBeFalse();

        $sangamonPdf = app(LienDocumentGenerator::class)->render($sangamonFiling);

        expect($sangamonPdf->getHtml())->toContain('height: 2in');
        expect(lienFixtureText($sangamonPdf))
            ->toContain('COUNTY OF SANGAMON')
            ->toContain('Property subject to the lien. 9025 Baywood Park Dr, Seminole, IL 33777 (Sangamon County, Illinois)');
    });

    it('leaves out the notice line for a contractor who contracted with the owner', function () {
        $project = lienFixtureProject('IL', 'Clinton', ['claimant_type' => ClaimantType::Gc, 'hired_by' => 'owner', 'prelim_notice_sent_at' => null]);
        $project->parties()->whereIn('role', [PartyRole::Customer->value, PartyRole::Gc->value])->delete();
        $filing = lienFixtureFiling($project, 'mechanics_lien', ['document_details_json' => ['contract_type' => 'oral']]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('as a direct contractor in contract with the owner.')
            ->toContain('Claimant contracted directly with the owner named above.')
            ->toContain('The contract was oral, on the following terms, time given and conditions:')
            ->not->toContain('Prior notice.')
            ->not->toContain('Original (general) contractor.');
    });
});

describe('Illinois subcontractor\'s notice of claim', function () {
    it('fills the § 60/24 form with the owner, the contractor, the work, the property and the amount, and serves only the owner when there is no lender', function () {
        $filing = lienFixtureFiling(lienFixtureProject('IL', 'Clinton'), 'prelim_notice');

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        expect($text)
            ->toContain("SUBCONTRACTOR'S NOTICE OF CLAIM (90-DAY NOTICE)")
            ->toContain('770 ILCS 60/24')
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('To: Owner or reputed owner Mike Stuntz 9025 Baywood Park Dr Seminole, IL 33777')
            // 770 ILCS 60/24(a), "The form of such notice may be as follows", verbatim with its blanks filled.
            ->toContain('To Mike Stuntz: You are hereby notified that I have been employed by Ken Walker Builders to furnish labor, services or materials for Removal of drywall, replacement of drywall, and damage repair throughout the home under his or her contract with you, on your property at 9025 Baywood Park Dr, Seminole, IL 33777 (Clinton County, Illinois); legal description: BAYWOOD PARK LOT 25; PIN 35-30-15-05699-000-0250 and that there was due to me, or is to become due (as the case may be) therefor, the sum of $4,213.75.')
            ->toContain('CLAIMANT: S G Roser Construction LLC')
            ->not->toContain('Construction lender')
            ->not->toContain('a subcontractor of')
            ->not->toContain('My commission expires')
            ->not->toContain('N/A');

        // "Dated at .... this .... day of ....., ....." is left for the signer; the signature follows.
        expect($pdf->getHtml())->toContain('Dated at <span class="fill fill-mid">&nbsp;</span> this <span class="fill fill-short">&nbsp;</span> day of <span class="fill fill-mid">&nbsp;</span>, <span class="fill fill-short">&nbsp;</span>.');
        expect(strpos($text, 'You are hereby notified'))->toBeLessThan(strpos($text, 'Dated at'))
            ->and(strpos($text, 'Dated at'))->toBeLessThan(strpos($text, 'By (signature)'));

        $package = LienDocumentPackage::forFiling($filing);

        expect($package->form->recipientRoles())->toBe(['owner', 'lender']);
        expect(collect($package->rules())->pluck('value', 'label')['Serve'])
            ->toBe('The owner and the construction lender by certified mail, return receipt requested.');
    });

    it('names the original contractor for a sub-subcontractor and serves the lender when there is one', function () {
        $project = lienFixtureProject('IL', 'Clinton', [
            'claimant_type' => ClaimantType::SubSubContractor,
            'hired_by' => 'subcontractor',
            'legal_description' => null,
        ]);
        $project->parties()->where('role', PartyRole::Customer->value)->update(['name' => 'Pat Lane', 'company_name' => 'Lane Drywall LLC']);
        lienFixtureParty($project, PartyRole::Lender, 'Loan Servicing', 'Midwest Community Bank', [
            'address1' => '1 Bank Plaza', 'city' => 'Carlyle', 'state' => 'IL', 'zip' => '62231',
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render(lienFixtureFiling($project, 'prelim_notice')));

        expect($text)
            ->toContain('To: Owner or reputed owner Mike Stuntz')
            ->toContain('To: Construction lender Midwest Community Bank Loan Servicing 1 Bank Plaza Carlyle, IL 62231')
            ->toContain('I have been employed by Lane Drywall LLC, a subcontractor of Ken Walker Builders, to furnish labor, services or materials for Removal of drywall')
            ->toContain('on your property at 9025 Baywood Park Dr, Seminole, IL 33777 (Clinton County, Illinois); PIN 35-30-15-05699-000-0250 and that there was due to me');
    });
});

describe('Illinois release', function () {
    it('releases the recorded claim under an acknowledgment and prints the § 60/35(c) statement in quarter-inch bold letters', function () {
        $filing = lienFixtureFiling(lienFixtureProject('IL', 'Clinton'), 'lien_release', [
            'document_details_json' => ['original_lien' => [
                'recording_reference' => 'Document No. 2026R01234',
                'recorded_at' => '2026-06-25',
                'county' => 'Clinton',
            ]],
        ]);

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        expect($text)
            ->toContain("RELEASE AND SATISFACTION OF MECHANIC'S LIEN")
            ->toContain('770 ILCS 60/35')
            ->toContain('COUNTY OF CLINTON')
            ->toContain('Releases Claim for Lien recorded June 25, 2026 as Document No. 2026R01234')
            ->toContain('KNOW ALL PERSONS BY THESE PRESENTS that S G Roser Construction LLC ("Claimant"), whose address is 4200 Lakeland Hwy, Lakeland, FL 33801,')
            ->toContain('is the claimant under that certain Claim for Lien recorded on June 25, 2026 as Document No. 2026R01234 in the official records of Clinton County, Illinois, against the real property described below, owned by Mike Stuntz:')
            ->toContain('hereby releases, discharges and cancels the lien and the Claim for Lien of record, and authorizes and directs the Clinton County Clerk and Recorder to cancel it of record.')
            ->toContain('The claim for lien described above has been paid, and Claimant acknowledges its satisfaction and release under 770 ILCS 60/35.')
            ->toContain('before me, the undersigned notary public, personally appeared Steven Roser, President of S G Roser Construction LLC, personally known to me or proved to me on the basis of satisfactory evidence')
            ->toContain('and acknowledged that he or she executed it in that capacity on behalf of S G Roser Construction LLC for the purposes stated in it.')
            // 770 ILCS 60/35(c), verbatim.
            ->toContain('FOR THE PROTECTION OF THE OWNER, THIS RELEASE SHOULD BE FILED WITH THE RECORDER IN WHOSE OFFICE THE CLAIM FOR LIEN WAS FILED.')
            ->not->toContain('being first duly sworn')
            ->not->toContain('Subscribed and sworn')
            ->not->toContain('Amount claimed');

        // "In bold letters at least 1/4 inch in height": DejaVu Serif Bold capitals at 28pt are about 0.28 inch.
        expect($pdf->getHtml())->toContain('style="font-size: 28pt; font-weight: bold;');

        // Page 1 keeps the caption and index line; the statement follows the notary certificate.
        expect(strpos($text, 'Releases Claim for Lien'))->toBeLessThan(strpos($text, 'KNOW ALL PERSONS'))
            ->and(strpos($text, '(Notary seal)'))->toBeLessThan(strpos($text, 'FOR THE PROTECTION OF THE OWNER'));

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['Signing'])->toBe('Signed and acknowledged before a notary');
        expect($rules['Serve'])->toBe('The owner by certified mail, return receipt requested.');
    });
});

describe('Illinois notice of intent', function () {
    it('sends the owner and the general contractor a courtesy demand with the 10-day deadline', function () {
        $filing = lienFixtureFiling(lienFixtureProject('IL', 'Clinton'), 'noi');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('NOTICE OF INTENT TO FILE A CLAIM FOR LIEN')
            ->toContain('770 ILCS 60/1 et seq.')
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('To: Owner or reputed owner Mike Stuntz')
            ->toContain('To: Original (direct) contractor Ken Walker Builders')
            ->toContain('under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid')
            ->toContain('Unless payment in full of the amount stated above is received within 10 days after the date of this notice, Claimant intends to record a Claim for Lien against the property and its improvements');
    });
});

describe('Illinois rules', function () {
    it('keeps the county recorder, the PIN, the blank top of page 1, the sworn claim and every kind\'s service', function () {
        $il = LienDocumentRegistry::for('IL');
        $lien = $il['kinds']['mechanics_lien'];
        $release = $il['kinds']['lien_release'];
        $notice = $il['kinds']['prelim_notice'];
        $noi = $il['kinds']['noi'];

        expect($il['recording']['filing_office'])->toMatchArray(['label' => 'County Recorder', 'method' => 'erecord', 'address_lines' => [], 'vendor' => null]);
        expect($il['recording'])->toMatchArray(['parcel_label' => 'PIN', 'preparer_in_space' => false, 'top_margin_in' => 3.0, 'other_margin_in' => 1.0]);

        expect($lien)->toMatchArray([
            'title' => 'Claim for Lien',
            'statute' => '770 ILCS 60/7 and 60/28',
            'body' => 'documents.lien.instruments.bodies.generic-lien',
        ]);
        expect($lien['sections'])->toMatchArray(['amount' => 'breakdown', 'gc' => true, 'hiring_party' => true, 'contract_date' => true, 'contract_type' => true, 'prior_notice' => true]);
        expect($lien['execution'])->toMatchArray(['verification' => 'sworn', 'notary' => true, 'notary_form' => 'jurat', 'notary_variant' => null, 'statement' => true]);
        expect($lien['service'])->toMatchArray(['recipients' => ['owner'], 'days_after' => 10, 'method' => 'certified_mail']);
        expect($lien['clauses']['affirmations'])->toHaveCount(4);
        expect($lien['attachments'])->toBe(['A copy of the written contract, when the claim says the contract was written.']);

        expect($release)->toMatchArray([
            'title' => 'Release and Satisfaction of Mechanic\'s Lien',
            'statute' => '770 ILCS 60/35',
            'body' => 'documents.lien.instruments.bodies.generic-release',
        ]);
        expect($release['execution'])->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment']);
        expect($release['service'])->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail']);
        expect($release['clauses']['after_execution'])->toBe(['documents.lien.instruments.clauses.il-release-filing-statement']);
        expect($release['clauses']['notice_box'])->toBeNull();

        expect($notice)->toMatchArray([
            'title' => 'Subcontractor\'s Notice of Claim (90-Day Notice)',
            'statute' => '770 ILCS 60/24',
            'body' => 'documents.lien.letters.bodies.il-subcontractor-notice',
        ]);
        expect($notice['service'])->toMatchArray(['recipients' => ['owner', 'lender'], 'days_after' => null, 'method' => 'certified_mail']);
        expect($notice['execution']['notary'])->toBeFalse();

        expect($noi)->toMatchArray(['title' => 'Notice of Intent to File a Claim for Lien', 'body' => 'documents.lien.letters.bodies.generic-noi']);
        expect($noi['sections']['demand_days'])->toBe(10);
        expect($noi['service'])->toMatchArray(['recipients' => ['owner', 'gc'], 'days_after' => null, 'method' => 'certified_mail']);

        // Staff-facing notes: the open questions start "Undecided:" and no note names a person to ask.
        $notes = array_merge($il['recording']['notes'], ...array_map(fn (array $entry) => $entry['notes'], array_values($il['kinds'])));

        expect(collect($notes)->filter(fn (string $note) => str_starts_with($note, 'Undecided:')))->toHaveCount(2);

        foreach ($notes as $note) {
            expect($note)->not->toContain('Major');
        }

        expect(LienDocumentRegistry::county('IL', 'clinton'))->not->toBeNull();
        expect(LienDocumentRegistry::county('IL', 'sangamon'))->toBeNull();
    });
});
