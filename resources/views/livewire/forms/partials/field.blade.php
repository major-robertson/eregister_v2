@php
$type = $field['type'] ?? 'text';
$wireModel = "{$prefix}.{$fieldKey}";
// Fields that drive conditionals or are toggle types need .live for instant visibility updates
$needsLive = ($drivesConditional ?? false) || in_array($type, ['checkbox', 'radio', 'select']);
$stateCode = $stateCode ?? null;
$stateName = $stateCode ? config("states.{$stateCode}", '') : '';
$label = str_replace('{state_name}', $stateName, $field['label'] ?? ucwords(str_replace('_', ' ', $fieldKey)));

// Compute contextual badge + help text once here so each typed partial
// just renders the result. `badge_when` and `help_when` are both lists
// of {condition, ...} entries evaluated first-match-wins via the
// existing ConditionEvaluator. No match (or no _when at all) means the
// badge stays null and help falls back to the static `help` string.
$badge = null;
$resolvedHelp = $field['help'] ?? null;
$hasConditionalContent = ! empty($field['badge_when']) || ! empty($field['help_when']);
if ($hasConditionalContent) {
    $evaluator = app(\App\Domains\Forms\Engine\ConditionEvaluator::class);
    $conditionContext = [
        'coreData' => $this->coreData ?? [],
        'stateData' => $this->stateData ?? [],
    ];

    foreach ($field['badge_when'] ?? [] as $candidate) {
        if ($evaluator->evaluate($candidate['condition'] ?? [], $conditionContext)) {
            $badge = $candidate;
            break;
        }
    }

    foreach ($field['help_when'] ?? [] as $candidate) {
        if ($evaluator->evaluate($candidate['condition'] ?? [], $conditionContext)) {
            $resolvedHelp = $candidate['help'] ?? $resolvedHelp;
            break;
        }
    }
}
@endphp

@switch($type)
@case('text')
@include('livewire.forms.partials.fields.text', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label', 'badge', 'resolvedHelp'))
@break

@case('email')
@include('livewire.forms.partials.fields.text', array_merge(compact('fieldKey', 'field', 'wireModel', 'needsLive',
'label', 'badge', 'resolvedHelp'), ['inputType' => 'email']))
@break

@case('textarea')
@include('livewire.forms.partials.fields.textarea', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label', 'badge', 'resolvedHelp'))
@break

@case('select')
@include('livewire.forms.partials.fields.select', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label', 'badge', 'resolvedHelp'))
@break

@case('radio')
@include('livewire.forms.partials.fields.radio', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label', 'badge', 'resolvedHelp'))
@break

@case('checkbox')
@include('livewire.forms.partials.fields.checkbox', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label', 'resolvedHelp'))
@break

@case('date')
@include('livewire.forms.partials.fields.date', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label', 'badge', 'resolvedHelp'))
@break

@case('percent')
@include('livewire.forms.partials.fields.percent', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label', 'badge', 'resolvedHelp'))
@break

@case('address')
@include('livewire.forms.partials.fields.address', compact('fieldKey', 'field', 'prefix', 'label'))
@break

@case('repeater')
@include('livewire.forms.partials.fields.repeater', [
'fieldKey' => $fieldKey,
'field' => $field,
'prefix' => $prefix,
'data' => $data,
'label' => $label,
'statePersonFields' => $statePersonFields ?? [],
])
@break

@case('person_state_extra')
@include('livewire.forms.partials.fields.person-state-extra', [
'field' => $field,
'responsiblePeople' => $this->coreData['responsible_people'] ?? [],
'stateCode' => $this->currentStateCode(),
])
@break

@case('matrix')
@include('livewire.forms.partials.fields.matrix', [
'fieldKey' => $fieldKey,
'field' => $field,
'prefix' => $prefix,
'data' => $data,
'label' => $label,
'resolvedHelp' => $resolvedHelp,
])
@break

@case('anywhere_states')
@include('livewire.forms.partials.fields.anywhere-states', [
'fieldKey' => $fieldKey,
'field' => $field,
'prefix' => $prefix,
'data' => $data,
'label' => $label,
'badge' => $badge,
'resolvedHelp' => $resolvedHelp,
])
@break

@default
@include('livewire.forms.partials.fields.text', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label', 'badge', 'resolvedHelp'))
@endswitch
