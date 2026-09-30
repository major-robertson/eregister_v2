<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Documents\LienCountyKey;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Documents\LienDocumentResolver;
use App\Domains\Lien\Documents\LienDocumentUnavailable;
use App\Domains\Lien\Documents\ResolvedLienDocument;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Waivers\WaiverStateRegistry;

beforeEach(fn () => LienDocumentRegistry::flush());
afterEach(fn () => LienDocumentRegistry::flush());

if (! function_exists('liendocFiling')) {
    /**
     * A paid filing of the given kind, with its project in $state and the
     * given county (null keeps the factory's county).
     */
    function liendocFiling(string $kind, string $state, ?string $county = null, array $filingOverrides = [], array $projectOverrides = []): LienFiling
    {
        $project = LienProject::factory()
            ->forBusiness(Business::factory()->create())
            ->create(array_merge([
                'jobsite_state' => $state,
                'jobsite_county' => $county ?? 'Test County',
            ], $projectOverrides));

        $type = LienDocumentType::where('slug', $kind)->firstOrFail();

        return LienFiling::factory()->forProject($project)->paid()->create(array_merge([
            'document_type_id' => $type->id,
        ], $filingOverrides));
    }
}

describe('registry', function () {
    it('loads all 50 states with every default key merged in', function () {
        $all = LienDocumentRegistry::all();

        expect($all)->toHaveCount(50);
        expect(array_keys($all))->toBe(array_keys(WaiverStateRegistry::STATE_NAMES));

        foreach ($all as $code => $rules) {
            expect($rules['state'])->toBe($code);
            expect($rules['state_name'])->toBe(WaiverStateRegistry::STATE_NAMES[$code]);
            expect($rules)->toHaveKeys(['attorney_only', 'recording', 'execution', 'service', 'kinds']);

            expect($rules['recording'])->toHaveKeys([
                'filing_office', 'top_margin_in', 'other_margin_in', 'min_font_pt', 'page_numbers',
                'caption', 'index_line', 'index_block', 'index_roles', 'preparer_in_space', 'legend', 'cover_sheet', 'parcel_label',
                'fee_note', 'adds_cover_page', 'notes',
            ]);
            expect($rules['recording']['filing_office'])->toHaveKeys(['label', 'method', 'address_lines', 'vendor']);
            expect($rules['recording']['top_margin_in'])->toBeGreaterThanOrEqual(1.0);
            expect($rules['execution'])->toHaveKeys(['verification', 'notary', 'notary_form', 'notary_variant', 'witness']);
            expect($rules['service'])->toHaveKeys(['recipients', 'days_after', 'method', 'proof', 'certificate_on_instrument', 'perjury_state']);

            expect(array_keys($rules['kinds']))->toEqualCanonicalizing(LienDocumentRegistry::KINDS);

            foreach ($rules['kinds'] as $kind => $entry) {
                expect($entry)->toHaveKeys([
                    'enabled', 'disabled_reason', 'title', 'statute', 'body', 'template_version',
                    'sections', 'clauses', 'execution', 'service', 'attachments', 'notes',
                ]);
                expect($entry['title'])->toBeString()->not->toBe('');
                expect($entry['template_version'])->toBeInt();
                expect($entry['body'])->toStartWith('documents.lien.');
                expect($entry['sections'])->toHaveKeys(['amount', 'gc', 'lender', 'prior_notice', 'first_furnish', 'last_furnish']);
                expect($entry['clauses'])->toHaveKeys(['notice_box', 'bold_statement', 'after_property', 'before_signature', 'affirmations', 'demand']);
                expect($entry['execution'])->toHaveKeys(['verification', 'notary', 'notary_form']);
                expect($entry['service']['recipients'])->toBeArray()->not->toBeEmpty();

                foreach ($entry['service']['recipients'] as $role) {
                    expect($role)->toBeIn(['owner', 'gc', 'lender', 'customer', 'subcontractor', 'other']);
                }

                if (! $entry['enabled']) {
                    expect($entry['disabled_reason'])->toBeString()->not->toBe('');
                }
            }
        }
    });

    it('normalizes lowercase state codes to the same rules', function () {
        expect(LienDocumentRegistry::for('fl'))->toBe(LienDocumentRegistry::for('FL'));
        expect(LienDocumentRegistry::isSupported('tx'))->toBeTrue();
        expect(LienDocumentRegistry::isSupported('ZZ'))->toBeFalse();
    });

    it('seeds a state without a data file from the lien state rules', function () {
        // New Mexico has no lien_documents file: notarized, sworn, served on the
        // owner 15 days after recording, prelim to owner and GC, per the seed.
        $nm = LienDocumentRegistry::for('NM');

        expect($nm['execution']['notary'])->toBeTrue();
        expect($nm['execution']['verification'])->toBe('sworn');
        expect($nm['execution']['notary_form'])->toBe('jurat');
        expect($nm['kinds']['mechanics_lien']['service']['recipients'])->toBe(['owner']);
        expect($nm['kinds']['mechanics_lien']['service']['days_after'])->toBe(15);
        expect($nm['kinds']['prelim_notice']['service']['recipients'])->toBe(['owner', 'gc']);
        expect($nm['kinds']['prelim_notice']['execution']['notary'])->toBeFalse();
        expect($nm['kinds']['mechanics_lien']['body'])->toBe('documents.lien.instruments.bodies.generic-lien');
        expect($nm['recording']['filing_office']['label'])->toBe('County recorder');
    });

    it('marks the attorney-referral states', function () {
        foreach (['HI', 'MD', 'DE'] as $state) {
            expect(LienDocumentRegistry::for($state)['attorney_only'])->toBeTrue();
        }

        expect(LienDocumentRegistry::for('FL')['attorney_only'])->toBeFalse();
    });

    it('keeps the Florida statutory pieces', function () {
        $fl = LienDocumentRegistry::for('FL');
        $lien = $fl['kinds']['mechanics_lien'];
        $nto = $fl['kinds']['prelim_notice'];

        expect($lien['body'])->toBe('documents.lien.instruments.bodies.fl-claim-of-lien');
        expect($lien['title'])->toBe('Claim of Lien');
        expect($lien['clauses']['notice_box'])->toStartWith('WARNING! THIS LEGAL DOCUMENT REFLECTS THAT A CONSTRUCTION LIEN');
        expect($lien['sections']['amount_in_words'])->toBeTrue();
        expect($lien['execution'])->toMatchArray(['verification' => 'sworn', 'notary' => true, 'notary_form' => 'jurat', 'notary_variant' => 'fl']);
        expect($lien['service']['days_after'])->toBe(15);

        expect($nto['body'])->toBe('documents.lien.letters.bodies.fl-notice-to-owner');
        expect($nto['clauses']['notice_box'])->toContain("FLORIDA'S CONSTRUCTION LIEN LAW ALLOWS SOME UNPAID CONTRACTORS");
        expect($nto['clauses']['before_signature'])->toContain('IMPORTANT INFORMATION FOR YOUR PROTECTION');
        expect($nto['service']['recipients'])->toBe(['owner', 'gc', 'lender']);
        expect($nto['execution']['notary'])->toBeFalse();

        expect($fl['kinds']['lien_release']['execution']['notary_form'])->toBe('acknowledgment');
    });

    it('keeps the North Carolina form, certification and cover sheet', function () {
        $nc = LienDocumentRegistry::for('NC');
        $lien = $nc['kinds']['mechanics_lien'];

        expect($lien['body'])->toBe('documents.lien.instruments.bodies.nc-claim-of-lien');
        expect($lien['clauses']['affirmations'][0])->toBe('I hereby certify that I have served the parties listed in (2) above in accordance with the requirements of G.S. 44A-11.');
        expect($lien['sections']['lien_agent'])->toBeTrue();
        expect($lien['execution']['notary_variant'])->toBe('nc');
        expect($nc['recording']['cover_sheet'])->toBeTrue();
        expect($nc['recording']['parcel_label'])->toBe('PIN');
        expect($nc['kinds']['prelim_notice']['title'])->toBe('Notice to Lien Agent');
    });

    it('keeps the California verified, un-notarized claim with its notice and embedded proof of service', function () {
        $ca = LienDocumentRegistry::for('CA');
        $lien = $ca['kinds']['mechanics_lien'];

        expect($lien['execution'])->toMatchArray(['verification' => 'verified', 'notary' => false, 'notary_form' => null]);
        expect($lien['clauses']['notice_box'])->toBe('documents.lien.instruments.clauses.ca-notice-of-mechanics-lien');
        expect($lien['service']['certificate_on_instrument'])->toBeTrue();
        expect($ca['kinds']['prelim_notice']['body'])->toBe('documents.lien.letters.bodies.ca-preliminary-notice');
        expect($ca['kinds']['prelim_notice']['service']['recipients'])->toBe(['owner', 'gc', 'lender']);
        expect($ca['kinds']['lien_release']['execution']['notary'])->toBeTrue();
        expect($ca['kinds']['lien_release']['service']['certificate_on_instrument'])->toBeFalse();
    });

    it('keeps the Texas months-of-work affidavit and the notice of claim', function () {
        $tx = LienDocumentRegistry::for('TX');
        $lien = $tx['kinds']['mechanics_lien'];

        expect($lien['sections'])->toMatchArray(['months_of_work' => true, 'retainage' => true, 'prior_notice' => true]);
        expect($lien['service'])->toMatchArray(['recipients' => ['owner', 'gc'], 'days_after' => 5]);
        expect($tx['kinds']['prelim_notice']['body'])->toBe('documents.lien.letters.bodies.tx-notice-of-claim');
        expect($tx['kinds']['prelim_notice']['statute'])->toBe('Tex. Prop. Code § 53.056');
        expect($tx['recording']['parcel_label'])->toBe('Property ID');
    });

    it('keeps the Arizona affidavit items and the twenty day notice recipients', function () {
        $az = LienDocumentRegistry::for('AZ');
        $lien = $az['kinds']['mechanics_lien'];

        expect($lien['sections'])->toMatchArray(['license' => true, 'contract_type' => true, 'completion_date' => true]);
        expect($lien['attachments'])->toHaveCount(2);
        expect($az['kinds']['prelim_notice']['service']['recipients'])->toBe(['owner', 'gc', 'lender', 'customer']);
        expect($az['kinds']['prelim_notice']['service']['days_after'])->toBe(20);
        // The body prints the Notice to Property Owner where § 33-992.01(D) puts it; the receipt follows the signature.
        expect($az['kinds']['prelim_notice']['clauses']['notice_box'])->toBeNull();
        expect($az['kinds']['prelim_notice']['clauses']['after_execution'])->toBe(['documents.lien.letters.clauses.az-acknowledgment-of-receipt']);
    });

    it('keeps the Georgia 395-day statement and the cancellation block', function () {
        $ga = LienDocumentRegistry::for('GA');
        $lien = $ga['kinds']['mechanics_lien'];

        expect($lien['clauses']['bold_statement'])->toBe('This claim of lien expires and is void 395 days from the date of filing of the claim of lien if no notice of commencement of lien action is filed in that time period.');
        expect($lien['clauses']['before_signature'][0])->toContain('44-14-368');
        expect($lien['sections']['cancellation_block'])->toBeTrue();
        expect($ga['kinds']['prelim_notice']['title'])->toBe('Notice to Contractor');
    });

    it('every referenced body and clause view exists', function () {
        foreach (LienDocumentRegistry::all() as $rules) {
            foreach ($rules['kinds'] as $entry) {
                if (! $entry['enabled']) {
                    continue;
                }

                expect(view()->exists($entry['body']))->toBeTrue("missing view {$entry['body']}");

                $referenced = array_merge(
                    [$entry['clauses']['notice_box'] ?? null],
                    (array) ($entry['clauses']['after_property'] ?? []),
                    (array) ($entry['clauses']['before_signature'] ?? []),
                    (array) ($entry['clauses']['after_execution'] ?? []),
                );

                foreach ($referenced as $value) {
                    if (is_string($value) && str_starts_with($value, 'documents.lien.')) {
                        expect(view()->exists($value))->toBeTrue("missing clause view {$value}");
                    }
                }
            }
        }
    });
});

