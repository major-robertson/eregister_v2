<?php

namespace App\Domains\Lien\Support;

use App\Domains\Lien\Admin\Actions\UpdateLienDocumentDetails;
use App\Domains\Lien\Documents\LienDocumentPayload;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Enums\NocStatus;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienStateRule;
use Illuminate\Validation\Rule;

/**
 * The state's extra questions on the filing wizard's Work step (EREG-137):
 * facts a state's document prints, or its deadline turns on, that staff used
 * to ask for after payment.
 *
 * The catalogue is config('lien.wizard_fields'). Each state's document data
 * (database/data/lien_documents/{xx}.php) lists the keys it asks per
 * document type under `wizard_fields`. Answers are stored on the filing's
 * document_details_json under the keys the admin Document details card
 * edits (UpdateLienDocumentDetails), except PROJECT_FIELDS, which live on
 * the project.
 */
final class WizardFields
{
    /** Answers saved on the project, not in document_details_json. */
    public const PROJECT_FIELDS = ['noc_status'];

    /** @var list<string> */
    public const TYPES = ['text', 'date', 'select', 'radio'];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function catalogue(): array
    {
        return config('lien.wizard_fields', []);
    }

    /**
     * The catalogue keys the state's data lists for a document type, in its
     * order. Keys missing from the catalogue are skipped (a test checks the
     * data files never list one).
     *
     * @return list<string>
     */
    public static function listed(string $state, string $kind): array
    {
        if ($state === '' || $kind === '') {
            return [];
        }

        $listed = LienDocumentRegistry::for($state)['wizard_fields'][$kind] ?? [];
        $catalogue = self::catalogue();

        return array_values(array_unique(array_filter(
            is_array($listed) ? $listed : [],
            fn (mixed $key): bool => is_string($key) && isset($catalogue[$key]),
        )));
    }

    /**
     * The questions to ask on this project's document: the listed fields
     * whose `when` holds for this claimant, with ":notice" in the label and
     * hint replaced by the state's preliminary notice title and the options
     * resolved.
     *
     * @return array<string, array{type: string, label: string, hint: string, required: bool, max: int|null, required_with: string|null, options: array<string, string>}>
     */
    public static function forProject(LienProject $project, string $kind): array
    {
        $state = strtoupper((string) $project->jobsite_state);
        $questions = [];

        foreach (self::listed($state, $kind) as $key) {
            $field = self::catalogue()[$key];

            if (! self::applies($field['when'] ?? null, $project)) {
                continue;
            }

            $notice = (string) (LienDocumentRegistry::for($state)['kinds']['prelim_notice']['title'] ?? 'Preliminary Notice');

            $questions[$key] = [
                'type' => in_array($field['type'] ?? null, self::TYPES, true) ? $field['type'] : 'text',
                'label' => str_replace(':notice', $notice, (string) ($field['label'] ?? $key)),
                'hint' => str_replace(':notice', $notice, (string) ($field['hint'] ?? '')),
                'required' => (bool) ($field['required'] ?? false),
                'max' => isset($field['max']) ? (int) $field['max'] : null,
                'required_with' => $field['required_with'] ?? null,
                'options' => self::options($key),
            ];
        }

        return $questions;
    }

    /**
     * The choices for a select or radio field, value => label.
     *
     * @return array<string, string>
     */
    public static function options(string $key): array
    {
        return match ($key) {
            'contract_type' => collect(UpdateLienDocumentDetails::CONTRACT_TYPES)
                ->mapWithKeys(fn (string $type): array => [$type => ucfirst($type)])
                ->all(),
            'notice_served_method' => collect(UpdateLienDocumentDetails::NOTICE_METHODS)
                ->mapWithKeys(fn (string $method): array => [$method => ucfirst((string) LienDocumentPayload::deliveryLabel($method))])
                ->all(),
            'noc_status' => collect(NocStatus::cases())
                ->mapWithKeys(fn (NocStatus $status): array => [$status->value => $status->label()])
                ->all(),
            default => [],
        };
    }

    /**
     * Validation rules for one question's answer. A required field is
     * required; an optional one is nullable unless its `required_with`
     * partner (another key in the same array) has an answer.
     *
     * @param  array<string, mixed>  $question  from forProject()
     * @return list<mixed>
     */
    public static function rules(array $question, string $prefix): array
    {
        $rules = match (true) {
            $question['required'] => ['required'],
            $question['required_with'] !== null => ['nullable', 'required_with:'.$prefix.$question['required_with']],
            default => ['nullable'],
        };

        return array_merge($rules, match ($question['type']) {
            'date' => ['date', 'before_or_equal:today'],
            'select', 'radio' => [Rule::in(array_keys($question['options']))],
            default => ['string', 'max:'.($question['max'] ?? 255)],
        });
    }

    /**
     * Whether a field's `when` condition holds for this project. Only
     * prelim_required exists: the state requires a preliminary notice from
     * this claimant (lien_state_rules: from everyone, or from those who did
     * not contract with the owner).
     */
    private static function applies(?string $when, LienProject $project): bool
    {
        if ($when === null) {
            return true;
        }

        if ($when !== 'prelim_required') {
            return false;
        }

        $rule = LienStateRule::query()->find(strtoupper((string) $project->jobsite_state));

        if (! $rule?->pre_notice_required) {
            return false;
        }

        return $rule->pre_notice_required_for === 'everyone' || $project->contractedWithOwner() !== true;
    }
}
