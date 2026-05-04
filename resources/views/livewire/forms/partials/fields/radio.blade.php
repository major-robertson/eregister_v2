{{--
    Compact button-radio group. Each option is a real button with a
    min-width so Yes / No / etc. stay close to the question instead of
    stretching to full container width like the segmented variant did.
    On mobile we let the row span full width; from sm: up the buttons
    auto-size around their content, keeping the eye on the question.

    The `*:min-w-28` rule sets a comfortable minimum width on every
    direct child (each radio button) so labels of varying length —
    "Yes" / "No" / "All Year" / "Profit" — all render at a consistent
    size and don't visually wobble between adjacent fields.

    Flux's buttons variant wraps to a second row when the container
    can't fit the options, so the partial handles 2-, 3-, and 4-option
    fields uniformly without per-field tuning.
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
        variant="buttons"
        class="w-full sm:w-auto *:min-w-28"
    >
        @foreach ($field['options'] ?? [] as $value => $optionLabel)
            <flux:radio value="{{ $value }}">{{ $optionLabel }}</flux:radio>
        @endforeach
    </flux:radio.group>
    @error($wireModel)
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
    @enderror
</flux:field>
