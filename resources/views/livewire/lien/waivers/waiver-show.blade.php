@php
use App\Domains\Lien\Enums\WaiverStatus;
@endphp

<div class="max-w-3xl mx-auto space-y-6">
    <x-ui.page-header :title="$formTitle">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Waivers', 'url' => route('lien.waivers.index')],
                ['label' => $waiver->counterpartyDisplayName()],
            ]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <div class="flex items-center gap-3">
                <flux:badge :color="$waiver->status->color()">
                    {{ $waiver->status->label() }}
                </flux:badge>
                @if ($waiver->project)
                    <flux:button href="{{ route('lien.projects.show', $waiver->project) }}" variant="ghost" icon="folder" wire:navigate>
                        {{ $waiver->project->name }}
                    </flux:button>
                @endif
            </div>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('message'))
        <flux:callout color="green" icon="check-circle">
            {{ session('message') }}
        </flux:callout>
    @endif

    {{-- Wizard's save-and-send saved the waiver but the send failed --}}
    @if (session('esign_error'))
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading>Saved, but not sent</flux:callout.heading>
            <flux:callout.text>{{ session('esign_error') }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- GA/MS deemed-effective countdown --}}
    @if ($waiver->deemed_effective_at)
        <flux:callout :color="$waiver->deemed_effective_at->isPast() ? 'red' : 'amber'" icon="exclamation-triangle">
            <flux:callout.heading>
                {{ $waiver->deemed_effective_at->isPast() ? 'Deemed effective on '.$waiver->deemed_effective_at->format('M j, Y') : 'Becomes conclusively effective '.$waiver->deemed_effective_at->format('M j, Y') }}
            </flux:callout.heading>
            <flux:callout.text>
                In {{ $waiver->state }}, a signed waiver becomes conclusively effective on this date even
                if payment never arrives. If you haven't been paid, file an Affidavit of Nonpayment before
                then to preserve your lien rights.
            </flux:callout.text>
        </flux:callout>
    @endif

    {{-- Actions by status --}}
    <x-ui.card>
        <x-slot:header>Actions</x-slot:header>

        <div class="space-y-4">
            @if ($waiver->status === WaiverStatus::Draft)
                <flux:callout color="zinc" icon="information-circle">
                    This waiver hasn't been rendered to PDF yet.
                </flux:callout>
                <flux:button wire:click="generatePdf" wire:loading.attr="disabled" variant="primary" icon="document-arrow-down">
                    <span wire:loading.remove wire:target="generatePdf">Generate PDF</span>
                    <span wire:loading wire:target="generatePdf">Generating...</span>
                </flux:button>

            @elseif ($waiver->status === WaiverStatus::Generated)
                <div class="flex flex-wrap items-center gap-3">
                    @if ($hasGeneratedPdf)
                        <flux:button href="{{ route('lien.waivers.download', $waiver) }}" variant="outline" icon="arrow-down-tray">
                            Download PDF
                        </flux:button>
                    @endif

                    @if ($form === null || $form->esignAllowed)
                        <flux:button wire:click="sendForSignature" wire:loading.attr="disabled" variant="primary" icon="paper-airplane">
                            <span wire:loading.remove wire:target="sendForSignature">Send for signature</span>
                            <span wire:loading wire:target="sendForSignature">Sending...</span>
                        </flux:button>
                    @endif
                </div>

                @if ($form && ! $form->esignAllowed)
                    <flux:callout color="purple" icon="pencil-square">
                        <flux:callout.heading>Sign on paper, then upload</flux:callout.heading>
                        <flux:callout.text>
                            {{ $form->esignDisabledReason ?? 'This state requires in-person execution (notary or witness), so e-signing is unavailable.' }}
                        </flux:callout.text>
                    </flux:callout>
                @endif

                {{-- Upload signed copy (paid: storing a signed copy is a tracked feature) --}}
                <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    @if ($hasPaidAccess)
                        <form wire:submit="uploadSigned">
                            <flux:field>
                                <flux:label>Upload signed copy</flux:label>
                                <flux:file-upload wire:model="signedFile" accept=".pdf,.jpg,.jpeg,.png">
                                    <flux:file-upload.dropzone
                                        inline
                                        heading="Drop the signed waiver here or click to browse"
                                        text="PDF, JPG, or PNG"
                                        with-progress
                                    />
                                </flux:file-upload>
                                <flux:description>Marks the waiver as signed and stores the executed copy.</flux:description>
                                <flux:error name="signedFile" />
                            </flux:field>
                            @if ($signedFile)
                                <flux:text class="mt-2 text-sm">{{ $signedFile->getClientOriginalName() }}</flux:text>
                                <flux:button type="submit" size="sm" variant="primary" class="mt-2">
                                    Upload signed copy
                                </flux:button>
                            @endif
                        </form>
                    @else
                        <flux:callout icon="lock-closed">
                            <flux:callout.heading>Store the signed copy</flux:callout.heading>
                            <flux:callout.text>
                                Upgrade to store executed waivers in the portal with a tamper-evident audit trail.
                            </flux:callout.text>
                            <flux:callout.link href="#" wire:click.prevent="$set('showUpsellModal', true)">See what's included</flux:callout.link>
                        </flux:callout>
                    @endif
                </div>

            @elseif ($waiver->status === WaiverStatus::AwaitingSignature)
                <flux:callout color="amber" icon="clock">
                    <flux:callout.heading>Waiting on the signer</flux:callout.heading>
                    <flux:callout.text>
                        Sent {{ $waiver->sent_at?->eastern()->format('M j, Y g:i A') }} ET to
                        {{ $activeRequest?->signer_email_snapshot ?? $waiver->signer_email ?? 'the signer' }}.
                        We'll remind them automatically until it's signed.
                    </flux:callout.text>
                </flux:callout>

                <div class="flex flex-wrap items-center gap-3">
                    @if ($hasGeneratedPdf)
                        <flux:button href="{{ route('lien.waivers.download', $waiver) }}" variant="outline" icon="arrow-down-tray">
                            Download unsigned PDF
                        </flux:button>
                    @endif
                    <flux:button wire:click="$set('showVoidModal', true)" variant="danger" icon="x-circle">
                        Void request
                    </flux:button>
                </div>

                {{-- Signed outside the e-sign flow? Upload it and the request can be voided after. --}}
                @if ($hasPaidAccess)
                    <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                        <form wire:submit="uploadSigned">
                            <flux:field>
                                <flux:label>Upload signed copy</flux:label>
                                <flux:file-upload wire:model="signedFile" accept=".pdf,.jpg,.jpeg,.png">
                                    <flux:file-upload.dropzone
                                        inline
                                        heading="Signed on paper instead? Upload it here"
                                        text="PDF, JPG, or PNG"
                                        with-progress
                                    />
                                </flux:file-upload>
                                <flux:error name="signedFile" />
                            </flux:field>
                            @if ($signedFile)
                                <flux:text class="mt-2 text-sm">{{ $signedFile->getClientOriginalName() }}</flux:text>
                                <flux:button type="submit" size="sm" variant="primary" class="mt-2">
                                    Upload signed copy
                                </flux:button>
                            @endif
                        </form>
                    </div>
                @endif

            @elseif ($waiver->status === WaiverStatus::Signed)
                <div class="flex flex-wrap items-center gap-3">
                    @if ($hasSignedCopy)
                        <flux:button href="{{ route('lien.waivers.download', ['waiver' => $waiver, 'copy' => 'signed']) }}" variant="primary" icon="arrow-down-tray">
                            Download signed copy
                        </flux:button>
                    @endif
                    @if ($hasGeneratedPdf)
                        <flux:button href="{{ route('lien.waivers.download', $waiver) }}" variant="outline" icon="arrow-down-tray">
                            Download unsigned original
                        </flux:button>
                    @endif
                </div>

            @elseif ($waiver->status === WaiverStatus::Voided)
                <flux:callout color="red" icon="x-circle">
                    This waiver was voided{{ $waiver->voided_at ? ' on '.$waiver->voided_at->eastern()->format('M j, Y') : '' }} and is kept for the audit trail.
                </flux:callout>
                @if ($hasGeneratedPdf)
                    <flux:button href="{{ route('lien.waivers.download', $waiver) }}" variant="outline" icon="arrow-down-tray">
                        Download PDF
                    </flux:button>
                @endif
            @endif
        </div>
    </x-ui.card>

    {{-- Details --}}
    <x-ui.card>
        <x-slot:header>Waiver Details</x-slot:header>

        <x-ui.info-list>
            <x-ui.info-list.item label="Form">
                {{ $formTitle }}
            </x-ui.info-list.item>
            <x-ui.info-list.item label="Type">
                {{ $kindShortLabel }}
            </x-ui.info-list.item>
            <x-ui.info-list.item label="Direction">
                {{ $waiver->direction->label() }}
            </x-ui.info-list.item>
            <x-ui.info-list.item label="State">
                {{ \App\Domains\Lien\Waivers\WaiverStateRegistry::STATE_NAMES[$waiver->state] ?? $waiver->state }}
            </x-ui.info-list.item>
            @if ($waiver->amount_cents !== null)
                <x-ui.info-list.item label="Amount">
                    {{ $waiver->formattedAmount() }}
                </x-ui.info-list.item>
            @endif
            @if ($waiver->through_date)
                <x-ui.info-list.item label="Through date">
                    {{ $waiver->through_date->format('M j, Y') }}
                </x-ui.info-list.item>
            @endif
            <x-ui.info-list.item label="Counterparty">
                {{ $waiver->counterpartyDisplayName() }}
            </x-ui.info-list.item>
            @if ($waiver->counterparty_email)
                <x-ui.info-list.item label="Counterparty email">
                    {{ $waiver->counterparty_email }}
                </x-ui.info-list.item>
            @endif
            <x-ui.info-list.item label="Signer">
                {{ implode(', ', array_filter([$waiver->signer_name, $waiver->signer_title])) ?: '-' }}
            </x-ui.info-list.item>
            @if ($waiver->signer_email)
                <x-ui.info-list.item label="Signer email">
                    {{ $waiver->signer_email }}
                </x-ui.info-list.item>
            @endif
            @if ($waiver->invoice_number)
                <x-ui.info-list.item label="Invoice number">
                    {{ $waiver->invoice_number }}
                </x-ui.info-list.item>
            @endif
            @if ($waiver->check_maker || $waiver->check_number)
                <x-ui.info-list.item label="Check">
                    {{ implode(', ', array_filter([$waiver->check_maker, $waiver->check_number ? '#'.$waiver->check_number : null])) }}
                </x-ui.info-list.item>
            @endif
            @if ($waiver->exceptions)
                <x-ui.info-list.item label="Exceptions">
                    {{ $waiver->exceptions }}
                </x-ui.info-list.item>
            @endif
            @if ($waiver->generated_at)
                <x-ui.info-list.item label="Generated">
                    {{ $waiver->generated_at->eastern()->format('M j, Y g:i A') }} ET
                </x-ui.info-list.item>
            @endif
            @if ($waiver->sent_at)
                <x-ui.info-list.item label="Sent">
                    {{ $waiver->sent_at->eastern()->format('M j, Y g:i A') }} ET
                </x-ui.info-list.item>
            @endif
            @if ($waiver->signed_at)
                <x-ui.info-list.item label="Signed">
                    {{ $waiver->signed_at->eastern()->format('M j, Y g:i A') }} ET
                </x-ui.info-list.item>
            @endif
            @if ($waiver->deemed_effective_at)
                <x-ui.info-list.item label="Deemed effective">
                    {{ $waiver->deemed_effective_at->format('M j, Y') }}
                </x-ui.info-list.item>
            @endif
        </x-ui.info-list>
    </x-ui.card>

    {{-- Timeline --}}
    <x-ui.card>
        <x-slot:header>Timeline</x-slot:header>

        @if ($timeline->isEmpty())
            <p class="text-sm text-zinc-500">No activity yet.</p>
        @else
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-700">
                @foreach ($timeline as $item)
                    <li class="flex items-start gap-3 py-3 first:pt-0 last:pb-0" wire:key="timeline-{{ $loop->index }}">
                        <div class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-700">
                            <flux:icon name="clock" class="size-3.5 text-zinc-500" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-zinc-900 dark:text-white">{{ $item['label'] }}</p>
                            <p class="text-xs text-zinc-500">
                                {{ $item['at']->eastern()->format('M j, Y g:i A') }} ET
                                @if ($item['ip'])
                                    <span>&bull;</span> {{ $item['ip'] }}
                                @endif
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>

    {{-- Void confirmation modal --}}
    <flux:modal wire:model="showVoidModal" class="max-w-md">
        <div class="space-y-4">
            <flux:heading>Void this signature request?</flux:heading>
            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                The signer's link stops working immediately. The waiver returns to Generated so you can
                fix details and re-send it.
            </flux:text>

            <flux:field>
                <flux:label>Reason (optional)</flux:label>
                <flux:input wire:model="voidReason" placeholder="e.g., wrong amount, wrong signer" />
            </flux:field>

            <div class="flex justify-end gap-3 pt-2">
                <flux:button type="button" wire:click="$set('showVoidModal', false)" variant="ghost">
                    Cancel
                </flux:button>
                <flux:button wire:click="voidSignatureRequest" wire:loading.attr="disabled" variant="danger">
                    <span wire:loading.remove wire:target="voidSignatureRequest">Void request</span>
                    <span wire:loading wire:target="voidSignatureRequest">Voiding...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Upsell modal (e-sign gated) --}}
    <flux:modal wire:model="showUpsellModal" class="max-w-md">
        <x-lien.waiver-upsell heading="E-sign requires Waiver Pro" />
    </flux:modal>
</div>
