@php
    use App\Domains\Lien\Enums\DeadlineStatus;
@endphp

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
            $daysRemaining = $nextDeadline->daysUntilDue;
            $isOverdue = $nextDeadline->isOverdue;
            $isDueSoon = $nextDeadline->status === DeadlineStatus::DueSoon;
        @endphp
        <flux:callout :color="$isOverdue ? 'red' : ($isDueSoon ? 'amber' : 'blue')"
            :icon="$isOverdue ? 'exclamation-triangle' : 'clock'">
            <div class="flex items-center justify-between w-full">
                <div>
                    <strong>{{ $nextDeadline->getDocumentTypeName() }}</strong>
                    @if($isOverdue && $daysRemaining !== null)
                        is overdue by {{ abs($daysRemaining) }} days!
                    @elseif($daysRemaining === 0)
                        is due today!
                    @elseif($daysRemaining === 1)
                        is due tomorrow!
                    @elseif($daysRemaining !== null && $nextDeadline->deadlineDate)
                        is due in {{ $daysRemaining }} days ({{ $nextDeadline->deadlineDate->format('M j, Y') }})
                    @else
                        deadline unknown
                    @endif
                </div>
                @if($nextDeadline->canStart)
                <flux:button wire:click="startFiling({{ $nextDeadline->deadline->id }})"
                    :variant="$isOverdue ? 'danger' : 'primary'" size="sm">
                    {{ $nextDeadline->getActionButtonText() }}
                </flux:button>
                @endif
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
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $project->jobsite_state ?? 'Unknown' }} lien path · Order and timing vary by state</p>
                </div>

                @if(empty($steps))
                    @if(!$project->jobsite_state)
                    <p class="text-zinc-500">No deadlines calculated. Add a jobsite state to see your timeline.</p>
                    @else
                    <flux:callout color="amber" icon="exclamation-triangle">
                        No deadline rules found for {{ $project->jobsite_state }}. Please contact support or run the deadline rule seeder.
                    </flux:callout>
                    @endif
                @else
                    @php
                        // Step descriptions
                        $stepDescriptions = [
                            'prelim_notice' => 'Preserves your rights early.',
                            'noi' => 'Notify the owner you intend to file a lien.',
                            'mechanics_lien' => 'If still unpaid, file the lien.',
                            'lien_release' => 'If you get paid, release the lien.',
                            'demand_letter' => 'Formally demand payment and put the debtor on notice.',
                        ];

                        // Convert to array for iteration
                        $stepsArray = is_array($steps) ? array_values($steps) : $steps->values()->all();

                        // Split into required (main timeline) and optional (additional documents)
                        $requiredSteps = array_values(array_filter($stepsArray, fn ($s) => !$s->isOptional()));
                        $optionalSteps = array_values(array_filter($stepsArray, fn ($s) => $s->isOptional()));

                        // Sort optional steps: demand_letter always first
                        usort($optionalSteps, fn ($a, $b) =>
                            ($a->getDocumentTypeSlug() === 'demand_letter' ? 0 : 1) <=> ($b->getDocumentTypeSlug() === 'demand_letter' ? 0 : 1)
                        );

                        // Find demand letter step for promoted display
                        $demandLetterStep = collect($optionalSteps)->first(fn ($s) => $s->getDocumentTypeSlug() === 'demand_letter');
                        $otherOptionalSteps = array_values(array_filter($optionalSteps, fn ($s) => $s->getDocumentTypeSlug() !== 'demand_letter'));
                    @endphp

                    @if($hasAnyMissedDeadline)
                        {{-- Missed Deadline Notice --}}
                        <div class="flex items-center gap-2.5 px-3 py-2.5 mb-4 rounded-lg border border-red-200 dark:border-red-800 bg-red-50/80 dark:bg-red-900/10">
                            <flux:icon name="shield-exclamation" class="size-4 text-red-400 dark:text-red-500 shrink-0" />
                            <p class="text-sm text-red-700/80 dark:text-red-300/70">
                                <span class="font-medium">A required filing deadline has passed.</span>
                                A demand letter is your best remaining option to recover payment.
                            </p>
                        </div>

                        {{-- Promoted Demand Letter --}}
                        @if($demandLetterStep)
                            @php
                                $dlDeadline = $demandLetterStep->deadline;
                                $dlCompleted = $demandLetterStep->status === DeadlineStatus::Completed;
                            @endphp
                            <div class="mb-6 flex items-center justify-between gap-4 p-5 rounded-lg border-2 border-dashed border-amber-400 dark:border-amber-600 bg-amber-50/60 dark:bg-amber-900/15">
                                <div class="flex items-center gap-4 flex-1 min-w-0">
                                    <div class="flex items-center justify-center size-10 rounded-full bg-amber-500 text-white shrink-0">
                                        <flux:icon name="envelope" class="size-5" />
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-semibold text-zinc-900 dark:text-white">{{ $demandLetterStep->getDocumentTypeName() }}</span>
                                            @if($dlCompleted)
                                                <flux:badge size="sm" color="green">Sent</flux:badge>
                                            @else
                                                <flux:badge size="sm" color="amber" class="uppercase tracking-wide text-xs font-bold">Recommended</flux:badge>
                                            @endif
                                        </div>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-0.5">{{ $stepDescriptions['demand_letter'] }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    @if($dlCompleted && $dlDeadline->completedFiling)
                                        <flux:button href="{{ route('lien.filings.show', $dlDeadline->completedFiling) }}" size="sm" variant="outline">
                                            View
                                        </flux:button>
                                    @elseif($demandLetterStep->shouldShowActionButton())
                                        <flux:button wire:click="startFiling({{ $dlDeadline->id }})" variant="primary"
                                            class="hover:shadow-md hover:scale-105 active:scale-100 transition-all">
                                            Send →
                                        </flux:button>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @elseif(!$project->last_furnish_date)
                        {{-- Furnish Date Banner --}}
                        <div class="flex items-center gap-4 p-4 mb-6 rounded-lg border-2 border-dashed border-amber-300 bg-amber-50/50 dark:bg-amber-900/10 dark:border-amber-700">
                            <div class="flex items-center justify-center size-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 shrink-0">
                                <flux:icon name="calendar" class="size-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 dark:text-white">Add your last furnish date to unlock deadlines</p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">We'll calculate exactly when each step is due</p>
                            </div>
                            <flux:button href="{{ route('lien.projects.edit', $project) }}" variant="outline" size="sm" class="shrink-0">
                                Add date →
                            </flux:button>
                        </div>
                    @endif

                    <div class="relative {{ $hasAnyMissedDeadline ? 'opacity-50' : '' }}">
                        @foreach($requiredSteps as $index => $step)
                            @php
                                $deadline = $step->deadline;
                                $slug = $step->getDocumentTypeSlug();
                                $description = $stepDescriptions[$slug] ?? '';
                                $isCompleted = $step->status === DeadlineStatus::Completed;
                                $isNotApplicable = $step->status === DeadlineStatus::NotApplicable;
                                $isLocked = $step->status === DeadlineStatus::Locked;
                                $isMissed = $step->status === DeadlineStatus::Missed;
                                $isDueSoon = $step->status === DeadlineStatus::DueSoon;
                                $isDeadlineUnknown = $step->status === DeadlineStatus::DeadlineUnknown;
                                $isInDraft = $step->status === DeadlineStatus::InDraft;
                                $isAwaitingPayment = $step->status === DeadlineStatus::AwaitingPayment;
                                $isPurchased = $step->status === DeadlineStatus::Purchased;
                                $isInFulfillment = $step->status === DeadlineStatus::InFulfillment;
                                $isNotStarted = $step->status === DeadlineStatus::NotStarted;
                                $stepNumber = $index + 1;
                                $isFirst = $index === 0;
                                $isLast = $index === count($requiredSteps) - 1;

                                // Check for property warnings in status_meta
                                $hasPropertyWarning = is_array($step->statusMeta) && ($step->statusMeta['has_property_warning'] ?? false);

                                // Computed UI flags
                                $isNextStepHighlighted = $step->isNextStep && !$hasPropertyWarning && !$isCompleted && !$isMissed;
                                $hasFurnishDate = (bool) $project->last_furnish_date;
                            @endphp

                            <div class="relative flex gap-4 {{ !$isLast ? 'pb-6' : '' }}">
                                {{-- Step indicator with connecting line --}}
                                <div class="flex flex-col items-center">
                                    {{-- Circle --}}
                                    <div @class([
                                        'relative z-10 flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium shrink-0',
                                        'bg-green-500 text-white' => $isCompleted,
                                        'bg-red-500 text-white' => $isMissed && !$hasAnyMissedDeadline,
                                        'bg-zinc-300 dark:bg-zinc-600 text-zinc-500 dark:text-zinc-400' => $isMissed && $hasAnyMissedDeadline,
                                        'bg-amber-500 text-white' => $isDueSoon || $isAwaitingPayment || $isNextStepHighlighted,
                                        'bg-zinc-400 text-white' => $isNotApplicable || $isLocked,
                                        'bg-sky-500 text-white' => $isPurchased || $isInFulfillment,
                                        'bg-white dark:bg-zinc-800 border-2 border-zinc-300 dark:border-zinc-600 text-zinc-500 dark:text-zinc-400' => $isInDraft || ($isNotStarted && !$step->isNextStep) || ($isDeadlineUnknown && !$step->isNextStep),
                                    ])>
                                        @if($isCompleted)
                                            <flux:icon name="check" class="size-4" />
                                        @elseif($isLocked)
                                            <flux:icon name="lock-closed" class="size-4" />
                                        @elseif($isNotApplicable)
                                            <flux:icon name="minus" class="size-4" />
                                        @else
                                            {{ $stepNumber }}
                                        @endif
                                    </div>

                                    {{-- Connecting line --}}
                                    @if(!$isLast)
                                    <div @class([
                                        'w-0.5 flex-1 mt-2 border-l-2 border-dashed',
                                        'border-green-300 dark:border-green-700' => $isCompleted,
                                        'border-zinc-300 dark:border-zinc-600' => !$isCompleted,
                                    ])></div>
                                    @endif
                                </div>

                                {{-- Step content --}}
                                <div class="flex-1 min-w-0 pb-2">
                                    <div @class([
                                        'flex items-start justify-between gap-4 p-4 rounded-lg -mt-1',
                                        'border bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' => $isCompleted,
                                        'border bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' => $isMissed && !$hasAnyMissedDeadline,
                                        'border-2 border-dashed border-amber-300 dark:border-amber-600 bg-amber-50/50 dark:bg-amber-900/10' => $isNextStepHighlighted,
                                        'border bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800' => ($isDueSoon || $isAwaitingPayment || $hasPropertyWarning) && !$isNextStepHighlighted,
                                        'border bg-zinc-50 dark:bg-zinc-800/50 border-zinc-200 dark:border-zinc-700 opacity-60' => $isNotApplicable,
                                        'border bg-sky-50 dark:bg-sky-900/20 border-sky-200 dark:border-sky-800' => $isPurchased || $isInFulfillment,
                                        'border border-zinc-200 dark:border-zinc-700' => !$isCompleted && !$isMissed && !$isNextStepHighlighted && !($isDueSoon || $isAwaitingPayment || $hasPropertyWarning) && !$isNotApplicable && !($isPurchased || $isInFulfillment),
                                    ])>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span @class(['font-semibold text-zinc-900 dark:text-white', 'line-through text-zinc-400 dark:text-zinc-500' => $hasAnyMissedDeadline && $isMissed])>{{ $step->getDocumentTypeName() }}</span>
                                                @if($isCompleted)
                                                    <flux:badge size="sm" color="green">
                                                        {{ $deadline->wasCompletedExternally() ? 'Filed Myself' : 'Submitted' }}
                                                    </flux:badge>
                                                @elseif($isNextStepHighlighted)
                                                    <flux:badge size="sm" color="amber">Next step</flux:badge>
                                                @endif
                                            </div>
                                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ $hasAnyMissedDeadline && $isMissed ? 'Deadline passed' : $description }}</p>

                                            {{-- Status-specific messages --}}
                                            @if($isNotApplicable && $step->statusReason)
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2">
                                                    {{ config("lien.status_reasons.{$step->statusReason}", $step->statusReason) }}
                                                </p>
                                            @endif

                                            @if($isLocked && $step->lockedReason)
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2">
                                                    {{ $step->lockedReason }}
                                                </p>
                                            @endif

                                            @if($hasPropertyWarning)
                                                <p class="text-sm text-amber-600 dark:text-amber-400 mt-2">
                                                    {{ config("lien.status_reasons." . ($step->statusMeta['property_warning_reason'] ?? ''), 'Property restrictions may apply.') }}
                                                </p>
                                            @endif

                                            {{-- NOC shortening indicator --}}
                                            @if(is_array($step->statusMeta) && ($step->statusMeta['noc_shortened'] ?? false))
                                                <p class="text-sm text-amber-600 dark:text-amber-400 mt-2">
                                                    Deadline shortened by NOC (was {{ \Carbon\Carbon::parse($step->statusMeta['original_due_date'])->format('M j, Y') }})
                                                </p>
                                            @endif

                                            {{-- Deadline unknown - neutral message (hidden when furnish date banner is shown) --}}
                                            @if($isDeadlineUnknown && !empty($step->missingFieldLabels) && $hasFurnishDate)
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                                    Add {{ implode(', ', $step->missingFieldLabels) }} to see deadline
                                                </p>
                                            @endif

                                            {{-- Due date --}}
                                            @if($step->deadlineDate)
                                                <p class="text-sm text-zinc-400 dark:text-zinc-500 mt-1">
                                                    Due {{ $step->deadlineDate->format('M j, Y') }}
                                                    @if($step->daysUntilDue !== null)
                                                        <span class="text-zinc-500 dark:text-zinc-400">({{ $step->daysUntilDue }} days)</span>
                                                    @endif
                                                </p>
                                            @endif

                                        </div>

                                        <div class="flex flex-col items-end gap-2 shrink-0">
                                            {{-- Status badges --}}
                                            @if($isCompleted)
                                                {{-- Shown via green styling --}}
                                            @elseif($isNotApplicable)
                                                <flux:badge color="zinc" size="sm">N/A</flux:badge>
                                            @elseif($isLocked)
                                                <flux:badge color="zinc" size="sm">
                                                    <flux:icon name="lock-closed" class="size-3 mr-1" />
                                                    Locked
                                                </flux:badge>
                                            @elseif($isMissed)
                                                <flux:badge color="{{ $hasAnyMissedDeadline ? 'zinc' : 'red' }}" size="sm">{{ $hasAnyMissedDeadline ? 'Deadline passed' : 'Overdue' }}</flux:badge>
                                            @elseif($isDueSoon)
                                                <flux:badge color="amber" size="sm">Due Soon</flux:badge>
                                            @elseif($isDeadlineUnknown && $hasFurnishDate)
                                                <flux:badge color="zinc" size="sm">Deadline Unknown</flux:badge>
                                            @elseif($isAwaitingPayment)
                                                <flux:badge color="amber" size="sm">Awaiting Payment</flux:badge>
                                            @elseif($isPurchased || $isInFulfillment)
                                                <flux:badge color="sky" size="sm">In Progress</flux:badge>
                                            @elseif($isInDraft)
                                                <flux:badge color="zinc" size="sm">Draft</flux:badge>
                                            @endif

                                            {{-- Action Buttons --}}
                                            <div class="flex items-center gap-2">
                                                @if($isCompleted && $deadline->completedFiling)
                                                    <flux:button href="{{ route('lien.filings.show', $deadline->completedFiling) }}" size="sm" variant="outline">
                                                        View
                                                    </flux:button>
                                                @elseif($isPurchased || $isInFulfillment)
                                                    @if($step->activeFiling)
                                                        <flux:button href="{{ route('lien.filings.show', $step->activeFiling) }}" size="sm" variant="outline">
                                                            View
                                                        </flux:button>
                                                    @endif
                                                @elseif($step->shouldShowActionButton())
                                                    @if($isNextStepHighlighted || $isMissed || $isDueSoon)
                                                        <flux:button wire:click="startFiling({{ $deadline->id }})" size="sm" variant="primary"
                                                            class="hover:shadow-md hover:scale-105 active:scale-100 transition-all">
                                                            {{ $step->getActionButtonText() }}{{ $isNextStepHighlighted ? ' →' : '' }}
                                                        </flux:button>
                                                    @else
                                                        <flux:button wire:click="startFiling({{ $deadline->id }})" size="sm" variant="outline"
                                                            class="hover:bg-zinc-100 hover:border-zinc-400 dark:hover:bg-zinc-700 dark:hover:border-zinc-500 hover:shadow-sm active:scale-95 transition-all">
                                                            {{ $step->getActionButtonText() }}
                                                        </flux:button>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Additional Documents (Optional Steps) --}}
                    @php
                        // When demand letter is promoted (all deadlines passed), show remaining optional steps
                        $displayOptionalSteps = $hasAnyMissedDeadline ? $otherOptionalSteps : $optionalSteps;
                    @endphp
                    @if(count($displayOptionalSteps) > 0)
                        <div x-data="{ open: false }" class="mt-6 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                            <button
                                @click="open = !open"
                                class="flex items-center justify-between w-full py-2 px-1 text-left group hover:bg-zinc-50 dark:hover:bg-zinc-800/50 rounded-lg transition-colors"
                            >
                                <div class="flex items-center gap-2.5">
                                    <flux:icon name="document-text" class="size-5 text-zinc-400 dark:text-zinc-500" />
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">Additional Documents</span>
                                    <span class="text-sm text-zinc-400 dark:text-zinc-500">({{ count($displayOptionalSteps) }} available)</span>
                                </div>
                                <flux:icon name="chevron-down" class="size-4 text-zinc-400 dark:text-zinc-500 transition-transform duration-200" x-bind:class="open && 'rotate-180'" />
                            </button>

                            <div x-show="open" x-collapse x-cloak class="mt-3 space-y-3">
                                @foreach($displayOptionalSteps as $step)
                                    @php
                                        $deadline = $step->deadline;
                                        $slug = $step->getDocumentTypeSlug();
                                        $description = $stepDescriptions[$slug] ?? '';
                                        $isCompleted = $step->status === DeadlineStatus::Completed;
                                        $isInDraft = $step->status === DeadlineStatus::InDraft;
                                        $isNotApplicable = $step->status === DeadlineStatus::NotApplicable;
                                        $isLocked = $step->status === DeadlineStatus::Locked;
                                    @endphp

                                    <div @class([
                                        'flex items-center justify-between gap-4 p-4 rounded-lg border',
                                        'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' => $isCompleted,
                                        'bg-zinc-50 dark:bg-zinc-800/50 border-zinc-200 dark:border-zinc-700 opacity-60' => $isNotApplicable,
                                        'border-zinc-200 dark:border-zinc-700' => !$isCompleted && !$isNotApplicable,
                                    ])>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-medium text-zinc-900 dark:text-white">{{ $step->getDocumentTypeName() }}</span>
                                                <flux:badge size="sm" color="zinc">Optional</flux:badge>
                                                @if($isCompleted)
                                                    <flux:badge size="sm" color="green">
                                                        {{ $deadline->wasCompletedExternally() ? 'Filed Myself' : 'Submitted' }}
                                                    </flux:badge>
                                                @elseif($isInDraft)
                                                    <flux:badge size="sm" color="zinc">Draft</flux:badge>
                                                @endif
                                            </div>
                                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ $description }}</p>
                                        </div>

                                        <div class="shrink-0">
                                            @if($isCompleted && $deadline->completedFiling)
                                                <flux:button href="{{ route('lien.filings.show', $deadline->completedFiling) }}" size="sm" variant="outline">
                                                    View
                                                </flux:button>
                                            @elseif($step->shouldShowActionButton())
                                                <flux:button wire:click="startFiling({{ $deadline->id }})" size="sm" variant="outline"
                                                    class="hover:bg-zinc-100 hover:border-zinc-400 dark:hover:bg-zinc-700 dark:hover:border-zinc-500 hover:shadow-sm active:scale-95 transition-all">
                                                    {{ $step->getActionButtonText() }}
                                                </flux:button>
                                            @elseif($isNotApplicable)
                                                <flux:badge color="zinc" size="sm">N/A</flux:badge>
                                            @elseif($isLocked)
                                                <flux:badge color="zinc" size="sm">
                                                    <flux:icon name="lock-closed" class="size-3 mr-1" />
                                                    Locked
                                                </flux:badge>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
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
