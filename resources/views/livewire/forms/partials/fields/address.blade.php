@php
    $basePath = "{$prefix}.{$fieldKey}";
    $hideLabel = $hideLabel ?? false;
@endphp

<div class="space-y-4" wire:key="address-{{ $basePath }}">
    @if (!$hideLabel)
        <flux:label class="text-base font-medium">{{ $label }}</flux:label>
    @endif

    <flux:field wire:key="field-{{ $basePath }}-line1">
        <flux:label>Street Address</flux:label>
        <flux:input
            wire:model="{{ $basePath }}.line1"
            placeholder="123 Main Street"
            name="{{ $basePath }}.line1"
        />
        @error("{$basePath}.line1")
            <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
        @enderror
    </flux:field>

    <flux:field wire:key="field-{{ $basePath }}-line2">
        <flux:label>Address Line 2 (optional)</flux:label>
        <flux:input
            wire:model="{{ $basePath }}.line2"
            placeholder="Suite 100"
            name="{{ $basePath }}.line2"
        />
    </flux:field>

    <div class="grid grid-cols-6 gap-4">
        <flux:field class="col-span-3" wire:key="field-{{ $basePath }}-city">
            <flux:label>City</flux:label>
            <flux:input
                wire:model="{{ $basePath }}.city"
                placeholder="City"
                name="{{ $basePath }}.city"
            />
            @error("{$basePath}.city")
                <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
            @enderror
        </flux:field>

        <flux:field class="col-span-1" wire:key="field-{{ $basePath }}-state">
            <flux:label>State</flux:label>
            <flux:select wire:model="{{ $basePath }}.state" name="{{ $basePath }}.state">
                <flux:select.option value="">--</flux:select.option>
                @foreach (config('states') as $code => $name)
                    <flux:select.option value="{{ $code }}">{{ $code }}</flux:select.option>
                @endforeach
            </flux:select>
            @error("{$basePath}.state")
                <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
            @enderror
        </flux:field>

        <flux:field class="col-span-2" wire:key="field-{{ $basePath }}-zip">
            <flux:label>ZIP Code</flux:label>
            <flux:input
                wire:model="{{ $basePath }}.zip"
                placeholder="12345"
                name="{{ $basePath }}.zip"
            />
            @error("{$basePath}.zip")
                <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
            @enderror
        </flux:field>
    </div>
</div>
