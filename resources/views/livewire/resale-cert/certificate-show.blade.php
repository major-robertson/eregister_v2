<div class="space-y-6">
    <x-ui.page-header :title="$certificate->displayName().' — Certificate #'.$certificate->id">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Certificates', 'url' => route('resale-cert.certificates.index')],
                ['label' => 'Certificate #'.$certificate->id],
            ]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @if ($certificate->pdf_path)
                <flux:button href="{{ route('resale-cert.certificates.download', $certificate) }}" variant="primary" icon="arrow-down-tray">
                    Download PDF
                </flux:button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @if ($certificate->isExpired())
        <flux:callout variant="danger" icon="exclamation-triangle">
            <flux:callout.heading>This certificate has expired</flux:callout.heading>
            <flux:callout.text>
                It expired {{ $certificate->expiration_date->format('F j, Y') }}. Generate a renewal to stay compliant.
            </flux:callout.text>
        </flux:callout>
    @elseif ($certificate->isExpiringSoon())
        <flux:callout color="amber" icon="clock">
            Expires {{ $certificate->expiration_date->format('F j, Y') }}
            ({{ (int) now()->diffInDays($certificate->expiration_date) }} days from now).
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card>
                <x-slot:header>
                    <flux:heading size="lg">Certificate Information</flux:heading>
                </x-slot:header>

                <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-zinc-500">Form</dt>
                        <dd class="font-medium text-text-primary">{{ $certificate->displayName() }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Status</dt>
                        <dd><flux:badge :color="$certificate->statusColor()" size="sm">{{ $certificate->statusLabel() }}</flux:badge></dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Issue date</dt>
                        <dd class="text-text-primary">{{ $certificate->issue_date->format('F j, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Expiration</dt>
                        <dd class="text-text-primary">{{ $certificate->expiration_date?->format('F j, Y') ?? 'Never expires' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-zinc-500">{{ $certificate->is_blanket ? 'Coverage' : 'Items' }}</dt>
                        <dd class="text-text-primary">{{ $certificate->item_description }}</dd>
                    </div>
                    @if ($certificate->isUniform())
                        <div class="sm:col-span-2">
                            <dt class="text-zinc-500">States covered</dt>
                            <dd class="text-text-primary">
                                {{ implode(', ', array_keys($certificate->business_snapshot['selected_states_tax_ids'] ?? [])) }}
                            </dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-zinc-500">Created by</dt>
                        <dd class="text-text-primary">{{ $certificate->createdBy?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Created</dt>
                        <dd class="text-text-primary">{{ $certificate->created_at->eastern()->format('M j, Y g:i A') }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            @php
                $business = $certificate->business_snapshot;
                $vendor = $certificate->vendor_snapshot;
            @endphp

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <x-ui.card>
                    <x-slot:header>
                        <flux:heading size="lg">Purchaser (You)</flux:heading>
                    </x-slot:header>

                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-zinc-500">Legal name</dt>
                            <dd class="text-text-primary">{{ $business['legal_name'] ?? '—' }}</dd>
                        </div>
                        @if (! empty($business['dba']))
                            <div>
                                <dt class="text-zinc-500">DBA</dt>
                                <dd class="text-text-primary">{{ $business['dba'] }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-zinc-500">Address</dt>
                            <dd class="text-text-primary">
                                {{ $business['address']['line1'] ?? '' }}<br>
                                {{ $business['address']['city'] ?? '' }}, {{ $business['address']['state'] ?? '' }}
                                {{ $business['address']['postal_code'] ?? '' }}
                            </dd>
                        </div>
                        @if (! empty($business['tax_id']))
                            <div>
                                <dt class="text-zinc-500">Tax ID ({{ $business['tax_id_source_state'] ?? '' }})</dt>
                                <dd class="text-text-primary">{{ $business['tax_id'] }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-ui.card>

                <x-ui.card>
                    <x-slot:header>
                        <flux:heading size="lg">Seller (Vendor)</flux:heading>
                    </x-slot:header>

                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-zinc-500">Legal name</dt>
                            <dd class="text-text-primary">{{ $vendor['legal_name'] ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">Address</dt>
                            <dd class="text-text-primary">
                                {{ $vendor['address']['line1'] ?? '' }}<br>
                                {{ $vendor['address']['city'] ?? '' }}, {{ $vendor['address']['state'] ?? '' }}
                                {{ $vendor['address']['postal_code'] ?? '' }}
                            </dd>
                        </div>
                        @if (! empty($vendor['contact']['name']))
                            <div>
                                <dt class="text-zinc-500">Contact</dt>
                                <dd class="text-text-primary">{{ $vendor['contact']['name'] }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-ui.card>
            </div>
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <x-slot:header>
                    <flux:heading size="lg">Actions</flux:heading>
                </x-slot:header>

                <div class="space-y-2">
                    @if ($certificate->pdf_path)
                        <flux:button href="{{ route('resale-cert.certificates.download', $certificate) }}"
                            variant="primary" icon="arrow-down-tray" class="w-full">
                            Download PDF
                        </flux:button>
                    @else
                        <flux:callout color="amber" icon="exclamation-triangle">
                            PDF not generated. Delete this certificate and generate it again.
                        </flux:callout>
                    @endif

                    @if ($certificate->isExpired() || $certificate->isExpiringSoon())
                        <flux:button
                            href="{{ route('resale-cert.certificates.create', ['vendor_id' => $certificate->resale_vendor_id]) }}"
                            variant="primary" class="w-full" wire:navigate>
                            Generate Renewal
                        </flux:button>
                    @endif

                    <flux:button variant="danger" icon="trash" class="w-full"
                        wire:click="deleteCertificate"
                        wire:confirm="Delete this certificate? The PDF will be removed.">
                        Delete Certificate
                    </flux:button>
                </div>
            </x-ui.card>

            <x-ui.card>
                <x-slot:header>
                    <flux:heading size="lg">Audit Trail</flux:heading>
                </x-slot:header>

                @if ($this->auditEvents->isEmpty())
                    <flux:text class="text-sm text-zinc-500">No recorded events.</flux:text>
                @else
                    <div class="space-y-3">
                        @foreach ($this->auditEvents as $event)
                            <div class="flex items-start gap-2 text-sm" wire:key="audit-{{ $event->id }}">
                                <flux:icon :name="$event->event_type->icon()" class="mt-0.5 size-4 shrink-0 text-zinc-400" />
                                <div class="min-w-0">
                                    <flux:text class="font-medium text-text-primary">
                                        {{ $event->event_type->label() }}
                                    </flux:text>
                                    <flux:text class="text-xs text-zinc-500">
                                        {{ $event->actor?->name ?? 'System' }}
                                        · {{ $event->occurred_at->eastern()->format('M j, Y g:i A') }}
                                        @if ($event->ip_address)
                                            · IP {{ $event->ip_address }}
                                        @endif
                                    </flux:text>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($certificate->pdf_sha256)
                        <flux:text class="mt-4 block break-all text-xs text-zinc-400">
                            PDF SHA-256: {{ $certificate->pdf_sha256 }}
                        </flux:text>
                    @endif
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
