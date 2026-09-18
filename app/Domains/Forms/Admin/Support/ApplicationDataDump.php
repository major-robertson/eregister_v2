<?php

namespace App\Domains\Forms\Admin\Support;

use App\Domains\Forms\Engine\AnswerFormatter;
use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Engine\SensitiveDataProtector;
use App\Domains\Forms\FormTypeConfig;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Everything stored for one form application — application metadata,
 * payments, every shared answer and every state's answers — flattened
 * into labelled, display-ready rows for the admin "All Application
 * Data" section.
 *
 * Unlike the answer-summary partial, nothing is truncated or skipped:
 * repeater rows list every field (including the per-state person
 * questions saved on each responsible person), per-person state extras
 * are mapped to the person's name, and keys the definition no longer
 * knows about still render under their raw names. Sensitive values are
 * decrypted, since admins need them to file.
 *
 * Shapes (every value is a string, escaped by Blade on output):
 *   Block = array{title: ?string, rows: list<Row>}
 *   Row   = array{label: string, value: string}
 *         | array{label: string, items: list<array{title: string, blocks: list<Block>}>}
 */
class ApplicationDataDump
{
    /** Address keys that are geocoding metadata rather than answers. */
    private const ADDRESS_META_KEYS = ['lat', 'lng', 'place_id', 'formatted_address'];

    /** Str::headline() lowercases acronyms ("Fein", "Ssn") — restore them. */
    private const ACRONYMS = [
        'Abc' => 'ABC', 'Cdtfa' => 'CDTFA', 'Dba' => 'DBA', 'Dmv' => 'DMV', 'Dob' => 'DOB',
        'Ein' => 'EIN', 'Fein' => 'FEIN', 'Id' => 'ID', 'Llc' => 'LLC', 'Naics' => 'NAICS',
        'Sla' => 'SLA', 'Sos' => 'SOS', 'Ssn' => 'SSN',
    ];

    public function __construct(
        private FormRegistry $registry,
        private SensitiveDataProtector $protector,
        private Encrypter $encrypter,
        private AnswerFormatter $answers,
    ) {}

    /**
     * @return array{
     *     application: list<array<string, mixed>>,
     *     core: list<array<string, mixed>>,
     *     states: list<array{code: string, name: string, record_id: ?int, meta: list<array<string, mixed>>, blocks: list<array<string, mixed>>}>
     * }
     */
    public function build(FormApplication $application): array
    {
        $formType = (string) $application->form_type;
        $base = $this->registry->getBase($formType);

        $records = $application->states()->get()->keyBy('state_code');

        // Selected states first (the order the customer picked them), then
        // any stray state record so nothing saved is hidden.
        $stateCodes = collect($application->selected_states ?? [])
            ->merge($records->keys())
            ->unique()
            ->values();

        $definitions = $stateCodes
            ->mapWithKeys(fn (string $code): array => [$code => $this->registry->get($formType, $code)])
            ->all();

        $coreData = $this->decryptRemaining(
            $this->protector->decryptCoreData($application->core_data ?? [], $base)
        );

        // Per-person state questions (NY's compliance radios, PA's county)
        // are saved on the shared responsible_people rows, so those rows are
        // labelled from every state's person-extra schema.
        $personExtraSchemas = collect($definitions)
            ->map(fn (array $definition): array => $this->personExtraSchema($definition))
            ->filter()
            ->all();

        $core = $this->blocks($base['core_steps'] ?? [], $coreData, null, [
            'person_extra_schemas' => $personExtraSchemas,
        ]);

        $people = collect($coreData['responsible_people'] ?? [])
            ->filter(fn ($person): bool => is_array($person) && isset($person['_id']))
            ->keyBy('_id')
            ->all();

        $states = $stateCodes->map(function (string $code) use ($records, $definitions, $people): array {
            /** @var FormApplicationState|null $record */
            $record = $records->get($code);
            $definition = $definitions[$code];

            $data = $record
                ? $this->decryptRemaining($this->protector->decryptStateData($record->data ?? [], $definition))
                : [];

            return [
                'code' => $code,
                'name' => $this->stateName($code),
                'record_id' => $record?->id,
                'meta' => $this->stateMetaRows($record),
                'blocks' => $this->blocks($definition['state_steps'] ?? [], $data, $code, ['people' => $people]),
            ];
        })->all();

        return [
            'application' => $this->applicationRows($application, $stateCodes->all()),
            'core' => $core,
            'states' => $states,
        ];
    }

