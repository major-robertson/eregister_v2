<div class="mx-auto max-w-xl px-4 py-12">
    <div class="mb-8 text-center">
        <flux:heading size="xl">Business Setup</flux:heading>
        <p class="mt-2 text-text-secondary">
            Step {{ $step }} of 2
        </p>
    </div>

    {{-- Progress bar --}}
    <div class="mb-8 flex gap-2">
        <div class="h-2 flex-1 rounded {{ $step >= 1 ? 'bg-primary' : 'bg-zinc-200' }}"></div>
        <div class="h-2 flex-1 rounded {{ $step >= 2 ? 'bg-primary' : 'bg-zinc-200' }}"></div>
    </div>

    <x-ui.card>
        @if ($step === 1)
            {{-- Step 1: Business Name --}}
            <flux:heading size="lg" class="mb-4">What's your business name?</flux:heading>

            <form wire:submit="nextStep" class="space-y-6">
                <flux:field>
                    <flux:label>Legal Business Name</flux:label>
                    <flux:input
                        wire:model="legalName"
                        placeholder="Enter your legal business name"
                        autofocus
                    />
                    @error('legalName')
                        <flux:text class="text-sm text-danger">{{ $message }}</flux:text>
                    @enderror
                </flux:field>

                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">
                        Continue
                    </flux:button>
                </div>
            </form>
        @else
            {{-- Step 2: Address --}}
            <flux:heading size="lg" class="mb-4">Business Address</flux:heading>

            <form wire:submit="complete" class="space-y-4">
                <flux:field>
                    <flux:label>Street Address</flux:label>
                    <flux:input
                        wire:model="businessAddress.line1"
                        placeholder="123 Main Street"
                    />
                    @error('businessAddress.line1')
                        <flux:text class="text-sm text-danger">{{ $message }}</flux:text>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Address Line 2 (optional)</flux:label>
                    <flux:input
                        wire:model="businessAddress.line2"
                        placeholder="Suite 100"
                    />
                </flux:field>

                <div class="grid grid-cols-6 gap-4">
                    <flux:field class="col-span-3">
                        <flux:label>City</flux:label>
                        <flux:input wire:model="businessAddress.city" placeholder="City" />
                        @error('businessAddress.city')
                            <flux:text class="text-sm text-danger">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    <flux:field class="col-span-1">
                        <flux:label>State</flux:label>
                        <flux:select wire:model="businessAddress.state">
                            <flux:select.option value="">--</flux:select.option>
                            @foreach ($states as $code => $name)
                                <flux:select.option value="{{ $code }}">{{ $code }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @error('businessAddress.state')
                            <flux:text class="text-sm text-danger">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    <flux:field class="col-span-2">
                        <flux:label>ZIP Code</flux:label>
                        <flux:input wire:model="businessAddress.zip" placeholder="12345" />
                        @error('businessAddress.zip')
                            <flux:text class="text-sm text-danger">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>
                </div>

                <div class="flex justify-between pt-4">
                    <flux:button type="button" wire:click="previousStep" variant="ghost">
                        Back
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        Complete Setup
                    </flux:button>
                </div>
            </form>
        @endif
    </x-ui.card>
</div>
