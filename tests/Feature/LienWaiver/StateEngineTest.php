<?php

use App\Domains\Lien\Enums\WaiverKind;
use App\Domains\Lien\Waivers\ResolvedWaiverForm;
use App\Domains\Lien\Waivers\WaiverFormResolver;
use App\Domains\Lien\Waivers\WaiverFormUnavailable;
use App\Domains\Lien\Waivers\WaiverStateRegistry;

beforeEach(fn () => WaiverStateRegistry::flush());
afterEach(fn () => WaiverStateRegistry::flush());

if (! function_exists('stateEngineKindValues')) {
    /** @return list<string> the four canonical kind values, in enum order */
    function stateEngineKindValues(): array
    {
        return array_map(fn (WaiverKind $kind) => $kind->value, WaiverKind::cases());
    }
}

describe('registry', function () {
    it('loads all 50 states with the generic defaults merged in', function () {
        $all = WaiverStateRegistry::all();

        expect($all)->toHaveCount(50);
        expect(array_keys($all))->toBe(array_keys(WaiverStateRegistry::STATE_NAMES));

        foreach ($all as $code => $rules) {
            expect($rules['state'])->toBe($code);
            expect($rules['state_name'])->toBe(WaiverStateRegistry::STATE_NAMES[$code]);

            // Every key of the defaults survives the merge, even for states
            // whose data file only overrides a few of them.
            expect($rules)->toHaveKeys([
                'family', 'statute', 'compliance_standard', 'notarization_required',
                'witness_required', 'esign_allowed', 'esign_disabled_reason',
                'deemed_effective_days', 'affidavit_of_nonpayment',
                'advance_waiver_note', 'ui_notes', 'extra_clauses', 'kinds', 'landing',
            ]);
            expect($rules['landing'])->toBeArray();
            expect($rules['ui_notes'])->toBeArray();
            expect($rules['extra_clauses'])->toBeArray();

            // All four canonical kinds are always present (merge is per-kind).
            expect($rules['kinds'])->toHaveCount(4);
            expect(array_keys($rules['kinds']))->toEqualCanonicalizing(stateEngineKindValues());

            foreach ($rules['kinds'] as $kindValue => $entry) {
                expect($entry)->toHaveKeys([
                    'enabled', 'template', 'title', 'template_version',
                    'disabled_reason', 'redirect_kind',
                    'residential_template', 'residential_title',
                ]);

                if ($entry['enabled']) {
                    expect($entry['template'])->toStartWith('documents.lien.waivers.bodies.');
                    expect($entry['title'])->toBeString()->not->toBe('');
                    expect($entry['template_version'])->toBeInt();
                } else {
                    // A disabled kind always explains itself and steers the
                    // wizard to an enabled kind in the same state.
                    expect($entry['disabled_reason'])->toBeString()->not->toBe('');
                    expect($entry['redirect_kind'])->toBeIn(stateEngineKindValues());
                    expect($rules['kinds'][$entry['redirect_kind']]['enabled'])->toBeTrue();
                }
            }

            // If e-sign is off for the state, the UI has copy to show.
            if ($rules['esign_allowed'] === false) {
                expect($rules['esign_disabled_reason'])->toBeString()->not->toBe('');
            }
        }
    });

    it('normalizes lowercase state codes to the same rules', function () {
        expect(WaiverStateRegistry::for('ga'))->toBe(WaiverStateRegistry::for('GA'));
        expect(WaiverStateRegistry::isSupported('tx'))->toBeTrue();
        expect(WaiverStateRegistry::isSupported('ZZ'))->toBeFalse();
    });

    it('gives AZ, CA, NV, TX, and MI four enabled statutory templates', function () {
        foreach (['AZ' => 'az', 'CA' => 'ca', 'NV' => 'nv', 'TX' => 'tx', 'MI' => 'mi'] as $state => $prefix) {
            $rules = WaiverStateRegistry::for($state);

            expect($rules['family'])->toBe('statutory_four');
            expect($rules['statute'])->not->toBeNull();

            foreach ($rules['kinds'] as $kindValue => $entry) {
                expect($entry['enabled'])->toBeTrue();
                expect($entry['template'])->toStartWith("documents.lien.waivers.bodies.{$prefix}-");
            }
        }
    });

    it('GA and MS enable only conditional kinds, redirect the unconditional ones, and carry deemed-effective windows', function () {
        foreach (['GA' => 90, 'MS' => 60] as $state => $days) {
            $rules = WaiverStateRegistry::for($state);

            expect($rules['family'])->toBe('statutory_two');
            expect($rules['deemed_effective_days'])->toBe($days);
            expect($rules['esign_allowed'])->toBeFalse();
            expect($rules['esign_disabled_reason'])->toBeString()->not->toBe('');
            expect($rules['affidavit_of_nonpayment'])->toBeTrue();

            expect($rules['kinds']['conditional_progress']['enabled'])->toBeTrue();
            expect($rules['kinds']['conditional_final']['enabled'])->toBeTrue();

            expect($rules['kinds']['unconditional_progress']['enabled'])->toBeFalse();
            expect($rules['kinds']['unconditional_progress']['redirect_kind'])->toBe('conditional_progress');
            expect($rules['kinds']['unconditional_final']['enabled'])->toBeFalse();
            expect($rules['kinds']['unconditional_final']['redirect_kind'])->toBe('conditional_final');
        }

        expect(WaiverStateRegistry::for('GA')['kinds']['conditional_progress']['template'])
            ->toBe('documents.lien.waivers.bodies.ga-interim');
        expect(WaiverStateRegistry::for('GA')['kinds']['conditional_final']['template'])
            ->toBe('documents.lien.waivers.bodies.ga-final');
        expect(WaiverStateRegistry::for('MS')['kinds']['conditional_progress']['template'])
            ->toBe('documents.lien.waivers.bodies.ms-interim');
        expect(WaiverStateRegistry::for('MS')['notarization_required'])->toBeTrue();
        expect(WaiverStateRegistry::for('GA')['witness_required'])->toBeTrue();
    });

    it('WY maps both unconditional kinds to its single notarized form and disables the conditional ones', function () {
        $rules = WaiverStateRegistry::for('WY');

        expect($rules['family'])->toBe('statutory_single');
        expect($rules['notarization_required'])->toBeTrue();
        expect($rules['esign_allowed'])->toBeFalse();
        expect($rules['esign_disabled_reason'])->toBeString()->not->toBe('');

        foreach (['unconditional_progress', 'unconditional_final'] as $kind) {
            expect($rules['kinds'][$kind]['enabled'])->toBeTrue();
            expect($rules['kinds'][$kind]['template'])->toBe('documents.lien.waivers.bodies.wy-lien-waiver');
            expect($rules['kinds'][$kind]['title'])->toBe('Lien Waiver');
        }

        expect($rules['kinds']['conditional_progress']['enabled'])->toBeFalse();
        expect($rules['kinds']['conditional_progress']['redirect_kind'])->toBe('unconditional_progress');
        expect($rules['kinds']['conditional_final']['enabled'])->toBeFalse();
        expect($rules['kinds']['conditional_final']['redirect_kind'])->toBe('unconditional_final');
    });

    it('UT enables only the conditional kinds', function () {
        $rules = WaiverStateRegistry::for('UT');

        expect($rules['kinds']['conditional_progress']['enabled'])->toBeTrue();
        expect($rules['kinds']['conditional_progress']['template'])->toBe('documents.lien.waivers.bodies.ut-conditional-progress');
        expect($rules['kinds']['conditional_final']['enabled'])->toBeTrue();
        expect($rules['kinds']['conditional_final']['template'])->toBe('documents.lien.waivers.bodies.ut-final');

        expect($rules['kinds']['unconditional_progress']['enabled'])->toBeFalse();
        expect($rules['kinds']['unconditional_progress']['redirect_kind'])->toBe('conditional_progress');
        expect($rules['kinds']['unconditional_final']['enabled'])->toBeFalse();
        expect($rules['kinds']['unconditional_final']['redirect_kind'])->toBe('conditional_final');

        // Utah's statutory forms carry no execution formalities.
        expect($rules['notarization_required'])->toBeFalse();
        expect($rules['witness_required'])->toBeFalse();
        expect($rules['esign_allowed'])->toBeTrue();
    });

    it('FL enables all four safe-harbor kinds', function () {
        $rules = WaiverStateRegistry::for('FL');

        expect($rules['family'])->toBe('safe_harbor');

        $expected = [
            'conditional_progress' => 'documents.lien.waivers.bodies.fl-conditional-progress',
            'unconditional_progress' => 'documents.lien.waivers.bodies.fl-progress',
            'conditional_final' => 'documents.lien.waivers.bodies.fl-conditional-final',
            'unconditional_final' => 'documents.lien.waivers.bodies.fl-final',
        ];

        foreach ($expected as $kind => $template) {
            expect($rules['kinds'][$kind]['enabled'])->toBeTrue();
            expect($rules['kinds'][$kind]['template'])->toBe($template);
        }
    });

    it('CO carries the § 38-22-119(2) third-party-debts clause and no other state injects extra clauses', function () {
        $co = WaiverStateRegistry::for('CO');

        expect($co['family'])->toBe('generic');
        expect($co['extra_clauses'])->toHaveCount(1);
        expect($co['extra_clauses'][0])
            ->toContain('all debts owed to any third party')
            ->toContain('paid or will be timely paid')
            ->toContain('C.R.S. § 38-22-119(2)');

        foreach (WaiverStateRegistry::all() as $code => $rules) {
            if ($code !== 'CO') {
                expect($rules['extra_clauses'])->toBe([]);
            }
        }
    });

    it('MO keeps the generic house forms plus a residential swap on unconditional final only', function () {
        $rules = WaiverStateRegistry::for('MO');

        expect($rules['family'])->toBe('special');

        // All four kinds stay enabled on the generic bodies; MO's data file
        // overrides only the residential keys of unconditional_final, and the
        // per-kind merge must preserve the default template/enabled values.
        foreach (stateEngineKindValues() as $kind) {
            expect($rules['kinds'][$kind]['enabled'])->toBeTrue();
            expect($rules['kinds'][$kind]['template'])
                ->toBe('documents.lien.waivers.bodies.generic-'.str_replace('_', '-', $kind));
        }

        expect($rules['kinds']['unconditional_final']['residential_template'])
            ->toBe('documents.lien.waivers.bodies.mo-unconditional-final-residential');
        expect($rules['kinds']['unconditional_final']['residential_title'])
            ->toBe('Unconditional Final Lien Waiver for Residential Real Property');

        expect($rules['kinds']['conditional_progress']['residential_template'])->toBeNull();
        expect($rules['kinds']['conditional_final']['residential_template'])->toBeNull();
    });
});

