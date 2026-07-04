<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.page-header title="Resale Profile Setup" subtitle="Three quick steps and you're generating certificates." />

    {{-- Step indicator --}}
    @php $steps = ['Certificate Details', 'Tax Registrations', 'E-Signature']; @endphp
    <div class="flex items-center justify-center gap-2">
        @foreach ($steps as $index => $label)
            @php
                $stepNum = $index + 1;
                $isActive = $step === $stepNum;
                $isComplete = $step > $stepNum;
            @endphp
            <button
                type="button"
                wire:click="goToStep({{ $stepNum }})"
                @disabled($stepNum > $step)
                @class([
                    'flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium transition-colors',
                    'bg-blue-600 text-white' => $isActive,
                    'bg-green-500 text-white' => $isComplete,
                    'bg-zinc-200 text-zinc-500' => ! $isActive && ! $isComplete,
                ])
            >
                @if ($isComplete)
                    <flux:icon name="check" class="h-4 w-4" />
                @else
                    {{ $stepNum }}
                @endif
            </button>
            <span @class(['hidden text-sm md:inline', 'font-medium text-text-primary' => $isActive, 'text-zinc-500' => ! $isActive])>
                {{ $label }}
            </span>
            @if ($stepNum < count($steps))
                <div class="h-px w-8 bg-zinc-200"></div>
            @endif
        @endforeach
    </div>

    <x-ui.card>
        @if ($step === 1)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Certificate Details</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">
                        This information prints on every certificate you generate.
                    </flux:text>
                </div>

                <flux:field>
                    <flux:label>Products / services you sell *</flux:label>
                    <flux:textarea wire:model="products_description" rows="2" maxlength="100"
                        placeholder="e.g. Apparel, footwear, and fashion accessories" />
                    <flux:description>Max 100 characters — appears as the property description on certificates.</flux:description>
                    <flux:error name="products_description" />
                </flux:field>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Business contact email *</flux:label>
                        <flux:input type="email" wire:model="contact_email" />
                        <flux:error name="contact_email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Business phone *</flux:label>
                        <flux:input wire:model="contact_phone" mask="(999) 999-9999" placeholder="(555) 123-4567" />
                        <flux:error name="contact_phone" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Your title *</flux:label>
                    <flux:input wire:model="signer_title" placeholder="Owner, Manager, CFO..." />
                    <flux:description>Prints next to your signature as the authorized signer's title.</flux:description>
                    <flux:error name="signer_title" />
                </flux:field>
            </div>
        @elseif ($step === 2)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">State Tax Registrations</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">
                        Add every state where you hold a sales tax permit. The first one is your home state —
                        its tax id is used for states where you aren't registered (when they allow it).
                    </flux:text>
                </div>

                <flux:callout color="emerald" icon="receipt-percent">
                    <flux:callout.heading>Don't have a sales &amp; use tax registration ID?</flux:callout.heading>
                    <flux:callout.text>
                        We can register your business for a sales tax permit in any state — we prepare and
                        file the paperwork for you.
                        <a href="{{ route('sales-tax.registrations.start') }}" class="font-medium underline">
                            Get registered here</a>, then come back and add your new permit.
                    </flux:callout.text>
                </flux:callout>

                <flux:error name="registrations" />

                <div class="space-y-3">
                    @foreach ($registrations as $index => $registration)
                        <div class="flex items-start gap-3" wire:key="registration-{{ $index }}">
                            <div class="w-52">
                                <flux:select variant="combobox" clearable placeholder="Select state..."
                                    wire:model="registrations.{{ $index }}.state_code">
                                    @foreach ($this->states as $state)
                                        <flux:select.option value="{{ $state->state_code }}">{{ $state->state_name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="registrations.{{ $index }}.state_code" />
                            </div>
                            <div class="flex-1">
                                <flux:input wire:model="registrations.{{ $index }}.tax_id" placeholder="Tax / permit ID" />
                                <flux:error name="registrations.{{ $index }}.tax_id" />
                            </div>
                            <div class="flex h-10 items-center gap-2">
                                @if ($index === 0)
                                    <flux:badge color="blue" size="sm">Home</flux:badge>
                                @else
                                    <flux:button type="button" variant="ghost" size="sm" icon="trash"
                                        wire:click="removeRegistration({{ $index }})" />
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <flux:button type="button" variant="ghost" size="sm" icon="plus" wire:click="addRegistration">
                    Add another registration
                </flux:button>
            </div>
        @else
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Your E-Signature</flux:heading>
                    <flux:text class="mt-1 text-zinc-500">
                        Draw your signature once — it's applied to every certificate automatically.
                    </flux:text>
                </div>

                <livewire:resale-cert.signature-pad />

                <flux:error name="signature" />
            </div>
        @endif

        <div class="mt-8 flex items-center justify-between border-t border-border pt-4">
            <div>
                @if ($step > 1)
                    <flux:button type="button" wire:click="previousStep" variant="ghost">Back</flux:button>
                @endif
            </div>
            <div>
                @if ($step < $totalSteps)
                    <flux:button type="button" wire:click="nextStep" variant="primary">Continue</flux:button>
                @else
                    <flux:button wire:click="finish" variant="primary" :disabled="! $this->hasSignature">
                        Finish Setup
                    </flux:button>
                @endif
            </div>
        </div>
    </x-ui.card>
</div>
