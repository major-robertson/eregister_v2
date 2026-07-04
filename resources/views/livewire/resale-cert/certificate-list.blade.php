<div class="space-y-6">
    <x-ui.page-header title="Certificates" subtitle="Every resale certificate you've generated.">
        <x-slot:actions>
            <flux:button wire:click="exportCsv" variant="ghost" icon="arrow-down-tray">Export CSV</flux:button>
            <flux:button href="{{ route('resale-cert.certificates.create') }}" variant="primary" icon="plus" wire:navigate>
                Generate Certificate
            </flux:button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('success'))
        <flux:callout color="green" icon="check-circle">{{ session('success') }}</flux:callout>
    @endif

    <x-ui.card>
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <div class="min-w-56 flex-1">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by vendor, description, or ID..." icon="magnifying-glass" />
            </div>
            <flux:select wire:model.live="vendorFilter" class="w-44">
                <option value="">All vendors</option>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}">{{ $vendor->legal_name }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="stateFilter" class="w-44">
                <option value="">All states</option>
                @foreach ($states as $state)
                    <option value="{{ $state->state_code }}">{{ $state->state_name }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="statusFilter" class="w-40">
                <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="expiring">Expiring soon</option>
                <option value="expired">Expired</option>
            </flux:select>
        </div>

        @if ($certificates->isEmpty())
            <div class="py-12 text-center">
                <flux:icon name="document-check" class="mx-auto h-12 w-12 text-zinc-400" />
                <flux:heading size="lg" class="mt-2">No certificates found</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Generate a certificate or adjust your filters.</flux:text>
                <flux:button href="{{ route('resale-cert.certificates.create') }}" variant="primary" class="mt-4" wire:navigate>
                    Generate a certificate
                </flux:button>
            </div>
        @else
            <flux:table :paginate="$certificates">
                <flux:table.columns>
                    <flux:table.column>Form</flux:table.column>
                    <flux:table.column>Vendor</flux:table.column>
                    <flux:table.column>Issued</flux:table.column>
                    <flux:table.column>Expires</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($certificates as $certificate)
                        <flux:table.row wire:key="certificate-{{ $certificate->id }}">
                            <flux:table.cell class="font-medium text-text-primary">
                                {{ $certificate->displayName() }}
                                @if ($certificate->isUniform())
                                    <flux:text class="text-xs text-zinc-500">
                                        Covers {{ count($certificate->business_snapshot['selected_states_tax_ids'] ?? []) }} states
                                    </flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $certificate->vendor?->legal_name }}</flux:table.cell>
                            <flux:table.cell>{{ $certificate->issue_date->format('M j, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $certificate->expiration_date?->format('M j, Y') ?? 'Never' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$certificate->statusColor()" size="sm">{{ $certificate->statusLabel() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button href="{{ route('resale-cert.certificates.show', $certificate) }}" variant="ghost" size="sm" wire:navigate>
                                        View
                                    </flux:button>
                                    @if ($certificate->pdf_path)
                                        <flux:button href="{{ route('resale-cert.certificates.download', $certificate) }}"
                                            variant="ghost" size="sm" icon="arrow-down-tray" />
                                    @endif
                                    <flux:button variant="ghost" size="sm" icon="trash"
                                        wire:click="deleteCertificate({{ $certificate->id }})"
                                        wire:confirm="Delete this certificate? The PDF will be removed." />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </x-ui.card>
</div>
