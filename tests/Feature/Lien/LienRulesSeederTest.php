<?php

use App\Domains\Lien\Enums\CalcMethod;
use App\Domains\Lien\Enums\DeadlineTrigger;
use App\Domains\Lien\Models\LienDeadlineRule;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienStateRule;

uses()->group('slow');

beforeEach(function () {
    // Seed if tables are empty (RefreshDatabase resets the DB for each test)
    if (LienDocumentType::count() === 0) {
        $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    }
    if (LienStateRule::count() === 0) {
        $this->artisan('db:seed', ['--class' => 'LienStateRuleSeeder']);
    }
    if (LienDeadlineRule::count() === 0) {
        $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);
    }
});

describe('LienStateRuleSeeder', function () {
    it('seeds all 50 states with correct data', function () {
        // Verify 50 states seeded
        expect(LienStateRule::count())->toBe(50);

        // Verify all states have required columns
        $states = LienStateRule::all();
        foreach ($states as $state) {
            expect($state->state)->toHaveLength(2);
            expect($state->data_source)->toBe('csv_v3');
            expect($state->enforcement_deadline_trigger)->not->toBeNull();
        }

        // Verify lien rights flags exist for all claimant types
        $firstState = LienStateRule::first();
        expect($firstState->gc_has_lien_rights)->toBeIn([true, false]);
        expect($firstState->sub_has_lien_rights)->toBeIn([true, false]);
        expect($firstState->subsub_has_lien_rights)->toBeIn([true, false]);
        expect($firstState->supplier_owner_has_lien_rights)->toBeIn([true, false]);
        expect($firstState->supplier_gc_has_lien_rights)->toBeIn([true, false]);
        expect($firstState->supplier_sub_has_lien_rights)->toBeIn([true, false]);

        // Verify Texas data
        $tx = LienStateRule::find('TX');
        expect($tx)->not->toBeNull();
        expect($tx->prelim_delivery_method)->toBe('certified_mail');
        expect($tx->prelim_recipients)->toBe('owner_gc');
        expect($tx->lien_anchor_logic)->toBe('later_of');
        expect($tx->lien_anchor_alt_field)->toBe('special_fab_delivery_date');
        expect($tx->gc_has_lien_rights)->toBeTrue();
        expect($tx->sub_has_lien_rights)->toBeTrue();
        expect($tx->owner_occupied_special_rules)->toBeTrue();
        expect((float) $tx->enforcement_deadline_months)->toBe(12.0);
        expect($tx->enforcement_deadline_trigger)->toBe('lien_recorded_date');

        // Verify California data
        $ca = LienStateRule::find('CA');
        expect($ca)->not->toBeNull();
        expect($ca->pre_notice_required)->toBeTrue();
        expect($ca->noc_shortens_deadline)->toBeTrue();
        expect($ca->lien_after_noc_days)->toBe(30);
        expect($ca->notarization_required)->toBeFalse();
        expect($ca->verification_type)->toBe('verified');
        expect($ca->enforcement_calc_method)->toBe('days_after_date');
        expect($ca->enforcement_deadline_days)->toBe(90);

        // Verify Virginia data
        $va = LienStateRule::find('VA');
        expect($va)->not->toBeNull();
        expect($va->pre_notice_required)->toBeFalse();
        expect($va->lien_anchor_logic)->toBe('later_of');
        expect($va->lien_anchor_alt_field)->toBe('contract_terminated_date');
    });
});

describe('LienDeadlineRuleSeeder', function () {
    it('seeds deadline rules with correct data for all claimant types and scopes', function () {
        // Verify rules exist
        expect(LienDeadlineRule::count())->toBeGreaterThan(0);

        // Verify all deadline rules have csv_v3 data source
        $nonCsvRules = LienDeadlineRule::where('data_source', '!=', 'csv_v3')->count();
        expect($nonCsvRules)->toBe(0);

        // Verify preliminary notice rules exist
        $prelimNotice = LienDocumentType::where('slug', 'prelim_notice')->first();
        $prelimRules = LienDeadlineRule::where('document_type_id', $prelimNotice->id)->count();
        expect($prelimRules)->toBeGreaterThan(0);

        // Verify mechanics lien rules for all claimant types
        $mechanicsLien = LienDocumentType::where('slug', 'mechanics_lien')->first();

        $claimantTypes = LienDeadlineRule::where('document_type_id', $mechanicsLien->id)
            ->distinct('claimant_type')
            ->pluck('claimant_type')
            ->toArray();

        expect($claimantTypes)->toContain('gc');
        expect($claimantTypes)->toContain('sub');
        expect($claimantTypes)->toContain('subsub');
        expect($claimantTypes)->toContain('supplier_owner');
        expect($claimantTypes)->toContain('supplier_gc');
        expect($claimantTypes)->toContain('supplier_sub');

        // Verify residential and commercial scopes
        $scopes = LienDeadlineRule::where('document_type_id', $mechanicsLien->id)
            ->distinct('effective_scope')
            ->pluck('effective_scope')
            ->toArray();

        expect($scopes)->toContain('residential');
        expect($scopes)->toContain('commercial');

        // Verify Texas GC residential rule
        $txGcRes = LienDeadlineRule::where('state', 'TX')
            ->where('document_type_id', $mechanicsLien->id)
            ->where('claimant_type', 'gc')
            ->where('effective_scope', 'residential')
            ->first();

        expect($txGcRes)->not->toBeNull();
        expect($txGcRes->calc_method)->toBe(CalcMethod::MonthDayAfterMonthOfDate);
        expect($txGcRes->offset_months)->toBe(3);
        expect($txGcRes->day_of_month)->toBe(15);
        expect($txGcRes->trigger_event)->toBe(DeadlineTrigger::Completion);

        // Verify Texas GC commercial rule
        $txGcCom = LienDeadlineRule::where('state', 'TX')
            ->where('document_type_id', $mechanicsLien->id)
            ->where('claimant_type', 'gc')
            ->where('effective_scope', 'commercial')
            ->first();

        expect($txGcCom)->not->toBeNull();
        expect($txGcCom->calc_method)->toBe(CalcMethod::MonthDayAfterMonthOfDate);
        expect($txGcCom->offset_months)->toBe(4);
        expect($txGcCom->day_of_month)->toBe(15);

        // Verify New York rules
        $nyRes = LienDeadlineRule::where('state', 'NY')
            ->where('document_type_id', $mechanicsLien->id)
            ->where('effective_scope', 'residential')
            ->first();

        $nyCom = LienDeadlineRule::where('state', 'NY')
            ->where('document_type_id', $mechanicsLien->id)
            ->where('effective_scope', 'commercial')
            ->first();

        expect($nyRes)->not->toBeNull();
        expect($nyRes->calc_method)->toBe(CalcMethod::MonthsAfterDate);
        expect($nyRes->offset_months)->toBe(4);

        expect($nyCom)->not->toBeNull();
        expect($nyCom->calc_method)->toBe(CalcMethod::MonthsAfterDate);
        expect($nyCom->offset_months)->toBe(8);

        // Verify Virginia rules
        $vaRule = LienDeadlineRule::where('state', 'VA')
            ->where('document_type_id', $mechanicsLien->id)
            ->first();

        expect($vaRule)->not->toBeNull();
        expect($vaRule->calc_method)->toBe(CalcMethod::DaysAfterEndOfMonthOfDate);
        expect($vaRule->offset_days)->toBe(90);
    });
});
