@php
$type = $field['type'] ?? 'text';
$wireModel = "{$prefix}.{$fieldKey}";
// Fields that drive conditionals or are toggle types need .live for instant visibility updates
$needsLive = ($drivesConditional ?? false) || in_array($type, ['checkbox', 'radio', 'select']);
$stateCode = $stateCode ?? null;
$stateName = $stateCode ? config("states.{$stateCode}", '') : '';
$label = str_replace('{state_name}', $stateName, $field['label'] ?? ucwords(str_replace('_', ' ', $fieldKey)));
@endphp

@switch($type)
@case('text')
@include('livewire.forms.partials.fields.text', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label'))
@break

@case('email')
@include('livewire.forms.partials.fields.text', array_merge(compact('fieldKey', 'field', 'wireModel', 'needsLive',
'label'), ['inputType' => 'email']))
@break

@case('select')
@include('livewire.forms.partials.fields.select', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label'))
@break

@case('radio')
@include('livewire.forms.partials.fields.radio', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label'))
@break

@case('checkbox')
@include('livewire.forms.partials.fields.checkbox', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label'))
@break

@case('date')
@include('livewire.forms.partials.fields.date', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label'))
@break

@case('percent')
@include('livewire.forms.partials.fields.percent', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label'))
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
@include('livewire.forms.partials.fields.text', compact('fieldKey', 'field', 'wireModel', 'needsLive', 'label'))
@endswitch