describe('resolver', function () {
    it('resolves statutory template keys with the execution rules that travel with them', function () {
        $resolver = new WaiverFormResolver;

        $tx = $resolver->resolve('TX', WaiverKind::ConditionalProgress);
        expect($tx)->toBeInstanceOf(ResolvedWaiverForm::class);
        expect($tx->state)->toBe('TX');
        expect($tx->kind)->toBe(WaiverKind::ConditionalProgress);
        expect($tx->template)->toBe('documents.lien.waivers.bodies.tx-conditional-progress');
        expect($tx->title)->toBe('Conditional Waiver and Release on Progress Payment');
        expect($tx->templateVersion)->toBe(1);
        expect($tx->complianceStandard)->toBe('substantial');
        expect($tx->notarizationRequired)->toBeFalse();
        expect($tx->esignAllowed)->toBeTrue();
        expect($tx->deemedEffectiveDays)->toBeNull();

        // Lowercase input normalizes; GA's rules ride along on the form.
        $ga = $resolver->resolve('ga', WaiverKind::ConditionalProgress);
        expect($ga->state)->toBe('GA');
        expect($ga->template)->toBe('documents.lien.waivers.bodies.ga-interim');
        expect($ga->title)->toBe('Waiver and Release of Lien and Payment Bond Rights Upon Interim Payment');
        expect($ga->witnessRequired)->toBeTrue();
        expect($ga->esignAllowed)->toBeFalse();
        expect($ga->esignDisabledReason)->not->toBeNull();
        expect($ga->deemedEffectiveDays)->toBe(90);
        expect($ga->statute)->toBe('O.C.G.A. § 44-14-366');
        expect($ga->uiNotes)->not->toBeEmpty();

        // Nevada is the verbatim-compliance state.
        $nv = $resolver->resolve('NV', WaiverKind::UnconditionalFinal);
        expect($nv->template)->toBe('documents.lien.waivers.bodies.nv-unconditional-final');
        expect($nv->complianceStandard)->toBe('verbatim');

        // A generic state falls through to the house forms; CO's mandatory
        // § 38-22-119(2) clause rides along, statutory states carry none.
        $co = $resolver->resolve('CO', WaiverKind::UnconditionalProgress);
        expect($co->template)->toBe('documents.lien.waivers.bodies.generic-unconditional-progress');
        expect($co->complianceStandard)->toBe('generic');
        expect($co->extraClauses)->toHaveCount(1);
        expect($co->extraClauses[0])->toContain('C.R.S. § 38-22-119(2)');
        expect($tx->extraClauses)->toBe([]);
    });

    it('swaps in the MO residential statutory body only for residential unconditional final', function () {
        $resolver = new WaiverFormResolver;

        $residential = $resolver->resolve('MO', WaiverKind::UnconditionalFinal, 'residential');
        expect($residential->template)->toBe('documents.lien.waivers.bodies.mo-unconditional-final-residential');
        expect($residential->title)->toBe('Unconditional Final Lien Waiver for Residential Real Property');

        // Commercial or unknown property class keeps the generic body.
        expect($resolver->resolve('MO', WaiverKind::UnconditionalFinal, 'commercial')->template)
            ->toBe('documents.lien.waivers.bodies.generic-unconditional-final');
        expect($resolver->resolve('MO', WaiverKind::UnconditionalFinal, null)->template)
            ->toBe('documents.lien.waivers.bodies.generic-unconditional-final');

        // Residential does not touch MO's other kinds...
        expect($resolver->resolve('MO', WaiverKind::ConditionalProgress, 'residential')->template)
            ->toBe('documents.lien.waivers.bodies.generic-conditional-progress');

        // ...and is a no-op in states without a residential_template.
        expect($resolver->resolve('CA', WaiverKind::UnconditionalFinal, 'residential')->template)
            ->toBe('documents.lien.waivers.bodies.ca-unconditional-final');
    });

    it('throws WaiverFormUnavailable with the state explanation for a disabled kind', function () {
        $resolver = new WaiverFormResolver;

        expect(fn () => $resolver->resolve('GA', WaiverKind::UnconditionalProgress))
            ->toThrow(WaiverFormUnavailable::class, 'Georgia has no unconditional waiver');

        expect(fn () => $resolver->resolve('MS', WaiverKind::UnconditionalFinal))
            ->toThrow(WaiverFormUnavailable::class, 'Mississippi has no unconditional waiver form');

        expect(fn () => $resolver->resolve('UT', WaiverKind::UnconditionalFinal))
            ->toThrow(WaiverFormUnavailable::class);

        expect(fn () => $resolver->resolve('WY', WaiverKind::ConditionalProgress))
            ->toThrow(WaiverFormUnavailable::class, 'single statutory lien waiver form');
    });

    it('availableKinds returns every canonical kind with availability, naming, and redirects', function () {
        $resolver = new WaiverFormResolver;

        $ga = $resolver->availableKinds('GA');
        expect(array_keys($ga))->toBe(stateEngineKindValues());

        $interim = $ga['conditional_progress'];
        expect($interim['kind'])->toBe(WaiverKind::ConditionalProgress);
        expect($interim['enabled'])->toBeTrue();
        expect($interim['title'])->toBe('Waiver and Release of Lien and Payment Bond Rights Upon Interim Payment');
        expect($interim['disabled_reason'])->toBeNull();
        expect($interim['redirect_kind'])->toBeNull();

        $unconditional = $ga['unconditional_progress'];
        expect($unconditional['kind'])->toBe(WaiverKind::UnconditionalProgress);
        expect($unconditional['enabled'])->toBeFalse();
        expect($unconditional['disabled_reason'])->toContain('Georgia has no unconditional waiver');
        expect($unconditional['redirect_kind'])->toBe('conditional_progress');

        // Generic state: all four enabled under the house titles, no redirects.
        $co = $resolver->availableKinds('CO');
        foreach ($co as $entry) {
            expect($entry['enabled'])->toBeTrue();
            expect($entry['disabled_reason'])->toBeNull();
            expect($entry['redirect_kind'])->toBeNull();
        }
        expect($co['conditional_progress']['title'])
            ->toBe('Conditional Waiver and Release of Lien: Progress Payment');
    });
});
