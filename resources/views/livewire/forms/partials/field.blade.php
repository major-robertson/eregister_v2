@php
$type = $field['type'] ?? 'text';
$wireModel = "{$prefix}.{$fieldKey}";
// Fields that drive conditionals or are toggle types need .live for instant visibility updates
$needsLive = ($drivesConditional ?? false) || in_array($type, ['checkbox', 'radio', 'select']);
$stateCode = $stateCode ?? null;
$stateName = $stateCode ? config("states.{$stateCode}", '') : '';
$label = str_replace('{state_name}', $stateName, $field['label'] ?? ucwords(str_replace('_', ' ', $fieldKey)));

// Compute the contextual badge once here so each typed partial just
// renders the result. `badge_when` is a list of {condition, label, color}
// entries evaluated first-match-wins via the existing ConditionEvaluator.
// No match (or no badge_when at all) leaves $badge null.
$badge = null;
if (! empty($field['badge_when'])) {
    $evaluator = app(\App\Domains\Forms\Engine\ConditionEvaluator::class);
    $badgeContext = [
        'coreData' => $this->coreData ?? [],
        'stateData' => $this->stateData ?? [],
    ];
    foreach ($field['badge_when'] as $candidate) {
        if ($evaluator->evaluate($candidate['condition'] ?? [], $badgeContext)) {
            $badge = $candidate;
            break;
        }
    }
}
@endphp

@switch($type)
@case('text')
@include('livewire.forms.partials.fields.text', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label', 'badge'))
@break

@case('email')
@include('livewire.forms.partials.fields.text', array_merge(compact('fieldKey', 'field', 'wireModel', 'needsLive',
'label', 'badge'), ['inputType' => 'email']))
@break

@case('select')
@include('livewire.forms.partials.fields.select', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label', 'badge'))
@break

@case('radio')
@include('livewire.forms.partials.fields.radio', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label'))
@break

@case('checkbox')
@include('livewire.forms.partials.fields.checkbox', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label'))
@break

@case('date')
@include('livewire.forms.partials.fields.date', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label', 'badge'))
@break

@case('percent')
@include('livewire.forms.partials.fields.percent', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label', 'badge'))
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

@default
@include('livewire.forms.partials.fields.text', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label', 'badge'))
@endswitch