describe('county files', function () {
    it('loads a county file and leaves unknown counties to the state', function () {
        $jackson = LienDocumentRegistry::county('MO', 'jackson');

        expect($jackson)->not->toBeNull();
        expect($jackson['state'])->toBe('MO');
        expect($jackson['key'])->toBe('jackson');
        expect($jackson['recording']['index_block'])->toBeTrue();
        expect($jackson['recording']['index_roles'])->toBe(['grantor' => 'owner', 'grantee' => 'claimant']);
        expect($jackson['notes'])->not->toBeEmpty();

        expect(LienDocumentRegistry::county('MO', 'clay'))->toBeNull();
        expect(LienDocumentRegistry::county('MO', null))->toBeNull();
        expect(LienDocumentRegistry::county('mo', 'jackson'))->toBe($jackson);
    });

    it('normalizes county names into file keys', function (?string $input, ?string $expected) {
        expect(LienCountyKey::normalize($input))->toBe($expected);
    })->with([
        ['Los Angeles County', 'los-angeles'],
        ['Los Angeles', 'los-angeles'],
        ['  Jackson   County ', 'jackson'],
        ['St. Louis City', 'st-louis-city'],
        ['St. Louis County', 'st-louis'],
        ["Prince George's County", 'prince-georges'],
        ['Orleans Parish', 'orleans'],
        ['Matanuska-Susitna Borough', 'matanuska-susitna'],
        ['County', null],
        ['', null],
        [null, null],
    ]);

    it('prints the county without the County suffix in captions', function () {
        expect(LienCountyKey::displayName('Jackson County'))->toBe('Jackson');
        expect(LienCountyKey::displayName('Jackson'))->toBe('Jackson');
        expect(LienCountyKey::displayName(' Orleans Parish '))->toBe('Orleans');
        expect(LienCountyKey::displayName(''))->toBeNull();
    });
});

