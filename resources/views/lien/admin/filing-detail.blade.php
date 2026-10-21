<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.liens.board') }}" class="text-gray-500 hover:text-gray-700" wire:navigate>
            <flux:icon name="arrow-left" class="size-5" />
        </a>
        <div class="flex-1">
            <flux:heading size="xl">Filing Detail</flux:heading>
            <flux:text class="mt-1">
                {{ $filing->project?->business?->name }} — {{ $filing->project?->name }}
            </flux:text>
        </div>
        @if ($canSendEsign)
        <flux:button wire:click="confirmSendForEsign" icon="pencil-square" variant="primary" size="sm">
            {{ $hasPriorEsign ? 'Re-send for E-Sign' : 'Send for E-Sign' }}
        </flux:button>
        @endif
        @if ($filing->datesChangedAfterDeadlineBlock())
        <flux:badge color="amber" size="lg" icon="exclamation-triangle">Dates changed after deadline notice</flux:badge>
        @endif
        @if ($isDeleted)
        <flux:badge color="zinc" size="lg">Deleted</flux:badge>
        @else
        <flux:badge color="{{ $kanbanColumn?->color() ?? $filing->status->color() }}" size="lg">
            {{ $kanbanColumn?->label() ?? $filing->status->label() }}@if ($filing->status === \App\Domains\Lien\Enums\FilingStatus::SubmittedForRecording && $filing->recording_method)
                <span class="ml-1 opacity-80">— {{ $filing->recording_method->label() }}</span>
            @endif
        </flux:badge>
        @if ($filing->hasCustomerEmail())
        <flux:badge color="blue" size="lg" icon="envelope">
            Customer emailed &middot; {{ $filing->customer_emailed_at->diffForHumans() }}@if ($filing->customer_emailed_front_url) &middot; <a href="{{ $filing->customer_emailed_front_url }}" target="_blank" rel="noopener" class="ml-1 underline">Open in Front</a>@endif
        </flux:badge>
        @endif
        @endif
    </div>

    @if (session('success'))
    <flux:callout variant="success" icon="check-circle">
        {{ session('success') }}
    </flux:callout>
    @endif

    @if (session('error'))
    <flux:callout variant="danger" icon="x-circle">
        {{ session('error') }}
    </flux:callout>
    @endif

    @if (session('warning'))
    <flux:callout variant="warning" icon="exclamation-triangle">
        {{ session('warning') }}
    </flux:callout>
    @endif

    @if ($isDeleted)
    <flux:callout variant="danger" icon="trash">
        <flux:callout.heading>Filing deleted</flux:callout.heading>
        <flux:callout.text>
            Deleted on {{ $filing->deleted_at?->eastern()->format('M j, Y g:i A') ?? 'unknown' }}.
            This filing is no longer visible to the customer and all automated emails have been stopped.
        </flux:callout.text>
    </flux:callout>
    @endif

    {{-- The customer emailed us (Front webhook): reminders are paused until the status changes or "Still waiting". --}}
    @if (! $isDeleted && $filing->hasCustomerEmail() && $canChangeStatus)
    <form wire:submit="stillWaiting" class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
        <div class="flex flex-wrap items-center gap-3">
            <flux:text class="text-sm text-blue-900">
                Read the customer's email in Front, then change the status, or mark it still waiting{{ $filing->status->isWaitingOnCustomer() ? ' to restart the reminders' : '' }}.
            </flux:text>
            <div class="flex flex-1 items-center gap-2">
                <flux:input size="sm" wire:model="stillWaitingNote" placeholder="Note (optional)" class="min-w-48 flex-1" />
                <flux:button type="submit" size="sm" icon="clock">Still waiting</flux:button>
            </div>
        </div>
        <flux:error name="stillWaitingNote" />
    </form>
    @endif

    <!-- Quick Summary -->
    <div class="grid grid-cols-2 gap-x-6 gap-y-2 rounded-lg border border-border bg-white px-5 py-4 text-sm lg:grid-cols-4">
        <div>
            <flux:text class="text-xs text-gray-500">Business</flux:text>
            <flux:text class="font-medium">{{ $filing->project?->business?->name ?? 'Unknown' }}</flux:text>
        </div>
        <div>
            <flux:text class="text-xs text-gray-500">Filed By</flux:text>
            @if ($filing->createdBy)
            <flux:text class="font-medium">{{ $filing->createdBy->name }}</flux:text>
            <flux:text class="text-xs text-gray-400">{{ $filing->createdBy->email }}</flux:text>
            @else
            <flux:text class="font-medium text-gray-400">Unknown</flux:text>
            @endif
        </div>
        <div>
            <flux:text class="text-xs text-gray-500">Document</flux:text>
            <flux:text class="font-medium">{{ $filing->documentType?->name ?? 'Unknown' }}</flux:text>
            <flux:text class="text-xs text-gray-400">{{ $filing->service_level?->label() ?? '' }}</flux:text>
        </div>
        <div>
            <flux:text class="text-xs text-gray-500">Project Location</flux:text>
            <flux:text class="font-medium">{{ $filing->project?->jobsite_state ?? 'Unknown' }}</flux:text>
            <flux:text class="truncate text-xs text-gray-400">{{ $filing->project?->jobsiteAddressLine() ?: 'No address' }}</flux:text>
        </div>
    </div>

    @if ($isDeleted)
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-border bg-white p-6">
            <flux:heading size="lg" class="mb-4">Filing Reference</flux:heading>
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <flux:text class="text-gray-500">Filing ID</flux:text>
                    <flux:text class="font-mono">{{ $filing->public_id }}</flux:text>
                </div>
                <div class="flex items-center justify-between">
                    <flux:text class="text-gray-500">Deleted</flux:text>
                    <flux:text class="font-medium text-red-600">{{ $filing->deleted_at?->eastern()->format('M j, Y g:i A') ?? '—' }}</flux:text>
                </div>
                <div class="flex items-center justify-between">
                    <flux:text class="text-gray-500">Created</flux:text>
                    <flux:text class="font-medium">{{ $filing->created_at->format('M j, Y') }}</flux:text>
                </div>
                @if ($filing->createdBy)
                <div class="flex items-center justify-between">
                    <flux:text class="text-gray-500">Filed By</flux:text>
                    <flux:text class="font-medium">{{ $filing->createdBy->email }}</flux:text>
                </div>
                @endif
            </div>
        </div>

        <div class="rounded-lg border border-border bg-white p-6">
            <flux:heading size="lg" class="mb-4">Payment Info</flux:heading>
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <flux:text class="text-gray-500">Amount Claimed</flux:text>
                    <flux:text class="font-medium">{{ $filing->formattedAmountClaimed() ?? 'N/A' }}</flux:text>
                </div>
                <div class="flex items-center justify-between">
                    <flux:text class="text-gray-500">Paid</flux:text>
                    <flux:text class="font-medium">
                        @if ($filing->paid_at)
                        {{ $filing->paid_at->eastern()->format('M j, Y g:i A') }}
                        @else
                        Not paid
                        @endif
                    </flux:text>
                </div>
                @if ($refundablePayment)
                <div class="flex items-center justify-between">
                    <flux:text class="text-gray-500">Payment Amount</flux:text>
                    <flux:text class="font-medium">{{ $refundablePayment->formattedAmount() }}</flux:text>
                </div>
                @if ($refundablePayment->isRefunded())
                <div class="flex items-center justify-between">
                    <flux:text class="text-gray-500">Refunded</flux:text>
                    <flux:text class="font-medium text-red-600">{{ $refundablePayment->refunded_at->eastern()->format('M j, Y g:i A') }}</flux:text>
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>
    @else
    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Filing Summary Card -->
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Filing Summary</flux:heading>

                @php
                    $breakdown = $filing->payload_json['amount_breakdown'] ?? [];
                @endphp

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-sm text-gray-500">Amount Claimed</flux:text>
                        <flux:text class="font-medium">
                            @if ($filing->amount_claimed_cents)
                            ${{ number_format($filing->amount_claimed_cents / 100, 2) }}
                            @else
                            N/A
                            @endif
                        </flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Base Contract Amount</flux:text>
                        <flux:text class="font-medium">
                            @if (!empty($breakdown['base_contract_amount']))
                            ${{ number_format((float) $breakdown['base_contract_amount'], 2) }}
                            @else
                            N/A
                            @endif
                        </flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Change Orders</flux:text>
                        <flux:text class="font-medium">
                            ${{ number_format((float) ($breakdown['change_orders'] ?? 0), 2) }}
                        </flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Payments Received</flux:text>
                        <flux:text class="font-medium">
                            ${{ number_format((float) ($breakdown['payments_received'] ?? 0), 2) }}
                        </flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Credit Deductions</flux:text>
                        <flux:text class="font-medium">
                            ${{ number_format((float) ($breakdown['credits_deductions'] ?? 0), 2) }}
                        </flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Uncompleted Work</flux:text>
                        <flux:text class="font-medium">
                            ${{ number_format((float) ($breakdown['uncompleted_work'] ?? 0), 2) }}
                        </flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Written Contract</flux:text>
                        <flux:text class="font-medium">
                            {{ ($breakdown['has_written_contract'] ?? null) === '1' ? 'Yes' : 'No' }}
                        </flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Jurisdiction</flux:text>
                        <flux:text class="font-medium">
                            {{ $filing->jurisdiction_county ?? 'Unknown' }}, {{ $filing->jurisdiction_state ?? 'Unknown'
                            }}
                        </flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Parties</flux:text>
                        <flux:text class="font-medium">
                            {{ count($filing->parties_snapshot_json ?? []) }} parties
                        </flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Current Status</flux:text>
                        <flux:badge color="{{ $filing->status->color() }}">
                            {{ $filing->status->label() }}@if ($filing->status === \App\Domains\Lien\Enums\FilingStatus::SubmittedForRecording && $filing->recording_method)
                                <span class="ml-1 opacity-80">— {{ $filing->recording_method->label() }}</span>
                            @endif
                        </flux:badge>
                    </div>
                </div>
            </div>

            <!-- Business & Filer Information Card -->
            @php
            $business = $filing->project?->business;
            @endphp
            @if ($business)
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Business & Filer Info</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    @if ($business->legal_name)
                    <div>
                        <flux:text class="text-sm text-gray-500">Legal Name</flux:text>
                        <flux:text class="font-medium">{{ $business->legal_name }}</flux:text>
                    </div>
                    @endif

                    @if ($business->dba_name)
                    <div>
                        <flux:text class="text-sm text-gray-500">DBA Name</flux:text>
                        <flux:text class="font-medium">{{ $business->dba_name }}</flux:text>
                    </div>
                    @endif

                    @if ($business->entity_type)
                    <div>
                        <flux:text class="text-sm text-gray-500">Entity Type</flux:text>
                        <flux:text class="font-medium">{{ $business->entity_type }}</flux:text>
                    </div>
                    @endif

                    @if ($business->phone)
                    <div>
                        <flux:text class="text-sm text-gray-500">Phone</flux:text>
                        <flux:text class="font-medium">{{ $business->phone }}</flux:text>
                    </div>
                    @endif

                    @if ($business->business_address)
                    @php
                    $addr = $business->business_address;
                    $addrParts = array_filter([
                    $addr['line1'] ?? null,
                    $addr['line2'] ?? null,
                    ]);
                    $cityStateZip = array_filter([
                    $addr['city'] ?? null,
                    $addr['state'] ?? null,
                    ]);
                    @endphp
                    <div>
                        <flux:text class="text-sm text-gray-500">Business Address</flux:text>
                        @foreach ($addrParts as $line)
                        <flux:text class="font-medium">{{ $line }}</flux:text>
                        @endforeach
                        @if (!empty($cityStateZip))
                        <flux:text class="font-medium">{{ implode(', ', $cityStateZip) }} {{ $addr['zip'] ?? '' }}
                        </flux:text>
                        @endif
                    </div>
                    @endif

                    @if ($business->mailing_address)
                    @php
                    $mail = $business->mailing_address;
                    $mailParts = array_filter([
                    $mail['line1'] ?? null,
                    $mail['line2'] ?? null,
                    ]);
                    $mailCityStateZip = array_filter([
                    $mail['city'] ?? null,
                    $mail['state'] ?? null,
                    ]);
                    @endphp
                    <div>
                        <flux:text class="text-sm text-gray-500">Mailing Address</flux:text>
                        @foreach ($mailParts as $line)
                        <flux:text class="font-medium">{{ $line }}</flux:text>
                        @endforeach
                        @if (!empty($mailCityStateZip))
                        <flux:text class="font-medium">{{ implode(', ', $mailCityStateZip) }} {{ $mail['zip'] ?? '' }}
                        </flux:text>
                        @endif
                    </div>
                    @endif

                    @if ($business->state_of_incorporation)
                    <div>
                        <flux:text class="text-sm text-gray-500">State of Incorporation</flux:text>
                        <flux:text class="font-medium">{{ $business->state_of_incorporation }}</flux:text>
                    </div>
                    @endif

                    @if ($business->contractor_license_number)
                    <div>
                        <flux:text class="text-sm text-gray-500">Contractor License #</flux:text>
                        <flux:text class="font-mono text-sm">{{ $business->contractor_license_number }}</flux:text>
                    </div>
                    @endif
                </div>

                {{-- Filed By --}}
                @if ($filing->createdBy)
                <div class="mt-4 border-t border-gray-100 pt-4">
                    <flux:text class="mb-2 text-sm font-medium text-gray-700">Filed By</flux:text>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:text class="text-sm text-gray-500">Name</flux:text>
                            <flux:text class="font-medium">{{ $filing->createdBy->name }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-500">Email</flux:text>
                            <flux:text class="font-medium">{{ $filing->createdBy->email }}</flux:text>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Responsible People --}}
                @if (!empty($business->responsible_people))
                <div class="mt-4 border-t border-gray-100 pt-4">
                    <flux:text class="mb-2 text-sm font-medium text-gray-700">Responsible People</flux:text>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($business->responsible_people as $person)
                        <div class="rounded-lg border border-gray-200 p-2">
                            <flux:text class="font-medium">{{ $person['name'] ?? 'Unknown' }}</flux:text>
                            @if (!empty($person['title']))
                            <flux:text class="text-sm text-gray-500">{{ $person['title'] }}</flux:text>
                            @endif
                            @if (!empty($person['can_sign_liens']))
                            <flux:badge size="sm" color="green" class="mt-1">Can Sign Liens</flux:badge>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Project Information Card -->
            @if ($filing->project)
            <div class="rounded-lg border border-border bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg">Project Information</flux:heading>
                    @if ($canUpdate)
                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="editProjectDetails">Edit</flux:button>
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-sm text-gray-500">Project Name</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->name }}</flux:text>
                    </div>

                    @if ($filing->project->job_number)
                    <div>
                        <flux:text class="text-sm text-gray-500">Job Number</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->job_number }}</flux:text>
                    </div>
                    @endif

                    @if ($filing->project->claimant_type)
                    <div>
                        <flux:text class="text-sm text-gray-500">Claimant Type</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->claimant_type->label() }}</flux:text>
                    </div>
                    @endif

                    <div class="sm:col-span-2">
                        <flux:text class="text-sm text-gray-500">Jobsite Address</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->jobsiteAddressLine() ?: 'Not specified' }}
                        </flux:text>
                    </div>

                    @if ($filing->project->jobsite_county)
                    <div>
                        <flux:text class="text-sm text-gray-500">County</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->jobsite_county }}</flux:text>
                    </div>
                    @endif

                    @if ($filing->project->property_class)
                    <div>
                        <flux:text class="text-sm text-gray-500">Property Class</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->property_class }}</flux:text>
                    </div>
                    @endif

                    @if ($filing->project->legal_description)
                    <div class="sm:col-span-2">
                        <flux:text class="text-sm text-gray-500">Legal Description</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->legal_description }}</flux:text>
                    </div>
                    @endif

                    @if ($filing->project->apn)
                    <div>
                        <flux:text class="text-sm text-gray-500">APN</flux:text>
                        <flux:text class="font-mono text-sm">{{ $filing->project->apn }}</flux:text>
                    </div>
                    @endif

                    @if ($filing->project->first_furnish_date)
                    <div>
                        <flux:text class="text-sm text-gray-500">First Furnish Date</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->first_furnish_date->format('M j, Y') }}
                        </flux:text>
                    </div>
                    @endif

                    @if ($filing->project->last_furnish_date)
                    <div>
                        <flux:text class="text-sm text-gray-500">Last Furnish Date</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->last_furnish_date->format('M j, Y') }}
                        </flux:text>
                    </div>
                    @endif

                    @if ($filing->project->completion_date)
                    <div>
                        <flux:text class="text-sm text-gray-500">Completion Date</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->completion_date->format('M j, Y') }}
                        </flux:text>
                    </div>
                    @endif

                    @if ($filing->project->noc_status)
                    <div>
                        <flux:text class="text-sm text-gray-500">NOC Status</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->noc_status->label() }}</flux:text>
                    </div>
                    @endif

                    @if ($filing->project->noc_recorded_at)
                    <div>
                        <flux:text class="text-sm text-gray-500">NOC Recorded</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->noc_recorded_at->format('M j, Y') }}
                        </flux:text>
                    </div>
                    @endif

                    @if ($filing->project->hasFinancialData())
                    <div>
                        <flux:text class="text-sm text-gray-500">Base Contract</flux:text>
                        <flux:text class="font-medium">${{ number_format($filing->project->base_contract_amount_cents /
                            100, 2) }}</flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Balance Due</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->formattedBalanceDue() ?? 'N/A' }}
                        </flux:text>
                    </div>
                    @endif

                    <div>
                        <div class="flex items-center gap-1">
                            <flux:text class="text-sm text-gray-500">Owner is Tenant</flux:text>
                            <flux:tooltip content="Was the work performed for someone renting the property rather than the property owner?">
                                <flux:icon name="question-mark-circle" class="size-4 text-gray-400" />
                            </flux:tooltip>
                        </div>
                        <flux:text class="font-medium">{{ $filing->project->owner_is_tenant ? 'Yes' : 'No' }}
                        </flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Written Contract</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->has_written_contract ? 'Yes' : 'No' }}
                        </flux:text>
                    </div>
                </div>
            </div>
            @endif

            <!-- Filing Application Card -->
            <div class="rounded-lg border border-border bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg">Filing Application</flux:heading>
                    @if ($canUpdate)
                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="editFilingDetails">Edit</flux:button>
                    @endif
                </div>

                <div class="mb-4">
                    <flux:text class="text-sm text-gray-500">Description of Work</flux:text>
                    <flux:text class="font-medium">{{ $filing->description_of_work ?: 'Not specified' }}</flux:text>
                </div>

                @if (!empty($filing->parties_snapshot_json))
                <div class="mt-4">
                    <flux:text class="mb-3 text-sm font-medium text-gray-700">Parties (as submitted)</flux:text>
                    <div class="space-y-3">
                        @foreach ($filing->parties_snapshot_json as $party)
                        <div class="rounded-lg border border-gray-200 p-3">
                            <div class="flex items-start justify-between">
                                <div>
                                    <flux:badge size="sm" color="zinc">
                                        {{ \App\Domains\Lien\Enums\PartyRole::tryFrom($party['role'] ?? '')?->label() ??
                                        ucfirst($party['role'] ?? 'Unknown') }}
                                    </flux:badge>
                                    <flux:text class="mt-1 font-medium">
                                        {{ $party['company_name'] ?? $party['name'] ?? 'Unknown' }}
                                    </flux:text>
                                    @if (!empty($party['company_name']) && !empty($party['name']))
                                    <flux:text class="text-sm text-gray-500">{{ $party['name'] }}</flux:text>
                                    @endif
                                </div>
                            </div>
                            @php
                            $addressParts = array_filter([
                            $party['address1'] ?? null,
                            $party['address2'] ?? null,
                            $party['city'] ?? null,
                            $party['state'] ?? null,
                            $party['zip'] ?? null,
                            ]);
                            @endphp
                            @if (!empty($addressParts))
                            <flux:text class="mt-1 text-sm text-gray-500">{{ implode(', ', $addressParts) }}</flux:text>
                            @endif
                            @if (!empty($party['email']) || !empty($party['phone']))
                            <div class="mt-1 flex gap-4 text-sm text-gray-500">
                                @if (!empty($party['email']))
                                <span>{{ $party['email'] }}</span>
                                @endif
                                @if (!empty($party['phone']))
                                <span>{{ $party['phone'] }}</span>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <flux:text class="text-gray-500">No parties snapshot available.</flux:text>
                @endif
            </div>

            <!-- Parties (live / editable) -->
            @if ($filing->project)
            <div class="rounded-lg border border-border bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg">Parties</flux:heading>
                    @if ($canUpdate)
                    <flux:button size="sm" variant="ghost" icon="plus" wire:click="addParty">Add Party</flux:button>
                    @endif
                </div>

                @forelse ($filing->project->parties as $party)
                <div wire:key="party-{{ $party->id }}" class="mb-3 rounded-lg border border-gray-200 p-3 last:mb-0">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <flux:badge size="sm" color="zinc">{{ $party->role?->label() ?? 'Party' }}</flux:badge>
                            <flux:text class="mt-1 font-medium">{{ $party->displayName() ?: 'Unnamed' }}</flux:text>
                            @if ($party->company_name && $party->name)
                            <flux:text class="text-sm text-gray-500">{{ $party->name }}</flux:text>
                            @endif
                            @if ($party->addressLine())
                            <flux:text class="mt-1 text-sm text-gray-500">{{ $party->addressLine() }}</flux:text>
                            @endif
                            @if ($party->email || $party->phone)
                            <div class="mt-1 flex gap-4 text-sm text-gray-500">
                                @if ($party->email)<span>{{ $party->email }}</span>@endif
                                @if ($party->phone)<span>{{ $party->phone }}</span>@endif
                            </div>
                            @endif
                        </div>
                        @if ($canUpdate)
                        <div class="flex shrink-0 gap-1">
                            <flux:button size="xs" variant="ghost" icon="pencil-square" wire:click="editParty({{ $party->id }})" />
                            <flux:button size="xs" variant="ghost" icon="trash" wire:click="confirmRemoveParty({{ $party->id }})" />
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <flux:text class="text-gray-500">No parties yet.</flux:text>
                @endforelse
            </div>

            {{-- Recipients: who this filing's document is served on. Added one at a
                 time (the party's address is snapshotted as of that moment) and
                 updated with the delivery method, tracking and sent / delivered
                 times once it goes out. The proofs of service and labels read
                 these snapshots. --}}
            @if ($filing->hasGeneratedDocuments())
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Recipients</flux:heading>

                @forelse ($filing->recipients as $recipient)
                <div wire:key="recipient-{{ $recipient->id }}" class="mb-3 rounded-lg border border-gray-200 p-3 last:mb-0">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <flux:badge size="sm" color="zinc">{{ $recipient->party?->role?->label() ?? 'Recipient' }}</flux:badge>
                            <flux:text class="mt-1 font-medium">{{ $recipient->snapshotName() }}</flux:text>
                            <flux:text class="text-sm text-gray-500">{{ $recipient->snapshotAddressLine() ?: 'No address in the snapshot' }}</flux:text>
                            <flux:text class="mt-1 text-xs text-gray-500">
                                {{ ucfirst(\App\Domains\Lien\Documents\LienDocumentPayload::deliveryLabel($recipient->delivery_method) ?? 'delivery method not set') }}
                                @if ($recipient->tracking_number) &middot; {{ $recipient->tracking_number }} @endif
                                @if ($recipient->sent_at) &middot; Sent {{ $recipient->sent_at->eastern()->format('M j, Y g:i A') }} @else &middot; Not sent yet @endif
                                @if ($recipient->delivered_at) &middot; Delivered {{ $recipient->delivered_at->eastern()->format('M j, Y g:i A') }} @endif
                            </flux:text>
                        </div>
                        @if ($canUpdate)
                        <flux:button size="xs" variant="ghost" icon="pencil-square" wire:click="editRecipient({{ $recipient->id }})" />
                        @endif
                    </div>
                </div>
                @empty
                <flux:text class="text-sm text-gray-500">No recipients yet. Add the parties this document is served on.</flux:text>
                @endforelse

                @if ($canUpdate)
                @php
                    $recipientPartyIds = $filing->recipients->pluck('party_id')->all();
                    $addableParties = $filing->project->parties
                        ->reject(fn ($party) => $party->role === \App\Domains\Lien\Enums\PartyRole::Claimant || in_array($party->id, $recipientPartyIds, true));
                @endphp
                @if ($addableParties->isNotEmpty())
                <div class="mt-4 border-t border-gray-200 pt-3">
                    <flux:text class="mb-2 text-xs text-gray-500">Add as recipient (snapshots the party's current address):</flux:text>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($addableParties as $party)
                        <flux:button wire:key="add-recipient-{{ $party->id }}" size="xs" variant="filled" icon="plus" wire:click="addRecipient({{ $party->id }})">
                            {{ $party->displayName() ?: 'Unnamed' }} ({{ $party->role->label() }})
                        </flux:button>
                        @endforeach
                    </div>
                </div>
                @endif
                @endif
            </div>
            @endif
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Actions Card -->
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Actions</flux:heading>

                <!-- Needs Review Toggle -->
                @if ($canUpdate)
                <div class="mb-4 flex items-center justify-between rounded-lg border border-gray-200 p-3">
                    <div>
                        <flux:text class="font-medium">Needs Review</flux:text>
                        <flux:text class="text-sm text-gray-500">Flag for manager attention</flux:text>
                    </div>
                    <flux:switch wire:click="toggleNeedsReview" :checked="$filing->needs_review" />
                </div>
                @endif

                <!-- Status Change Form -->
                @if ($canChangeStatus && count($allowedTransitions) > 0)
                <form wire:submit="updateStatus" class="space-y-4">
                    <flux:field>
                        <flux:label>Change Status</flux:label>
                        <flux:select wire:model.live="newStatus">
                            <flux:select.option value="">Select new status...</flux:select.option>
                            @foreach ($allowedTransitions as $status)
                            <flux:select.option value="{{ $status->value }}">
                                {{ $status->label() }}
                            </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="newStatus" />
                    </flux:field>

                    {{-- Recording-detail capture: required only when transitioning to SubmittedForRecording.
                         Submitted-at is prefilled with now() by LienFilingDetail::updatedNewStatus(). --}}
                    @if ($newStatus === \App\Domains\Lien\Enums\FilingStatus::SubmittedForRecording->value)
                    <div class="space-y-3 rounded-lg border border-teal-200 bg-teal-50 p-4">
                        <flux:text class="text-sm font-medium text-teal-900">
                            How was this submitted to the county?
                        </flux:text>

                        <flux:field>
                            <flux:label>Recording Method <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model="recordingMethod">
                                <flux:select.option value="">Select method...</flux:select.option>
                                @foreach (\App\Domains\Lien\Enums\RecordingMethod::cases() as $method)
                                <flux:select.option value="{{ $method->value }}">{{ $method->label() }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="recordingMethod" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Submitted At <span class="text-red-500">*</span></flux:label>
                            <flux:input type="datetime-local" wire:model="recordingSubmittedAt" />
                            <flux:error name="recordingSubmittedAt" />
                        </flux:field>
                    </div>
                    @endif

                    {{-- Milestone documents (EREG-130): the recorded copy at Recorded, the proof of
                         service and tracking number at Mailed, both at Complete. All optional; the
                         milestone email attaches them and the customer's timeline links them. --}}
                    @php
                        $milestoneStatus = \App\Domains\Lien\Enums\FilingStatus::tryFrom($newStatus);
                        $isMilestone = $milestoneStatus !== null && in_array($milestoneStatus, \App\Mail\FilingMilestone::STATUSES, true);
                        $takesRecordedCopy = in_array($newStatus, ['recorded', 'complete'], true);
                        $takesProofOfService = in_array($newStatus, ['mailed', 'complete'], true);
                    @endphp
                    @if ($takesRecordedCopy || $takesProofOfService)
                    <div class="space-y-3 rounded-lg border border-teal-200 bg-teal-50 p-4">
                        <flux:text class="text-sm font-medium text-teal-900">
                            Documents for the customer
                        </flux:text>

                        @if ($newStatus === 'recorded')
                        <flux:field>
                            <flux:label>Recording reference (instrument number, or book and page)</flux:label>
                            <flux:input wire:model="recordingReference" placeholder="2026-0012345" />
                            <flux:error name="recordingReference" />
                        </flux:field>
                        @endif

                        @if ($takesRecordedCopy)
                        <flux:field>
                            <flux:label>Recorded copy (optional)</flux:label>
                            <flux:file-upload wire:model="recordedCopy" accept=".pdf,application/pdf">
                                <flux:file-upload.dropzone
                                    inline
                                    heading="Drop the recorded PDF here or click to browse"
                                    text="PDF, up to 10 MB"
                                    with-progress
                                />
                            </flux:file-upload>
                            @if ($recordedCopy)
                            <flux:text class="mt-1 text-sm">{{ $recordedCopy->getClientOriginalName() }}</flux:text>
                            @endif
                            <flux:error name="recordedCopy" />
                        </flux:field>
                        @endif

                        @if ($newStatus === 'mailed')
                        <flux:field>
                            <flux:label>Certified mail tracking number (optional)</flux:label>
                            <flux:input wire:model="trackingNumber" placeholder="9407 1118 9876 5432 1012 34" />
                            <flux:error name="trackingNumber" />
                        </flux:field>
                        @endif

                        @if ($takesProofOfService)
                        <flux:field>
                            <flux:label>Proof of service (optional)</flux:label>
                            <flux:file-upload wire:model="proofOfService" accept=".pdf,application/pdf">
                                <flux:file-upload.dropzone
                                    inline
                                    heading="Drop the proof of service PDF here or click to browse"
                                    text="PDF, up to 10 MB"
                                    with-progress
                                />
                            </flux:file-upload>
                            @if ($proofOfService)
                            <flux:text class="mt-1 text-sm">{{ $proofOfService->getClientOriginalName() }}</flux:text>
                            @endif
                            <flux:error name="proofOfService" />
                        </flux:field>
                        @endif
                    </div>
                    @endif

                    @if ($isMilestone)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        @if (! \App\Mail\FilingMilestone::sendsFor($filing, $milestoneStatus))
                        <flux:text class="text-sm text-gray-600">Self-serve filing: no email goes out.</flux:text>
                        @elseif ($filing->createdBy)
                        <flux:text class="text-sm text-gray-700">
                            We'll email {{ $filing->createdBy->name }} &lt;{{ $filing->createdBy->email }}&gt; when you save.
                            @if ($takesRecordedCopy || $takesProofOfService)
                            It attaches the newest {{ match (true) { $takesRecordedCopy && $takesProofOfService => 'recorded copy and proof of service', $takesRecordedCopy => 'recorded copy', default => 'proof of service' } }} on the filing, if there is one.
                            @endif
                        </flux:text>
                        <flux:text class="mt-1 text-xs text-gray-500">
                            <span class="font-medium">Subject:</span>
                            {{ \App\Mail\FilingMilestone::subjectFor($milestoneStatus, $filing->documentType?->name, \App\Mail\FilingMilestone::countyName($filing)) }}
                        </flux:text>
                        @else
                        <flux:text class="text-sm text-red-700">This filing has no customer user, so no email will go out.</flux:text>
                        @endif
                    </div>
                    @endif

                    {{-- Awaiting Client / E-Signature / Notary: the customer is emailed this
                         message on save (FilingActionRequested), so it comes first and is required. --}}
                    @php
                        $requestStatus = \App\Domains\Lien\Enums\FilingStatus::tryFrom($newStatus);
                        $sendsCustomerRequest = $requestStatus?->isWaitingOnCustomer() ?? false;
                    @endphp
                    @if ($sendsCustomerRequest)
                    <div class="space-y-3 rounded-lg border-2 border-amber-400 bg-amber-50 p-4">
                        <div class="flex items-center gap-2">
                            <flux:icon name="envelope" class="size-5 shrink-0 text-amber-600" />
                            <flux:heading size="sm" class="text-amber-900">Message to the customer</flux:heading>
                        </div>

                        @if ($filing->createdBy)
                        <flux:text class="text-sm text-amber-900">
                            This will be emailed to {{ $filing->createdBy->name }} &lt;{{ $filing->createdBy->email }}&gt; when you save.
                        </flux:text>
                        @else
                        <flux:text class="text-sm text-red-700">
                            This filing has no customer user, so no email will go out. The message still shows on their filing page.
                        </flux:text>
                        @endif

                        <flux:field>
                            <flux:label>Message <span class="text-red-500">*</span></flux:label>
                            <flux:textarea wire:model="customerMessage" rows="5"
                                placeholder="Say exactly what you need, in plain words." />
                            <flux:error name="customerMessage" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Attach a PDF (optional)</flux:label>
                            <flux:file-upload wire:model="customerAttachment" accept=".pdf,application/pdf">
                                <flux:file-upload.dropzone
                                    inline
                                    heading="Drop a PDF here or click to browse"
                                    text="PDF, up to 10 MB"
                                    with-progress
                                />
                            </flux:file-upload>
                            @if ($customerAttachment)
                            <flux:text class="mt-1 text-sm">{{ $customerAttachment->getClientOriginalName() }}</flux:text>
                            @endif
                            <flux:error name="customerAttachment" />
                        </flux:field>

                        <flux:text class="text-xs text-amber-800">
                            <span class="font-medium">Subject:</span>
                            {{ \App\Mail\FilingActionRequested::subjectFor($requestStatus, $filing->documentType?->name) }}
                        </flux:text>
                    </div>

                    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-3">
                        <flux:field>
                            <flux:label class="text-gray-500!">Internal note (the customer never sees this)</flux:label>
                            <flux:textarea wire:model="note" rows="2"
                                placeholder="A note for the team..." />
                            <flux:error name="note" />
                        </flux:field>
                    </div>
                    @else
                    <flux:field>
                        <flux:label>Note (optional)</flux:label>
                        <flux:textarea wire:model="note" rows="3"
                            placeholder="Add a note about this status change..." />
                        <flux:error name="note" />
                    </flux:field>
                    @endif

                    <flux:button type="submit" variant="primary" class="w-full">
                        {{ $sendsCustomerRequest ? 'Send to customer and update status' : 'Update Status' }}
                    </flux:button>
                </form>
                @elseif (!$canChangeStatus)
                <flux:text class="text-sm text-gray-500">
                    You don't have permission to change the status.
                </flux:text>
                @else
                <flux:text class="text-sm text-gray-500">
                    No status transitions available from current status.
                </flux:text>
                @endif

            </div>

            {{-- Draft for review (EREG-140): every draft version, newest first, and
                 Send for review, which emails the newest one to the customer to
                 approve or send back with changes (FilingShow). --}}
            @if ($canChangeStatus || $drafts->isNotEmpty())
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Draft for review</flux:heading>

                @if ($drafts->isNotEmpty())
                <div class="space-y-2">
                    @foreach ($drafts as $draft)
                    @php
                        $draftUploader = $draftUploaders->get($draft->getCustomProperty('uploaded_by_user_id'));
                        $draftOutcome = match ($draftOutcomes->get($draft->id)) {
                            'draft_sent_for_review' => ['Sent for review', 'text-amber-700'],
                            'draft_approved' => ['Approved', 'text-green-700'],
                            'draft_changes_requested' => ['Changes requested', 'text-red-700'],
                            default => null,
                        };
                    @endphp
                    <a wire:key="draft-{{ $draft->id }}"
                        href="{{ $draft->getTemporaryUrl(now()->addMinutes(30)) }}"
                        target="_blank"
                        class="flex items-center gap-3 rounded-lg border border-gray-200 p-2 hover:bg-gray-50">
                        <flux:badge size="sm" color="zinc">v{{ $draft->getCustomProperty('version') }}</flux:badge>
                        <div class="min-w-0 flex-1">
                            <flux:text class="truncate text-sm font-medium">{{ $draft->file_name }}</flux:text>
                            <flux:text class="text-xs text-gray-500">
                                {{ $draftUploader?->name ?? 'Unknown' }} &middot; {{ $draft->created_at->eastern()->format('M j, Y g:i A T') }}@if ($draft->getCustomProperty('source') === 'generated') &middot; From the generated document @endif
                            </flux:text>
                            @if ($draftOutcome)
                            <flux:text class="text-xs font-medium {{ $draftOutcome[1] }}">{{ $draftOutcome[0] }}</flux:text>
                            @endif
                        </div>
                        <flux:icon name="arrow-down-tray" class="size-4 shrink-0 text-gray-400" />
                    </a>
                    @endforeach
                </div>
                @else
                <flux:text class="text-sm text-gray-500">No draft yet. Upload a PDF to send it to the customer for review.</flux:text>
                @endif

                @if ($canChangeStatus)
                <form wire:submit="uploadDraft" class="mt-4 space-y-3">
                    <flux:field>
                        <flux:file-upload wire:model="draftFile" accept=".pdf,application/pdf">
                            <flux:file-upload.dropzone
                                inline
                                heading="Drop a PDF here or click to browse"
                                text="PDF, up to 20 MB"
                                with-progress
                            />
                        </flux:file-upload>
                        @if ($draftFile)
                        <flux:text class="mt-1 text-sm">{{ $draftFile->getClientOriginalName() }}</flux:text>
                        @endif
                        <flux:error name="draftFile" />
                    </flux:field>

                    <flux:button type="submit" size="sm" icon="arrow-up-tray" class="w-full">Upload draft</flux:button>
                </form>

                @if ($canUseGeneratedDraft)
                <flux:button size="sm" icon="document-duplicate" class="mt-2 w-full" wire:click="useGeneratedDraft">
                    Use the generated document
                </flux:button>
                @endif

                <div class="mt-4 border-t border-gray-100 pt-4">
                    @if ($canSendDraft)
                    <flux:button variant="primary" icon="paper-airplane" class="w-full" wire:click="confirmSendDraftForReview">
                        Send for review
                    </flux:button>
                    @elseif ($reviewOpen)
                    <flux:text class="text-sm text-gray-500">Waiting on the customer's review. Their answer shows under Requests and answers.</flux:text>
                    @elseif ($drafts->isNotEmpty())
                    <flux:text class="text-sm text-gray-500">A draft can't be sent for review from the “{{ $filing->status->label() }}” status.</flux:text>
                    @endif

                    {{-- EREG-141: why Send for E-Sign is not offered for this document. --}}
                    @if ($esignBlockedReason)
                    <flux:text class="mt-2 text-sm text-gray-500">{{ $esignBlockedReason }}</flux:text>
                    @endif
                </div>
                @endif
            </div>
            @endif

            {{-- Send for review dialog (always present; toggled by showSendDraftModal) --}}
            @if ($canChangeStatus)
            <flux:modal wire:model="showSendDraftModal" class="max-w-lg">
                <form wire:submit="sendDraftForReview" class="space-y-5">
                    @php $latestDraft = $drafts->first(); @endphp
                    <div>
                        <flux:heading size="lg">Send the draft for review?</flux:heading>
                        <flux:subheading class="mt-2">
                            @if ($filing->createdBy)
                            We'll email {{ $latestDraft ? 'version '.$latestDraft->getCustomProperty('version').' ('.$latestDraft->file_name.')' : 'the newest draft' }}
                            to {{ $filing->createdBy->name }} &lt;{{ $filing->createdBy->email }}&gt; with a Review the draft button.
                            @else
                            This filing has no customer user, so no email will go out. The draft still shows on their filing page.
                            @endif
                            The filing moves to <strong>Awaiting Client</strong> and the usual reminders start.
                        </flux:subheading>
                    </div>

                    <flux:field>
                        <flux:label>Message for the customer <span class="text-red-500">*</span></flux:label>
                        <flux:textarea wire:model="draftMessage" rows="5" />
                        <flux:error name="draftMessage" />
                    </flux:field>

                    <flux:checkbox wire:model="draftNeedsSignature"
                        label="This document will need the customer's signature afterwards"
                        description="When they approve, the filing goes to Needs Review instead of Ready to File, so you can send the signature request." />

                    <flux:text class="text-xs text-gray-500">
                        <span class="font-medium">Subject:</span>
                        {{ \App\Mail\FilingActionRequested::subjectFor(\App\Domains\Lien\Enums\FilingStatus::AwaitingClient, $filing->documentType?->name, true) }}
                    </flux:text>

                    <div class="flex justify-end gap-2">
                        <flux:modal.close>
                            <flux:button variant="filled">Cancel</flux:button>
                        </flux:modal.close>
                        <flux:button type="submit" variant="primary" icon="paper-airplane">
                            Send for review
                        </flux:button>
                    </div>
                </form>
            </flux:modal>
            @endif

            {{-- Requests and answers: the latest request we emailed the customer and
                 everything they sent back from their filing page, then every file
                 they uploaded there (answers' files and signed documents). --}}
            @if ($clientRequest || $clientResponses->isNotEmpty() || $customerUploads->isNotEmpty())
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Requests and answers</flux:heading>

                @if ($clientRequest)
                @php
                    $requestedAt = ! empty($clientRequest['requested_at']) ? \Illuminate\Support\Carbon::parse($clientRequest['requested_at']) : null;
                    $resolvedAt = ! empty($clientRequest['resolved_at']) ? \Illuminate\Support\Carbon::parse($clientRequest['resolved_at']) : null;
                    $requestFile = ! empty($clientRequest['attachment_media_id']) ? $requestFiles->get($clientRequest['attachment_media_id']) : null;
                @endphp
                <div @class([
                    'rounded-lg border p-3',
                    'border-amber-300 bg-amber-50' => $clientRequestOpen,
                    'border-gray-200 bg-gray-50' => ! $clientRequestOpen,
                ])>
                    <div class="flex items-center justify-between gap-2">
                        <flux:text class="text-xs font-medium {{ $clientRequestOpen ? 'text-amber-800' : 'text-gray-500' }}">
                            {{ $clientRequestOpen ? 'Open request' : 'Latest request' }}
                        </flux:text>
                        @if ($requestedAt)
                        <flux:text class="text-xs text-gray-400">{{ $requestedAt->eastern()->format('M j, g:i A') }}</flux:text>
                        @endif
                    </div>
                    <p class="mt-2 whitespace-pre-line text-sm text-gray-800">{{ $clientRequest['text'] ?? '' }}</p>
                    @if ($requestFile)
                    <a href="{{ $requestFile->getTemporaryUrl(now()->addMinutes(30)) }}" target="_blank"
                       class="mt-2 inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
                        <flux:icon name="paper-clip" class="size-4" /> {{ $requestFile->file_name }}
                    </a>
                    @endif
                    @if (($clientRequest['type'] ?? null) === 'review')
                    @php $reviewFile = ! empty($clientRequest['draft_media_id']) ? $requestFiles->get($clientRequest['draft_media_id']) : null; @endphp
                    <flux:text class="mt-2 text-xs font-medium text-gray-600">
                        Draft for review{{ $reviewFile ? ': v'.$reviewFile->getCustomProperty('version') : '' }}@if (! empty($clientRequest['needs_signature'])) &middot; Needs their signature afterwards @endif
                    </flux:text>
                    @if ($reviewFile)
                    <a href="{{ $reviewFile->getTemporaryUrl(now()->addMinutes(30)) }}" target="_blank"
                       class="mt-1 inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
                        <flux:icon name="paper-clip" class="size-4" /> {{ $reviewFile->file_name }}
                    </a>
                    @endif
                    @endif
                    @if ($resolvedAt)
                    <flux:text class="mt-2 text-xs text-gray-500">Closed {{ $resolvedAt->eastern()->format('M j, g:i A') }}</flux:text>
                    @endif
                </div>
                @endif

                @foreach ($clientResponses as $response)
                @php
                    // For an approval, media_id is the approved draft; otherwise the customer's own file.
                    $answerFile = ! empty($response->payload_json['media_id']) ? $requestFiles->get($response->payload_json['media_id']) : null;
                    [$responseLabel, $responseColor] = match ($response->event_type) {
                        'draft_approved' => ['Customer approved the draft', 'text-green-700'],
                        'draft_changes_requested' => ['Customer asked for changes', 'text-red-700'],
                        default => ['Customer answered', 'text-green-700'],
                    };
                @endphp
                <div wire:key="client-response-{{ $response->id }}" class="mt-3 border-t border-gray-100 pt-3">
                    <flux:text class="text-xs font-medium {{ $responseColor }}">
                        {{ $responseLabel }} &middot; {{ $response->created_at->eastern()->format('M j, g:i A') }}
                        @if ($response->creator) &middot; {{ $response->creator->name }} @endif
                    </flux:text>
                    @if ($response->event_type !== 'client_response' && ! empty($response->payload_json['version']))
                    <flux:text class="mt-1 text-xs text-gray-600">
                        {{ $response->event_type === 'draft_approved' ? 'Approved' : 'On' }} version {{ $response->payload_json['version'] }}@if ($response->event_type === 'draft_approved' && ! empty($response->payload_json['needs_signature'])) &middot; Needs their signature next @endif
                    </flux:text>
                    @endif
                    @if (! empty($response->payload_json['text']))
                    <p class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $response->payload_json['text'] }}</p>
                    @endif
                    @if ($answerFile)
                    <a href="{{ $answerFile->getTemporaryUrl(now()->addMinutes(30)) }}" target="_blank"
                       class="mt-1 inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
                        <flux:icon name="paper-clip" class="size-4" /> {{ $answerFile->file_name }}
                    </a>
                    @endif
                </div>
                @endforeach

                @if ($customerUploads->isNotEmpty())
                <div @class(['mt-4 border-t border-gray-100 pt-3' => $clientRequest || $clientResponses->isNotEmpty()])>
                    <flux:heading size="sm" class="mb-2">Customer uploads</flux:heading>
                    <div class="space-y-2">
                        @foreach ($customerUploads as $upload)
                        <a wire:key="customer-upload-{{ $upload->id }}"
                            href="{{ route('admin.liens.customer-uploads.download', [$filing->public_id, $upload->id]) }}"
                            target="_blank"
                            class="flex items-center gap-3 rounded-lg border border-gray-200 p-2 hover:bg-gray-50">
                            <flux:icon name="document-text" class="size-5 shrink-0 text-gray-400" />
                            <div class="min-w-0 flex-1">
                                <flux:text class="truncate text-sm font-medium">{{ $upload->file_name }}</flux:text>
                                <flux:text class="text-xs text-gray-500">
                                    {{ $upload->human_readable_size }} &middot; {{ $upload->created_at->eastern()->format('M j, Y g:i A T') }}@if ($upload->getCustomProperty('kind') === 'signed_document') &middot; Signed document @elseif ($upload->getCustomProperty('kind') === 'draft_changes') &middot; Changes to the draft @endif
                                </flux:text>
                            </div>
                            <flux:icon name="arrow-down-tray" class="size-4 shrink-0 text-gray-400" />
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Send for E-Sign confirmation modal (always present; toggled by showSendEsignModal) --}}
            <flux:modal wire:model="showSendEsignModal" class="max-w-md">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ $hasPriorEsign ? 'Re-send for e-signature?' : 'Send for e-signature?' }}</flux:heading>
                        <flux:subheading class="mt-2">
                            @if ($filing->isDemandLetter())
                            We'll generate and lock one demand letter for each party checked below,
                            then email {{ $filing->createdBy?->name ?? 'the filing creator' }}
                            (<span class="font-medium">{{ $filing->createdBy?->email }}</span>) a link to review and sign.
                            @else
                            We'll lock the newest draft below exactly as uploaded,
                            then email {{ $filing->createdBy?->name ?? 'the filing creator' }}
                            (<span class="font-medium">{{ $filing->createdBy?->email }}</span>) a link to review and sign it.
                            The signed copy gets a page with the signature and the certificate.
                            @endif
                            The filing status will change to <strong>Awaiting E-Signature</strong>.
                        </flux:subheading>
                    </div>

                    @if ($filing->isDemandLetter())
                    <div>
                        <flux:checkbox.group wire:model="esignPartyIds" label="Send a letter to">
                            @foreach ($demandRecipients as $party)
                            <flux:checkbox
                                wire:key="esign-party-{{ $party->id }}"
                                value="{{ $party->id }}"
                                label="{{ $party->displayName() ?: 'Unnamed party' }} ({{ $party->role?->label() ?? 'Recipient' }})"
                                description="{{ $party->addressLine() ?: 'No address on file' }}"
                            />
                            @endforeach
                        </flux:checkbox.group>
                        <flux:error name="esignPartyIds" />
                    </div>
                    @elseif ($esignDraft)
                    {{-- EREG-141: the draft version that will be signed. --}}
                    <div class="flex items-center gap-3 rounded-lg border border-gray-200 p-3">
                        <flux:badge size="sm" color="zinc">v{{ $esignDraft->getCustomProperty('version') }}</flux:badge>
                        <div class="min-w-0 flex-1">
                            <flux:text class="truncate text-sm font-medium">{{ $esignDraft->file_name }}</flux:text>
                            <flux:text class="text-xs text-gray-500">
                                Uploaded {{ $esignDraft->created_at->eastern()->format('M j, Y g:i A T') }}{{ $esignDraftUploader ? ' by '.$esignDraftUploader->name : '' }}
                            </flux:text>
                        </div>
                    </div>
                    @endif

                    @if ($hasPriorEsign)
                    <flux:callout variant="warning" icon="exclamation-triangle">
                        <flux:callout.text>
                            This filing has <strong>already been e-signed</strong>. Sending again starts a brand-new
                            signature request with a new signing link. The previously signed copies stay available
                            to download{{ $filing->isDemandLetter() ? ' under Demand Letters' : '' }}.
                        </flux:callout.text>
                    </flux:callout>
                    @endif

                    <div class="flex justify-end gap-2">
                        <flux:modal.close>
                            <flux:button variant="filled">Cancel</flux:button>
                        </flux:modal.close>
                        <flux:button wire:click="sendForEsign" variant="primary" icon="pencil-square">
                            {{ $hasPriorEsign ? 'Re-send for E-Sign' : 'Send for E-Sign' }}
                        </flux:button>
                    </div>
                </div>
            </flux:modal>

            {{-- E-Signature status panel: appears once a session has been started --}}
            @if ($esignRequest)
            <div class="rounded-lg border border-border bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg">E-Signature</flux:heading>
                    <flux:badge color="{{ $esignRequest->status->color() }}">{{ $esignRequest->status->label() }}</flux:badge>
                </div>

                <div class="space-y-2 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <flux:text class="text-gray-500">Signer</flux:text>
                        <div class="text-right">
                            <flux:text class="font-medium">{{ $esignRequest->signer_name_snapshot ?: '—' }}</flux:text>
                            <flux:text class="block text-xs text-gray-400">{{ $esignRequest->signer_email_snapshot }}</flux:text>
                        </div>
                    </div>
                    @if ($esignRequest->invited_at)
                    <div class="flex items-center justify-between">
                        <flux:text class="text-gray-500">Sent</flux:text>
                        <flux:text class="font-medium">{{ $esignRequest->invited_at->eastern()->format('M j, Y g:i A') }}</flux:text>
                    </div>
                    @endif
                    @if ($esignRequest->first_opened_at)
                    <div class="flex items-center justify-between">
                        <flux:text class="text-gray-500">Opened</flux:text>
                        <flux:text class="font-medium">{{ $esignRequest->first_opened_at->eastern()->format('M j, Y g:i A') }}</flux:text>
                    </div>
                    @endif
                    @if ($esignRequest->completed_at)
                    <div class="flex items-center justify-between">
                        <flux:text class="text-gray-500">Signed</flux:text>
                        <flux:text class="font-medium">{{ $esignRequest->completed_at->eastern()->format('M j, Y g:i A') }}</flux:text>
                    </div>
                    @endif
                    @if ($esignReminders->isNotEmpty())
                    <div class="flex items-center justify-between">
                        <flux:text class="text-gray-500">Last reminder</flux:text>
                        <flux:text class="font-medium">
                            {{ $esignReminders->last()->occurred_at->eastern()->format('M j, Y g:i A') }}@if ($esignReminders->count() > 1) <span class="text-xs font-normal text-gray-400">({{ $esignReminders->count() }} sent)</span>@endif
                        </flux:text>
                    </div>
                    @endif
                    @if ($esignAwaiting && $esignRequest->expires_at)
                    <div class="flex items-center justify-between">
                        <flux:text class="text-gray-500">Link expires</flux:text>
                        <flux:text :class="$esignRequest->isExpired() ? 'font-medium text-red-600' : 'font-medium'">
                            {{ $esignRequest->expires_at->eastern()->format('M j, Y g:i A') }}@if ($esignRequest->isExpired()) (expired)@endif
                        </flux:text>
                    </div>
                    @endif
                    @if ($esignRequest->consent)
                    <div class="flex items-center justify-between">
                        <flux:text class="text-gray-500">Consent</flux:text>
                        <flux:text class="font-medium text-xs">{{ $esignRequest->consent->consent_scope }} · {{ $esignRequest->consent->version }}</flux:text>
                    </div>
                    @endif
                </div>

                @if ($esignAwaiting && $esignRequest->invitation_bounced_at)
                <flux:callout variant="danger" icon="exclamation-triangle" class="mt-4">
                    <flux:callout.heading>The signing email is bouncing</flux:callout.heading>
                    <flux:callout.text>
                        Email to {{ $esignRequest->signer_email_snapshot }} can't be delivered, so reminders are off.
                        @if ($signingUrl)
                        Copy the signing link below and share it with the signer directly, or void and re-send once their address is fixed.
                        @else
                        Void and re-send once their address is fixed.
                        @endif
                    </flux:callout.text>
                </flux:callout>
                @elseif ($canRemindSigner && $esignRequest->isExpired())
                <flux:callout variant="warning" icon="clock" class="mt-4">
                    <flux:callout.text>
                        The signing link expired {{ $esignRequest->expires_at->eastern()->format('M j, Y') }}.
                        Sending a reminder renews it for another {{ config('esign.signing.invitation_link_ttl_days', 14) }} days.
                    </flux:callout.text>
                </flux:callout>
                @endif

                {{-- Copyable signing link, for sharing outside email (text, chat, a call).
                     Keyed on the expiry so a renewed link replaces the field's value. --}}
                @if ($signingUrl)
                <div class="mt-4" wire:key="esign-link-{{ $esignRequest->id }}-{{ $esignRequest->expires_at?->timestamp }}">
                    <flux:input size="sm" readonly copyable :value="$signingUrl" label="Signing link"
                        description="Works only for {{ $esignRequest->signer_email_snapshot }}, who signs in to use it." />
                </div>
                @endif

                @if ($esignRequest->intent_statement)
                <flux:text class="mt-3 block rounded bg-gray-50 p-2 text-xs text-gray-600">{{ $esignRequest->intent_statement }}</flux:text>
                @endif

                <div class="mt-4 space-y-2">
                    @foreach ($esignRequest->documents as $doc)
                    <div wire:key="esign-doc-{{ $doc->id }}" class="rounded-lg border border-gray-200 p-2">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <flux:text class="font-mono text-xs text-gray-500">{{ $doc->document_identifier }}</flux:text>
                                <flux:text class="truncate text-sm font-medium">{{ $doc->label }}</flux:text>
                            </div>
                            @if ($doc->signed_at)
                            <flux:badge size="sm" color="green">Signed</flux:badge>
                            @elseif ($doc->locked_at)
                            <flux:badge size="sm" color="zinc">Locked</flux:badge>
                            @endif
                        </div>
                        <div class="mt-1 space-y-0.5 text-[11px] text-gray-400">
                            @if ($doc->locked_document_hash)
                            <div>locked: <span class="font-mono">{{ \Illuminate\Support\Str::limit($doc->locked_document_hash, 16, '…') }}</span></div>
                            @endif
                            @if ($doc->signed_document_hash)
                            <div>signed: <span class="font-mono">{{ \Illuminate\Support\Str::limit($doc->signed_document_hash, 16, '…') }}</span></div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Uploaded documents (EREG-141): the signed PDF from the latest
                     completed request, so it stays here after a re-send. Demand
                     letters list theirs in the Demand Letters card below. --}}
                @if (! $filing->isDemandLetter() && $signedLetters->isNotEmpty())
                <div class="mt-4 space-y-2">
                    @foreach ($signedLetters as $signedDoc)
                    <a wire:key="signed-doc-{{ $signedDoc->id }}" title="{{ $signedDoc->label }}"
                        href="{{ route('admin.liens.esign.documents.download', $signedDoc->public_id) }}"
                        class="flex items-center gap-3 rounded-lg border border-gray-200 p-2 hover:bg-gray-50">
                        <flux:icon name="document-check" class="size-5 shrink-0 text-green-600" />
                        <div class="min-w-0 flex-1">
                            <flux:text class="truncate text-sm font-medium">Signed PDF: {{ $signedDoc->label }}</flux:text>
                            <flux:text class="text-xs text-gray-500">{{ $signedDoc->document_identifier }} · Signed {{ $signedDoc->signed_at->eastern()->format('M j, Y g:i A T') }}</flux:text>
                        </div>
                        <flux:icon name="arrow-down-tray" class="size-4 shrink-0 text-gray-400" />
                    </a>
                    @endforeach
                </div>
                @endif

                <div class="mt-4 flex flex-wrap gap-2 border-t border-gray-100 pt-4">
                    @if ($canRemindSigner)
                    <flux:button size="sm" icon="envelope" wire:click="sendSignerReminder"
                        wire:confirm="Email {{ $esignRequest->signer_email_snapshot }} a reminder with the signing link? This also renews the link for {{ config('esign.signing.invitation_link_ttl_days', 14) }} days.">Send reminder</flux:button>
                    @endif
                    <flux:button size="sm" variant="ghost" icon="shield-check" wire:click="verifyChain">Verify audit chain</flux:button>
                    @if ($esignRequest->isActive() && $canChangeStatus)
                    <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="voidEsign"
                        wire:confirm="Void this signature request? The signer's link will stop working.">Void</flux:button>
                    @endif
                </div>
            </div>
            @endif

            {{-- Demand letter downloads, under the E-Signature panel. Signed copies come
                 from the latest completed session, so they survive a re-send; each
                 recipient's draft is generated from the filing's current details. --}}
            @if ($filing->isDemandLetter())
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Demand Letters</flux:heading>

                <div class="space-y-2">
                    @foreach ($signedLetters as $letter)
                    <a wire:key="signed-letter-{{ $letter->id }}" title="{{ $letter->label }}"
                        href="{{ route('admin.liens.esign.documents.download', $letter->public_id) }}"
                        class="flex items-center gap-3 rounded-lg border border-gray-200 p-2 hover:bg-gray-50">
                        <flux:icon name="document-check" class="size-5 shrink-0 text-green-600" />
                        <div class="min-w-0 flex-1">
                            <flux:text class="truncate text-sm font-medium">{{ $letter->label }}</flux:text>
                            <flux:text class="text-xs text-gray-500">{{ $letter->document_identifier }} · Signed {{ $letter->signed_at->eastern()->format('M j, Y') }}</flux:text>
                        </div>
                        <flux:icon name="arrow-down-tray" class="size-4 shrink-0 text-gray-400" />
                    </a>
                    @endforeach

                    @forelse ($demandRecipients as $party)
                    <a wire:key="draft-letter-{{ $party->id }}"
                        href="{{ route('admin.liens.demand-letter', [$filing->public_id, $party->id]) }}"
                        class="flex items-center gap-3 rounded-lg border border-gray-200 p-2 hover:bg-gray-50">
                        <flux:icon name="document-text" class="size-5 shrink-0 text-gray-400" />
                        <div class="min-w-0 flex-1">
                            <flux:text class="truncate text-sm font-medium">{{ $party->displayName() ?: 'Unnamed party' }}</flux:text>
                            <flux:text class="text-xs text-gray-500">{{ $party->role->label() }} · Unsigned draft</flux:text>
                        </div>
                        <flux:icon name="arrow-down-tray" class="size-4 shrink-0 text-gray-400" />
                    </a>
                    @empty
                    <flux:text class="text-sm text-gray-500">Add a recipient under Parties to generate a letter.</flux:text>
                    @endforelse
                </div>

                @if ($demandRecipients->count() > 1)
                <flux:button size="sm" icon="document-duplicate" class="mt-3 w-full"
                    :href="route('admin.liens.demand-letters', $filing->public_id)">
                    Download all drafts ({{ $demandRecipients->count() }})
                </flux:button>
                @endif
            </div>
            @endif

            {{-- Generated documents for the other kinds (prelim notice, NOI, lien,
                 release): the rule strip from the state and county data files,
                 pre-flight warnings, and the download links. Drafts render from
                 the filing's live data; nothing is stored. --}}
            @if ($documentPackage !== null)
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Documents</flux:heading>

                @if ($documentPackage->form === null)
                <flux:text class="text-sm text-gray-500">{{ $documentPackage->unavailableReason }}</flux:text>
                @else
                <dl class="mb-4 space-y-1.5 text-sm">
                    @foreach ($documentPackage->rules() as $rule)
                    <div class="flex gap-2">
                        <dt class="w-16 shrink-0 text-gray-500">{{ $rule['label'] }}</dt>
                        <dd class="min-w-0 flex-1 text-gray-800">{{ $rule['value'] }}</dd>
                    </div>
                    @endforeach
                </dl>

                @if ($documentPackage->form->notes !== [])
                <details class="mb-4 text-sm">
                    <summary class="cursor-pointer text-gray-500">Filing notes ({{ count($documentPackage->form->notes) }})</summary>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-gray-700">
                        @foreach ($documentPackage->form->notes as $note)
                        <li>{{ $note }}</li>
                        @endforeach
                    </ul>
                </details>
                @endif

                @if ($documentPackage->warnings !== [])
                <flux:callout variant="warning" icon="exclamation-triangle" class="mb-4">
                    <flux:callout.heading>Check before generating</flux:callout.heading>
                    <flux:callout.text>
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($documentPackage->warnings as $warning)
                            <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </flux:callout.text>
                </flux:callout>
                @endif

                @if (! $documentPackage->isAvailable())
                <flux:text class="text-sm text-gray-500">{{ $documentPackage->unavailableReason }}</flux:text>
                @else
                <div class="space-y-2">
                    @foreach ($documentPackage->items as $item)
                    <a wire:key="document-{{ $item['document']->value }}-{{ $item['recipient'] ?? 'all' }}"
                        href="{{ $item['url'] }}"
                        class="flex items-center gap-3 rounded-lg border border-gray-200 p-2 hover:bg-gray-50">
                        <flux:icon name="document-text" class="size-5 shrink-0 text-gray-400" />
                        <div class="min-w-0 flex-1">
                            <flux:text class="truncate text-sm font-medium">{{ $item['label'] }}</flux:text>
                            <flux:text class="text-xs text-gray-500">{{ $item['sublabel'] }} · Draft for signing</flux:text>
                        </div>
                        <flux:icon name="arrow-down-tray" class="size-4 shrink-0 text-gray-400" />
                    </a>
                    @endforeach
                </div>
                @if ($documentPackage->zipUrl())
                <flux:button size="sm" icon="archive-box-arrow-down" class="mt-3 w-full" :href="$documentPackage->zipUrl()">
                    Download the package ({{ count($documentPackage->items) }} files)
                </flux:button>
                @endif
                <flux:text class="mt-3 text-xs text-gray-500">Drafts are built from the filing's current details each time. Fix the data and regenerate; never mark up a signed copy.</flux:text>
                @endif
                @endif
            </div>

            {{-- Document details: the facts the generated documents need that the
                 application never asked for (signer, license, contract, prior notice,
                 the lien a release refers to). Stored on the filing and audited; not
                 part of payload_json. --}}
            <div class="rounded-lg border border-border bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg">Document details</flux:heading>
                    @if ($canUpdate)
                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="editDocumentDetails">Edit</flux:button>
                    @endif
                </div>

                @php
                    $documentDetails = $filing->document_details_json ?? [];
                    $originalLien = $documentDetails['original_lien'] ?? [];
                    $money = fn ($cents) => $cents === null ? null : '$'.number_format(((int) $cents) / 100, 2);
                    $day = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('M j, Y') : null;
                    $noiServedTo = ['owner' => 'the owner', 'gc' => 'the general contractor', 'both' => 'the owner and the general contractor'];
                    $documentRows = array_filter([
                        'Signer' => trim(implode(', ', array_filter([$documentDetails['signer_name'] ?? null, $documentDetails['signer_title'] ?? null]))) ?: null,
                        'License number' => $documentDetails['license_number'] ?? null,
                        'Contract' => trim(implode(', ', array_filter([
                            isset($documentDetails['contract_type']) ? ucfirst($documentDetails['contract_type']) : null,
                            $day($documentDetails['contract_date'] ?? null),
                        ]))) ?: null,
                        'Estimated price' => $money($documentDetails['estimated_price_cents'] ?? null),
                        'Notice served' => trim(implode(' by ', array_filter([
                            $day($documentDetails['notice_served_at'] ?? null),
                            \App\Domains\Lien\Documents\LienDocumentPayload::deliveryLabel($documentDetails['notice_served_method'] ?? null),
                        ]))) ?: null,
                        'Months of work' => $documentDetails['months_of_work'] ?? null,
                        'Owner interest' => $documentDetails['owner_interest'] ?? null,
                        'Block / lot' => trim(implode(' / ', array_filter([$documentDetails['block'] ?? null, $documentDetails['lot'] ?? null]))) ?: null,
                        'Original lien' => trim(implode(', ', array_filter([
                            $originalLien['recording_reference'] ?? null,
                            isset($originalLien['book']) || isset($originalLien['page']) ? 'Book '.($originalLien['book'] ?? '—').' Page '.($originalLien['page'] ?? '—') : null,
                            isset($originalLien['county']) ? $originalLien['county'].' County' : null,
                            $day($originalLien['recorded_at'] ?? null),
                        ]))) ?: null,
                        'Other lien info' => $originalLien['other_info'] ?? null,
                        'Amount received' => $money($originalLien['amount_received_cents'] ?? null),
                        'Attachments' => $documentDetails['attachments_note'] ?? null,
                        // The customer's notice-of-intent answer from the filing wizard (read-only here)
                        'NOI served' => isset($documentDetails['noi_served_on'])
                            ? $day($documentDetails['noi_served_on'])
                                .(isset($documentDetails['noi_served_to']) ? ' to '.($noiServedTo[$documentDetails['noi_served_to']] ?? $documentDetails['noi_served_to']) : '')
                                .(isset($documentDetails['noi_confirmed_at']) ? ' (customer confirmed '.\Illuminate\Support\Carbon::parse($documentDetails['noi_confirmed_at'])->eastern()->format('M j, Y').')' : '')
                            : null,
                        'NOI' => ($documentDetails['noi_not_applicable_reason'] ?? null) === 'customer_says_not_applicable' ? 'Customer says it does not apply to them' : null,
                    ]);
                @endphp

                @if ($documentRows === [])
                <flux:text class="text-sm text-gray-500">Nothing set yet. Until then the documents use the business's lien signer and the project's dates.</flux:text>
                @else
                <dl class="space-y-1.5 text-sm">
                    @foreach ($documentRows as $label => $value)
                    <div class="flex gap-2">
                        <dt class="w-28 shrink-0 text-gray-500">{{ $label }}</dt>
                        <dd class="min-w-0 flex-1 text-gray-800">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
                @endif
            </div>
            @endif

            {{-- Recorded copies and proofs of service uploaded on the status form
                 (EREG-130), newest first. The newest of each is what the
                 milestone emails attach and the customer's timeline links. --}}
            @if ($milestoneFiles->isNotEmpty())
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Recorded copy and proof of service</flux:heading>

                <div class="space-y-2">
                    @foreach ($milestoneFiles as $milestoneFile)
                    <a wire:key="milestone-file-{{ $milestoneFile->id }}"
                        href="{{ $milestoneFile->getTemporaryUrl(now()->addMinutes(30)) }}"
                        target="_blank"
                        class="flex items-center gap-3 rounded-lg border border-gray-200 p-2 hover:bg-gray-50">
                        <flux:icon name="document-text" class="size-5 shrink-0 text-gray-400" />
                        <div class="min-w-0 flex-1">
                            <flux:text class="truncate text-sm font-medium">{{ $milestoneFile->file_name }}</flux:text>
                            <flux:text class="text-xs text-gray-500">
                                {{ $milestoneFile->collection_name === 'recorded' ? 'Recorded copy' : 'Proof of service' }} &middot; {{ $milestoneFile->human_readable_size }} &middot; {{ $milestoneFile->created_at->eastern()->format('M j, Y g:i A T') }}
                            </flux:text>
                        </div>
                        <flux:icon name="arrow-down-tray" class="size-4 shrink-0 text-gray-400" />
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Recording Details Card: appears once recording_method has been set on this filing.
                 Lets admins fill in or correct provider/reference/submitted_at after the SubmittedForRecording
                 transition without needing to re-run a status change. --}}
            @if ($filing->recording_method !== null && $canUpdate)
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Recording Details</flux:heading>

                <form wire:submit="updateRecordingDetails" class="space-y-4">
                    <flux:field>
                        <flux:label>Recording Method <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model="recordingMethod">
                            @foreach (\App\Domains\Lien\Enums\RecordingMethod::cases() as $method)
                            <flux:select.option value="{{ $method->value }}">{{ $method->label() }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="recordingMethod" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Submitted At <span class="text-red-500">*</span></flux:label>
                        <flux:input type="datetime-local" wire:model="recordingSubmittedAt" />
                        <flux:error name="recordingSubmittedAt" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Recording reference</flux:label>
                        <flux:input wire:model="recordingReference" placeholder="Instrument number, or book and page" />
                        <flux:error name="recordingReference" />
                    </flux:field>

                    <flux:button type="submit" variant="primary" class="w-full">
                        Save Recording Details
                    </flux:button>
                </form>
            </div>
            @endif

            <!-- Payment & Filing Info Card -->
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Payment & Filing Info</flux:heading>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <flux:text class="text-sm text-gray-500">Document Type</flux:text>
                        <flux:text class="font-medium">{{ $filing->documentType?->name ?? 'Unknown' }}</flux:text>
                    </div>

                    <div class="flex items-center justify-between">
                        <flux:text class="text-sm text-gray-500">Service Level</flux:text>
                        <flux:text class="font-medium">{{ $filing->service_level?->label() ?? 'Unknown' }}</flux:text>
                    </div>

                    <flux:separator />

                    <div class="flex items-center justify-between">
                        <flux:text class="text-sm text-gray-500">Created</flux:text>
                        <flux:text class="font-medium">{{ $filing->created_at->eastern()->format('M j, Y g:i A') }}</flux:text>
                    </div>

                    <div class="flex items-center justify-between">
                        <flux:text class="text-sm text-gray-500">Paid</flux:text>
                        <flux:text class="font-medium">
                            @if ($filing->paid_at)
                            {{ $filing->paid_at->eastern()->format('M j, Y g:i A') }}
                            @else
                            Not paid
                            @endif
                        </flux:text>
                    </div>

                    @if ($refundablePayment?->isRefunded())
                    <div class="flex items-center justify-between">
                        <flux:text class="text-sm text-gray-500">Refunded</flux:text>
                        <flux:text class="font-medium text-red-600">{{ $refundablePayment->refunded_at->eastern()->format('M j, Y g:i A') }}</flux:text>
                    </div>
                    @endif
                </div>

                @if ($canRefund)
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <flux:button wire:click="confirmRefund" variant="danger" class="w-full" icon="arrow-uturn-left">
                        Refund {{ $refundablePayment->formattedAmount() }}
                    </flux:button>
                </div>
                @elseif ($refundablePayment?->isRefunded())
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <div class="flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 p-3">
                        <flux:icon name="arrow-uturn-left" class="size-5 text-red-500" />
                        <div>
                            <flux:text class="font-medium text-red-800">Payment Refunded</flux:text>
                            <flux:text class="text-sm text-red-600">
                                {{ $refundablePayment->formattedAmount() }}
                            </flux:text>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Danger Zone --}}
            @if ($canDelete)
            <div class="rounded-lg border border-red-200 bg-red-50 p-6">
                <flux:heading size="lg" class="mb-2 text-red-800">Danger Zone</flux:heading>
                <flux:text class="mb-4 text-sm text-red-700">
                    Deleting this filing will hide it from the customer and stop all automated emails. Admins will still see it marked as deleted.
                </flux:text>
                <flux:button wire:click="confirmDelete" variant="danger" class="w-full" icon="trash">
                    Delete Filing
                </flux:button>
            </div>

            <flux:modal wire:model="showDeleteModal" class="max-w-md">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Delete this filing?</flux:heading>
                        <flux:subheading class="mt-2">
                            The customer will no longer be able to see this filing, and all automated reminder emails for it will stop. Admins will still see it marked as deleted.
                        </flux:subheading>
                    </div>

                    <div class="flex justify-end gap-2">
                        <flux:modal.close>
                            <flux:button variant="filled">Cancel</flux:button>
                        </flux:modal.close>

                        <flux:button wire:click="deleteFiling" variant="danger" icon="trash">
                            Delete Filing
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
            @endif

            <!-- Refund Confirmation Modal -->
            @if ($canRefund)
            <flux:modal wire:model="showRefundModal" class="max-w-md">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Confirm Refund</flux:heading>
                        <flux:subheading class="mt-2">
                            Are you sure you want to refund <strong>{{ $refundablePayment->formattedAmount() }}</strong>?
                            This action cannot be undone.
                        </flux:subheading>
                    </div>

                    <div class="flex justify-end gap-2">
                        <flux:modal.close>
                            <flux:button variant="filled">Cancel</flux:button>
                        </flux:modal.close>

                        <flux:button wire:click="refundPayment" variant="danger">
                            Refund {{ $refundablePayment->formattedAmount() }}
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
            @endif

            <!-- Add Comment Card -->
            @if ($canAddComment)
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Add Comment</flux:heading>

                <form wire:submit="addComment" class="space-y-4">
                    <flux:field>
                        <flux:textarea wire:model="comment" rows="3"
                            placeholder="Write a comment..." />
                        <flux:error name="comment" />
                    </flux:field>

                    <flux:button type="submit" variant="primary" class="w-full">
                        Add Comment
                    </flux:button>
                </form>
            </div>
            @endif

            <!-- Activity Log -->
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Activity Log</flux:heading>

                @if ($activityLog->isNotEmpty())
                <div class="space-y-2">
                    @foreach ($activityLog as $event)
                    <div wire:key="event-{{ $event->id }}" class="border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                        @if ($event->event_type === 'status_changed')
                        @php
                        $toStatus = \App\Domains\Lien\Enums\FilingStatus::tryFrom($event->payload_json['to'] ?? '');
                        $note = $event->payload_json['meta']['note'] ?? null;
                        // Prefer the historical snapshot from event meta. For events written
                        // before we started snapshotting recording_method, fall back to the
                        // filing's current recording_method when the transition was into
                        // SubmittedForRecording — best-effort label so old entries aren't blank.
                        $recordingMethodAtTime = \App\Domains\Lien\Enums\RecordingMethod::tryFrom(
                            $event->payload_json['meta']['recording_method'] ?? ''
                        );
                        if (! $recordingMethodAtTime
                            && $toStatus === \App\Domains\Lien\Enums\FilingStatus::SubmittedForRecording
                            && $filing->recording_method) {
                            $recordingMethodAtTime = $filing->recording_method;
                        }
                        @endphp
                        <div class="flex items-center gap-2 flex-wrap">
                            @if ($toStatus)
                            <flux:badge size="sm" color="{{ $toStatus->color() }}">
                                {{ $toStatus->label() }}@if ($recordingMethodAtTime)
                                    <span class="ml-1 opacity-80">— {{ $recordingMethodAtTime->label() }}</span>
                                @endif
                            </flux:badge>
                            @endif
                            <flux:text class="text-xs text-gray-400">
                                {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                @if ($event->creator)
                                &middot; {{ $event->creator->name }}
                                @endif
                            </flux:text>
                        </div>
                        @if ($note)
                        <flux:text class="mt-1 text-sm text-gray-600">{{ $note }}</flux:text>
                        @endif
                        @php
                            // Milestone details saved with the change (EREG-130).
                            $eventMeta = $event->payload_json['meta'] ?? [];
                            $eventMilestoneFiles = collect([$eventMeta['recorded_media_id'] ?? null, $eventMeta['proof_of_service_media_id'] ?? null])
                                ->filter()
                                ->map(fn ($mediaId) => $milestoneFiles->get($mediaId))
                                ->filter();
                        @endphp
                        @if (! empty($eventMeta['recording_reference']))
                        <flux:text class="mt-1 text-xs text-gray-600">Recording reference: {{ $eventMeta['recording_reference'] }}</flux:text>
                        @endif
                        @if (! empty($eventMeta['tracking_number']))
                        <flux:text class="mt-1 text-xs text-gray-600">Tracking: {{ $eventMeta['tracking_number'] }}</flux:text>
                        @endif
                        @foreach ($eventMilestoneFiles as $eventMilestoneFile)
                        <a href="{{ $eventMilestoneFile->getTemporaryUrl(now()->addMinutes(30)) }}" target="_blank"
                           class="mt-1 inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
                            <flux:icon name="paper-clip" class="size-4" /> {{ $eventMilestoneFile->file_name }}
                        </a>
                        @endforeach
                        @elseif ($event->event_type === 'note_added')
                        <div class="flex items-start gap-2">
                            <flux:icon name="chat-bubble-left" class="mt-0.5 size-4 shrink-0 text-blue-400" />
                            <div class="min-w-0">
                                <flux:text class="text-sm text-gray-600">{{ $event->payload_json['comment'] ?? '' }}</flux:text>
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                    @if ($event->creator)
                                    &middot; {{ $event->creator->name }}
                                    @endif
                                </flux:text>
                            </div>
                        </div>
                        @elseif ($event->event_type === 'payment_refunded')
                        <div class="flex items-start gap-2">
                            <flux:icon name="arrow-uturn-left" class="mt-0.5 size-4 shrink-0 text-red-400" />
                            <div class="min-w-0">
                                <flux:text class="text-sm font-medium text-red-600">Refunded {{ $event->payload_json['amount'] ?? '' }}</flux:text>
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                    @if ($event->creator)
                                    &middot; {{ $event->creator->name }}
                                    @endif
                                </flux:text>
                            </div>
                        </div>
                        @elseif ($event->event_type === 'recording_details_updated')
                        @php $changes = $event->payload_json['changes'] ?? []; @endphp
                        <div class="flex items-start gap-2">
                            <flux:icon name="arrow-up-tray" class="mt-0.5 size-4 shrink-0 text-teal-500" />
                            <div class="min-w-0">
                                <flux:text class="text-sm font-medium text-teal-700">Recording details updated</flux:text>
                                @if (! empty($changes))
                                <ul class="mt-1 space-y-0.5 text-xs text-gray-600">
                                    @foreach ($changes as $field => $change)
                                    <li>
                                        <span class="font-medium">{{ str_replace('_', ' ', \Illuminate\Support\Str::after($field, 'recording_')) }}:</span>
                                        <span class="text-gray-400">{{ $change['from'] ?? 'empty' }}</span>
                                        &rarr;
                                        <span>{{ $change['to'] ?? 'empty' }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                    @if ($event->creator)
                                    &middot; {{ $event->creator->name }}
                                    @endif
                                </flux:text>
                            </div>
                        </div>
                        @elseif ($event->event_type === 'application_project_updated')
                        <div class="flex items-start gap-2">
                            <flux:icon name="pencil-square" class="mt-0.5 size-4 shrink-0 text-indigo-500" />
                            <div class="min-w-0">
                                <flux:text class="text-sm font-medium text-indigo-700">Project details updated</flux:text>
                                @include('lien.admin.partials.application-change-list', ['changes' => $event->payload_json['changes'] ?? []])
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                    @if ($event->creator) &middot; {{ $event->creator->name }} @endif
                                </flux:text>
                            </div>
                        </div>
                        @elseif ($event->event_type === 'application_filing_updated')
                        <div class="flex items-start gap-2">
                            <flux:icon name="pencil-square" class="mt-0.5 size-4 shrink-0 text-indigo-500" />
                            <div class="min-w-0">
                                <flux:text class="text-sm font-medium text-indigo-700">Filing details updated</flux:text>
                                @include('lien.admin.partials.application-change-list', ['changes' => $event->payload_json['changes'] ?? []])
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                    @if ($event->creator) &middot; {{ $event->creator->name }} @endif
                                </flux:text>
                            </div>
                        </div>
                        @elseif ($event->event_type === 'document_details_updated')
                        <div class="flex items-start gap-2">
                            <flux:icon name="pencil-square" class="mt-0.5 size-4 shrink-0 text-indigo-500" />
                            <div class="min-w-0">
                                <flux:text class="text-sm font-medium text-indigo-700">Document details updated</flux:text>
                                @include('lien.admin.partials.application-change-list', ['changes' => $event->payload_json['changes'] ?? []])
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                    @if ($event->creator) &middot; {{ $event->creator->name }} @endif
                                </flux:text>
                            </div>
                        </div>
                        @elseif ($event->event_type === 'recipient_added')
                        <div class="flex items-start gap-2">
                            <flux:icon name="user-plus" class="mt-0.5 size-4 shrink-0 text-indigo-500" />
                            <div class="min-w-0">
                                <flux:text class="text-sm font-medium text-indigo-700">Recipient added: {{ $event->payload_json['recipient']['name'] ?? 'Unnamed' }}</flux:text>
                                @if (! empty($event->payload_json['recipient']['address']))
                                <flux:text class="text-xs text-gray-600">{{ $event->payload_json['recipient']['address'] }}</flux:text>
                                @endif
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                    @if ($event->creator) &middot; {{ $event->creator->name }} @endif
                                </flux:text>
                            </div>
                        </div>
                        @elseif ($event->event_type === 'recipient_updated')
                        <div class="flex items-start gap-2">
                            <flux:icon name="paper-airplane" class="mt-0.5 size-4 shrink-0 text-indigo-500" />
                            <div class="min-w-0">
                                <flux:text class="text-sm font-medium text-indigo-700">Recipient updated: {{ $event->payload_json['recipient']['name'] ?? 'Unnamed' }}</flux:text>
                                @include('lien.admin.partials.application-change-list', ['changes' => $event->payload_json['changes'] ?? []])
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                    @if ($event->creator) &middot; {{ $event->creator->name }} @endif
                                </flux:text>
                            </div>
                        </div>
                        @elseif ($event->event_type === 'application_parties_updated')
                        @php
                        $partyMeta = $event->payload_json['party'] ?? [];
                        $partyVerb = match ($partyMeta['action'] ?? 'updated') {
                            'added' => 'Party added',
                            'removed' => 'Party removed',
                            default => 'Party updated',
                        };
                        @endphp
                        <div class="flex items-start gap-2">
                            <flux:icon name="users" class="mt-0.5 size-4 shrink-0 text-indigo-500" />
                            <div class="min-w-0">
                                <flux:text class="text-sm font-medium text-indigo-700">{{ $partyVerb }}</flux:text>
                                @if (! empty($partyMeta['label']))
                                <flux:text class="text-xs text-gray-600">{{ $partyMeta['label'] }}</flux:text>
                                @endif
                                @include('lien.admin.partials.application-change-list', ['changes' => $event->payload_json['changes'] ?? []])
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                    @if ($event->creator) &middot; {{ $event->creator->name }} @endif
                                </flux:text>
                            </div>
                        </div>
                        @elseif (in_array($event->event_type, ['esign_sent', 'esign_reminder_sent', 'esign_completed'], true))
                        <div class="flex items-start gap-2">
                            <flux:icon :name="$event->event_type === 'esign_reminder_sent' ? 'envelope' : 'pencil-square'" class="mt-0.5 size-4 shrink-0 text-purple-500" />
                            <div class="min-w-0">
                                <flux:text class="text-sm font-medium text-purple-700">{{ $event->description() }}</flux:text>
                                @if ($event->event_type === 'esign_sent' && ! empty($event->payload_json['signer_email']))
                                <flux:text class="text-xs text-gray-600">
                                    to {{ $event->payload_json['signer_email'] }} &middot;
                                    @if (isset($event->payload_json['version']))
                                    draft v{{ $event->payload_json['version'] }} ({{ $event->payload_json['file_name'] ?? 'PDF' }})
                                    @else
                                    {{ $event->payload_json['documents'] ?? 1 }} letter(s)
                                    @endif
                                </flux:text>
                                @elseif ($event->event_type === 'esign_reminder_sent' && ! empty($event->payload_json['signer_email']))
                                <flux:text class="text-xs text-gray-600">to {{ $event->payload_json['signer_email'] }}</flux:text>
                                @elseif ($event->event_type === 'esign_completed' && isset($event->payload_json['version']))
                                <flux:text class="text-xs text-gray-600">draft v{{ $event->payload_json['version'] }} &middot; signed copy saved with the filing</flux:text>
                                @endif
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                    @if ($event->creator) &middot; {{ $event->creator->name }} @endif
                                </flux:text>
                            </div>
                        </div>
                        @elseif (in_array($event->event_type, ['client_request_sent', 'client_response'], true))
                        @php
                            $isAnswer = $event->event_type === 'client_response';
                            $eventFileId = $event->payload_json[$isAnswer ? 'media_id' : 'attachment_media_id'] ?? null;
                            $eventFile = $eventFileId ? $requestFiles->get($eventFileId) : null;
                        @endphp
                        <div class="flex items-start gap-2">
                            <flux:icon :name="$isAnswer ? 'chat-bubble-left-ellipsis' : 'envelope'" class="mt-0.5 size-4 shrink-0 {{ $isAnswer ? 'text-green-500' : 'text-amber-500' }}" />
                            <div class="min-w-0">
                                <flux:text class="text-sm font-medium {{ $isAnswer ? 'text-green-700' : 'text-amber-700' }}">{{ $event->description() }}</flux:text>
                                @if (! $isAnswer && ! empty($event->payload_json['sent_to']))
                                <flux:text class="text-xs text-gray-600">to {{ $event->payload_json['sent_to'] }}</flux:text>
                                @endif
                                @if (! empty($event->payload_json['text']))
                                <p class="mt-1 whitespace-pre-line text-sm text-gray-600">{{ $event->payload_json['text'] }}</p>
                                @endif
                                @if ($eventFile)
                                <a href="{{ $eventFile->getTemporaryUrl(now()->addMinutes(30)) }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                                    <flux:icon name="paper-clip" class="size-3" /> {{ $eventFile->file_name }}
                                </a>
                                @elseif (! empty($event->payload_json['file_name']))
                                <flux:text class="text-xs text-gray-500">{{ $event->payload_json['file_name'] }} (file removed)</flux:text>
                                @endif
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                    @if ($event->creator) &middot; {{ $event->creator->name }} @endif
                                </flux:text>
                            </div>
                        </div>
                        @elseif (in_array($event->event_type, ['draft_sent_for_review', 'draft_approved', 'draft_changes_requested'], true))
                        @php
                            // media_id: the draft (sent, approved) or the customer's own file (changes).
                            $draftEventFile = ! empty($event->payload_json['media_id']) ? $requestFiles->get($event->payload_json['media_id']) : null;
                            [$draftEventIcon, $draftEventIconColor, $draftEventTextColor] = match ($event->event_type) {
                                'draft_approved' => ['check-circle', 'text-green-500', 'text-green-700'],
                                'draft_changes_requested' => ['pencil-square', 'text-red-500', 'text-red-700'],
                                default => ['document-magnifying-glass', 'text-amber-500', 'text-amber-700'],
                            };
                        @endphp
                        <div class="flex items-start gap-2">
                            <flux:icon :name="$draftEventIcon" class="mt-0.5 size-4 shrink-0 {{ $draftEventIconColor }}" />
                            <div class="min-w-0">
                                <flux:text class="text-sm font-medium {{ $draftEventTextColor }}">{{ $event->description() }}</flux:text>
                                @if (! empty($event->payload_json['version']))
                                <flux:text class="text-xs text-gray-600">
                                    Version {{ $event->payload_json['version'] }}@if ($event->event_type !== 'draft_changes_requested' && ! empty($event->payload_json['file_name'])) &middot; {{ $event->payload_json['file_name'] }}@endif
                                    @if (! empty($event->payload_json['needs_signature'])) &middot; needs their signature afterwards @endif
                                </flux:text>
                                @endif
                                @if (! empty($event->payload_json['text']))
                                <p class="mt-1 whitespace-pre-line text-sm text-gray-600">{{ $event->payload_json['text'] }}</p>
                                @endif
                                @if ($draftEventFile)
                                <a href="{{ $draftEventFile->getTemporaryUrl(now()->addMinutes(30)) }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                                    <flux:icon name="paper-clip" class="size-3" /> {{ $draftEventFile->file_name }}
                                </a>
                                @endif
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                    @if ($event->creator) &middot; {{ $event->creator->name }} @endif
                                </flux:text>
                            </div>
                        </div>
                        @elseif ($event->event_type === 'customer_emailed')
                        <div class="flex items-start gap-2">
                            <flux:icon name="envelope" class="mt-0.5 size-4 shrink-0 text-blue-500" />
                            <div class="min-w-0">
                                <flux:text class="text-sm font-medium text-blue-700">{{ $event->description() }}</flux:text>
                                @if (! empty($event->payload_json['sender']))
                                <flux:text class="text-xs text-gray-600">from {{ $event->payload_json['sender'] }}</flux:text>
                                @endif
                                @if (! empty($event->payload_json['front_url']))
                                <a href="{{ $event->payload_json['front_url'] }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                                    <flux:icon name="arrow-top-right-on-square" class="size-3" /> Open in Front
                                </a>
                                @endif
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                </flux:text>
                            </div>
                        </div>
                        @elseif ($event->event_type === 'reply_dismissed')
                        <div class="flex items-start gap-2">
                            <flux:icon name="clock" class="mt-0.5 size-4 shrink-0 text-gray-400" />
                            <div class="min-w-0">
                                <flux:text class="text-sm font-medium text-gray-700">{{ $event->description() }}</flux:text>
                                @if (! empty($event->payload_json['note']))
                                <flux:text class="text-sm text-gray-600">{{ $event->payload_json['note'] }}</flux:text>
                                @endif
                                @if (! empty($event->payload_json['reminders_restarted']))
                                <flux:text class="text-xs text-gray-500">Reminders restarted</flux:text>
                                @endif
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                    @if ($event->creator) &middot; {{ $event->creator->name }} @endif
                                </flux:text>
                            </div>
                        </div>
                        @elseif ($event->event_type === 'document_uploaded')
                        @php
                            $uploadedFile = ! empty($event->payload_json['media_id']) ? $requestFiles->get($event->payload_json['media_id']) : null;
                            $uploadedName = $event->payload_json['original_name'] ?? $event->payload_json['file_name'] ?? null;
                        @endphp
                        <div class="flex items-start gap-2">
                            <flux:icon name="arrow-up-tray" class="mt-0.5 size-4 shrink-0 text-green-500" />
                            <div class="min-w-0">
                                <flux:text class="text-sm font-medium text-green-700">{{ $event->description() }}</flux:text>
                                @if ($uploadedFile)
                                <a href="{{ route('admin.liens.customer-uploads.download', [$filing->public_id, $uploadedFile->id]) }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                                    <flux:icon name="paper-clip" class="size-3" /> {{ $uploadedFile->file_name }}
                                </a>
                                @elseif ($uploadedName)
                                <flux:text class="text-xs text-gray-500">{{ $uploadedName }} (file removed)</flux:text>
                                @endif
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->eastern()->format('M j, g:i A') }}
                                    @if ($event->creator) &middot; {{ $event->creator->name }} @endif
                                </flux:text>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <flux:text class="text-gray-500">No activity recorded.</flux:text>
                @endif
            </div>

            <!-- Lien Rights Summary Card -->
            @if ($filing->project)
            <div class="rounded-lg border border-border bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg">Lien Rights Summary</flux:heading>
                    <a href="{{ route('admin.liens.lien-rules-overview', ['state' => $filing->project->jobsite_state]) }}"
                       class="text-sm text-blue-600 hover:underline" wire:navigate>
                        View Rules Overview
                    </a>
                </div>

                {{-- Filing Document Type Status Banner --}}
                @php
                $docBg = match($filingDocStatus['color']) {
                    'green' => 'bg-green-50 border border-green-200',
                    'red' => 'bg-red-50 border border-red-200',
                    'yellow' => 'bg-amber-50 border border-amber-200',
                    default => 'bg-gray-50 border border-gray-200',
                };
                $docDot = match($filingDocStatus['color']) {
                    'green' => 'bg-green-500',
                    'red' => 'bg-red-500',
                    'yellow' => 'bg-amber-500',
                    default => 'bg-gray-400',
                };
                $docTextColor = match($filingDocStatus['color']) {
                    'green' => 'text-green-800',
                    'red' => 'text-red-800',
                    'yellow' => 'text-amber-800',
                    default => 'text-gray-700',
                };
                $docSubColor = match($filingDocStatus['color']) {
                    'green' => 'text-green-600',
                    'red' => 'text-red-600',
                    'yellow' => 'text-amber-600',
                    default => 'text-gray-500',
                };
                @endphp
                <div class="mb-4 rounded-lg p-3 {{ $docBg }}">
                    <div class="flex items-center gap-3">
                        <span class="inline-block size-3 shrink-0 rounded-full {{ $docDot }}"></span>
                        <div>
                            <flux:text class="font-semibold {{ $docTextColor }}">
                                {{ $filingDocStatus['docName'] }}: {{ $filingDocStatus['label'] }}
                            </flux:text>
                            @if ($filingDocStatus['dueDate'])
                            <flux:text class="text-xs {{ $docSubColor }}">
                                Due: {{ $filingDocStatus['dueDate'] }}
                            </flux:text>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Key Project Factors --}}
                <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div>
                        <flux:text class="text-xs text-gray-500">Project State</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->jobsite_state ?? 'Unknown' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-gray-500">Claimant Type</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->claimant_type?->label() ?? 'Unknown' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-gray-500">Property Class</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->property_class ? ucfirst($filing->project->property_class) : 'Not set' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-gray-500">NOC Status</flux:text>
                        <flux:text class="font-medium">
                            {{ $filing->project->noc_status?->label() ?? 'Unknown' }}
                            @if ($filing->project->noc_recorded_at)
                            — {{ $filing->project->noc_recorded_at->format('M j, Y') }}
                            @endif
                        </flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-gray-500">First Furnish</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->first_furnish_date?->format('M j, Y') ?? 'Not set' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-gray-500">Last Furnish</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->last_furnish_date?->format('M j, Y') ?? 'Not set' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-gray-500">Completion</flux:text>
                        <flux:text class="font-medium">{{ $filing->project->completion_date?->format('M j, Y') ?? 'Not set' }}</flux:text>
                    </div>
                    <div>
                        <div class="flex items-center gap-1">
                            <flux:text class="text-xs text-gray-500">Owner is Tenant</flux:text>
                            <flux:tooltip content="Was the work performed for someone renting the property rather than the property owner?">
                                <flux:icon name="question-mark-circle" class="size-3.5 text-gray-400" />
                            </flux:tooltip>
                        </div>
                        <flux:text class="font-medium">{{ $filing->project->owner_is_tenant ? 'Yes' : 'No' }}</flux:text>
                    </div>
                </div>
            </div>
            @endif

            <!-- Required Deadlines Card -->
            @if ($requiredDeadlines->isNotEmpty())
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Required Deadlines</flux:heading>
                <div class="space-y-2">
                    @foreach ($requiredDeadlines as $deadline)
                    <div wire:key="req-deadline-{{ $deadline->id }}" class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2">
                        <div class="min-w-0 flex-1">
                            <flux:text class="text-sm font-medium">{{ $deadline->documentType?->name ?? 'Unknown' }}</flux:text>
                            <flux:badge size="sm" color="{{ $deadline->getStatusColor() }}" class="mt-0.5">
                                {{ $deadline->getStatusLabel() }}
                            </flux:badge>
                        </div>
                        <div class="text-right shrink-0 ml-3">
                            @php
                            $daysLeft = $deadline->daysRemaining();
                            $showDaysLeft = $daysLeft !== null
                                && $deadline->status !== \App\Domains\Lien\Enums\DeadlineStatus::Completed
                                && $deadline->status !== \App\Domains\Lien\Enums\DeadlineStatus::NotApplicable;
                            $daysLeftClass = match(true) {
                                $daysLeft === null => 'text-gray-500',
                                $daysLeft < 0 => 'text-red-600 font-semibold',
                                $daysLeft <= 7 => 'text-amber-600 font-medium',
                                $daysLeft <= 30 => 'text-amber-500',
                                default => 'text-green-600',
                            };
                            $daysLeftLabel = match(true) {
                                $daysLeft === null => '',
                                $daysLeft < 0 => abs($daysLeft) . ' days overdue',
                                $daysLeft === 0 => 'Due today',
                                default => $daysLeft . ' days left',
                            };
                            @endphp
                            @if ($deadline->due_date)
                            <flux:text class="text-sm font-medium">{{ $deadline->due_date->format('M j, Y') }}</flux:text>
                            @if ($showDaysLeft)
                            <flux:text class="text-xs {{ $daysLeftClass }}">{{ $daysLeftLabel }}</flux:text>
                            @endif
                            @else
                            <flux:text class="text-xs text-gray-400">Unknown</flux:text>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
    @endif

    <!-- All Fields Collapsible -->
    <div x-data="{ open: false }" class="rounded-lg border border-border bg-white">
        <button @click="open = !open" class="flex w-full items-center justify-between p-4 text-left hover:bg-gray-50"
            type="button">
            <flux:heading size="lg">All Fields</flux:heading>
            <flux:icon name="chevron-down" class="size-5 text-gray-400 transition-transform duration-200"
                ::class="open && 'rotate-180'" />
        </button>

        <div x-show="open" x-collapse class="border-t border-border">
            <div class="overflow-x-auto p-4">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Field</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        {{-- Filing Model Attributes --}}
                        <tr class="bg-gray-50">
                            <td colspan="2" class="px-4 py-2 font-medium text-gray-700">Filing Attributes</td>
                        </tr>
                        @foreach ($filing->getAttributes() as $key => $value)
                        @if (!in_array($key, ['payload_json', 'parties_snapshot_json']))
                        <tr>
                            <td class="px-4 py-2 font-mono text-xs text-gray-600">{{ $key }}</td>
                            <td class="px-4 py-2 text-gray-900">
                                @if (is_null($value))
                                <span class="text-gray-400">null</span>
                                @elseif (is_bool($value))
                                <span class="font-medium">{{ $value ? 'true' : 'false' }}</span>
                                @elseif ($value instanceof \DateTimeInterface)
                                {{ $value->format('Y-m-d H:i:s') }}
                                @elseif (is_array($value))
                                <pre
                                    class="max-w-md overflow-auto whitespace-pre-wrap text-xs">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                @else
                                {{ Str::limit((string) $value, 200) }}
                                @endif
                            </td>
                        </tr>
                        @endif
                        @endforeach

                        {{-- Payload JSON --}}
                        @if (!empty($filing->payload_json))
                        <tr class="bg-gray-50">
                            <td colspan="2" class="px-4 py-2 font-medium text-gray-700">Payload JSON</td>
                        </tr>
                        @foreach ($filing->payload_json as $key => $value)
                        <tr>
                            <td class="px-4 py-2 font-mono text-xs text-gray-600">{{ $key }}</td>
                            <td class="px-4 py-2 text-gray-900">
                                @if (is_null($value))
                                <span class="text-gray-400">null</span>
                                @elseif (is_bool($value))
                                <span class="font-medium">{{ $value ? 'true' : 'false' }}</span>
                                @elseif (is_array($value))
                                <pre
                                    class="max-w-md overflow-auto whitespace-pre-wrap text-xs">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                @else
                                {{ Str::limit((string) $value, 200) }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @endif

                        {{-- Parties Snapshot JSON --}}
                        @if (!empty($filing->parties_snapshot_json))
                        <tr class="bg-gray-50">
                            <td colspan="2" class="px-4 py-2 font-medium text-gray-700">Parties Snapshot</td>
                        </tr>
                        @foreach ($filing->parties_snapshot_json as $index => $party)
                        <tr>
                            <td class="px-4 py-2 font-mono text-xs text-gray-600">party[{{ $index }}]</td>
                            <td class="px-4 py-2 text-gray-900">
                                <pre
                                    class="max-w-md overflow-auto whitespace-pre-wrap text-xs">{{ json_encode($party, JSON_PRETTY_PRINT) }}</pre>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================ Edit modals ============================ --}}
    @if ($canUpdate)

    {{-- Edit Project Details --}}
    <flux:modal wire:model="showProjectModal" class="max-w-2xl">
        <form wire:submit="updateProjectDetails" class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Project Details</flux:heading>
                <flux:subheading>Corrections re-sync the fulfillment snapshot and recalculate deadlines.</flux:subheading>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Project Name</flux:label>
                    <flux:input wire:model="projectForm.name" />
                    <flux:error name="projectForm.name" />
                </flux:field>
                <flux:field>
                    <flux:label>Job Number</flux:label>
                    <flux:input wire:model="projectForm.job_number" />
                    <flux:error name="projectForm.job_number" />
                </flux:field>

                <flux:field>
                    <flux:label>What you provided</flux:label>
                    <flux:select wire:model="projectForm.provided_type">
                        <flux:select.option value="labor">Labor</flux:select.option>
                        <flux:select.option value="materials_only">Materials only</flux:select.option>
                        <flux:select.option value="both">Both</flux:select.option>
                    </flux:select>
                    <flux:error name="projectForm.provided_type" />
                </flux:field>
                <flux:field>
                    <flux:label>Who hired you</flux:label>
                    <flux:select wire:model="projectForm.hired_by">
                        <flux:select.option value="owner">Owner</flux:select.option>
                        <flux:select.option value="direct_contractor">Direct contractor</flux:select.option>
                        <flux:select.option value="subcontractor">Subcontractor</flux:select.option>
                    </flux:select>
                    <flux:error name="projectForm.hired_by" />
                </flux:field>

                <flux:field>
                    <flux:label>Property Class</flux:label>
                    <flux:select wire:model="projectForm.property_class">
                        <flux:select.option value="">Not set</flux:select.option>
                        <flux:select.option value="residential">Residential</flux:select.option>
                        <flux:select.option value="commercial">Commercial</flux:select.option>
                        <flux:select.option value="government">Government</flux:select.option>
                    </flux:select>
                    <flux:error name="projectForm.property_class" />
                </flux:field>
                <flux:field>
                    <flux:label>Property Context</flux:label>
                    <flux:select wire:model="projectForm.property_context">
                        <flux:select.option value="">Not set</flux:select.option>
                        <flux:select.option value="unknown">Unknown</flux:select.option>
                        <flux:select.option value="tenant_improvement">Tenant improvement</flux:select.option>
                        <flux:select.option value="owner_occupied">Owner occupied</flux:select.option>
                    </flux:select>
                    <flux:error name="projectForm.property_context" />
                </flux:field>
            </div>

            <flux:separator text="Jobsite" />
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field class="sm:col-span-2">
                    <flux:label>Address</flux:label>
                    <flux:input wire:model="projectForm.jobsite_address1" />
                    <flux:error name="projectForm.jobsite_address1" />
                </flux:field>
                <flux:field class="sm:col-span-2">
                    <flux:label>Address 2</flux:label>
                    <flux:input wire:model="projectForm.jobsite_address2" />
                    <flux:error name="projectForm.jobsite_address2" />
                </flux:field>
                <flux:field>
                    <flux:label>City</flux:label>
                    <flux:input wire:model="projectForm.jobsite_city" />
                    <flux:error name="projectForm.jobsite_city" />
                </flux:field>
                <flux:field>
                    <flux:label>State</flux:label>
                    <flux:input wire:model="projectForm.jobsite_state" maxlength="2" />
                    <flux:error name="projectForm.jobsite_state" />
                </flux:field>
                <flux:field>
                    <flux:label>ZIP</flux:label>
                    <flux:input wire:model="projectForm.jobsite_zip" />
                    <flux:error name="projectForm.jobsite_zip" />
                </flux:field>
                <flux:field>
                    <flux:label>County</flux:label>
                    <flux:input wire:model="projectForm.jobsite_county" />
                    <flux:error name="projectForm.jobsite_county" />
                </flux:field>
                <flux:field class="sm:col-span-2">
                    <flux:label>Legal Description</flux:label>
                    <flux:textarea wire:model="projectForm.legal_description" rows="2" />
                    <flux:error name="projectForm.legal_description" />
                </flux:field>
                <flux:field>
                    <flux:label>APN</flux:label>
                    <flux:input wire:model="projectForm.apn" />
                    <flux:error name="projectForm.apn" />
                </flux:field>
            </div>

            <flux:separator text="Dates & NOC" />
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>First Furnish Date</flux:label>
                    <flux:date-picker wire:model="projectForm.first_furnish_date" />
                    <flux:error name="projectForm.first_furnish_date" />
                </flux:field>
                <flux:field>
                    <flux:label>Last Furnish Date</flux:label>
                    <flux:date-picker wire:model="projectForm.last_furnish_date" />
                    <flux:error name="projectForm.last_furnish_date" />
                </flux:field>
                <flux:field>
                    <flux:label>Completion Date</flux:label>
                    <flux:date-picker wire:model="projectForm.completion_date" />
                    <flux:error name="projectForm.completion_date" />
                </flux:field>
                <flux:field>
                    <flux:label>NOC Status</flux:label>
                    <flux:select wire:model="projectForm.noc_status">
                        <flux:select.option value="">Not set</flux:select.option>
                        <flux:select.option value="unknown">Not sure</flux:select.option>
                        <flux:select.option value="no">No</flux:select.option>
                        <flux:select.option value="yes">Yes</flux:select.option>
                    </flux:select>
                    <flux:error name="projectForm.noc_status" />
                </flux:field>
                <flux:field>
                    <flux:label>NOC Recorded</flux:label>
                    <flux:date-picker wire:model="projectForm.noc_recorded_at" />
                    <flux:error name="projectForm.noc_recorded_at" />
                </flux:field>
            </div>

            <flux:separator text="Amounts (USD)" />
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Base Contract</flux:label>
                    <flux:input type="number" step="0.01" wire:model="projectForm.base_contract_amount" />
                    <flux:error name="projectForm.base_contract_amount" />
                </flux:field>
                <flux:field>
                    <flux:label>Change Orders</flux:label>
                    <flux:input type="number" step="0.01" wire:model="projectForm.change_orders" />
                    <flux:error name="projectForm.change_orders" />
                </flux:field>
                <flux:field>
                    <flux:label>Credits / Deductions</flux:label>
                    <flux:input type="number" step="0.01" wire:model="projectForm.credits_deductions" />
                    <flux:error name="projectForm.credits_deductions" />
                </flux:field>
                <flux:field>
                    <flux:label>Payments Received</flux:label>
                    <flux:input type="number" step="0.01" wire:model="projectForm.payments_received" />
                    <flux:error name="projectForm.payments_received" />
                </flux:field>
                <flux:field>
                    <flux:label>Uncompleted Work</flux:label>
                    <flux:input type="number" step="0.01" wire:model="projectForm.uncompleted_work" />
                    <flux:error name="projectForm.uncompleted_work" />
                </flux:field>
            </div>

            <div class="flex items-center gap-6">
                <flux:switch wire:model="projectForm.owner_is_tenant" label="Owner is tenant" />
                <flux:switch wire:model="projectForm.has_written_contract" label="Written contract" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled" type="button">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Save Project Details</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Filing Details --}}
    <flux:modal wire:model="showFilingModal" class="max-w-lg">
        <form wire:submit="updateFilingDetails" class="space-y-5">
            <div>
                <flux:heading size="lg">Edit Filing Details</flux:heading>
                <flux:subheading>Corrections re-sync the fulfillment snapshot.</flux:subheading>
            </div>

            <flux:field>
                <flux:label>Claim Amount (USD)</flux:label>
                <flux:input type="number" step="0.01" wire:model="filingForm.amount_claimed" />
                <flux:error name="filingForm.amount_claimed" />
            </flux:field>
            <flux:field>
                <flux:label>Description of Work</flux:label>
                <flux:textarea wire:model="filingForm.description_of_work" rows="3" />
                <flux:error name="filingForm.description_of_work" />
            </flux:field>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Jurisdiction State</flux:label>
                    <flux:input wire:model="filingForm.jurisdiction_state" maxlength="2" />
                    <flux:error name="filingForm.jurisdiction_state" />
                </flux:field>
                <flux:field>
                    <flux:label>Jurisdiction County</flux:label>
                    <flux:input wire:model="filingForm.jurisdiction_county" />
                    <flux:error name="filingForm.jurisdiction_county" />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>Service Level</flux:label>
                <flux:select wire:model="filingForm.service_level">
                    @foreach (\App\Domains\Lien\Enums\ServiceLevel::cases() as $level)
                    <flux:select.option value="{{ $level->value }}">{{ $level->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="filingForm.service_level" />
            </flux:field>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled" type="button">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Save Filing Details</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Document Details --}}
    @if ($documentPackage !== null)
    <flux:modal wire:model="showDocumentModal" class="max-w-lg">
        <form wire:submit="updateDocumentDetails" class="space-y-5">
            <div>
                <flux:heading size="lg">Edit Document Details</flux:heading>
                <flux:subheading>Facts the generated documents print. Blank fields fall back to the business and project.</flux:subheading>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Signer name</flux:label>
                    <flux:input wire:model="documentForm.signer_name" />
                    <flux:error name="documentForm.signer_name" />
                </flux:field>
                <flux:field>
                    <flux:label>Signer title</flux:label>
                    <flux:input wire:model="documentForm.signer_title" placeholder="President, Owner, Manager" />
                    <flux:error name="documentForm.signer_title" />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>Contractor license number</flux:label>
                <flux:input wire:model="documentForm.license_number" />
                <flux:error name="documentForm.license_number" />
            </flux:field>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Contract date</flux:label>
                    <flux:input type="date" wire:model="documentForm.contract_date" />
                    <flux:error name="documentForm.contract_date" />
                </flux:field>
                <flux:field>
                    <flux:label>Contract type</flux:label>
                    <flux:select wire:model="documentForm.contract_type">
                        <flux:select.option value="">Use the project's answer</flux:select.option>
                        <flux:select.option value="written">Written</flux:select.option>
                        <flux:select.option value="oral">Oral</flux:select.option>
                    </flux:select>
                    <flux:error name="documentForm.contract_type" />
                </flux:field>
            </div>

            @if ($filing->documentKind() === 'prelim_notice')
            <flux:field>
                <flux:label>Estimated total price (USD)</flux:label>
                <flux:input type="number" step="0.01" wire:model="documentForm.estimated_price" />
                <flux:error name="documentForm.estimated_price" />
            </flux:field>
            @endif

            @if ($filing->documentKind() === 'mechanics_lien')
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Prior notice served on</flux:label>
                    <flux:input type="date" wire:model="documentForm.notice_served_at" />
                    <flux:description>Blank uses the project's preliminary notice date.</flux:description>
                    <flux:error name="documentForm.notice_served_at" />
                </flux:field>
                <flux:field>
                    <flux:label>Served by</flux:label>
                    <flux:select wire:model="documentForm.notice_served_method">
                        <flux:select.option value="">Not set</flux:select.option>
                        @foreach (\App\Domains\Lien\Admin\Actions\UpdateLienDocumentDetails::NOTICE_METHODS as $method)
                        <flux:select.option value="{{ $method }}">{{ ucfirst(\App\Domains\Lien\Documents\LienDocumentPayload::deliveryLabel($method)) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="documentForm.notice_served_method" />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>Months of work (Texas)</flux:label>
                <flux:input wire:model="documentForm.months_of_work" placeholder="May 2026 - June 2026" />
                <flux:error name="documentForm.months_of_work" />
            </flux:field>
            <div class="grid gap-4 sm:grid-cols-3">
                <flux:field>
                    <flux:label>Owner interest (NY)</flux:label>
                    <flux:input wire:model="documentForm.owner_interest" placeholder="fee simple" />
                    <flux:error name="documentForm.owner_interest" />
                </flux:field>
                <flux:field>
                    <flux:label>Block</flux:label>
                    <flux:input wire:model="documentForm.block" />
                    <flux:error name="documentForm.block" />
                </flux:field>
                <flux:field>
                    <flux:label>Lot</flux:label>
                    <flux:input wire:model="documentForm.lot" />
                    <flux:error name="documentForm.lot" />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>Attachments note</flux:label>
                <flux:input wire:model="documentForm.attachments_note" placeholder="Copy of contract; statement of account" />
                <flux:error name="documentForm.attachments_note" />
            </flux:field>
            @endif

            @if ($filing->documentKind() === 'lien_release')
            <flux:separator text="Lien being released" />
            <flux:field>
                <flux:label>Recording reference (instrument or file number)</flux:label>
                <flux:input wire:model="documentForm.original_lien.recording_reference" />
                <flux:error name="documentForm.original_lien.recording_reference" />
            </flux:field>
            <div class="grid gap-4 sm:grid-cols-3">
                <flux:field>
                    <flux:label>Book</flux:label>
                    <flux:input wire:model="documentForm.original_lien.book" />
                    <flux:error name="documentForm.original_lien.book" />
                </flux:field>
                <flux:field>
                    <flux:label>Page</flux:label>
                    <flux:input wire:model="documentForm.original_lien.page" />
                    <flux:error name="documentForm.original_lien.page" />
                </flux:field>
                <flux:field>
                    <flux:label>Recorded on</flux:label>
                    <flux:input type="date" wire:model="documentForm.original_lien.recorded_at" />
                    <flux:error name="documentForm.original_lien.recorded_at" />
                </flux:field>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>County of record</flux:label>
                    <flux:input wire:model="documentForm.original_lien.county" />
                    <flux:error name="documentForm.original_lien.county" />
                </flux:field>
                <flux:field>
                    <flux:label>Amount received (USD)</flux:label>
                    <flux:input type="number" step="0.01" wire:model="documentForm.original_lien.amount_received" />
                    <flux:error name="documentForm.original_lien.amount_received" />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>Other identifying information</flux:label>
                <flux:textarea wire:model="documentForm.original_lien.other_info" rows="2" />
                <flux:description>For our reference only; not printed on the release.</flux:description>
                <flux:error name="documentForm.original_lien.other_info" />
            </flux:field>
            @endif

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled" type="button">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Save Document Details</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Recipient (service facts) --}}
    <flux:modal wire:model="showRecipientModal" class="max-w-lg">
        <form wire:submit="updateRecipient" class="space-y-5">
            <div>
                <flux:heading size="lg">Edit Recipient</flux:heading>
                <flux:subheading>How and when the copy went out. Times are Eastern.</flux:subheading>
            </div>

            <flux:field>
                <flux:label>Delivery method</flux:label>
                <flux:select wire:model="recipientForm.delivery_method">
                    <flux:select.option value="">Not set</flux:select.option>
                    @foreach (\App\Domains\Lien\Admin\Actions\UpdateLienFilingRecipient::DELIVERY_METHODS as $method)
                    <flux:select.option value="{{ $method }}">{{ ucfirst(\App\Domains\Lien\Documents\LienDocumentPayload::deliveryLabel($method)) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="recipientForm.delivery_method" />
            </flux:field>
            <flux:field>
                <flux:label>Tracking or article number</flux:label>
                <flux:input wire:model="recipientForm.tracking_number" placeholder="7018 1830 0000 4535 9376" />
                <flux:error name="recipientForm.tracking_number" />
            </flux:field>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Sent at</flux:label>
                    <flux:input type="datetime-local" wire:model="recipientForm.sent_at" />
                    <flux:error name="recipientForm.sent_at" />
                </flux:field>
                <flux:field>
                    <flux:label>Delivered at</flux:label>
                    <flux:input type="datetime-local" wire:model="recipientForm.delivered_at" />
                    <flux:error name="recipientForm.delivered_at" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled" type="button">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Save Recipient</flux:button>
            </div>
        </form>
    </flux:modal>
    @endif

    {{-- Add / Edit Party --}}
    <flux:modal wire:model="showPartyModal" class="max-w-lg">
        <form wire:submit="saveParty" class="space-y-5">
            <flux:heading size="lg">{{ $editingPartyId ? 'Edit Party' : 'Add Party' }}</flux:heading>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field class="sm:col-span-2">
                    <flux:label>Role</flux:label>
                    <flux:select wire:model="partyForm.role">
                        @foreach (\App\Domains\Lien\Enums\PartyRole::cases() as $role)
                        <flux:select.option value="{{ $role->value }}">{{ $role->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="partyForm.role" />
                </flux:field>
                <flux:field>
                    <flux:label>Name</flux:label>
                    <flux:input wire:model="partyForm.name" />
                    <flux:error name="partyForm.name" />
                </flux:field>
                <flux:field>
                    <flux:label>Company Name</flux:label>
                    <flux:input wire:model="partyForm.company_name" />
                    <flux:error name="partyForm.company_name" />
                </flux:field>
                <flux:field class="sm:col-span-2">
                    <flux:label>Address</flux:label>
                    <flux:input wire:model="partyForm.address1" />
                    <flux:error name="partyForm.address1" />
                </flux:field>
                <flux:field class="sm:col-span-2">
                    <flux:label>Address 2</flux:label>
                    <flux:input wire:model="partyForm.address2" />
                    <flux:error name="partyForm.address2" />
                </flux:field>
                <flux:field>
                    <flux:label>City</flux:label>
                    <flux:input wire:model="partyForm.city" />
                    <flux:error name="partyForm.city" />
                </flux:field>
                <flux:field>
                    <flux:label>State</flux:label>
                    <flux:input wire:model="partyForm.state" maxlength="2" />
                    <flux:error name="partyForm.state" />
                </flux:field>
                <flux:field>
                    <flux:label>ZIP</flux:label>
                    <flux:input wire:model="partyForm.zip" />
                    <flux:error name="partyForm.zip" />
                </flux:field>
                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input type="email" wire:model="partyForm.email" />
                    <flux:error name="partyForm.email" />
                </flux:field>
                <flux:field>
                    <flux:label>Phone</flux:label>
                    <flux:input wire:model="partyForm.phone" />
                    <flux:error name="partyForm.phone" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled" type="button">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingPartyId ? 'Save Party' : 'Add Party' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Remove Party confirmation --}}
    <flux:modal wire:model="showRemovePartyModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Remove this party?</flux:heading>
                <flux:subheading class="mt-2">
                    The party will be removed from the application and the fulfillment snapshot re-synced.
                    Any unsent mailing for this party will be withdrawn.
                </flux:subheading>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="removeParty" variant="danger" icon="trash">Remove Party</flux:button>
            </div>
        </div>
    </flux:modal>

    @endif
</div>