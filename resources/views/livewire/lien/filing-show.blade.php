<div class="max-w-3xl mx-auto space-y-6">
    <x-ui.page-header :title="$filing->documentType->name">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Lien Projects', 'url' => route('lien.projects.index')],
                ['label' => $filing->project->name, 'url' => route('lien.projects.show', $filing->project)],
                ['label' => $filing->documentType->name],
            ]" />
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <div class="flex items-center gap-3">
                <flux:button
                    href="{{ route('lien.projects.show', $filing->project) }}"
                    variant="primary"
                    color="blue"
                    icon="arrow-left"
                >
                    Back to Project
                </flux:button>
                <flux:button
                    href="{{ route('lien.projects.index') }}"
                    variant="primary"
                    color="blue"
                    icon="home"
                >
                    All Projects
                </flux:button>
            </div>
        </x-slot:actions>
    </x-ui.page-header>

    @if(request()->query('checkout') === 'success')
        <flux:callout color="green" icon="check-circle">
            Payment successful! Your filing has been submitted.
        </flux:callout>
    @endif

    @if(session('message'))
        <flux:callout color="green" icon="check-circle">
            {{ session('message') }}
        </flux:callout>
    @endif

    {{-- Hero Status Panel --}}
    <div class="rounded-xl border border-border bg-white overflow-hidden">
        <div class="p-8 text-center">
            {{-- Status Icon --}}
            <div @class([
                'inline-flex items-center justify-center w-16 h-16 rounded-full mb-4',
                'bg-zinc-100 text-zinc-600' => $filing->status->value === 'draft',
                'bg-amber-100 text-amber-600' => $filing->status->value === 'awaiting_payment',
                'bg-sky-100 text-sky-600' => $filing->status->value === 'paid',
                'bg-blue-100 text-blue-600' => $filing->status->value === 'in_fulfillment',
                'bg-indigo-100 text-indigo-600' => $filing->status->value === 'mailed',
                'bg-violet-100 text-violet-600' => $filing->status->value === 'recorded',
                'bg-green-100 text-green-600' => $filing->status->value === 'complete',
                'bg-red-100 text-red-600' => $filing->status->value === 'canceled',
            ])>
                <flux:icon name="{{ $filing->status->icon() }}" class="w-8 h-8" />
            </div>

            {{-- Big Status Label --}}
            <h2 class="text-2xl font-semibold text-zinc-900 mb-2">
                {{ $filing->status->label() }}
            </h2>

            {{-- Status Description --}}
            <p class="text-zinc-600 mb-4">
                {{ $filing->status->userDescription() }}
            </p>

            {{-- What's Next --}}
            @if($filing->status->whatsNext())
                <div class="inline-flex items-center gap-2 text-sm text-zinc-500 bg-zinc-50 px-4 py-2 rounded-full">
                    <flux:icon name="arrow-right" class="w-4 h-4" />
                    <span>{{ $filing->status->whatsNext() }}</span>
                </div>
            @endif

            {{-- Primary Action --}}
            @if($filing->status->value === 'awaiting_payment')
                <div class="mt-6">
                    <flux:button
                        href="{{ route('lien.filings.checkout', $filing) }}"
                        variant="primary"
                        size="lg"
                    >
                        Complete Payment
                    </flux:button>
                </div>
            @endif

            @if($canDownload)
                <div class="mt-6">
                    <flux:button
                        href="{{ route('lien.filings.download', $filing) }}"
                        variant="primary"
                        icon="download"
                    >
                        Download Document
                    </flux:button>
                </div>
            @endif
        </div>

        {{-- Tracking Info (if mailed) --}}
        @if($filing->mailing_tracking_number && in_array($filing->status->value, ['mailed', 'recorded', 'complete']))
            <div class="border-t border-border px-8 py-4 bg-zinc-50">
                <div class="flex items-center justify-center gap-2 text-sm">
                    <flux:icon name="truck" class="w-4 h-4 text-zinc-400" />
                    <span class="text-zinc-600">Tracking:</span>
                    <span class="font-mono text-zinc-900">{{ $filing->mailing_tracking_number }}</span>
                </div>
            </div>
        @endif
    </div>

    {{-- Self-Serve Status Update (for non-full-service filings) --}}
    @if(!$filing->isFullService() && $filing->isPaid() && in_array($filing->status->value, ['paid', 'mailed', 'recorded']))
        <x-ui.card>
            <x-slot:header>Update Status</x-slot:header>

            <div class="space-y-4">
                @if($filing->status->value === 'paid')
                    <div>
                        <flux:field>
                            <flux:label>Tracking Number (optional)</flux:label>
                            <flux:input wire:model="tracking_number" placeholder="e.g., 9400111899223..." />
                        </flux:field>
                        <flux:button
                            wire:click="markMailed"
                            variant="primary"
                            size="sm"
                            class="mt-2 w-full"
                        >
                            Mark as Mailed
                        </flux:button>
                    </div>
                @elseif($filing->status->value === 'mailed')
                    <flux:button
                        wire:click="markRecorded"
                        variant="primary"
                        size="sm"
                        class="w-full"
                    >
                        Mark as Recorded
                    </flux:button>
                @elseif($filing->status->value === 'recorded')
                    <flux:button
                        wire:click="markComplete"
                        variant="primary"
                        size="sm"
                        class="w-full"
                    >
                        Mark as Complete
                    </flux:button>
                @endif
            </div>
        </x-ui.card>
    @endif

    {{-- Proof Documents (for self-serve) --}}
    @if(!$filing->isFullService() && $filing->isPaid())
        <x-ui.card>
            <x-slot:header>Proof Documents</x-slot:header>

            @if($proofs->isNotEmpty())
                <div class="space-y-2 mb-4">
                    @foreach($proofs as $proof)
                        <div class="flex items-center justify-between text-sm">
                            <span>{{ $proof->file_name }}</span>
                            <a href="{{ $proof->getTemporaryUrl(now()->addMinutes(5)) }}"
                               target="_blank"
                               class="text-blue-600 hover:underline">
                                View
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            <form wire:submit="uploadProof">
                <flux:field>
                    <flux:label>Upload Proof</flux:label>
                    <input type="file" wire:model="proofFile" class="text-sm" accept=".pdf,.jpg,.jpeg,.png" />
                    <flux:error name="proofFile" />
                </flux:field>
                @if($proofFile)
                    <flux:button type="submit" size="sm" class="mt-2">
                        Upload
                    </flux:button>
                @endif
            </form>
        </x-ui.card>
    @endif

    {{-- Filing Details --}}
    <x-ui.card>
        <x-slot:header>Filing Details</x-slot:header>

        <x-ui.info-list>
            <x-ui.info-list.item label="Document Type">
                {{ $filing->documentType->name }}
            </x-ui.info-list.item>
            <x-ui.info-list.item label="Jurisdiction">
                {{ $filing->jurisdiction_county }}, {{ $filing->jurisdiction_state }}
            </x-ui.info-list.item>
            @if($filing->amount_claimed_cents)
                <x-ui.info-list.item label="Amount Claimed">
                    {{ $filing->formattedAmountClaimed() }}
                </x-ui.info-list.item>
            @endif
            @if($filing->description_of_work)
                <x-ui.info-list.item label="Description">
                    {{ $filing->description_of_work }}
                </x-ui.info-list.item>
            @endif
            <x-ui.info-list.item label="Service Level">
                {{ $filing->service_level->label() }}
            </x-ui.info-list.item>
            @if($filing->paid_at)
                <x-ui.info-list.item label="Paid">
                    {{ $filing->paid_at->format('M j, Y') }}
                </x-ui.info-list.item>
            @endif
        </x-ui.info-list>
    </x-ui.card>

    {{-- Recipients --}}
    @if($recipients->isNotEmpty())
        <x-ui.card>
            <x-slot:header>Recipients</x-slot:header>

            <div class="space-y-3">
                @foreach($recipients as $recipient)
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <div class="font-medium">{{ $recipient->snapshotName() }}</div>
                        <div class="text-sm text-zinc-500">{{ $recipient->snapshotAddressLine() }}</div>
                        @if($recipient->tracking_number)
                            <div class="text-xs text-zinc-400 mt-1">
                                Tracking: {{ $recipient->tracking_number }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    @endif
</div>