describe('resolver', function () {
    it('resolves a Florida lien to the Florida body with the state rules', function () {
        $filing = liendocFiling('mechanics_lien', 'FL', 'Pinellas County');

        $doc = app(LienDocumentResolver::class)->resolve($filing);

        expect($doc)->toBeInstanceOf(ResolvedLienDocument::class);
        expect($doc->state)->toBe('FL');
        expect($doc->stateName)->toBe('Florida');
        expect($doc->kind)->toBe('mechanics_lien');
        expect($doc->family)->toBe('instrument');
        expect($doc->isInstrument())->toBeTrue();
        expect($doc->title)->toBe('Claim of Lien');
        expect($doc->body)->toBe('documents.lien.instruments.bodies.fl-claim-of-lien');
        expect($doc->notaryRequired())->toBeTrue();
        expect($doc->countyKey)->toBe('pinellas');
        expect($doc->countyName)->toBe('Pinellas');
        expect($doc->recorderSpaceInches())->toBe(2.0);
        expect($doc->recipientRoles())->toBe(['owner']);
        expect($doc->warnings)->toBe([]);
        expect($doc->toArray()['recorder_space_in'])->toBe(2.0);
    });

    it('merges a county file over the state recording facts', function () {
        $filing = liendocFiling('mechanics_lien', 'MO', 'Jackson County');

        $doc = app(LienDocumentResolver::class)->resolve($filing);

        expect($doc->countyKey)->toBe('jackson');
        expect($doc->recording['index_block'])->toBeTrue();
        expect($doc->recording['index_roles'])->toBe(['grantor' => 'owner', 'grantee' => 'claimant']);
        expect($doc->recording['filing_office']['label'])->toBe('Jackson County Recorder of Deeds');
        expect($doc->recording['filing_office']['vendor'])->toBe('CSC (ep.erecording.com)');
        expect($doc->notes)->toContain('The recorder wants the grantor (owner) and grantee (claimant) named on page 1 exactly as they are indexed.');

        $clay = app(LienDocumentResolver::class)->resolve(liendocFiling('mechanics_lien', 'MO', 'Clay County'));

        expect($clay->countyKey)->toBe('clay');
        expect($clay->recording['preparer_in_space'])->toBeTrue();
        expect($clay->recording['filing_office']['label'])->toBe('Clerk of the Circuit Court (county where the property is located)');
    });

    it('prefers the filing jurisdiction over the jobsite and warns when they differ', function () {
        $filing = liendocFiling('mechanics_lien', 'GA', 'Fulton County', [
            'jurisdiction_state' => 'fl',
            'jurisdiction_county' => 'Duval County',
        ]);

        $doc = app(LienDocumentResolver::class)->resolve($filing);

        expect($doc->state)->toBe('FL');
        expect($doc->countyKey)->toBe('duval');
        expect($doc->warnings)->toContain("The filing's jurisdiction state (FL) differs from the jobsite state (GA).");
    });

    it('falls back to the jobsite county and warns when an instrument has no county at all', function () {
        $filing = liendocFiling('mechanics_lien', 'TX', 'Denton County', ['jurisdiction_county' => null]);

        expect(app(LienDocumentResolver::class)->resolve($filing)->countyKey)->toBe('denton');

        $noCounty = liendocFiling('mechanics_lien', 'TX', null, ['jurisdiction_county' => null], [
            'jobsite_county' => null,
            'jobsite_county_google' => null,
        ]);

        $doc = app(LienDocumentResolver::class)->resolve($noCounty);

        expect($doc->countyKey)->toBeNull();
        expect($doc->warnings)->toContain('No county is set on the filing or project; the caption and filing office cannot be filled in.');
    });

    it('resolves the letter kinds with their own execution rules', function () {
        $prelim = app(LienDocumentResolver::class)->resolve(liendocFiling('prelim_notice', 'AZ', 'Maricopa County'));

        expect($prelim->family)->toBe('letter');
        expect($prelim->isLetter())->toBeTrue();
        expect($prelim->notaryRequired())->toBeFalse();
        expect($prelim->recipientRoles())->toBe(['owner', 'gc', 'lender', 'customer']);
        expect($prelim->title)->toBe('Arizona Preliminary Twenty Day Lien Notice');

        $noi = app(LienDocumentResolver::class)->resolve(liendocFiling('noi', 'NC', 'Wake County'));

        expect($noi->body)->toBe('documents.lien.letters.bodies.generic-noi');
        expect($noi->section('demand_days'))->toBe(10);
    });

    it('lets a caller ask for a different kind than the filing type', function () {
        $filing = liendocFiling('mechanics_lien', 'FL', 'Polk County');

        $release = app(LienDocumentResolver::class)->resolve($filing, 'lien_release');

        expect($release->kind)->toBe('lien_release');
        expect($release->title)->toBe('Release of Lien');
        expect($release->execution['notary_form'])->toBe('acknowledgment');
    });

    it('refuses demand letters, unknown kinds, attorney states and filings without a state', function () {
        $demand = liendocFiling('demand_letter', 'FL', 'Polk County');

        expect(fn () => app(LienDocumentResolver::class)->resolve($demand))
            ->toThrow(LienDocumentUnavailable::class, 'Demand letters use their own generator.');

        expect(fn () => app(LienDocumentResolver::class)->resolve($demand, 'bogus'))
            ->toThrow(LienDocumentUnavailable::class, 'This filing type has no generated document.');

        $hawaii = liendocFiling('mechanics_lien', 'HI', 'Honolulu County');

        expect(fn () => app(LienDocumentResolver::class)->resolve($hawaii))
            ->toThrow(LienDocumentUnavailable::class, 'Hawaii liens are filed through an attorney');

        $noState = liendocFiling('mechanics_lien', 'FL', 'Polk County', ['jurisdiction_state' => null], ['jobsite_state' => null]);

        expect(fn () => app(LienDocumentResolver::class)->resolve($noState))
            ->toThrow(LienDocumentUnavailable::class, 'no jurisdiction state');
    });
});
