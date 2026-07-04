<div class="space-y-6">
    <x-ui.page-header :title="$vendor->legal_name">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Vendors', 'url' => route('resale-cert.vendors.index')],
                ['label' => $vendor->legal_name],
            ]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <flux:button href="{{ route('resale-cert.vendors.edit', $vendor) }}" variant="ghost" icon="pencil" wire:navigate>
                Edit
            </flux:button>
            <flux:button href="{{ route('resale-cert.certificates.create', ['vendor_id' => $vendor->id]) }}" variant="primary" icon="plus" wire:navigate>
                Generate Certificate
            </flux:button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('success'))
        <flux:callout color="green" icon="check-circle">{{ session('success') }}</flux:callout>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-ui.card>
            <x-slot:header>
                <flux:heading size="lg">Vendor Details</flux:heading>
            </x-slot:header>

            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-zinc-500">Address</dt>
                    <dd class="text-text-primary">
                        {{ $vendor->address_line1 }}@if ($vendor->address_line2), {{ $vendor->address_line2 }}@endif<br>
                        {{ $vendor->cityStateZip() }}
                    </dd>
                </div>
                @if ($vendor->contact_name || $vendor->contact_email || $vendor->contact_phone)
                    <div>
                        <dt class="text-zinc-500">Contact</dt>
                        <dd class="text-text-primary">
                            {{ $vendor->contact_name }}
                            @if ($vendor->contact_email)<br>{{ $vendor->contact_email }}@endif
                            @if ($vendor->contact_phone)<br>{{ $vendor->contact_phone }}@endif
                        </dd>
                    </div>
                @endif
            </dl>
        </x-ui.card>

        <div class="lg:col-span-2">
            <x-ui.card>
                <x-slot:header>
                    <flux:heading size="lg">Certificates for this Vendor</flux:heading>
                </x-slot:header>

                @if ($this->certificates->isEmpty())
                    <div class="py-8 text-center">
                        <flux:text class="text-zinc-500">No certificates issued to this vendor yet.</flux:text>
                    </div>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Form</flux:table.column>
                            <flux:table.column>Issued</flux:table.column>
                            <flux:table.column>Expires</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column align="end">Actions</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($this->certificates as $certificate)
                                <flux:table.row wire:key="certificate-{{ $certificate->id }}">
                                    <flux:table.cell class="font-medium text-text-primary">
                                        {{ $certificate->displayName() }}
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $certificate->issue_date->format('M j, Y') }}</flux:table.cell>
                                    <flux:table.cell>{{ $certificate->expiration_date?->format('M j, Y') ?? 'Never' }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge :color="$certificate->statusColor()" size="sm">{{ $certificate->statusLabel() }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell align="end">
                                        <flux:button href="{{ route('resale-cert.certificates.show', $certificate) }}"
                                            variant="ghost" size="sm" wire:navigate>
                                            View
                                        </flux:button>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
