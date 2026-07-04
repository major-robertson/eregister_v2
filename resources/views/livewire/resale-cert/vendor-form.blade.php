<div class="mx-auto max-w-2xl space-y-6">
    <x-ui.page-header :title="$vendor ? 'Edit Vendor' : 'Add Vendor'">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Vendors', 'url' => route('resale-cert.vendors.index')],
                ['label' => $vendor ? $vendor->legal_name : 'New'],
            ]" />
        </x-slot:breadcrumbs>
    </x-ui.page-header>

    <form wire:submit="save">
        <x-ui.card>
            <div class="space-y-4">
                <flux:field>
                    <flux:label>Legal name *</flux:label>
                    <flux:input wire:model="legal_name" placeholder="Supplier legal business name" />
                    <flux:error name="legal_name" />
                </flux:field>

                <flux:field>
                    <flux:label>Address line 1 *</flux:label>
                    <flux:input wire:model="address_line1" />
                    <flux:error name="address_line1" />
                </flux:field>

                <flux:field>
                    <flux:label>Address line 2</flux:label>
                    <flux:input wire:model="address_line2" />
                    <flux:error name="address_line2" />
                </flux:field>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>City *</flux:label>
                        <flux:input wire:model="city" />
                        <flux:error name="city" />
                    </flux:field>

                    <flux:field>
                        <flux:label>State *</flux:label>
                        <flux:select variant="combobox" clearable placeholder="Select..." wire:model="state">
                            @foreach (config('states') as $code => $name)
                                <flux:select.option value="{{ $code }}">{{ $name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="state" />
                    </flux:field>

                    <flux:field>
                        <flux:label>ZIP *</flux:label>
                        <flux:input wire:model="postal_code" mask="99999-9999" placeholder="12345" />
                        <flux:error name="postal_code" />
                    </flux:field>
                </div>

                <flux:separator />

                <flux:heading size="sm">Contact (optional)</flux:heading>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>Name</flux:label>
                        <flux:input wire:model="contact_name" />
                        <flux:error name="contact_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input type="email" wire:model="contact_email" />
                        <flux:error name="contact_email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Phone</flux:label>
                        <flux:input wire:model="contact_phone" mask="(999) 999-9999" placeholder="(555) 123-4567" />
                        <flux:error name="contact_phone" />
                    </flux:field>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t border-border pt-4">
                <flux:button href="{{ route('resale-cert.vendors.index') }}" variant="ghost" wire:navigate>Cancel</flux:button>
                <flux:button type="submit" variant="primary">{{ $vendor ? 'Save Changes' : 'Create Vendor' }}</flux:button>
            </div>
        </x-ui.card>
    </form>
</div>
