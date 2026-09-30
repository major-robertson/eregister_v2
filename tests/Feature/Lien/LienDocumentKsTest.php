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

describe('Kansas mechanic\'s lien statement', function () {
    it('renders the Johnson County statement with the license, contract date, affirmations, sworn verification and MOU legend', function () {
        $filing = lienFixtureFiling(lienFixtureProject('KS', 'Johnson'), 'mechanics_lien', [
            'document_details_json' => ['contract_date' => '2026-03-05', 'license_number' => 'JC-2026-0010245'],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            "MECHANIC'S LIEN STATEMENT",
            'K.S.A. 60-1102 and 60-1103',
            'STATE OF KANSAS',
            'COUNTY OF JOHNSON',
            'S G Roser Construction LLC ("Claimant") claims a lien under K.S.A. 60-1102 and 60-1103 upon the real property and improvements described below',
            "Contractor's license number: JC-2026-0010245",
            'Claimant furnished the labor, services, equipment or materials described below as a subcontractor.',
            'Person who contracted with Claimant. Ken Walker Builders',
            'Date of the contract: March 5, 2026',
            'Property subject to the lien. 9025 Baywood Park Dr, Seminole, KS 33777 (Johnson County, Kansas)',
            'After deducting all just credits and offsets, the amount claimed is $4,213.75.',
            'Amount claimed, after deducting all just credits and offsets $ 4,213.75',
            'Prior notice. Claimant served its Warning Statement (Notice to Owner) on August 7, 2026',
            // K.S.A. 60-1102(a) and 60-1103(a)(1) contents, inside the verified statement.
            "Claimant's address stated above is sufficient for service of process (K.S.A. 60-1102(a)(2)).",
            'A reasonably itemized statement of the claim, or a copy of the written instrument or promissory note that evidences it, is attached as Exhibit A and is part of this statement (K.S.A. 60-1102(a)(4)).',
            'If Claimant is a subcontractor or supplier, the contractor is named above, either as the person who contracted with Claimant or as the original (general) contractor (K.S.A. 60-1103(a)(1)).',
            'This statement is filed within the time K.S.A. 60-1102 and 60-1103 allow after Claimant last furnished labor, equipment, material or supplies: four months for an original contractor, three months for a subcontractor or supplier, or five months after a timely notice of extension.',
            "The undersigned, being first duly sworn, states that he or she is the President of S G Roser Construction LLC, the claimant named above; that he or she is authorized to make this Mechanic's Lien Statement on its behalf",
            'and that the statements in it are true of his or her own knowledge.',
            'Subscribed and sworn to (or affirmed) before me on this',
            'by Steven Roser, President of S G Roser Construction LLC, who is personally known to me',
            'My commission expires',
            "Notary's printed or typed name",
            'Submitted electronically by eRegister in compliance with Kansas statutes governing recordable documents and the terms of the Memorandum of Understanding with the Johnson County Register of Deeds. K.S.A. 28-115.',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // An acknowledgment or a best-knowledge verification does not verify a Kansas lien statement.
        expect($text)->not->toContain('best of')->not->toContain('acknowledged before me')->not->toContain('N/A');

        // Page 1's top three inches stay clear: the legend, then the preparer block, print below the rule.
        expect(strpos($text, 'Space above this line'))->toBeLessThan(strpos($text, 'Submitted electronically by eRegister'))
            ->and(strpos($text, 'Submitted electronically by eRegister'))->toBeLessThan(strpos($text, 'Prepared by, recording requested by and return to'))
            ->and(strpos($text, 'Prepared by, recording requested by and return to'))->toBeLessThan(strpos($text, "MECHANIC'S LIEN STATEMENT"));
    });

    it('merges the Johnson County recording facts and leaves other counties on the statutory office', function () {
        $johnsonFiling = lienFixtureFiling(lienFixtureProject('KS', 'Johnson'), 'mechanics_lien');
        $sedgwickFiling = lienFixtureFiling(lienFixtureProject('KS', 'Sedgwick'), 'mechanics_lien');

        $johnson = app(LienDocumentResolver::class)->resolve($johnsonFiling);
        $sedgwick = app(LienDocumentResolver::class)->resolve($sedgwickFiling);

        expect($johnson->countyKey)->toBe('johnson');
        expect($johnson->recording['legend'])->toBe('Submitted electronically by eRegister in compliance with Kansas statutes governing recordable documents and the terms of the Memorandum of Understanding with the Johnson County Register of Deeds. K.S.A. 28-115.');
        expect($johnson->recording['preparer_in_space'])->toBeFalse();
        expect($johnson->recording['filing_office'])->toMatchArray([
            'label' => 'Johnson County Register of Deeds',
            'method' => 'erecord',
            'address_lines' => ['111 S. Cherry St., Ste 1200', 'Olathe, KS 66061'],
            'vendor' => null,
        ]);
        expect($johnson->recording['fee_note'])->toBe('$17 for a five-page instrument e-recorded (June 2026).');
        expect($johnson->notes)->toContain('Johnson County filings are set to e-record with the Register of Deeds, as the June 2026 lien was; K.S.A. 60-1102 names the clerk of the district court, so switch this county back if counsel says that office perfects the lien.');

        expect($sedgwick->countyKey)->toBe('sedgwick');
        expect($sedgwick->recording['legend'])->toBeNull();
        expect($sedgwick->recording['preparer_in_space'])->toBeTrue();
        expect($sedgwick->recording['filing_office'])->toMatchArray([
            'label' => 'Clerk of the District Court (county where the property is located)',
            'method' => 'mail',
            'address_lines' => [],
        ]);

        $johnsonRules = collect(LienDocumentPackage::forFiling($johnsonFiling)->rules())->pluck('value', 'label');
        $sedgwickRules = collect(LienDocumentPackage::forFiling($sedgwickFiling)->rules())->pluck('value', 'label');

        expect($johnsonRules['File with'])->toBe('Johnson County Register of Deeds, e-recording (111 S. Cherry St., Ste 1200, Olathe, KS 66061)');
        expect($johnsonRules['Signing'])->toBe('Sworn to and signed before a notary (jurat)');
        expect($sedgwickRules['File with'])->toBe('Clerk of the District Court (county where the property is located), by mail');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($sedgwickFiling));

        expect($text)->toContain('COUNTY OF SEDGWICK')->not->toContain('Submitted electronically by eRegister');
        expect(strpos($text, 'Prepared by, recording requested by'))->toBeLessThan(strpos($text, 'Space above this line'));
    });
});

describe('Kansas warning statement', function () {
    it('renders the K.S.A. 60-1103a(c) statement verbatim with the claimant, job, residence and contractor filled in, then the owner acknowledgment', function () {
        $filing = lienFixtureFiling(lienFixtureProject('KS', 'Johnson', ['job_number' => 'BP-2026-07']), 'prelim_notice');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain('WARNING STATEMENT (NOTICE TO OWNER)')
            ->toContain('K.S.A. 60-1103a')
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('To: Owner or reputed owner Mike Stuntz 9025 Baywood Park Dr Seminole, KS 33777')
            ->toContain('Notice to owner: S G Roser Construction LLC is a supplier or subcontractor providing materials or labor on Job No. BP-2026-07 at 9025 Baywood Park Dr, Seminole, KS 33777 under an agreement with Ken Walker Builders. Kansas law will allow this supplier or subcontractor to file a lien against your property for materials or labor not paid for by your contractor unless you have a waiver of lien signed by this supplier or subcontractor. If you receive a notice of filing of a lien statement by this supplier or subcontractor, you may withhold from your contractor the amount claimed until the dispute is settled.')
            ->toContain('CLAIMANT: S G Roser Construction LLC')
            ->toContain("Owner's acknowledgment (K.S.A. 60-1103a(b)(2))")
            ->toContain('The undersigned, an owner of the property described above, states that the warning statement above was given to the undersigned by (check one): the claimant named above the general contractor.')
            ->toContain('Signature of owner')
            ->toContain('Printed name of owner')
            ->not->toContain('My commission expires')
            ->not->toContain('N/A');

        // The claimant signs the statement; the owner's acknowledgment follows the signature.
        expect(strpos($text, 'Notice to owner:'))->toBeLessThan(strpos($text, 'By (signature)'))
            ->and(strpos($text, 'By (signature)'))->toBeLessThan(strpos($text, "Owner's acknowledgment"));
    });

    it('names the general contractor, else the party the claimant contracted with, and rules a blank for a missing job number', function () {
        $project = lienFixtureProject('KS', 'Johnson', ['job_number' => null]);
        $project->parties()->where('role', PartyRole::Customer->value)->update(['name' => 'Pat Lane', 'company_name' => 'Lane Drywall LLC']);
        $generator = app(LienDocumentGenerator::class);

        $pdf = $generator->render(lienFixtureFiling($project, 'prelim_notice'));

        expect(lienFixtureText($pdf))->toContain('providing materials or labor on Job No. at 9025 Baywood Park Dr, Seminole, KS 33777 under an agreement with Ken Walker Builders.');
        expect($pdf->getHtml())->toContain('Job No. <span class="fill fill-mid">&nbsp;</span> at');

        $project->parties()->where('role', PartyRole::Gc->value)->delete();

        expect(lienFixtureText($generator->render(lienFixtureFiling($project, 'prelim_notice'))))
            ->toContain('under an agreement with Lane Drywall LLC.');
    });
});

describe('Kansas release', function () {
    it('releases the recorded statement and is acknowledged before a notary', function () {
        $filing = lienFixtureFiling(lienFixtureProject('KS', 'Johnson'), 'lien_release', [
            'document_details_json' => ['original_lien' => [
                'recording_reference' => 'Book 202606 Page 007342',
                'recorded_at' => '2026-06-25',
                'county' => 'Johnson',
            ]],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain("RELEASE OF MECHANIC'S LIEN")
            ->toContain('K.S.A. 60-1101 et seq.')
            ->toContain('COUNTY OF JOHNSON')
            ->toContain("Releases Mechanic's Lien Statement recorded June 25, 2026 as Book 202606 Page 007342")
            ->toContain('KNOW ALL PERSONS BY THESE PRESENTS that S G Roser Construction LLC ("Claimant"), whose address is 4200 Lakeland Hwy, Lakeland, FL 33801,')
            ->toContain("is the claimant under that certain Mechanic's Lien Statement recorded on June 25, 2026 as Book 202606 Page 007342 in the official records of Johnson County, Kansas, against the real property described below, owned by Mike Stuntz:")
            ->toContain("hereby releases, discharges and cancels the lien and the Mechanic's Lien Statement of record, and authorizes and directs the Johnson County Register of Deeds to cancel it of record.")
            ->toContain('before me, the undersigned notary public, personally appeared Steven Roser, President of S G Roser Construction LLC, personally known to me or proved to me on the basis of satisfactory evidence')
            ->toContain('and acknowledged that he or she executed it in that capacity on behalf of S G Roser Construction LLC for the purposes stated in it.')
            ->not->toContain('being first duly sworn')
            ->not->toContain('Subscribed and sworn')
            ->not->toContain('Amount claimed');
    });
});

describe('Kansas rules', function () {
    it('keeps the statutory filing office with the open question, the sworn jurat, owner service and the courtesy notice of intent', function () {
        $ks = LienDocumentRegistry::for('KS');
        $lien = $ks['kinds']['mechanics_lien'];
        $warning = $ks['kinds']['prelim_notice'];

        expect($ks['recording']['filing_office'])->toMatchArray([
            'label' => 'Clerk of the District Court (county where the property is located)',
            'method' => 'mail',
            'address_lines' => [],
        ]);
        expect($ks['recording']['notes'][0])
            ->toContain('e-recorded with the Register of Deeds under the eRegister MOU')
            ->toContain('Major must confirm with counsel which office perfects the lien before the next Kansas filing');

        expect($lien['title'])->toBe('Mechanic\'s Lien Statement');
        expect($lien['body'])->toBe('documents.lien.instruments.bodies.generic-lien');
        expect($lien['sections'])->toMatchArray(['amount' => 'itemized', 'license' => true, 'contract_date' => true, 'gc' => true, 'hiring_party' => true, 'prior_notice' => true]);
        expect($lien['execution'])->toMatchArray(['verification' => 'sworn', 'notary' => true, 'notary_form' => 'jurat', 'notary_variant' => null, 'statement' => true]);
        expect($lien['service'])->toMatchArray(['recipients' => ['owner'], 'method' => 'certified_mail']);
        expect($lien['clauses']['affirmations'])->toHaveCount(4);
        expect($lien['attachments'])->toHaveCount(2);
        expect($lien['attachments'][0])->toStartWith('Exhibit A: itemized statement of the claim, or a copy of the written contract or promissory note');

        expect($warning['body'])->toBe('documents.lien.letters.bodies.ks-warning-statement');
        expect($warning['clauses']['after_execution'])->toBe(['documents.lien.letters.clauses.ks-owner-acknowledgment']);
        expect($warning['service'])->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail']);
        expect($warning['execution']['notary'])->toBeFalse();

        expect($ks['kinds']['lien_release']['execution'])->toMatchArray(['verification' => 'acknowledged', 'notary' => true, 'notary_form' => 'acknowledgment']);
        expect($ks['kinds']['noi']['body'])->toBe('documents.lien.letters.bodies.generic-noi');
        expect($ks['kinds']['noi']['sections']['demand_days'])->toBe(10);

        expect(LienDocumentRegistry::county('KS', 'sedgwick'))->toBeNull();
    });
});
