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

            <!-- Status History -->
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Status History</flux:heading>

                @if ($statusHistory->isNotEmpty())
                <div class="space-y-4">
                    @foreach ($statusHistory as $event)
                    @php
                    $fromStatus = \App\Domains\Lien\Enums\FilingStatus::tryFrom($event->payload_json['from'] ?? '');
                    $toStatus = \App\Domains\Lien\Enums\FilingStatus::tryFrom($event->payload_json['to'] ?? '');
                    $note = $event->payload_json['meta']['note'] ?? null;
                    @endphp
                    <div class="flex gap-3 border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                        <div class="mt-0.5">
                            <flux:icon name="{{ $toStatus?->icon() ?? 'arrow-right' }}" class="size-5 text-gray-400" />
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                @if ($fromStatus)
                                <flux:badge size="sm" color="{{ $fromStatus->color() }}">
                                    {{ $fromStatus->label() }}
                                </flux:badge>
                                @endif
                                <flux:icon name="arrow-right" class="size-4 text-gray-400" />
                                @if ($toStatus)
                                <flux:badge size="sm" color="{{ $toStatus->color() }}">
                                    {{ $toStatus->label() }}
                                </flux:badge>
                                @endif
                            </div>
                            @if ($note)
                            <flux:text class="mt-1 text-sm text-gray-600">{{ $note }}</flux:text>
                            @endif
                            <flux:text class="mt-1 text-xs text-gray-500">
                                {{ $event->created_at->format('M j, Y g:i A') }}
                                @if ($event->creator)
                                by {{ $event->creator->name }}
                                @endif
                            </flux:text>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <flux:text class="text-gray-500">No status changes recorded.</flux:text>
                @endif
            </div>

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

            <!-- Quick Info Card -->
            <div class="rounded-lg border border-border bg-white p-6">
                <flux:heading size="lg" class="mb-4">Quick Info</flux:heading>

                <div class="space-y-3">
                    <div>
                        <flux:text class="text-sm text-gray-500">Filing ID</flux:text>
                        <flux:text class="font-mono text-sm">{{ $filing->public_id }}</flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Business</flux:text>
                        <flux:text class="font-medium">{{ $filing->project?->business?->name ?? 'Unknown' }}</flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm text-gray-500">Project</flux:text>
                        <flux:text class="font-medium">{{ $filing->project?->name ?? 'Unknown' }}</flux:text>
                    </div>

                    @if ($filing->mailed_at)
                    <div>
                        <flux:text class="text-sm text-gray-500">Mailed</flux:text>
                        <flux:text class="font-medium">{{ $filing->mailed_at->format('M j, Y') }}</flux:text>
                    </div>
                    @endif

                    @if ($filing->mailing_tracking_number)
                    <div>
                        <flux:text class="text-sm text-gray-500">Tracking #</flux:text>
                        <flux:text class="font-mono text-sm">{{ $filing->mailing_tracking_number }}</flux:text>
                    </div>
                    @endif

                    @if ($filing->recorded_at)
                    <div>
                        <flux:text class="text-sm text-gray-500">Recorded</flux:text>
                        <flux:text class="font-medium">{{ $filing->recorded_at->format('M j, Y') }}</flux:text>
                    </div>
                    @endif

                    @if ($filing->completed_at)
                    <div>
                        <flux:text class="text-sm text-gray-500">Completed</flux:text>
                        <flux:text class="font-medium">{{ $filing->completed_at->format('M j, Y') }}</flux:text>
                    </div>
                    @endif
                </div>
            </div>
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