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
        <flux:badge color="{{ $kanbanColumn->color() }}" size="lg">
            {{ $kanbanColumn->label() }}
        </flux:badge>
    </div>

    @if (session('success'))
    <flux:callout variant="success" icon="check-circle">
        {{ session('success') }}
    </flux:callout>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Filing Summary Card -->
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Filing Summary</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-sm text-gray-500">Document Type</flux:text>
                        <flux:text class="font-medium">{{ $filing->documentType?->name ?? 'Unknown' }}</flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Service Level</flux:text>
                        <flux:text class="font-medium">{{ $filing->service_level->label() }}</flux:text>
                    </div>

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
                        <flux:text class="text-sm text-gray-500">Jurisdiction</flux:text>
                        <flux:text class="font-medium">
                            {{ $filing->jurisdiction_county ?? 'Unknown' }}, {{ $filing->jurisdiction_state ?? 'Unknown'
                            }}
                        </flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Created</flux:text>
                        <flux:text class="font-medium">{{ $filing->created_at->format('M j, Y g:i A') }}</flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Paid</flux:text>
                        <flux:text class="font-medium">
                            @if ($filing->paid_at)
                            {{ $filing->paid_at->format('M j, Y g:i A') }}
                            @else
                            Not paid
                            @endif
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
                            {{ $filing->status->label() }}
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
                <flux:heading size="lg" class="mb-4">Project Information</flux:heading>

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
                        <flux:text class="text-sm text-gray-500">Owner is Tenant</flux:text>
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
                <flux:heading size="lg" class="mb-4">Filing Application</flux:heading>

                @if ($filing->description_of_work)
                <div class="mb-4">
                    <flux:text class="text-sm text-gray-500">Description of Work</flux:text>
                    <flux:text class="font-medium">{{ $filing->description_of_work }}</flux:text>
                </div>
                @endif

                @if (!empty($filing->parties_snapshot_json))
                <div class="mt-4">
                    <flux:text class="mb-3 text-sm font-medium text-gray-700">Parties</flux:text>
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
                        <flux:select wire:model="newStatus">
                            <flux:select.option value="">Select new status...</flux:select.option>
                            @foreach ($allowedTransitions as $status)
                            <flux:select.option value="{{ $status->value }}">
                                {{ $status->label() }}
                            </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="newStatus" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Note (optional)</flux:label>
                        <flux:textarea wire:model="note" rows="3"
                            placeholder="Add a note about this status change..." />
                        <flux:error name="note" />
                    </flux:field>

                    <flux:button type="submit" variant="primary" class="w-full">
                        Update Status
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
                        @endphp
                        <div class="flex items-center gap-2 flex-wrap">
                            @if ($toStatus)
                            <flux:badge size="sm" color="{{ $toStatus->color() }}">
                                {{ $toStatus->label() }}
                            </flux:badge>
                            @endif
                            <flux:text class="text-xs text-gray-400">
                                {{ $event->created_at->format('M j, g:i A') }}
                                @if ($event->creator)
                                &middot; {{ $event->creator->name }}
                                @endif
                            </flux:text>
                        </div>
                        @if ($note)
                        <flux:text class="mt-1 text-sm text-gray-600">{{ $note }}</flux:text>
                        @endif
                        @elseif ($event->event_type === 'note_added')
                        <div class="flex items-start gap-2">
                            <flux:icon name="chat-bubble-left" class="mt-0.5 size-4 shrink-0 text-blue-400" />
                            <div class="min-w-0">
                                <flux:text class="text-sm text-gray-600">{{ $event->payload_json['comment'] ?? '' }}</flux:text>
                                <flux:text class="text-xs text-gray-400">
                                    {{ $event->created_at->format('M j, g:i A') }}
                                    @if ($event->creator)
                                    &middot; {{ $event->creator->name }}
                                    @endif
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
                <flux:heading size="lg" class="mb-4">Lien Rights Summary</flux:heading>

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
                        <flux:text class="text-xs text-gray-500">Owner is Tenant</flux:text>
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
</div>