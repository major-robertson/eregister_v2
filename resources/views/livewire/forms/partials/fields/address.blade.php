@php
$basePath = "{$prefix}.{$fieldKey}";
$hideLabel = $hideLabel ?? false;
// When true every sub-input renders disabled (read-only display). Used
// for the system-managed principal location row, which mirrors the
// Principal Business Address and is edited on the Contact & Address step.
$disabled = $disabled ?? false;
// Flux's native disabled state only mutes the text and keeps a white
// fill, so force an obvious gray. Flux forwards this class differently
// per control: flux:input puts it on a wrapper (so target the inner
// input via [&_input]), while flux:select puts it on the <select>
// element itself (so a bare !bg-zinc-100 hits it directly). Including
// both covers both controls; !important beats Flux's own bg-white.
$lockedClass = $disabled
    ? '!bg-zinc-100 cursor-not-allowed [&_input]:!bg-zinc-100 [&_input]:cursor-not-allowed dark:!bg-zinc-800 dark:[&_input]:!bg-zinc-800'
    : null;
@endphp

<div class="space-y-4" wire:key="address-{{ $basePath }}">
    @if (!$hideLabel)
    <flux:label class="text-base font-medium">{{ $label }}</flux:label>
    @endif

    <flux:field wire:key="field-{{ $basePath }}-line1">
        <flux:label>Street Address</flux:label>
        <flux:input wire:model="{{ $basePath }}.line1" placeholder="123 Main Street" name="{{ $basePath }}.line1" :disabled="$disabled" :class="$lockedClass" />
        @error("{$basePath}.line1")
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
        @enderror
    </flux:field>

    <flux:field wire:key="field-{{ $basePath }}-line2">
        <flux:label>Address Line 2 (optional)</flux:label>
        <flux:input wire:model="{{ $basePath }}.line2" placeholder="Suite 100" name="{{ $basePath }}.line2" :disabled="$disabled" :class="$lockedClass" />
    </flux:field>

    <div class="grid grid-cols-6 gap-4">
        <flux:field class="col-span-3" wire:key="field-{{ $basePath }}-city">
            <flux:label>City</flux:label>
            <flux:input wire:model="{{ $basePath }}.city" placeholder="City" name="{{ $basePath }}.city" :disabled="$disabled" :class="$lockedClass" />
            @error("{$basePath}.city")
            <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
            @enderror
        </flux:field>

        <flux:field class="col-span-1" wire:key="field-{{ $basePath }}-state">
            <flux:label>State</flux:label>
            {{-- Live so dependent selectors (e.g. the locations[] county
                 list keyed by this state) refresh as soon as it changes. --}}
            <flux:select wire:model.live="{{ $basePath }}.state" name="{{ $basePath }}.state" :disabled="$disabled" :class="$lockedClass">
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
                mask="99999"
                :disabled="$disabled"
                :class="$lockedClass"
            />
            @error("{$basePath}.zip")
            <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
            @enderror
        </flux:field>
    </div>
</div>