@props(['name', 'value', 'label', 'selected' => false, 'disabled' => false, 'disabledReason' => null])
<label @class([
    'state-card block',
    'state-card-selected' => $selected,
    'state-card-disabled' => $disabled,
])>
    <input 
        type="checkbox" 
        name="{{ $name }}" 
        value="{{ $value }}" 
        class="sr-only" 
        @checked($selected)
        @disabled($disabled)
        {{ $attributes }}
    />
    <span class="font-medium">{{ $label }}</span>
    @if($disabled && $disabledReason)
        <span class="mt-1 flex items-center gap-1 text-xs text-text-secondary">
            <flux:icon name="lock-closed" class="size-3" />
            {{ $disabledReason }}
        </span>
    @endif
</label>
