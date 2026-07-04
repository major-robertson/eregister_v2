<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.page-header title="Resale Certificate Settings" subtitle="What prints on your certificates, and where you're registered." />

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <x-slot:header>
                <flux:heading size="lg">Certificate Details</flux:heading>
            </x-slot:header>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Products / services you sell *</flux:label>
                    <flux:textarea wire:model="products_description" rows="2" maxlength="100" />
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
                    <flux:error name="signer_title" />
                </flux:field>
            </div>
        </x-ui.card>

        <x-ui.card>
            <x-slot:header>
                <flux:heading size="lg">Tax Registrations</flux:heading>
            </x-slot:header>

            <div class="space-y-4">
                <flux:callout color="emerald" icon="receipt-percent">
                    <flux:callout.text>
                        Don't have a sales &amp; use tax registration ID for a state you need?
                        <a href="{{ route('sales-tax.registrations.start') }}" class="font-medium underline">
                            We can register you</a> — paperwork prepared and filed for you.
                    </flux:callout.text>
                </flux:callout>

                <flux:error name="registrations" />

                <div class="space-y-3">
                    @foreach ($registrations as $index => $registration)
                        <div class="flex flex-wrap items-start gap-3" wire:key="registration-{{ $index }}">
                            <div class="w-48">
                                <flux:select variant="combobox" clearable placeholder="Select state..."
                                    wire:model="registrations.{{ $index }}.state_code">
                                    @foreach ($this->states as $state)
                                        <flux:select.option value="{{ $state->state_code }}">{{ $state->state_name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="registrations.{{ $index }}.state_code" />
                            </div>
                            <div class="min-w-40 flex-1">
                                <flux:input wire:model="registrations.{{ $index }}.tax_id" placeholder="Tax / permit ID" />
                                <flux:error name="registrations.{{ $index }}.tax_id" />
                            </div>
                            <div class="w-44">
                                <flux:select wire:model="registrations.{{ $index }}.expiration_rule">
                                    <option value="">Use default expiration</option>
                                    @foreach ($this->expirationRules as $rule => $label)
                                        <option value="{{ $rule }}">{{ $label }}</option>
                                    @endforeach
                                </flux:select>
                            </div>
                            <div class="flex h-10 items-center gap-2">
                                <label class="flex items-center gap-1.5 text-sm text-zinc-600">
                                    <input type="radio" name="homeStateIndex" value="{{ $index }}"
                                        wire:model.live="homeStateIndex"
                                        class="h-4 w-4 border-zinc-300 text-blue-600 focus:ring-blue-500" />
                                    Home
                                </label>
                                <flux:button type="button" variant="ghost" size="sm" icon="trash"
                                    wire:click="removeRegistration({{ $index }})"
                                    :disabled="$index === $homeStateIndex" />
                            </div>
                        </div>
                    @endforeach
                </div>

                <flux:button type="button" variant="ghost" size="sm" icon="plus" wire:click="addRegistration">
                    Add another registration
                </flux:button>
            </div>
        </x-ui.card>

        <x-ui.card>
            <x-slot:header>
                <flux:heading size="lg">Certificate Options</flux:heading>
            </x-slot:header>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Default expiration rule</flux:label>
                    <flux:select wire:model="default_expiration_rule">
                        @foreach ($this->expirationRules as $rule => $label)
                            <option value="{{ $rule }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:description>
                        Used for states without their own rule. Set per-state overrides on each registration above.
                    </flux:description>
                    <flux:error name="default_expiration_rule" />
                </flux:field>

                <flux:field>
                    <flux:checkbox wire:model="mtc_enabled" label="Enable MTC uniform certificates" />
                    <flux:description>
                        The MTC form covers many states with one document, but some vendors reject it.
                        We suggest leaving this disabled unless a vendor specifically accepts MTC.
                    </flux:description>
                </flux:field>
            </div>
        </x-ui.card>

        <x-ui.card>
            <x-slot:header>
                <flux:heading size="lg">E-Signature</flux:heading>
            </x-slot:header>

            <livewire:resale-cert.signature-pad />
        </x-ui.card>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">Save Settings</flux:button>
        </div>
    </form>
</div>
