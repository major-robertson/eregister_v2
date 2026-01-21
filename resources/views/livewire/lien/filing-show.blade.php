<div class="max-w-4xl mx-auto space-y-6">
    <x-ui.page-header :title="$filing->documentType->name">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Lien Projects', 'url' => route('lien.projects.index')],
                ['label' => $filing->project->name, 'url' => route('lien.projects.show', $filing->project)],
                ['label' => $filing->documentType->name],
            ]" />
        </x-slot:breadcrumbs>
    </x-ui.page-header>

    @if(request()->query('checkout') === 'success')
        <flux:callout color="green" icon="check-circle">
            Payment successful! Your filing has been processed.
        </flux:callout>
    @endif

    @if(session('message'))
        <flux:callout color="green" icon="check-circle">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Status Card --}}
            <x-ui.card>
                <x-slot:header>Filing Status</x-slot:header>

                <div class="flex items-center gap-4 mb-6">
                    <flux:badge size="lg" :color="$filing->status->color()">
                        {{ $filing->status->label() }}
                    </flux:badge>
                    <span class="text-sm text-zinc-500">
                        {{ $filing->service_level->label() }}
                    </span>
                </div>

                {{-- Status Timeline --}}
                <div class="space-y-4">
                    @foreach($events->take(5) as $event)
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5">
                                <flux:icon name="{{ $event->statusIcon() }}" class="w-5 h-5 text-zinc-400" />
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-sm">{{ $event->description() }}</div>
                                @if($event->hasMeta('tracking_number'))
                                    <div class="text-xs text-zinc-500">
                                        Tracking: {{ $event->meta('tracking_number') }}
                                    </div>
                                @endif
                                <div class="text-xs text-zinc-400">{{ $event->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

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
                    @if($filing->paid_at)
                        <x-ui.info-list.item label="Paid">
                            {{ $filing->paid_at->format('M j, Y g:i A') }}
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

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Actions --}}
            <x-ui.card>
                <x-slot:header>Actions</x-slot:header>

                <div class="space-y-3">
                    @if($canDownload)
                        <flux:button
                            href="{{ route('lien.filings.download', $filing) }}"
                            variant="primary"
                            class="w-full"
                            icon="download"
                        >
                            Download Document
                        </flux:button>
                    @endif

                    @if($filing->status->value === 'awaiting_payment')
                        <flux:button
                            href="{{ route('lien.filings.checkout', $filing) }}"
                            variant="primary"
                            class="w-full"
                        >
                            Complete Payment
                        </flux:button>
                    @endif
                </div>
            </x-ui.card>

            {{-- Self-Serve Actions --}}
            @if(!$filing->isFullService() && $filing->isPaid())
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

                {{-- Upload Proof --}}
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

            {{-- Full Service Status --}}
            @if($filing->isFullService() && $filing->isPaid())
                <x-ui.card>
                    <x-slot:header>Fulfillment Status</x-slot:header>

                    @if($filing->fulfillmentTask)
                        <div class="text-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <flux:badge :color="$filing->fulfillmentTask->status->color()">
                                    {{ $filing->fulfillmentTask->status->label() }}
                                </flux:badge>
                            </div>
                            @if($filing->fulfillmentTask->assignedTo)
                                <p class="text-zinc-500">
                                    Assigned to: {{ $filing->fulfillmentTask->assignedTo->name }}
                                </p>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-zinc-500">Your filing is being processed by our team.</p>
                    @endif
                </x-ui.card>
            @endif
        </div>
    </div>
</div>
