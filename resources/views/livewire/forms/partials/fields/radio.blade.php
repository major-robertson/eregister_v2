<flux:field wire:key="field-{{ $wireModel }}">
    <flux:label>{{ $label }}</flux:label>
    <div class="mt-2 space-y-2">
        @foreach ($field['options'] ?? [] as $value => $optionLabel)
            <label class="flex items-center gap-2">
                <input
                    type="radio"
                    wire:model.live="{{ $wireModel }}"
                    value="{{ $value }}"
                    name="{{ $wireModel }}"
                    class="h-4 w-4 border-neutral-300 text-blue-600 focus:ring-blue-500"
                >
                <span class="text-sm">{{ $optionLabel }}</span>
            </label>
        @endforeach
    </div>
    @error($wireModel)
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
    @enderror
</flux:field>
