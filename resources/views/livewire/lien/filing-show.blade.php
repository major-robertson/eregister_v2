<div class="max-w-3xl mx-auto space-y-6">
    <x-ui.page-header :title="$filing->documentType->name">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Projects', 'url' => route('lien.projects.index')],
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

    @if(session('upload_received'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.heading>We have your document</flux:callout.heading>
            <flux:callout.text>
                We received {{ session('upload_received') }}. We'll check it and keep going. If the scan is hard to read we'll ask for a clearer one.
            </flux:callout.text>
        </flux:callout>
    @endif

    @if(session('draft_approved'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.heading>Thanks. You approved the draft.</flux:callout.heading>
            <flux:callout.text>
                @if(session('draft_approved') === 'needs_signature')
                    Next we will send it to you to sign. We will email you when it is ready.
                @else
                    We will take it from here and email you when there is an update.
                @endif
            </flux:callout.text>
        </flux:callout>
    @endif

    @if(session('draft_changes'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.heading>Thanks. We have your changes.</flux:callout.heading>
            <flux:callout.text>
                We will update the draft and send it back to you to review.
            </flux:callout.text>
        </flux:callout>
    @endif

    {{-- A draft we sent for review (EREG-140): the PDF itself, then Approve or
         Request changes. Takes the place of the request panel below. --}}
    @if($reviewRequest)
        <div id="draft-review" class="scroll-mt-6 rounded-xl border-2 border-orange-300 bg-white overflow-hidden">
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="document-magnifying-glass" class="w-5 h-5 text-orange-600" />
                    <h2 class="text-lg font-semibold text-zinc-900">Review your draft</h2>
                </div>

                <div class="rounded-lg bg-orange-50 p-4 text-zinc-800 whitespace-pre-line">{{ $reviewRequest['text'] }}</div>

                <p class="text-sm text-zinc-500">
                    Sent by {{ $clientRequestSender ? $clientRequestSender.' at eRegister' : 'eRegister' }}{{ $clientRequestSentAt ? ' on '.$clientRequestSentAt->eastern()->format('M j, Y \a\t g:i A T') : '' }}.
                </p>

                @if($reviewDraftUrl)
                    {{-- Ignored on re-render, so the PDF does not reload while they answer. --}}
                    <div wire:ignore wire:key="draft-review-{{ $reviewDraft->id }}">
                        <iframe src="{{ $reviewDraftUrl }}"
                                title="Draft of your {{ $filing->documentType->name }}"
                                class="w-full h-[70vh] rounded-lg border border-border bg-zinc-50"></iframe>
                    </div>

                    <a href="{{ $reviewDraftUrl }}"
                       download="{{ $reviewDraft->file_name }}"
                       class="inline-flex items-center gap-2 text-sm text-blue-600 hover:underline">
                        <flux:icon name="arrow-down-tray" class="w-4 h-4" />
                        Download the draft ({{ $reviewDraft->file_name }})
                    </a>
                @else
                    <p class="text-sm text-zinc-600">We could not load the draft. Please reply to our email and we will send it again.</p>
                @endif

                <form wire:submit="approveDraft" class="space-y-4 border-t border-border pt-4">
                    <h3 class="text-base font-semibold text-zinc-900">Is everything correct?</h3>

                    <flux:checkbox wire:model="approveConfirmed" label="I have read the whole document and the information in it is correct." />

                    <flux:button type="submit" variant="primary" icon="check">
                        Approve the draft
                    </flux:button>
                </form>

                <form wire:submit="requestDraftChanges" class="space-y-4 border-t border-border pt-4">
                    <h3 class="text-base font-semibold text-zinc-900">Something needs to change?</h3>

                    <flux:field>
                        <flux:label>What should we change?</flux:label>
                        <flux:textarea wire:model="changes" rows="5" placeholder="Tell us what is wrong and what it should say." />
                        <flux:error name="changes" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Attach a file (optional)</flux:label>
                        <flux:file-upload wire:model="changesFile" accept=".pdf,.jpg,.jpeg,.png,.heic,.heif">
                            <flux:file-upload.dropzone
                                inline
                                heading="Drop a file here or click to browse"
                                text="PDF, JPG, PNG or HEIC, up to 20 MB"
                                with-progress
                            />
                        </flux:file-upload>
                        <flux:error name="changesFile" />
                    </flux:field>

                    @if($changesFile)
                        <flux:text class="text-sm">{{ $changesFile->getClientOriginalName() }}</flux:text>
                    @endif

                    <flux:button type="submit" icon="pencil-square">
                        Request changes
                    </flux:button>
                </form>
            </div>
        </div>
    @endif

    @if($signUrl)
        <flux:callout color="purple" icon="pencil-square">
            <flux:callout.heading>Your signature is needed</flux:callout.heading>
            <flux:callout.text>
                This document is ready for your electronic signature.
            </flux:callout.text>
            <x-slot:actions>
                <flux:button href="{{ $signUrl }}" variant="primary" icon="pencil-square">Review &amp; Sign</flux:button>
            </x-slot:actions>
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
                'bg-orange-100 text-orange-600' => $filing->status->value === 'awaiting_client',
                'bg-purple-100 text-purple-600' => $filing->status->value === 'awaiting_esign',
                'bg-violet-100 text-violet-600' => $filing->status->value === 'awaiting_notary',
                'bg-blue-100 text-blue-600' => $filing->status->value === 'in_fulfillment',
                'bg-indigo-100 text-indigo-600' => $filing->status->value === 'mailed',
                'bg-teal-100 text-teal-600' => $filing->status->value === 'submitted_for_recording',
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
                {{ $statusDescription }}
            </p>

            {{-- What's Next --}}
            @if($whatsNext)
                <div class="inline-flex items-center gap-2 text-sm text-zinc-500 bg-zinc-50 px-4 py-2 rounded-full">
                    <flux:icon name="information-circle" class="w-4 h-4" />
                    <span>{{ $whatsNext }}</span>
                </div>
            @endif

            @if($propertyEntry)
                <div class="mt-6">
                    <flux:button href="#property-entry" variant="primary" icon="arrow-down">
                        Enter legal description
                    </flux:button>
                </div>
            @endif

            {{-- Primary Action --}}
            @if($filing->status->value === 'draft' && $filing->project_deadline_id)
                <div class="mt-6">
                    <flux:button
                        href="{{ route('lien.filings.start', ['project' => $filing->project, 'deadline' => $filing->project_deadline_id]) }}"
                        variant="primary"
                        icon="pencil-square"
                    >
                        Continue Filing
                    </flux:button>
                </div>
            @endif

            @if($filing->status->value === 'awaiting_payment')
                <div class="mt-6">
                    <x-ui.action-button href="{{ route('lien.filings.checkout', $filing) }}">
                        Complete Payment
                    </x-ui.action-button>
                </div>
            @endif

            @if($clientRequest && ! $reviewRequest && $filing->status->value === 'awaiting_client')
                <div class="mt-6">
                    <flux:button href="#client-request" variant="primary" icon="arrow-down">
                        Answer the request
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

    {{-- How to sign (EREG-142): while the filing is Awaiting E-Signature or Awaiting Notary.
         TODO: link "upload the scan" to EREG-129's upload box once both are on main. --}}
    @if($signingInstructions)
        <x-ui.card>
            <x-slot:header>{{ $signingInstructions['heading'] }}</x-slot:header>

            <ol class="list-decimal pl-5 space-y-1 text-sm text-zinc-700">
                @foreach($signingInstructions['steps'] as $instructionStep)
                    <li>{{ $instructionStep }}</li>
                @endforeach
            </ol>
        </x-ui.card>
    @endif

    {{-- "Enter legal description" (EREG-136): the customer chose "I need help finding it" at order time.
         TODO: EREG-128 request mechanism replaces this panel after merge. --}}
    @if($propertyEntry)
        <div id="property-entry" class="scroll-mt-6 rounded-xl border-2 border-orange-300 bg-white overflow-hidden">
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="map" class="w-5 h-5 text-orange-600" />
                    <h2 class="text-lg font-semibold text-zinc-900">Enter the legal description or parcel number</h2>
                </div>

                <p class="text-zinc-700">
                    To record your {{ Str::lower($filing->documentType->name) }} we need the legal description or parcel number of the property.
                </p>

                @if($filing->project?->jobsiteAddressLine())
                    <p class="text-sm text-zinc-600"><span class="font-medium">Property:</span> {{ $filing->project->jobsiteAddressLine() }}</p>
                @endif

                <ol class="list-decimal pl-5 space-y-1 text-sm text-zinc-700">
                    <li>
                        Look up the property on the
                        @if($propertyLookup->assessorUrl)
                            <a href="{{ $propertyLookup->assessorUrl }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">county assessor site</a>
                        @else
                            county assessor site
                        @endif
                        and copy the {{ $propertyLookup->parcelNumberPhrase() }}.
                    </li>
                    <li>The legal description is on your deed or the owner's deed. The county recorder's office can give you a copy.</li>
                    <li>Enter it below. Copy it exactly as written.</li>
                </ol>

                <form wire:submit="saveProperty" class="space-y-4 border-t border-border pt-4">
                    <flux:field>
                        <flux:label>Legal description</flux:label>
                        <flux:textarea wire:model="propertyLegalDescription" rows="4" placeholder="Lot 12, Block 3, Sunset Hills Subdivision, as recorded in Plat Book 44, Page 7" />
                        <flux:description>As it appears on the deed.</flux:description>
                        <flux:error name="propertyLegalDescription" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ Str::ucfirst($propertyLookup->parcelNumberPhrase()) }}</flux:label>
                        <flux:input wire:model="propertyApn" placeholder="e.g., 123-456-789" />
                        <flux:error name="propertyApn" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Deed or tax bill page (optional)</flux:label>
                        <flux:file-upload wire:model="propertyFile" accept=".pdf,.jpg,.jpeg,.png,.heic,.heif">
                            <flux:file-upload.dropzone
                                inline
                                heading="Drop a file here or click to browse"
                                text="PDF, JPG, PNG or HEIC, up to 10 MB"
                                with-progress
                            />
                        </flux:file-upload>
                        <flux:error name="propertyFile" />
                    </flux:field>

                    @if($propertyFile)
                        <flux:text class="text-sm">{{ $propertyFile->getClientOriginalName() }}</flux:text>
                    @endif

                    <flux:field>
                        <flux:checkbox wire:model="propertyConfirmed" :label="$propertyConfirmation" />
                        <flux:error name="propertyConfirmed" />
                    </flux:field>

                    <flux:button type="submit" variant="primary">
                        Save
                    </flux:button>
                </form>
            </div>
        </div>
    @endif

    {{-- While the filing waits on the customer (Awaiting Client / E-Signature / Notary):
         the request we emailed, if there is one, answered right here, and the upload
         box for the signed document, so there is one place to respond. --}}
    @if($showUploadBox)
        <div id="client-request" class="scroll-mt-6 rounded-xl border-2 border-orange-300 bg-white overflow-hidden">
            <div class="p-6 space-y-4">
                @if($clientRequest)
                <div class="flex items-center gap-2">
                    <flux:icon name="chat-bubble-left-ellipsis" class="w-5 h-5 text-orange-600" />
                    <h2 class="text-lg font-semibold text-zinc-900">What we need from you</h2>
                </div>

                <div class="rounded-lg bg-orange-50 p-4 text-zinc-800 whitespace-pre-line">{{ $clientRequest['text'] }}</div>

                <p class="text-sm text-zinc-500">
                    Sent by {{ $clientRequestSender ? $clientRequestSender.' at eRegister' : 'eRegister' }}{{ $clientRequestSentAt ? ' on '.$clientRequestSentAt->eastern()->format('M j, Y \a\t g:i A T') : '' }}.
                </p>

                @if($clientRequestAttachment)
                    <a href="{{ $clientRequestAttachment->getTemporaryUrl(now()->addMinutes(30)) }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 text-sm text-blue-600 hover:underline">
                        <flux:icon name="paper-clip" class="w-4 h-4" />
                        {{ $clientRequestAttachment->file_name }}
                    </a>
                @endif
                @endif

                @unless($uploadAfterAnswer)
                    @include('livewire.lien._signed-document-upload', ['inRequestPanel' => $clientRequest !== null])
                @endunless

                @if($clientRequest)
                <form wire:submit="submitAnswer" class="space-y-4 border-t border-border pt-4">
                    <flux:field>
                        <flux:label>Your answer</flux:label>
                        <flux:textarea wire:model="answer" rows="5" placeholder="Type your answer here." />
                        <flux:error name="answer" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Attach a file (optional)</flux:label>
                        <flux:file-upload wire:model="answerFile" accept=".pdf,.jpg,.jpeg,.png,.heic,.heif">
                            <flux:file-upload.dropzone
                                inline
                                heading="Drop a file here or click to browse"
                                text="PDF, JPG, PNG or HEIC, up to 20 MB"
                                with-progress
                            />
                        </flux:file-upload>
                        <flux:error name="answerFile" />
                    </flux:field>

                    @if($answerFile)
                        <flux:text class="text-sm">{{ $answerFile->getClientOriginalName() }}</flux:text>
                    @endif

                    <flux:button type="submit" variant="primary" icon="paper-airplane">
                        Send answer
                    </flux:button>
                </form>
                @endif

                @if($uploadAfterAnswer)
                    @include('livewire.lien._signed-document-upload', ['inRequestPanel' => true])
                @endif
            </div>
        </div>
    @endif

    {{-- Timeline (EREG-130): what happened so far, oldest first. Status
         milestones with their documents and tracking, our requests and the
         customer's answers, uploads and draft reviews. --}}
    @if($timeline !== [])
        <x-ui.card>
            <x-slot:header>Timeline</x-slot:header>

            <ol class="space-y-5">
                @foreach($timeline as $item)
                    <li wire:key="timeline-{{ $loop->index }}" class="flex gap-3">
                        <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-full bg-zinc-100 text-zinc-600">
                            <flux:icon name="{{ $item['icon'] }}" class="w-4 h-4" />
                        </div>

                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-3">
                                <p class="font-medium text-zinc-900">{{ $item['title'] }}</p>
                                <p class="text-xs text-zinc-500">{{ $item['at']->eastern()->format('M j, Y \a\t g:i A T') }}</p>
                            </div>

                            @if($item['text'])
                                <p class="text-sm text-zinc-600">{{ $item['text'] }}</p>
                            @endif

                            @if($item['quote'])
                                <p class="rounded-lg bg-zinc-50 p-3 text-sm text-zinc-700 whitespace-pre-line">{{ $item['quote'] }}</p>
                            @endif

                            @foreach($item['details'] as $detail)
                                <p class="text-sm text-zinc-600">{{ $detail }}</p>
                            @endforeach

                            @if($item['tracking'])
                                <p class="text-sm text-zinc-600">
                                    Tracking:
                                    <a href="{{ $item['tracking']['url'] }}" target="_blank" rel="noopener" class="font-mono text-blue-600 hover:underline">{{ $item['tracking']['number'] }}</a>
                                </p>
                            @endif

                            @foreach($item['links'] as $link)
                                <a href="{{ $link['url'] }}"
                                   target="_blank"
                                   class="flex items-center gap-2 text-sm text-blue-600 hover:underline">
                                    <flux:icon name="arrow-down-tray" class="w-4 h-4" />
                                    {{ $link['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ol>
        </x-ui.card>
    @endif

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
                    <flux:file-upload wire:model="proofFile" accept=".pdf,.jpg,.jpeg,.png">
                        <flux:file-upload.dropzone
                            inline
                            heading="Drop file here or click to browse"
                            text="PDF, JPG, or PNG"
                            with-progress
                        />
                    </flux:file-upload>
                    <flux:error name="proofFile" />
                </flux:field>
                @if($proofFile)
                    <flux:text class="mt-2 text-sm">{{ $proofFile->getClientOriginalName() }}</flux:text>
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
                {{ $filing->service_level?->label() ?? 'Unknown' }}
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
