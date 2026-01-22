<div class="space-y-6">
    <x-ui.page-header :title="$project->name">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Lien Projects', 'url' => route('lien.projects.index')],
                ['label' => $project->name],
            ]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <flux:button href="{{ route('lien.projects.edit', $project) }}" variant="ghost" icon="pencil">
                Edit
            </flux:button>
        </x-slot:actions>
    </x-ui.page-header>

    @if(session('message'))
        <flux:callout color="green" icon="check-circle">
            {{ session('message') }}
        </flux:callout>
    @endif

    {{-- Next Deadline Alert --}}
    @if($nextDeadline)
        @php
            $daysRemaining = $nextDeadline->daysRemaining();
            $isOverdue = $daysRemaining !== null && $daysRemaining < 0;
            $isDueSoon = $daysRemaining !== null && $daysRemaining >= 0 && $daysRemaining <= 7;
            $canFileNext = $nextDeadline->canFile();
        @endphp
        <flux:callout
            :color="$isOverdue ? 'red' : ($isDueSoon ? 'amber' : 'blue')"
            :icon="$isOverdue ? 'exclamation-triangle' : 'clock'"
        >
            <div class="flex items-center justify-between w-full">
                <div>
                    <strong>{{ $nextDeadline->documentType->name }}</strong>
                    @if($isOverdue)
                        is overdue by {{ abs($daysRemaining) }} days!
                    @elseif($daysRemaining === 0)
                        is due today!
                    @elseif($daysRemaining === 1)
                        is due tomorrow!
                    @else
                        is due in {{ $daysRemaining }} days ({{ $nextDeadline->due_date->format('M j, Y') }})
                    @endif
                    @if(!$canFileNext && $nextDeadline->getFilingBlockerReason())
                        <span class="text-sm opacity-75">({{ $nextDeadline->getFilingBlockerReason() }})</span>
                    @endif
                </div>
                <flux:button
                    wire:click="startFiling({{ $nextDeadline->id }})"
                    :variant="($isOverdue || $isDueSoon) && $canFileNext ? 'primary' : 'ghost'"
                    :disabled="!$canFileNext"
                    size="sm"
                >
                    {{ $canFileNext ? 'Start Filing' : 'File' }}
                </flux:button>
            </div>
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Project Details --}}
            <x-ui.card>
                <x-slot:header>Project Details</x-slot:header>

                <x-ui.info-list>
                    <x-ui.info-list.item label="Claimant Type">
                        {{ $project->claimant_type->label() }}
                    </x-ui.info-list.item>
                    @if($project->job_number)
                        <x-ui.info-list.item label="Job Number">
                            {{ $project->job_number }}
                        </x-ui.info-list.item>
                    @endif
                    <x-ui.info-list.item label="Jobsite Address">
                        {{ $project->jobsiteAddressLine() }}
                    </x-ui.info-list.item>
                    @if($project->jobsite_county)
                        <x-ui.info-list.item label="County">
                            {{ $project->jobsite_county }}
                        </x-ui.info-list.item>
                    @endif
                    @if($project->legal_description)
                        <x-ui.info-list.item label="Legal Description">
                            {{ $project->legal_description }}
                        </x-ui.info-list.item>
                    @endif
                    @if($project->apn)
                        <x-ui.info-list.item label="APN">
                            {{ $project->apn }}
                        </x-ui.info-list.item>
                    @endif
                </x-ui.info-list>
            </x-ui.card>

            {{-- Important Dates --}}
            <x-ui.card>
                <x-slot:header>Important Dates</x-slot:header>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @php
                        $dates = [
                            'Contract' => $project->contract_date,
                            'First Furnish' => $project->first_furnish_date,
                            'Last Furnish' => $project->last_furnish_date,
                            'Completion' => $project->completion_date,
                            'NOC Recorded' => $project->noc_recorded_date,
                        ];
                    @endphp
                    @foreach($dates as $label => $date)
                        <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                            <div class="text-xs text-zinc-500 uppercase tracking-wide">{{ $label }}</div>
                            <div class="mt-1 font-medium {{ $date ? '' : 'text-zinc-400' }}">
                                {{ $date ? $date->format('M j, Y') : 'Not set' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            {{-- Timeline / Deadlines --}}
            <x-ui.card>
                <x-slot:header>Filing Timeline</x-slot:header>

                @if($deadlines->isEmpty())
                    @if(!$project->jobsite_state)
                        <p class="text-zinc-500">No deadlines calculated. Add a jobsite state to see your timeline.</p>
                    @else
                        <flux:callout color="amber" icon="exclamation-triangle">
                            No deadline rules found for {{ $project->jobsite_state }}. Please contact support or run the deadline rule seeder.
                        </flux:callout>
                    @endif
                @else
                    @php
                        $hasPlaceholder = $deadlines->contains(fn($d) => $d->isPlaceholder());
                    @endphp

                    @if($hasPlaceholder)
                        <flux:callout color="amber" icon="exclamation-triangle" class="mb-4">
                            Timeline dates for {{ $project->jobsite_state }} are estimates.
                            Consult local requirements before filing.
                        </flux:callout>
                    @endif

                    <div class="space-y-4">
                        @foreach($deadlines as $deadline)
                            <div class="flex items-center gap-4 p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg {{ $deadline->status->value === 'completed' ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ $deadline->documentType->name }}</span>
                                        @if($deadline->rule?->is_required)
                                            <flux:badge size="sm" color="red">Required</flux:badge>
                                        @endif
                                    </div>
                                    <div class="text-sm text-zinc-500 mt-1">
                                        @if($deadline->hasMissingFields())
                                            <span class="text-amber-600">
                                                Needs: {{ implode(', ', array_map(fn($f) => str_replace('_', ' ', $f), $deadline->missing_fields_json)) }}
                                            </span>
                                        @elseif($deadline->due_date)
                                            Due: {{ $deadline->due_date->format('M j, Y') }}
                                        @else
                                            No deadline calculated
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    @php
                                        $canFile = $deadline->canFile();
                                        $blockerReason = $deadline->getFilingBlockerReason();
                                    @endphp

                                    {{-- Deadline Status Badge (timing-related) --}}
                                    @if($deadline->status->value === 'completed')
                                        {{-- No timing badge needed when completed --}}
                                    @elseif($deadline->status->value === 'not_applicable')
                                        {{-- No timing badge needed when N/A --}}
                                    @elseif($deadline->isOverdue())
                                        <flux:badge color="red">Overdue</flux:badge>
                                    @elseif($deadline->isDueSoon())
                                        <flux:badge color="amber">Due Soon</flux:badge>
                                    @elseif($deadline->due_date)
                                        <flux:badge color="zinc">{{ $deadline->daysRemaining() }} days</flux:badge>
                                    @endif

                                    {{-- Filing Status Badge --}}
                                    <flux:badge size="sm" :color="$deadline->getFilingStatusColor()">
                                        {{ $deadline->getFilingStatusLabel() }}
                                    </flux:badge>

                                    {{-- Action Button - always visible --}}
                                    @if($deadline->completedFiling)
                                        <flux:button
                                            href="{{ route('lien.filings.show', $deadline->completedFiling) }}"
                                            size="sm"
                                            variant="ghost"
                                        >
                                            View Filing
                                        </flux:button>
                                    @else
                                        <flux:button
                                            wire:click="startFiling({{ $deadline->id }})"
                                            size="sm"
                                            :variant="$canFile ? 'primary' : 'ghost'"
                                            :disabled="!$canFile"
                                        >
                                            {{ $canFile ? 'Start Filing' : 'File' }}
                                        </flux:button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Parties --}}
            <x-ui.card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <span>Parties</span>
                        <flux:button size="sm" variant="ghost" icon="plus">Add</flux:button>
                    </div>
                </x-slot:header>

                @if($parties->isEmpty())
                    <p class="text-sm text-zinc-500">No parties added yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach($parties as $party)
                            <div class="text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ $party->displayName() }}</span>
                                    <flux:badge size="sm">{{ $party->role->label() }}</flux:badge>
                                </div>
                                @if($party->addressLine())
                                    <div class="text-zinc-500 text-xs mt-0.5">{{ $party->addressLine() }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            {{-- Recent Filings --}}
            <x-ui.card>
                <x-slot:header>Recent Filings</x-slot:header>

                @if($filings->isEmpty())
                    <p class="text-sm text-zinc-500">No filings yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach($filings as $filing)
                            <a href="{{ route('lien.filings.show', $filing) }}"
                               class="block p-2 -mx-2 rounded hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">{{ $filing->documentType->name }}</span>
                                    <flux:badge size="sm" :color="$filing->status->color()">
                                        {{ $filing->status->label() }}
                                    </flux:badge>
                                </div>
                                <div class="text-xs text-zinc-500 mt-0.5">
                                    {{ $filing->created_at->format('M j, Y') }}
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            {{-- Danger Zone --}}
            <x-ui.card>
                <x-slot:header>Danger Zone</x-slot:header>
                <flux:button
                    wire:click="deleteProject"
                    wire:confirm="Are you sure you want to delete this project? This action cannot be undone."
                    variant="danger"
                    size="sm"
                    class="w-full"
                >
                    Delete Project
                </flux:button>
            </x-ui.card>
        </div>
    </div>
</div>
