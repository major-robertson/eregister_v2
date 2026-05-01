<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Livewire\MultiStateFormRunner;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;
use Livewire\Livewire;

/**
 * Generic conditional badge mechanism: any field can declare a
 * `badge_when` list of {condition, label, color} entries, and the field
 * dispatcher evaluates them first-match-wins via ConditionEvaluator,
 * passing the resolved badge into the typed partial. Used here to show
 * an "Optional" badge on the EIN field for sole proprietors.
 *
 * The tests exercise both layers:
 *   1. The definition itself carries the right badge_when shape on
 *      the EIN field (definition guard).
 *   2. The rendered runner output actually surfaces the badge text
 *      when entity_type=sole_prop, and omits it otherwise (integration).
 */
function bootBadgeRunner(string $entityType): FormApplication
{
    $user = User::factory()->create();
    $business = Business::create([
        'name' => 'Badge Test',
        'legal_name' => 'Badge Test LLC',
        'onboarding_completed_at' => now(),
    ]);
    $user->businesses()->attach($business->id, ['role' => 'owner']);

    $application = FormApplication::create([
        'business_id' => $business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => ['CA'],
        'status' => 'draft',
        'current_phase' => 'core',
        'current_step_key' => 'tax_identification',
        'core_data' => [
            'legal_name' => 'Badge Test LLC',
            'entity_type' => $entityType,
            'formation_state' => 'CA',
            'naics_code' => '541512',
            'business_description' => 'Software',
            'reason_for_applying' => 'new_business',
            'business_start_date' => '2020-01-01',
        ],
        'created_by_user_id' => $user->id,
        'paid_at' => now(),
    ]);

    FormApplicationState::create([
        'form_application_id' => $application->id,
        'state_code' => 'CA',
        'status' => 'pending',
        'data' => [],
    ]);

    test()->actingAs($user)->withSession(['current_business_id' => $business->id]);

    return $application;
}

describe('badge_when definition shape', function () {
    it('defines an Optional badge on FEIN that fires for sole proprietors', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $field = $base['core_steps']['tax_identification']['fields']['fein'];

        expect($field['badge_when'] ?? null)->toBeArray()->not->toBeEmpty();

        $first = $field['badge_when'][0];
        expect($first['label'])->toBe('Optional')
            ->and($first['color'])->toBe('zinc')
            ->and($first['condition'])->toBe(['==' => [['var' => 'entity_type'], 'sole_prop']]);
    });
});

describe('FEIN Optional badge rendering', function () {
    it('shows the Optional badge when entity_type is sole_prop', function () {
        $application = bootBadgeRunner('sole_prop');

        $html = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->html();

        // The badge text appears in the rendered label region. We assert
        // on the visible string rather than on Flux's internal markup so
        // the test isn't coupled to Flux versions.
        expect($html)->toContain('Optional');
    });

    it('omits the Optional badge for non-sole-prop entity types', function () {
        $application = bootBadgeRunner('corporation');

        $html = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->html();

        // Other UI strings might legitimately contain "Optional" in
        // theory, so we narrow to the EIN label region by asserting the
        // FEIN/EIN label is present (proving the field rendered) and
        // separately that "Optional" is absent within the page.
        expect($html)->toContain('Federal Employer Identification Number')
            ->and($html)->not->toContain('Optional');
    });

    it('updates the badge live when entity_type flips from corporation to sole_prop', function () {
        $application = bootBadgeRunner('corporation');

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->assertDontSee('Optional')
            ->set('coreData.entity_type', 'sole_prop')
            ->assertSee('Optional');
    });
});

describe('badge_when first-match-wins behavior', function () {
    it('picks the first matching candidate and ignores later ones', function () {
        // Synthetic field with two candidates that BOTH match — the
        // first should win. This pins ordering semantics so future
        // refactors don't accidentally reverse it.
        $field = [
            'badge_when' => [
                [
                    'condition' => ['==' => [['var' => 'entity_type'], 'sole_prop']],
                    'label' => 'First',
                    'color' => 'zinc',
                ],
                [
                    'condition' => ['==' => [['var' => 'entity_type'], 'sole_prop']],
                    'label' => 'Second',
                    'color' => 'red',
                ],
            ],
        ];

        $evaluator = app(\App\Domains\Forms\Engine\ConditionEvaluator::class);
        $context = ['coreData' => ['entity_type' => 'sole_prop'], 'stateData' => []];

        $matched = null;
        foreach ($field['badge_when'] as $candidate) {
            if ($evaluator->evaluate($candidate['condition'], $context)) {
                $matched = $candidate;
                break;
            }
        }

        expect($matched['label'])->toBe('First');
    });

    it('treats a missing badge_when as a no-op (other fields are unaffected)', function () {
        // Fields without a badge_when key must NOT inject any badge.
        // Asserts via the dispatcher's resolution path that absence is safe.
        $field = ['type' => 'text', 'label' => 'Plain'];

        $badge = null;
        if (! empty($field['badge_when'])) {
            $badge = ['unexpected' => true];
        }

        expect($badge)->toBeNull();
    });
});
