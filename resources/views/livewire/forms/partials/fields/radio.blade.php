{{--
    Question radios rendered with Flux's segmented variant — the
    canonical Flux pattern for short option sets (Yes/No, Monthly/
    Quarterly). Proper selected-state highlight and theming come from
    Flux itself. (The custom radio-buttons partial remains available as
    an alternative filled-button style.)
--}}
<flux:field wire:key="field-{{ $wireModel }}">
    <flux:label :badge="$badge['label'] ?? null" :badge-color="$badge['color'] ?? null">
        {{ $label }}
    </flux:label>
    @if (! empty($resolvedHelp))
        @include('livewire.forms.partials.field-help', ['help' => $resolvedHelp])
    @endif
    <flux:radio.group
        wire:model.live="{{ $wireModel }}"
        variant="segmented"
        class="w-full sm:w-fit"
    >
        @foreach ($field['options'] ?? [] as $value => $optionLabel)
            <flux:radio value="{{ $value }}" label="{{ $optionLabel }}" />
        @endforeach
    </flux:radio.group>
    @error($wireModel)
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
    @enderror
</flux:field>
