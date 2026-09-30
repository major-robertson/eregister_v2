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

describe('Missouri statement of mechanic\'s lien', function () {
    it('renders the Jackson County statement with the index block, the itemized account, the ten-day notice and the sworn verification', function () {
        $filing = lienFixtureFiling(lienFixtureProject('MO', 'Jackson'), 'mechanics_lien', [
            'document_details_json' => ['notice_served_at' => '2026-06-20'],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        foreach ([
            "STATEMENT OF MECHANIC'S LIEN",
            'RSMo § 429.080',
            'STATE OF MISSOURI',
            'COUNTY OF JACKSON',
            "Title of document Statement of Mechanic's Lien",
            'Grantor (mailing address) Mike Stuntz, 9025 Baywood Park Dr, Seminole, MO 33777',
            'Grantee (mailing address) S G Roser Construction LLC, 4200 Lakeland Hwy, Lakeland, FL 33801',
            'Legal description BAYWOOD PARK LOT 25',
            'S G Roser Construction LLC ("Claimant") claims a lien under RSMo § 429.080 upon the real property',
            'Person who contracted with Claimant. Ken Walker Builders',
            'Date of the contract:',
            'After deducting all just credits and offsets, the amount claimed is $4,213.75.',
            'Amount claimed, after deducting all just credits and offsets $ 4,213.75',
            'This statement, with the itemized account attached as Exhibit A, is a just and true account of the demand due Claimant after all just credits have been given (RSMo § 429.080).',
            'This statement is filed within six months after the indebtedness accrued (RSMo § 429.080).',
            "Notice before filing. On June 20, 2026, Claimant served notice on the owner that it holds a claim against the building or improvement, stating the amount and from whom it is due. That notice was served at least ten days before this Statement of Mechanic's Lien was filed (RSMo § 429.100).",
            'The undersigned, being first duly sworn, states that he or she is the President of S G Roser Construction LLC',
            'Subscribed and sworn to (or affirmed) before me',
            'My commission expires',
        ] as $fragment) {
            expect($text)->toContain($fragment);
        }

        // The generic "Prior notice" item would name the § 429.012 notice to owner, so it stays off.
        expect($text)->not->toContain('Prior notice.')->not->toContain('N/A');

        // Jackson rejected anything inside the 3" space: the preparer prints below the rule, then the index block.
        expect(strpos($text, 'Space above this line'))->toBeLessThan(strpos($text, 'Prepared by, recording requested by'));
        expect(strpos($text, 'Grantor (mailing address)'))->toBeLessThan(strpos($text, "STATEMENT OF MECHANIC'S LIEN"));

        // The account, the affirmations and the ten-day recital all sit under the oath.
        $positions = array_map(fn (string $needle) => strpos($text, $needle), [
            'After deducting all just credits',
            'is a just and true account',
            'within six months after the indebtedness accrued',
            'Notice before filing.',
            'being first duly sworn',
        ]);
        $sorted = $positions;
        sort($sorted);

        expect($positions)->not->toContain(false)->toBe($sorted);
    });

    it('leaves the ten-day notice date blank until it is set and drops the recital for an original contractor', function () {
        $generator = app(LienDocumentGenerator::class);

        $unset = lienFixtureText($generator->render(lienFixtureFiling(lienFixtureProject('MO', 'Jackson'), 'mechanics_lien')));

        expect($unset)->toContain('Notice before filing. On , Claimant served notice on the owner that it holds a claim');

        $original = lienFixtureProject('MO', 'Clay', ['claimant_type' => ClaimantType::Gc, 'hired_by' => 'owner']);
        $text = lienFixtureText($generator->render(lienFixtureFiling($original, 'mechanics_lien', [
            'document_details_json' => ['notice_served_at' => '2026-06-20'],
        ])));

        expect($text)
            ->toContain('as a direct contractor in contract with the owner')
            ->not->toContain('Notice before filing.')
            ->not->toContain('RSMo § 429.100');
    });

    it('keeps the index block statewide, moves the preparer below the rule only in Jackson County and lists Exhibit A', function () {
        $resolver = app(LienDocumentResolver::class);
        $jackson = $resolver->resolve(lienFixtureFiling(lienFixtureProject('MO', 'Jackson'), 'mechanics_lien'));
        $clay = $resolver->resolve(lienFixtureFiling(lienFixtureProject('MO', 'Clay'), 'mechanics_lien'));

        foreach ([$jackson, $clay] as $form) {
            expect($form->title)->toBe("Statement of Mechanic's Lien");
            expect($form->statute)->toBe('RSMo § 429.080');
            expect($form->recording['index_block'])->toBeTrue();
            expect($form->recording['index_roles'])->toBe(['grantor' => 'owner', 'grantee' => 'claimant']);
            expect($form->sections)->toMatchArray(['amount' => 'itemized', 'gc' => true, 'hiring_party' => true, 'contract_date' => true, 'prior_notice' => false]);
            expect($form->clauses['before_signature'])->toBe(['documents.lien.instruments.clauses.mo-ten-day-notice']);
            expect($form->execution)->toMatchArray(['verification' => 'sworn', 'notary' => true, 'notary_form' => 'jurat']);
            expect($form->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail']);
            expect($form->attachments)->toBe(['Exhibit A: itemized account of the labor and materials furnished and the balance unpaid (RSMo § 429.080)']);
        }

        expect($jackson->recording['preparer_in_space'])->toBeFalse();
        expect($clay->recording['preparer_in_space'])->toBeTrue();

        // The county file keeps what the Recorder of Deeds accepted; the state names the circuit clerk (RSMo § 429.080).
        expect($jackson->recording['filing_office'])->toMatchArray(['label' => 'Jackson County Recorder of Deeds', 'method' => 'erecord', 'vendor' => 'CSC (ep.erecording.com)']);
        expect($clay->recording['filing_office'])->toMatchArray(['label' => 'Clerk of the Circuit Court (county where the property is located)', 'method' => 'mail']);

        expect(implode(' ', $jackson->notes))
            ->toContain('RSMo § 429.080 says the lien is filed with the clerk of the circuit court')
            ->toContain('16th Judicial Circuit');
        expect(implode(' ', $clay->notes))
            ->toContain('RSMo § 429.080 says the lien is filed with the clerk of the circuit court')
            ->not->toContain('16th Judicial Circuit');
    });

    it('describes where a Missouri statement files and how it is signed, served and supported', function () {
        $filing = lienFixtureFiling(lienFixtureProject('MO', 'Clay'), 'mechanics_lien');

        $rules = collect(LienDocumentPackage::forFiling($filing)->rules())->pluck('value', 'label');

        expect($rules['File with'])->toBe('Clerk of the Circuit Court (county where the property is located), by mail');
        expect($rules['Signing'])->toBe('Sworn to and signed before a notary (jurat)');
        expect($rules['Serve'])->toBe('The owner by certified mail, return receipt requested.');
        expect($rules['Attach'])->toBe('Exhibit A: itemized account of the labor and materials furnished and the balance unpaid (RSMo § 429.080)');
    });
});

describe('Missouri notice of claim and intent', function () {
    it('renders the § 429.100 notice of the claim, the amount and the ten-day wait, proved by the server\'s affidavit', function () {
        $filing = lienFixtureFiling(lienFixtureProject('MO', 'Jackson'), 'noi');

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain("NOTICE OF CLAIM AND INTENT TO FILE MECHANIC'S LIEN")
            ->toContain('RSMo § 429.100')
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('To: Owner or reputed owner Mike Stuntz')
            ->toContain('under a contract with Ken Walker Builders, and that $4,213.75 remains unpaid.')
            ->toContain('Amount unpaid $4,213.75')
            ->toContain("Under RSMo § 429.100, Claimant gives you notice that it holds a claim against the building or improvement on the property described above. The amount of the claim is the amount unpaid stated above. It is due from the person with whom Claimant contracted, named above. If the claim is not paid, Claimant intends to file its mechanic's lien against the property no sooner than ten days after this notice is served on you.")
            ->not->toContain('Unless payment in full')
            ->not->toContain('My commission expires');

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->title)->toBe("Notice of Claim and Intent to File Mechanic's Lien");
        expect($form->body)->toBe('documents.lien.letters.bodies.generic-noi');
        expect($form->section('demand_days'))->toBe(10);
        expect($form->service)->toMatchArray(['recipients' => ['owner'], 'days_after' => null, 'method' => 'certified_mail', 'proof' => 'affidavit']);
        expect($form->notaryRequired())->toBeFalse();
    });
});

describe('Missouri notice to owner', function () {
    it('prints the § 429.012 disclosure verbatim in ten-point bold type above the signature', function () {
        $project = lienFixtureProject('MO', 'Jackson', ['claimant_type' => ClaimantType::Gc, 'hired_by' => 'owner']);
        $filing = lienFixtureFiling($project, 'prelim_notice');
        $disclosure = 'FAILURE OF THIS CONTRACTOR TO PAY THOSE PERSONS SUPPLYING MATERIAL OR SERVICES TO COMPLETE THIS CONTRACT CAN RESULT IN THE FILING OF A MECHANIC\'S LIEN ON THE PROPERTY WHICH IS THE SUBJECT OF THIS CONTRACT PURSUANT TO CHAPTER 429, RSMO. TO AVOID THIS RESULT YOU MAY ASK THIS CONTRACTOR FOR "LIEN WAIVERS" FROM ALL PERSONS SUPPLYING MATERIAL OR SERVICES FOR THE WORK DESCRIBED IN THIS CONTRACT. FAILURE TO SECURE LIEN WAIVERS MAY RESULT IN YOUR PAYING FOR LABOR AND MATERIAL TWICE.';

        $pdf = app(LienDocumentGenerator::class)->render($filing);
        $text = lienFixtureText($pdf);

        expect($text)
            ->toContain("NOTICE TO OWNER (ORIGINAL CONTRACTOR'S DISCLOSURE)")
            ->toContain('RSMo § 429.012')
            ->toContain('Via certified mail, return receipt requested')
            ->toContain('To: Person who contracted with the claimant Ken Walker Builders')
            ->toContain('The contractor named below gives you this notice under RSMo § 429.012 about its contract to improve the property described below.')
            ->toContain('NOTICE TO OWNER '.$disclosure)
            ->not->toContain('My commission expires');

        // § 429.012.1: "in ten-point bold type", so exactly 10pt on the disclosure itself, not the 12pt .bold-statement.
        expect($pdf->getHtml())
            ->toContain('<p style="font-size: 10pt; font-weight: bold;">'.$disclosure.'</p>')
            ->not->toContain('class="bold-statement"');

        expect(strpos($text, $disclosure))->toBeLessThan(strpos($text, 'By (signature)'));

        $form = app(LienDocumentResolver::class)->resolve($filing);

        expect($form->body)->toBe('documents.lien.letters.bodies.mo-notice-to-owner');
        expect($form->recipientRoles())->toBe(['customer']);
        expect($form->service['method'])->toBe('certified_mail');
        expect($form->notaryRequired())->toBeFalse();
    });
});

describe('Missouri release', function () {
    it('renders the release with the acknowledgment and the lien it releases in the index block', function () {
        $filing = lienFixtureFiling(lienFixtureProject('MO', 'Clay'), 'lien_release', [
            'document_details_json' => ['original_lien' => [
                'recording_reference' => 'Instrument 2026E0071234',
                'recorded_at' => '2026-07-22',
                'county' => 'Clay',
            ]],
        ]);

        $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

        expect($text)
            ->toContain("RELEASE OF MECHANIC'S LIEN")
            ->toContain('RSMo § 429.120')
            ->toContain('COUNTY OF CLAY')
            ->toContain('Grantor (mailing address) S G Roser Construction LLC, 4200 Lakeland Hwy, Lakeland, FL 33801')
            ->toContain('Grantee (mailing address) Mike Stuntz, 9025 Baywood Park Dr, Seminole, MO 33777')
            ->toContain("Reference Statement of Mechanic's Lien recorded July 22, 2026 as Instrument 2026E0071234")
            ->toContain("is the claimant under that certain Statement of Mechanic's Lien recorded on July 22, 2026 as Instrument 2026E0071234 in the official records of Clay County, Missouri")
            ->toContain('authorizes and directs the Clerk of the Circuit Court (county where the property is located) to cancel it of record.')
            ->toContain("This release is Claimant's acknowledgment of satisfaction of the lien under RSMo § 429.120.")
            ->toContain('before me, the undersigned notary public, personally appeared Steven Roser, President of S G Roser Construction LLC')
            ->toContain('acknowledged that he or she executed it in that capacity on behalf of S G Roser Construction LLC for the purposes stated in it.')
            ->not->toContain('being first duly sworn')
            ->not->toContain('Notice before filing.');
    });
});
