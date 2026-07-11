<div class="space-y-6">
    <x-ui.page-header title="Waiver Contacts" subtitle="The GCs, subs, suppliers, and customers you exchange lien waivers with.">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Waivers', 'url' => route('lien.waivers.index')],
                ['label' => 'Contacts'],
            ]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <flux:button href="{{ route('lien.waivers.contacts.create') }}" variant="primary" icon="plus" wire:navigate>
                Add Contact
            </flux:button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('success'))
        <flux:callout color="green" icon="check-circle">{{ session('success') }}</flux:callout>
    @endif

    <x-ui.card>
        <div class="mb-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by company, contact, or email..." icon="magnifying-glass" />
        </div>

        @if ($contacts->isEmpty())
            <div class="py-12 text-center">
                <flux:icon name="user-group" class="mx-auto h-12 w-12 text-zinc-400" />
                <flux:heading size="lg" class="mt-2">No contacts yet</flux:heading>
                <flux:text class="mt-1 text-zinc-500">
                    Add the parties you send waivers to or collect them from. You can also add them on the fly while creating a waiver.
                </flux:text>
                <flux:button href="{{ route('lien.waivers.contacts.create') }}" variant="primary" class="mt-4" wire:navigate>
                    Add your first contact
                </flux:button>
            </div>
        @else
            <flux:table :paginate="$contacts">
                <flux:table.columns>
                    <flux:table.column>Company</flux:table.column>
                    <flux:table.column>Contact</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Location</flux:table.column>
                    <flux:table.column>Waivers</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($contacts as $contact)
                        <flux:table.row wire:key="contact-{{ $contact->id }}">
                            <flux:table.cell class="font-medium text-text-primary">
                                {{ $contact->company_name ?: '—' }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $contact->personName() ?: '—' }}</flux:table.cell>
                            <flux:table.cell>{{ $contact->email }}</flux:table.cell>
                            <flux:table.cell>
                                {{ trim(collect([$contact->city, $contact->state])->filter()->implode(', ')) }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="zinc" size="sm">{{ $contact->waivers_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button href="{{ route('lien.waivers.contacts.edit', $contact) }}" variant="ghost" size="sm" wire:navigate>
                                        Edit
                                    </flux:button>
                                    <flux:button variant="ghost" size="sm" icon="trash"
                                        wire:click="deleteContact({{ $contact->id }})"
                                        wire:confirm="Delete this contact?" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </x-ui.card>
</div>
