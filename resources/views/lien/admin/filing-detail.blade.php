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
                            {{ $filing->jurisdiction_county ?? 'Unknown' }}, {{ $filing->jurisdiction_state ?? 'Unknown' }}
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
                        <flux:switch
                            wire:click="toggleNeedsReview"
                            :checked="$filing->needs_review"
                        />
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
                            <flux:textarea
                                wire:model="note"
                                rows="3"
                                placeholder="Add a note about this status change..."
                            />
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
</div>
