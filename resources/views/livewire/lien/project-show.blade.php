<div class="space-y-6">
    <x-ui.page-header :title="$project->name">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Lien Projects', 'url' => route('lien.projects.index')],
                ['label' => $project->name],
            ]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            {{-- Alerts Status Badge --}}
            <flux:tooltip :content="$project->getAlertsStatusTooltip()">
                <flux:badge :color="$project->getAlertsStatusColor()">
                    {{ $project->getAlertsStatusLabel() }}
                </flux:badge>
            </flux:tooltip>
            <flux:dropdown>
                <flux:button variant="ghost" icon="ellipsis-horizontal">Actions</flux:button>
                <flux:menu>
                    <flux:menu.item href="{{ route('lien.projects.edit', $project) }}" icon="pencil">
                        Edit
                    </flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item wire:click="deleteProject"
                        wire:confirm="Are you sure you want to delete this project? This action cannot be undone."
                        variant="danger" icon="trash">
                        Delete
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
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
    $isOverdue = $daysRemaining !== null && $daysRemaining < 0; $isDueSoon=$daysRemaining !==null && $daysRemaining>= 0
        && $daysRemaining <= 7; $canStartNext=$nextDeadline->canStart();
            @endphp
            <flux:callout :color="$isOverdue ? 'red' : ($isDueSoon ? 'amber' : 'blue')"
                :icon="$isOverdue ? 'exclamation-triangle' : 'clock'">
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
                        @if($nextDeadline->hasMissingFields())
                        <span class="text-sm opacity-75">({{ $nextDeadline->getStatusLabel() }})</span>
                        @endif
                    </div>
                    <flux:button wire:click="startFiling({{ $nextDeadline->id }})"
                        :variant="$canStartNext ? 'danger' : 'ghost'"
                        :disabled="!$canStartNext" size="sm">
                        {{ $nextDeadline->getButtonText() }}
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

                    {{-- Payment Protection Steps Timeline --}}
                    <x-ui.card>
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Payment Protection Steps</h2>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $project->jobsite_state ?? 'Unknown' }} lien path</p>
                            <p class="text-sm text-zinc-400 dark:text-zinc-500">Order and timing vary by state.</p>
                        </div>

                        @if($deadlines->isEmpty())
                        @if(!$project->jobsite_state)
                        <p class="text-zinc-500">No deadlines calculated. Add a jobsite state to see your timeline.</p>
                        @else
                        <flux:callout color="amber" icon="exclamation-triangle">
                            No deadline rules found for {{ $project->jobsite_state }}. Please contact support or run the
                            deadline rule seeder.
                        </flux:callout>
                        @endif
                        @else
                        @php
                            // Find the first pending deadline (next step)
                            $nextStepId = $deadlines->first(fn($d) => $d->status->value === 'pending' && $d->canStart())?->id;

                            // Step descriptions and badges
                            $stepInfo = [
                                'prelim_notice' => [
                                    'description' => 'Preserves your rights early.',
                                    'badge' => 'Required',
                                    'badge_color' => 'blue',
                                ],
                                'noi' => [
                                    'description' => "If you're still unpaid, send an NOI.",
                                    'badge' => 'Optional',
                                    'badge_color' => 'zinc',
                                ],
                                'mechanics_lien' => [
                                    'description' => 'If still unpaid after NOI, file the lien.',
                                    'badge' => null,
                                    'badge_color' => null,
                                ],
                                'lien_release' => [
                                    'description' => 'If you get paid, release the lien.',
                                    'badge' => null,
                                    'badge_color' => null,
                                ],
                            ];
                        @endphp

                        <div class="relative">
                            @foreach($deadlines as $index => $deadline)
                            @php
                                $slug = $deadline->documentType->slug;
                                $info = $stepInfo[$slug] ?? ['description' => '', 'badge' => null, 'badge_color' => null];
                                $isNextStep = $deadline->id === $nextStepId;
                                $isCompleted = $deadline->status->value === 'completed';
                                $stepNumber = $loop->iteration;
                            @endphp

                            <div class="relative flex gap-4 {{ !$loop->last ? 'pb-6' : '' }}">
                                {{-- Step indicator with connecting line --}}
                                <div class="flex flex-col items-center">
                                    {{-- Circle --}}
                                    <div class="relative z-10 flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium shrink-0
                                        @if($isCompleted)
                                            bg-green-500 text-white
                                        @elseif($isNextStep)
                                            bg-blue-500 text-white
                                        @else
                                            bg-white dark:bg-zinc-800 border-2 border-zinc-300 dark:border-zinc-600 text-zinc-500 dark:text-zinc-400
                                        @endif
                                    ">
                                        @if($isCompleted)
                                            <flux:icon name="check" class="size-4" />
                                        @else
                                            {{ $stepNumber }}
                                        @endif
                                    </div>

                                    {{-- Connecting line --}}
                                    @if(!$loop->last)
                                    <div class="w-0.5 flex-1 mt-2 border-l-2 border-dashed
                                        @if($isCompleted)
                                            border-green-300 dark:border-green-700
                                        @else
                                            border-zinc-300 dark:border-zinc-600
                                        @endif
                                    "></div>
                                    @endif
                                </div>

                                {{-- Step content --}}
                                <div class="flex-1 min-w-0 pb-2">
                                    <div class="flex items-start justify-between gap-4 p-4 rounded-lg -mt-1
                                        @if($isNextStep)
                                            bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800
                                        @elseif($isCompleted)
                                            bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800
                                        @else
                                            border border-zinc-200 dark:border-zinc-700
                                        @endif
                                    ">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-semibold text-zinc-900 dark:text-white">{{ $deadline->documentType->name }}</span>
                                                @if($info['badge'])
                                                    <flux:badge size="sm" :color="$info['badge_color']">{{ $info['badge'] }}</flux:badge>
                                                @endif
                                            </div>
                                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ $info['description'] }}</p>
                                            @if($deadline->hasMissingFields())
                                            <p class="text-sm text-amber-600 dark:text-amber-400 mt-1">
                                                Needs: {{ implode(', ', array_map(fn($f) => str_replace('_', ' ', $f), $deadline->missing_fields_json)) }}
                                            </p>
                                            @endif
                                            @if($deadline->due_date)
                                            <p class="text-sm text-zinc-400 dark:text-zinc-500 mt-1">
                                                Due {{ $deadline->due_date->format('M j, Y') }}
                                                @if($deadline->daysRemaining() !== null)
                                                    <span class="text-zinc-500 dark:text-zinc-400">({{ $deadline->daysRemaining() }} days)</span>
                                                @endif
                                            </p>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-3 shrink-0">
                                            {{-- Status badges --}}
                                            @if($deadline->status->value === 'completed')
                                            {{-- Shown via green styling --}}
                                            @elseif($deadline->isOverdue())
                                            <flux:badge color="red" size="sm">Overdue</flux:badge>
                                            @elseif($deadline->isDueSoon())
                                            <flux:badge color="amber" size="sm">Due Soon</flux:badge>
                                            @endif

                                            {{-- Action Button --}}
                                            @if($deadline->completedFiling)
                                            <flux:button href="{{ route('lien.filings.show', $deadline->completedFiling) }}" size="sm" variant="ghost">
                                                View
                                            </flux:button>
                                            @elseif($isNextStep)
                                            <flux:button wire:click="startFiling({{ $deadline->id }})" size="sm" variant="primary">
                                                {{ $deadline->draftFiling ? 'Continue' : 'Start' }}
                                            </flux:button>
                                            @elseif($deadline->canStart())
                                            <flux:button wire:click="startFiling({{ $deadline->id }})" size="sm" variant="outline">
                                                {{ $deadline->draftFiling ? 'Continue' : 'Start' }}
                                            </flux:button>
                                            @endif
                                        </div>
                                    </div>
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
                </div>
            </div>
</div>