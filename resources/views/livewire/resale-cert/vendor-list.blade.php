<div class="space-y-6">
    <x-ui.page-header title="Vendors" subtitle="The suppliers you issue resale certificates to.">
        <x-slot:actions>
            <flux:button href="{{ route('resale-cert.vendors.create') }}" variant="primary" icon="plus" wire:navigate>
                Add Vendor
            </flux:button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('success'))
        <flux:callout color="green" icon="check-circle">{{ session('success') }}</flux:callout>
    @endif

    <x-ui.card>
        <div class="mb-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search vendors..." icon="magnifying-glass" />
        </div>

        @if ($vendors->isEmpty())
            <div class="py-12 text-center">
                <flux:icon name="building-storefront" class="mx-auto h-12 w-12 text-zinc-400" />
                <flux:heading size="lg" class="mt-2">No vendors yet</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Add the suppliers you buy from, then generate certificates for them.</flux:text>
                <flux:button href="{{ route('resale-cert.vendors.create') }}" variant="primary" class="mt-4" wire:navigate>
                    Add your first vendor
                </flux:button>
            </div>
        @else
            <flux:table :paginate="$vendors">
                <flux:table.columns>
                    <flux:table.column>Vendor</flux:table.column>
                    <flux:table.column>Location</flux:table.column>
                    <flux:table.column>Contact</flux:table.column>
                    <flux:table.column>Certificates</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($vendors as $vendor)
                        <flux:table.row wire:key="vendor-{{ $vendor->id }}">
                            <flux:table.cell class="font-medium text-text-primary">
                                {{ $vendor->legal_name }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $vendor->city }}, {{ $vendor->state }}</flux:table.cell>
                            <flux:table.cell>{{ $vendor->contact_name ?: '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="zinc" size="sm">{{ $vendor->certificates_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button href="{{ route('resale-cert.certificates.create', ['vendor_id' => $vendor->id]) }}"
                                        variant="ghost" size="sm" wire:navigate>
                                        Generate
                                    </flux:button>
                                    <flux:button href="{{ route('resale-cert.vendors.show', $vendor) }}" variant="ghost" size="sm" wire:navigate>
                                        View
                                    </flux:button>
                                    <flux:button variant="ghost" size="sm" icon="trash"
                                        wire:click="deleteVendor({{ $vendor->id }})"
                                        wire:confirm="Delete this vendor?" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </x-ui.card>
</div>
