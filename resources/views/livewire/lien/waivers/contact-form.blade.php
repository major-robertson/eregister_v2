<div class="mx-auto max-w-2xl space-y-6">
    <x-ui.page-header :title="$contact ? 'Edit Contact' : 'Add Contact'">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Waivers', 'url' => route('lien.waivers.index')],
                ['label' => 'Contacts', 'url' => route('lien.waivers.contacts.index')],
                ['label' => $contact ? 'Edit' : 'Add'],
            ]" />
        </x-slot:breadcrumbs>
    </x-ui.page-header>

    <x-ui.card>
        <form wire:submit="save" class="space-y-4">
            {{-- No field is individually required: a contact needs a company or a
                 name. The "one of them" error surfaces on Company. --}}
            <flux:field>
                <flux:label>Company name</flux:label>
                <flux:input wire:model="company_name" placeholder="Acme General Contractors LLC" />
                <flux:error name="company_name" />
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>First name</flux:label>
                    <flux:input wire:model="first_name" placeholder="Pat" />
                    <flux:error name="first_name" />
                </flux:field>
                <flux:field>
                    <flux:label>Last name</flux:label>
                    <flux:input wire:model="last_name" placeholder="Jones" />
                    <flux:error name="last_name" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input type="email" wire:model="email" placeholder="pat@acmegc.com" />
                <flux:error name="email" />
            </flux:field>

            <flux:field>
                <flux:label>Phone</flux:label>
                <flux:input wire:model="phone" placeholder="(555) 123-4567" />
                <flux:error name="phone" />
            </flux:field>

            <flux:separator text="Mailing address (optional)" />

            <flux:field>
                <flux:label>Street address</flux:label>
                <flux:input wire:model="address_line1" placeholder="123 Main St" />
                <flux:error name="address_line1" />
            </flux:field>

            <flux:field>
                <flux:label>Address line 2</flux:label>
                <flux:input wire:model="address_line2" placeholder="Suite 200" />
                <flux:error name="address_line2" />
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <flux:field>
                        <flux:label>City</flux:label>
                        <flux:input wire:model="city" />
                        <flux:error name="city" />
                    </flux:field>
                </div>
                <div class="sm:col-span-1">
                    <flux:field>
                        <flux:label>State</flux:label>
                        <flux:input wire:model="state" maxlength="2" placeholder="TX" />
                        <flux:error name="state" />
                    </flux:field>
                </div>
                <div class="sm:col-span-2">
                    <flux:field>
                        <flux:label>ZIP</flux:label>
                        <flux:input wire:model="postal_code" placeholder="75001" />
                        <flux:error name="postal_code" />
                    </flux:field>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <flux:button href="{{ route('lien.waivers.contacts.index') }}" variant="ghost" wire:navigate>Cancel</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $contact ? 'Save changes' : 'Add contact' }}
                </flux:button>
            </div>
        </form>
    </x-ui.card>
</div>
