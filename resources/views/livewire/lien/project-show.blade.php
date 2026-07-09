@php
use App\Domains\Lien\Enums\DeadlineStatus;
@endphp

<div class="space-y-6">
    <x-ui.page-header :title="$project->name" :subtitle="$subtitle">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Projects', 'url' => route('lien.projects.index')],
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

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        {{-- ============ LEFT: document actions ============ --}}
        <div class="min-w-0 space-y-6">

            {{-- Lien Waivers — first-class --}}
            <section class="overflow-hidden rounded-2xl border border-border bg-white">
                <div class="flex items-center gap-3 border-b border-border px-6 py-5">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-bold text-text-primary">Lien Waivers</h2>
                        <p class="mt-0.5 text-sm text-text-secondary">Exchange waivers as payments come in</p>
                    </div>
                    <flux:button
                        :href="route('lien.waivers.create', ['project' => $project->public_id])"
                        variant="primary" icon="plus" size="sm" wire:navigate>
                        Create waiver
                    </flux:button>
                </div>
                <div class="grid grid-cols-1 gap-3 p-6 sm:grid-cols-2">
                    @foreach($waiverTypeCards as $card)
                    <a href="{{ $card['url'] }}" wire:navigate
                        class="group flex items-center gap-3 rounded-xl border border-border p-4 transition-colors hover:border-primary/40 hover:bg-primary/5">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <flux:icon name="document-text" class="size-[18px]" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-text-primary">{{ $card['title'] }}</div>
                            <div class="mt-0.5 text-xs text-text-secondary">{{ $card['description'] }}</div>
                        </div>
                    </a>
                    @endforeach
                </div>
                {{-- Softer entry for anyone unsure of the type: the wizard's guided
                     questions pick the form for them. --}}
                <div class="border-t border-border px-6 py-3.5 text-center">
                    <a href="{{ route('lien.waivers.create', ['project' => $project->public_id]) }}" wire:navigate
                        class="text-sm text-text-secondary transition-colors hover:text-primary">
                        Not sure which you need? <span class="font-semibold text-primary">Answer 2 questions →</span>
                    </a>
                </div>
            </section>

            {{-- Lien Rights (payment-protection steps) --}}
            <section class="rounded-2xl border border-border bg-white p-6">
                <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                    <h2 class="text-lg font-bold text-text-primary">Lien Rights</h2>
                    <span class="text-sm text-text-secondary">{{ $project->jobsite_state ?? 'Unknown' }} lien path ·
                        order and timing vary by state</span>
                </div>

                @if(empty($steps))
                @if(!$project->jobsite_state)
                <p class="mt-4 text-sm text-text-secondary">No deadlines calculated. Add a jobsite state to see your
                    lien path.</p>
                @else
                <flux:callout class="mt-4" color="amber" icon="exclamation-triangle">
                    No deadline rules found for {{ $project->jobsite_state }}. Please contact support or run the
                    deadline rule seeder.
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

                $stepsArray = is_array($steps) ? array_values($steps) : $steps->values()->all();

                // Split into required (main timeline) and optional (additional documents)
                $requiredSteps = array_values(array_filter($stepsArray, fn ($s) => !$s->isOptional()));
                $optionalSteps = array_values(array_filter($stepsArray, fn ($s) => $s->isOptional()));

                // Demand letter always sorts first among optional steps
                usort($optionalSteps, fn ($a, $b) =>
                ($a->getDocumentTypeSlug() === 'demand_letter' ? 0 : 1) <=> ($b->getDocumentTypeSlug() ===
                    'demand_letter' ? 0 : 1)
                    );

                    $demandLetterStep = collect($optionalSteps)->first(fn ($s) => $s->getDocumentTypeSlug() ===
                    'demand_letter');
                    $otherOptionalSteps = array_values(array_filter($optionalSteps, fn ($s) =>
                    $s->getDocumentTypeSlug() !== 'demand_letter'));
                    @endphp

                    @if($hasAnyMissedDeadline)
                    {{-- Missed Deadline Notice --}}
                    <div
                        class="mt-4 flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50/70 px-4 py-3">
                        <flux:icon name="shield-exclamation" class="mt-0.5 size-4 shrink-0 text-red-400" />
                        <p class="text-sm text-red-700/90">
                            <span class="font-semibold">A required filing deadline has passed.</span>
                            A demand letter is your best remaining option to recover payment.
                        </p>
                    </div>

                    {{-- Promoted Demand Letter --}}
                    @if($demandLetterStep)
                    @php
                    $dlDeadline = $demandLetterStep->deadline;
                    $dlCompleted = $demandLetterStep->status === DeadlineStatus::Completed;
                    @endphp
                    <div
                        class="mt-4 flex items-center justify-between gap-4 rounded-xl border border-amber-300 bg-amber-50/60 p-4">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white">
                                <flux:icon name="envelope" class="size-5" />
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-text-primary">{{
                                        $demandLetterStep->getDocumentTypeName() }}</span>
                                    @if($dlCompleted)
                                    <flux:badge size="sm" color="green">Sent</flux:badge>
                                    @else
                                    <flux:badge size="sm" color="amber">Recommended</flux:badge>
                                    @endif
                                </div>
                                <p class="mt-0.5 text-sm text-text-secondary">{{ $stepDescriptions['demand_letter'] }}
                                </p>
                            </div>
                        </div>
                        <div class="shrink-0">
                            @if($dlCompleted && $dlDeadline->completedFiling)
                            <flux:button href="{{ route('lien.filings.show', $dlDeadline->completedFiling) }}" size="sm"
                                variant="outline">
                                View
                            </flux:button>
                            @elseif($demandLetterStep->shouldShowActionButton())
                            <flux:button wire:click="startFiling({{ $dlDeadline->id }})" variant="primary" size="sm">
                                Send
                            </flux:button>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif

                    {{-- Timeline --}}
                    <div class="mt-5 {{ $hasAnyMissedDeadline ? 'opacity-60' : '' }}">
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
                        $isAwaitingClient = $step->status === DeadlineStatus::AwaitingClient;
                        $isAwaitingEsign = $step->status === DeadlineStatus::AwaitingEsign;
                        $isAwaitingNotary = $step->status === DeadlineStatus::AwaitingNotary;
                        $isMailed = $step->status === DeadlineStatus::Mailed;
                        $isRecorded = $step->status === DeadlineStatus::Recorded;
                        $isNotStarted = $step->status === DeadlineStatus::NotStarted;
                        $stepNumber = $index + 1;
                        $isLast = $index === count($requiredSteps) - 1;

                        $hasPropertyWarning = is_array($step->statusMeta) && ($step->statusMeta['has_property_warning']
                        ?? false);
                        $isNextStepHighlighted = $step->isNextStep && !$hasPropertyWarning && !$isCompleted &&
                        !$isMissed;
                        $hasFurnishDate = (bool) $project->last_furnish_date;
                        $lineIsGreen = $isCompleted || $isMailed || $isRecorded;

                        // The actionable "next step" gets a subtle dashed outline
                        // only — no filled tints. Every other state is conveyed by
                        // its circle color and status badge on a clean plain row.
                        $rowBox = match(true) {
                        $isNextStepHighlighted => '-mt-1.5 rounded-xl border border-dashed border-amber-300 p-3.5',
                        $isNotApplicable => 'opacity-60',
                        default => '',
                        };
                        @endphp

                        <div class="flex gap-3.5">
                            {{-- Indicator column --}}
                            <div class="flex flex-col items-center">
                                <div @class([ 'relative z-10 flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold'
                                    , 'bg-green-500 text-white'=> $isCompleted || $isMailed || $isRecorded,
                                    'bg-red-500 text-white' => $isMissed && !$hasAnyMissedDeadline,
                                    'bg-zinc-300 text-zinc-500' => $isMissed && $hasAnyMissedDeadline,
                                    'bg-amber-500 text-white' => $isDueSoon || $isAwaitingPayment ||
                                    $isNextStepHighlighted,
                                    'bg-zinc-400 text-white' => $isNotApplicable || $isLocked,
                                    'bg-orange-500 text-white' => $isAwaitingClient,
                                    'bg-purple-500 text-white' => $isAwaitingEsign,
                                    'bg-violet-500 text-white' => $isAwaitingNotary,
                                    'bg-sky-500 text-white' => $isPurchased || $isInFulfillment,
                                    'border border-border bg-zinc-100 text-text-secondary' => !$isCompleted &&
                                    !$isMailed && !$isRecorded && !$isMissed && !$isDueSoon && !$isAwaitingPayment &&
                                    !$isNextStepHighlighted && !$isNotApplicable && !$isLocked && !$isAwaitingClient &&
                                    !$isAwaitingEsign && !$isAwaitingNotary && !$isPurchased && !$isInFulfillment,
                                    ])>
                                    @if($isCompleted || $isMailed || $isRecorded)
                                    <flux:icon name="check" class="size-4" />
                                    @elseif($isLocked)
                                    <flux:icon name="lock-closed" class="size-3.5" />
                                    @elseif($isNotApplicable)
                                    <flux:icon name="minus" class="size-3.5" />
                                    @else
                                    {{ $stepNumber }}
                                    @endif
                                </div>
                                @if(!$isLast)
                                <div @class([ 'my-1 w-px flex-1'
                                    , 'bg-green-300'=> $lineIsGreen,
                                    'bg-border' => !$lineIsGreen,
                                    ])></div>
                                @endif
                            </div>

                            {{-- Content column --}}
                            <div class="min-w-0 flex-1 pb-5">
                                <div class="{{ $rowBox }} flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span @class([ 'font-semibold text-text-primary'
                                                , 'text-zinc-400 line-through'=> $hasAnyMissedDeadline && $isMissed,
                                                ])>{{ $step->getDocumentTypeName() }}</span>
                                            @if($isCompleted)
                                            <flux:badge size="sm" color="green">
                                                {{ $deadline->wasCompletedExternally() ? 'Filed Myself' : 'Complete' }}
                                            </flux:badge>
                                            @elseif($isNextStepHighlighted)
                                            <flux:badge size="sm" color="amber">Next step</flux:badge>
                                            @endif
                                        </div>

                                        <p class="mt-1 text-sm text-text-secondary">{{ $hasAnyMissedDeadline &&
                                            $isMissed ? 'Deadline passed' : $description }}</p>

                                        {{-- Status-specific messages --}}
                                        @if($isNotApplicable && $step->statusReason)
                                        <p class="mt-2 text-sm text-text-secondary">
                                            {{ config("lien.status_reasons.{$step->statusReason}", $step->statusReason)
                                            }}
                                        </p>
                                        @endif

                                        @if($isLocked && $step->lockedReason)
                                        <p class="mt-2 text-sm text-text-secondary">{{ $step->lockedReason }}</p>
                                        @endif

                                        @if($hasPropertyWarning)
                                        <p class="mt-2 text-sm text-amber-600">
                                            {{ config("lien.status_reasons." .
                                            ($step->statusMeta['property_warning_reason'] ?? ''), 'Property
                                            restrictions may apply.') }}
                                        </p>
                                        @endif

                                        {{-- NOC shortening indicator --}}
                                        @if(is_array($step->statusMeta) && ($step->statusMeta['noc_shortened'] ??
                                        false))
                                        <p class="mt-2 text-sm text-amber-600">
                                            Deadline shortened by NOC (was {{
                                            \Carbon\Carbon::parse($step->statusMeta['original_due_date'])->format('M j,
                                            Y') }})
                                        </p>
                                        @endif

                                        {{-- Deadline unknown --}}
                                        @if($isDeadlineUnknown && !empty($step->missingFieldLabels) && $hasFurnishDate)
                                        <p class="mt-1 text-sm text-text-secondary">
                                            Add {{ implode(', ', $step->missingFieldLabels) }} to see deadline
                                        </p>
                                        @endif

                                        {{-- Due date --}}
                                        @if($step->deadlineDate)
                                        <p class="mt-1 text-sm text-zinc-400">
                                            Due {{ $step->deadlineDate->format('M j, Y') }}
                                            @if($step->daysUntilDue !== null)
                                            <span class="text-text-secondary">({{ $step->daysUntilDue }} days)</span>
                                            @endif
                                        </p>
                                        @endif
                                    </div>

                                    <div class="flex shrink-0 flex-col items-end gap-2">
                                        {{-- Status badges --}}
                                        @if($isCompleted)
                                        {{-- Shown via green styling --}}
                                        @elseif($isNotApplicable)
                                        <flux:badge color="zinc" size="sm">N/A</flux:badge>
                                        @elseif($isLocked)
                                        <flux:badge color="zinc" size="sm">
                                            <flux:icon name="lock-closed" class="mr-1 size-3" />
                                            Locked
                                        </flux:badge>
                                        @elseif($isMissed)
                                        <flux:badge color="{{ $hasAnyMissedDeadline ? 'zinc' : 'red' }}" size="sm">{{
                                            $hasAnyMissedDeadline ? 'Deadline passed' : 'Overdue' }}</flux:badge>
                                        @elseif($isDueSoon)
                                        <flux:badge color="amber" size="sm">Due Soon</flux:badge>
                                        @elseif($isDeadlineUnknown && $hasFurnishDate)
                                        <flux:badge color="zinc" size="sm">Deadline Unknown</flux:badge>
                                        @elseif($isAwaitingPayment)
                                        <flux:badge color="amber" size="sm">Awaiting Payment</flux:badge>
                                        @elseif($isAwaitingClient)
                                        <flux:badge color="orange" size="sm">Awaiting Client</flux:badge>
                                        @elseif($isAwaitingEsign)
                                        <flux:badge color="purple" size="sm">Awaiting E-Signature</flux:badge>
                                        @elseif($isAwaitingNotary)
                                        <flux:badge color="violet" size="sm">Awaiting Notary</flux:badge>
                                        @elseif($isMailed)
                                        <flux:badge color="teal" size="sm">Mailed</flux:badge>
                                        @elseif($isRecorded)
                                        <flux:badge color="cyan" size="sm">Recorded</flux:badge>
                                        @elseif($isPurchased || $isInFulfillment)
                                        <flux:badge color="sky" size="sm">In Progress</flux:badge>
                                        @elseif($isInDraft)
                                        <flux:badge color="zinc" size="sm">Draft</flux:badge>
                                        @endif

                                        {{-- Action buttons --}}
                                        <div class="flex items-center gap-2">
                                            @if($isCompleted && ($deadline->completedFiling || $step->activeFiling))
                                            <flux:button
                                                href="{{ route('lien.filings.show', $deadline->completedFiling ?? $step->activeFiling) }}"
                                                size="sm" variant="outline">
                                                View
                                            </flux:button>
                                            @elseif($isPurchased || $isInFulfillment || $isAwaitingClient || $isAwaitingEsign || $isAwaitingNotary || $isMailed || $isRecorded)
                                            @if($step->activeFiling)
                                            <flux:button href="{{ route('lien.filings.show', $step->activeFiling) }}"
                                                size="sm" variant="outline">
                                                View
                                            </flux:button>
                                            @endif
                                            @elseif($step->shouldShowActionButton())
                                            @if($isNextStepHighlighted || $isMissed || $isDueSoon)
                                            <flux:button wire:click="startFiling({{ $deadline->id }})" size="sm"
                                                variant="primary">
                                                {{ $step->getActionButtonText() }}{{ $isNextStepHighlighted ? ' →' : ''
                                                }}
                                            </flux:button>
                                            @else
                                            <flux:button wire:click="startFiling({{ $deadline->id }})" size="sm"
                                                variant="outline">
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

                    {{-- Additional Documents (optional steps) --}}
                    @php
                    $displayOptionalSteps = $hasAnyMissedDeadline ? $otherOptionalSteps : $optionalSteps;
                    @endphp
                    @if(count($displayOptionalSteps) > 0)
                    <flux:accordion class="mt-4 border-t border-border pt-2">
                        <flux:accordion.item>
                            <flux:accordion.heading>
                                <div class="flex items-center gap-2.5">
                                    <flux:icon name="document-text" class="size-5 text-zinc-400" />
                                    <span class="font-medium text-text-primary">Additional Documents</span>
                                    <span class="text-sm text-text-secondary">({{ count($displayOptionalSteps) }}
                                        available)</span>
                                </div>
                            </flux:accordion.heading>
                            <flux:accordion.content>
                                <div class="mt-3 space-y-3">
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

                                    <div @class([ 'flex items-center justify-between gap-4 rounded-xl border p-4'
                                        , 'border-green-200 bg-green-50/60'=> $isCompleted,
                                        'border-border bg-zinc-50 opacity-60' => $isNotApplicable,
                                        'border-border' => !$isCompleted && !$isNotApplicable,
                                        ])>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-medium text-text-primary">{{
                                                    $step->getDocumentTypeName() }}</span>
                                                <flux:badge size="sm" color="zinc">Optional</flux:badge>
                                                @if($isCompleted)
                                                <flux:badge size="sm" color="green">
                                                    {{ $deadline->wasCompletedExternally() ? 'Filed Myself' : 'Complete'
                                                    }}
                                                </flux:badge>
                                                @elseif($isInDraft)
                                                <flux:badge size="sm" color="zinc">Draft</flux:badge>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-sm text-text-secondary">{{ $description }}</p>
                                        </div>

                                        <div class="shrink-0">
                                            @if($isCompleted && $deadline->completedFiling)
                                            <flux:button
                                                href="{{ route('lien.filings.show', $deadline->completedFiling) }}"
                                                size="sm" variant="outline">
                                                View
                                            </flux:button>
                                            @elseif($step->shouldShowActionButton())
                                            <flux:button wire:click="startFiling({{ $deadline->id }})" size="sm"
                                                variant="outline">
                                                {{ $step->getActionButtonText() }}
                                            </flux:button>
                                            @elseif($isNotApplicable)
                                            <flux:badge color="zinc" size="sm">N/A</flux:badge>
                                            @elseif($isLocked)
                                            <flux:badge color="zinc" size="sm">
                                                <flux:icon name="lock-closed" class="mr-1 size-3" />
                                                Locked
                                            </flux:badge>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </flux:accordion.content>
                        </flux:accordion.item>
                    </flux:accordion>
                    @endif
                @endif
            </section>

            {{-- Documents (waivers + filings) --}}
            <section class="rounded-2xl border border-border bg-white p-6">
                <h2 class="text-lg font-bold text-text-primary">Documents</h2>
                <p class="mt-0.5 text-sm text-text-secondary">Waivers and filings on this project</p>

                @if($documents->isEmpty())
                <div class="mt-4 rounded-xl border border-dashed border-border px-6 py-7 text-center">
                    <p class="text-sm text-text-secondary">Nothing yet — waivers you create and liens you file will show
                        up here.</p>
                </div>
                @else
                <div class="mt-4 divide-y divide-border">
                    @foreach($documents as $doc)
                    <a href="{{ $doc['url'] }}" wire:navigate
                        class="-mx-2 flex items-center gap-3 rounded-lg px-2 py-3 transition-colors first:pt-0 last:pb-0 hover:bg-zinc-50">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-text-secondary">
                            <flux:icon :icon="$doc['icon']" class="size-[18px]" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-text-primary">{{ $doc['title'] }}</div>
                            <div class="truncate text-xs text-text-secondary">{{ $doc['subtitle'] }}</div>
                        </div>
                        <flux:badge size="sm" :color="$doc['status_color']">{{ $doc['status_label'] }}</flux:badge>
                    </a>
                    @endforeach
                </div>
                @endif
            </section>
        </div>

        {{-- ============ RIGHT: quiet reference ============ --}}
        <div class="min-w-0 space-y-6">

            {{-- Project Details --}}
            <section class="rounded-2xl border border-border bg-white p-5">
                <div class="flex items-center gap-2">
                    <h2 class="flex-1 text-base font-bold text-text-primary">Project Details</h2>
                    <a href="{{ route('lien.projects.edit', $project) }}" wire:navigate
                        class="text-sm font-semibold text-primary hover:underline">Edit</a>
                </div>
                <div class="mt-4 flex flex-col gap-3.5">
                    <div>
                        <div class="text-xs text-text-secondary">Jobsite Address</div>
                        <div class="mt-0.5 text-sm font-medium text-text-primary">{{ $project->jobsiteAddressLine() }}
                        </div>
                    </div>
                    @if($project->jobsite_county)
                    <div>
                        <div class="text-xs text-text-secondary">County</div>
                        <div class="mt-0.5 text-sm font-medium text-text-primary">{{ $project->jobsite_county }}</div>
                    </div>
                    @endif
                    <div>
                        <div class="text-xs text-text-secondary">Your Role</div>
                        <div class="mt-0.5 text-sm font-medium text-text-primary">{{ $project->claimant_type->label() }}
                        </div>
                    </div>
                    @if($project->job_number)
                    <div>
                        <div class="text-xs text-text-secondary">Job Number</div>
                        <div class="mt-0.5 text-sm font-medium text-text-primary">{{ $project->job_number }}</div>
                    </div>
                    @endif
                </div>
            </section>

            {{-- Deadlines --}}
            <section class="rounded-2xl border border-border bg-white p-5">
                <h2 class="text-base font-bold text-text-primary">Deadlines</h2>

                @forelse($upcomingDeadlines as $step)
                @php
                $days = $step->daysUntilDue;
                $overdue = $step->isOverdue;
                $dueSoon = $step->status === DeadlineStatus::DueSoon;
                @endphp
                <div class="mt-3.5 flex items-start gap-2.5">
                    <span @class([ 'mt-1.5 size-2 shrink-0 rounded-full'
                        , 'bg-red-500'=> $overdue,
                        'bg-amber-500' => $dueSoon && !$overdue,
                        'bg-primary' => !$overdue && !$dueSoon,
                        ])></span>
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-text-primary">{{ $step->getDocumentTypeName() }}</div>
                        <div class="mt-0.5 text-[13px] text-text-secondary">
                            {{ $step->deadlineDate->format('M j, Y') }} ·
                            @if($overdue && $days !== null)
                            overdue by {{ abs($days) }} days
                            @elseif($days === 0)
                            due today
                            @elseif($days === 1)
                            1 day left
                            @elseif($days !== null)
                            {{ $days }} days left
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                @if(!$project->last_furnish_date)
                <div class="mt-3.5 flex items-start gap-2.5">
                    <flux:icon name="calendar" class="mt-0.5 size-4 shrink-0 text-amber-500" />
                    <div class="min-w-0 text-[13px] text-text-secondary">
                        Add your last furnish date to calculate deadlines.
                        <a href="{{ route('lien.projects.edit', $project) }}" wire:navigate
                            class="font-semibold text-primary hover:underline">Add date</a>
                    </div>
                </div>
                @else
                <p class="mt-3.5 text-[13px] text-text-secondary">No upcoming deadlines.</p>
                @endif
                @endforelse

                <div class="mt-3.5 border-t border-border pt-3 text-[13px] text-text-secondary">
                    Deadlines update automatically as you add filings.
                </div>
            </section>
        </div>
    </div>
</div>