    /**
     * One block per wizard step (and per titled group within a step, so
     * short labels like CA's three "Diesel fuel" checkboxes keep their
     * context) that has saved answers, in definition order, plus a
     * trailing block for keys no step declares.
     *
     * @param  array<string, array<string, mixed>>  $steps
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     * @return list<array<string, mixed>>
     */
    private function blocks(array $steps, array $data, ?string $stateCode, array $context): array
    {
        $blocks = [];
        $declared = [];

        foreach ($steps as $stepKey => $step) {
            $fields = $step['fields'] ?? [];
            $stepTitle = $this->fillStateName($step['title'] ?? Str::headline((string) $stepKey), $stateCode);
            $declared += $fields;
            $grouped = [];

            foreach ($step['groups'] ?? [] as $group) {
                // Group field lists may nest pairs: [['first_name', 'last_name'], 'title'].
                $groupFields = collect(Arr::flatten($group['fields'] ?? []))
                    ->filter(fn ($key): bool => is_string($key) && isset($fields[$key]))
                    ->mapWithKeys(fn (string $key): array => [$key => $fields[$key]])
                    ->all();
                $groupTitle = $this->fillStateName($group['title'] ?? '', $stateCode);

                $blocks[] = [
                    'title' => in_array($groupTitle, ['', $stepTitle], true) ? $stepTitle : "{$stepTitle} — {$groupTitle}",
                    'rows' => $this->stepRows($groupFields, $data, $stateCode, $context),
                ];
                $grouped += $groupFields;
            }

            $blocks[] = [
                'title' => $stepTitle,
                'rows' => $this->stepRows(array_diff_key($fields, $grouped), $data, $stateCode, $context),
            ];
        }

        $blocks[] = ['title' => 'Other Saved Fields', 'rows' => $this->untypedRows(array_diff_key($data, $declared))];

        // Drop empty blocks and merge neighbours that share a title (a step
        // whose group is titled like the step, plus its ungrouped fields).
        $merged = [];

        foreach ($blocks as $block) {
            if ($block['rows'] === []) {
                continue;
            }

            $last = array_key_last($merged);

            if ($last !== null && $merged[$last]['title'] === $block['title']) {
                array_push($merged[$last]['rows'], ...$block['rows']);
            } else {
                $merged[] = $block;
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     * @return list<array<string, mixed>>
     */
    private function stepRows(array $fields, array $data, ?string $stateCode, array $context): array
    {
        $rows = [];

        foreach ($fields as $key => $field) {
            if (array_key_exists($key, $data)) {
                array_push($rows, ...$this->fieldRows((string) $key, $field, $data[$key], $stateCode, $context));
            }
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, mixed>  $context
     * @return list<array<string, mixed>>
     */
    private function fieldRows(string $key, array $field, mixed $value, ?string $stateCode, array $context): array
    {
        if ($this->isBlank($value)) {
            return [];
        }

        $label = $this->fillStateName($field['label'] ?? $this->headline($key), $stateCode);
        $type = $field['type'] ?? 'text';

        if ($type === 'matrix' && is_array($value)) {
            return $this->matrixRows($field, $value);
        }

        if ($type === 'repeater' && is_array($value)) {
            $extraSchemas = $key === 'responsible_people' ? ($context['person_extra_schemas'] ?? []) : [];

            return [$this->itemsRow($label, $this->repeaterItems($field, $value, $extraSchemas))];
        }

        if ($type === 'person_state_extra' && is_array($value)) {
            return [$this->itemsRow($label, $this->personExtraItems($field, $value, $context['people'] ?? []))];
        }

        // Any other nested shape is flattened under this field's label.
        if (is_array($value) && ! in_array($type, ['address', 'anywhere_states'], true)
            && (! array_is_list($value) || array_filter($value, 'is_array'))) {
            return $this->untypedRows($value, $label);
        }

        return $this->valueRow($label, $this->formatValue($value, $field));
    }

    /**
     * A matrix answer is one cell per state; each becomes its own row with
     * the state named in the label.
     *
     * @param  array<string, mixed>  $field
     * @param  array<string, mixed>  $cells
     * @return list<array<string, mixed>>
     */
    private function matrixRows(array $field, array $cells): array
    {
        $template = $field['label'] ?? 'Value';
        $cellField = ['type' => $field['cell_type'] ?? 'text'];
        $rows = [];

        foreach ($cells as $code => $cell) {
            if ($this->isBlank($cell)) {
                continue;
            }

            $label = str_contains($template, '{state_name}')
                ? $this->fillStateName($template, (string) $code)
                : $template.' — '.$this->stateName((string) $code);

            array_push($rows, ...$this->valueRow($label, $this->formatValue($cell, $cellField)));
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $field
     * @param  array<int|string, mixed>  $rows
     * @param  array<string, array<string, array<string, mixed>>>  $extraSchemas  state code => person-extra schema
     * @return list<array{title: string, blocks: list<array<string, mixed>>}>
     */
    private function repeaterItems(array $field, array $rows, array $extraSchemas): array
    {
        $itemLabel = $field['item_label'] ?? 'Item';
        $schema = $field['schema'] ?? [];
        $items = [];
        $number = 0;

        foreach ($rows as $row) {
            if ($this->isBlank($row)) {
                continue;
            }

            $number++;

            if (! is_array($row)) {
                $items[] = ['title' => "{$itemLabel} {$number}", 'blocks' => [
                    ['title' => null, 'rows' => $this->valueRow('Value', $this->formatValue($row, []))],
                ]];

                continue;
            }

            $name = trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? ''));
            $blocks = [['title' => null, 'rows' => $this->schemaRows($schema, $row)]];
            $claimed = array_fill_keys(array_keys($schema), true);

            foreach ($extraSchemas as $code => $extraSchema) {
                $blocks[] = [
                    'title' => $this->stateName($code).' Requirements',
                    'rows' => $this->schemaRows($extraSchema, $row),
                ];
                $claimed += array_fill_keys(array_keys($extraSchema), true);
            }

            $blocks[] = ['title' => 'Other Saved Fields', 'rows' => $this->untypedRows(array_diff_key($row, $claimed))];

            $items[] = [
                'title' => $itemLabel.' '.$number.($name !== '' ? " — {$name}" : ''),
                'blocks' => array_values(array_filter($blocks, fn (array $block): bool => $block['rows'] !== [])),
            ];
        }

        return $items;
    }

    /**
     * State data's responsible_people_extra is keyed by the person's _id on
     * the shared responsible_people rows; title each item with their name.
     *
     * @param  array<string, mixed>  $field
     * @param  array<string, mixed>  $extras
     * @param  array<string, array<string, mixed>>  $people
     * @return list<array{title: string, blocks: list<array<string, mixed>>}>
     */
    private function personExtraItems(array $field, array $extras, array $people): array
    {
        $schema = $field['schema'] ?? [];
        $items = [];

        foreach ($extras as $personId => $answers) {
            if ($this->isBlank($answers)) {
                continue;
            }

            $person = $people[$personId] ?? [];
            $name = trim(($person['first_name'] ?? '').' '.($person['last_name'] ?? ''));

            $rows = is_array($answers)
                ? [...$this->schemaRows($schema, $answers), ...$this->untypedRows(array_diff_key($answers, $schema))]
                : $this->valueRow('Value', $this->formatValue($answers, []));

            $items[] = [
                'title' => $name !== '' ? $name : "Person {$personId} (no longer on the application)",
                'blocks' => [['title' => null, 'rows' => $rows]],
            ];
        }

        return $items;
    }

    /**
     * Rows for the schema fields present in $row, in schema order.
     *
     * @param  array<string, array<string, mixed>>  $schema
     * @param  array<string, mixed>  $row
     * @return list<array<string, mixed>>
     */
    private function schemaRows(array $schema, array $row): array
    {
        $rows = [];

        foreach ($schema as $key => $field) {
            if (array_key_exists($key, $row)) {
                array_push($rows, ...$this->fieldRows((string) $key, $field, $row[$key], null, []));
            }
        }

        return $rows;
    }

    /**
     * Rows for keys no definition describes: labels come from the key
     * names and nested arrays are flattened, so nothing is dropped.
     *
     * @param  array<int|string, mixed>  $data
     * @return list<array<string, mixed>>
     */
    private function untypedRows(array $data, ?string $prefix = null): array
    {
        $rows = [];

        foreach ($data as $key => $value) {
            if ($key === '_id' || $this->isBlank($value)) {
                continue;
            }

            $name = is_int($key) ? '#'.($key + 1) : $this->headline($key);
            $label = $prefix !== null ? "{$prefix} › {$name}" : $name;

            if (! is_array($value) || $this->isAddress($value)) {
                array_push($rows, ...$this->valueRow($label, $this->formatValue($value, [])));
            } elseif (array_is_list($value) && ! array_filter($value, 'is_array')) {
                array_push($rows, ...$this->valueRow($label, $this->formatList($value, [])));
            } else {
                array_push($rows, ...$this->untypedRows($value, $label));
            }
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function formatValue(mixed $value, array $field): string
    {
        $type = $field['type'] ?? null;

        if (is_array($value)) {
            return match (true) {
                $type === 'anywhere_states' || $this->isAppliesValue($value) => $this->formatApplies($value),
                $type === 'address' || $this->isAddress($value) => $this->formatAddress($value),
                array_is_list($value) => $this->formatList($value, $field),
                default => json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '',
            };
        }

        return $this->answers->format($value, $field);
    }

    /**
     * @param  list<mixed>  $values
     * @param  array<string, mixed>  $field
     */
    private function formatList(array $values, array $field): string
    {
        return collect($values)
            ->reject(fn ($value): bool => $this->isBlank($value))
            ->map(fn ($value): string => $this->formatValue($value, $field))
            ->implode(', ');
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function formatApplies(array $value): string
    {
        $states = collect($value['states'] ?? [])
            ->filter(fn ($code): bool => is_string($code) && $code !== '')
            ->map(fn (string $code): string => $this->stateName($code))
            ->implode(', ');

        $anywhere = $value['anywhere'] ?? null;

        return match (true) {
            in_array($anywhere, [true, 1, '1'], true) => $states !== '' ? "Yes — {$states}" : 'Yes',
            in_array($anywhere, [false, 0, '0'], true) => 'No',
            default => $states,
        };
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function formatAddress(array $address): string
    {
        $stateZip = trim(($address['state'] ?? '').' '.($address['zip'] ?? ''));

        $line = collect([$address['line1'] ?? null, $address['line2'] ?? null, $address['city'] ?? null, $stateZip])
            ->filter(fn ($part): bool => is_string($part) && trim($part) !== '')
            ->map(fn (string $part): string => trim($part))
            ->implode(', ');

        if ($line === '' && filled($address['formatted_address'] ?? null)) {
            $line = trim((string) $address['formatted_address']);
        }

        $lines = [$line];

        if (filled($address['county'] ?? null)) {
            $lines[] = 'County: '.$address['county'];
        }

        if (filled($address['country'] ?? null) && strtoupper((string) $address['country']) !== 'US') {
            $lines[] = 'Country: '.$address['country'];
        }

        // Any other address key is shown too rather than silently dropped.
        $known = ['line1', 'line2', 'city', 'state', 'zip', 'county', 'country', ...self::ADDRESS_META_KEYS];

        foreach (array_diff_key($address, array_flip($known)) as $key => $extra) {
            if (! $this->isBlank($extra) && ! is_array($extra)) {
                $lines[] = $this->headline((string) $key).': '.$extra;
            }
        }

        return trim(implode("\n", array_filter($lines, fn (string $l): bool => $l !== '')));
    }

    /**
     * @param  list<string>  $stateCodes
     * @return list<array<string, mixed>>
     */
    private function applicationRows(FormApplication $application, array $stateCodes): array
    {
        $formType = (string) $application->form_type;
        $business = $application->business;
        $creator = $application->createdBy;

        $rows = [
            ['Application ID', '#'.$application->id],
            ['Form Type', (FormTypeConfig::exists($formType) ? FormTypeConfig::get($formType)['name'].' ' : '')."({$formType})"],
            ['Business', $business ? "{$business->name} (#{$business->id})" : ($application->business_id ? "#{$application->business_id}" : null)],
            ['Created By', $creator ? "{$creator->name} · {$creator->email}" : null],
            ['Selected States', collect($stateCodes)->map(fn (string $code): string => "{$this->stateName($code)} ({$code})")->implode(', ')],
            ['Application Status', Str::headline((string) $application->status)],
            ['Wizard Phase', Str::headline((string) $application->current_phase)],
            ['Definition Version', $application->definition_version],
            ['Started', $this->dateTime($application->created_at)],
            ['Paid', $this->dateTime($application->paid_at)],
            ['Submitted', $this->dateTime($application->submitted_at)],
            ['Locked', $this->dateTime($application->locked_at)],
            ['Last Updated', $this->dateTime($application->updated_at)],
            ['Stripe Checkout Session', $application->stripe_checkout_session_id],
            ['Stripe Payment Intent', $application->stripe_payment_intent_id],
            ['Stripe Subscription', $application->stripe_subscription_id],
        ];

        foreach ($application->payments()->oldest('id')->get() as $payment) {
            $rows[] = ['Payment #'.$payment->id, collect([
                '$'.number_format(($payment->amount_cents ?? 0) / 100, 2).' '.strtoupper((string) ($payment->currency ?? 'usd')),
                $payment->status?->label(),
                $payment->paid_at ? 'paid '.$this->dateTime($payment->paid_at) : null,
                $payment->refunded_at ? 'refunded '.$this->dateTime($payment->refunded_at) : null,
                $payment->stripe_payment_intent_id,
                $payment->livemode === false ? 'test mode' : null,
            ])->filter()->implode(' · ')];
        }

        return collect($rows)
            ->flatMap(fn (array $row): array => $this->valueRow($row[0], trim((string) $row[1])))
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function stateMetaRows(?FormApplicationState $record): array
    {
        if (! $record) {
            return $this->valueRow('State Record', 'None saved for this state');
        }

        return collect([
            ['State Record', '#'.$record->id],
            ['Admin Status', $record->current_admin_status?->label()],
            ['Admin Status Changed', $this->dateTime($record->current_admin_status_changed_at)],
            ['Wizard Status', $record->isComplete() ? 'Complete' : Str::headline((string) $record->status)],
            ['Completed', $this->dateTime($record->completed_at)],
        ])
            ->flatMap(fn (array $row): array => $this->valueRow($row[0], trim((string) $row[1])))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, array<string, mixed>>
     */
    private function personExtraSchema(array $definition): array
    {
        return $definition['state_steps']['state_responsible_people']['fields']['responsible_people_extra']['schema'] ?? [];
    }

    /**
     * Decrypt anything that still looks like a Laravel encrypted payload —
     * e.g. a value encrypted under a field the definition no longer marks
     * sensitive — so the dump never prints ciphertext.
     */
    private function decryptRemaining(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->decryptRemaining($item), $value);
        }

        if (is_string($value) && str_starts_with($value, 'eyJpdiI6')) {
            try {
                return $this->encrypter->decryptString($value);
            } catch (DecryptException) {
                return '•••• (could not decrypt)';
            }
        }

        return $value;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function itemsRow(string $label, array $items): array
    {
        return ['label' => $label, 'items' => $items];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function valueRow(string $label, string $value): array
    {
        return $value === '' ? [] : [['label' => $label, 'value' => $value]];
    }

    private function isBlank(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if (! $this->isBlank($item)) {
                    return false;
                }
            }

            return true;
        }

        return $value === null || (is_string($value) && trim($value) === '');
    }

    /**
     * @param  array<int|string, mixed>  $value
     */
    private function isAddress(array $value): bool
    {
        return array_key_exists('line1', $value) || array_key_exists('city', $value) || array_key_exists('zip', $value);
    }

    /**
     * @param  array<int|string, mixed>  $value
     */
    private function isAppliesValue(array $value): bool
    {
        return array_key_exists('anywhere', $value) && array_key_exists('states', $value);
    }

    private function fillStateName(string $text, ?string $stateCode): string
    {
        return str_replace('{state_name}', $stateCode ? $this->stateName($stateCode) : 'each state', $text);
    }

    private function stateName(string $code): string
    {
        return (string) config('states.'.$code, $code);
    }

    private function headline(string $key): string
    {
        return collect(explode(' ', Str::headline($key)))
            ->map(fn (string $word): string => self::ACRONYMS[$word] ?? $word)
            ->implode(' ');
    }

    private function dateTime(?CarbonInterface $at): ?string
    {
        return $at?->eastern()->format('M j, Y g:ia');
    }
}
