<div class="max-w-3xl mx-auto space-y-6">
    <x-ui.page-header title="New Lien Waiver">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Waivers', 'url' => route('lien.waivers.index')],
                ['label' => 'New Waiver'],
            ]" />
        </x-slot:breadcrumbs>
    </x-ui.page-header>

    {{-- Progress Steps --}}
    <div class="flex items-center justify-between mb-8">
        @foreach ($stepTitles as $stepNum => $label)
            @php
                $isSkipped = $this->stepIsSkipped($stepNum);
                $isActive = $step === $stepNum;
                // Deep-link-skipped steps (project/type) read as already done.
                $isComplete = $step > $stepNum || $isSkipped;
                $isClickable = $isComplete && ! $isSkipped && ! $isActive;
            @endphp
            <div class="flex items-center {{ $stepNum < $totalSteps ? 'flex-1' : '' }}">
                <button wire:click="goToStep({{ $stepNum }})"
                    @class([
                        'flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium transition',
                        'bg-blue-600 text-white' => $isActive,
                        'bg-green-500 text-white' => $isComplete && ! $isActive,
                        'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400' => ! $isActive && ! $isComplete,
                        'cursor-pointer' => $isClickable,
                        'cursor-default' => ! $isClickable,
                    ])
                    @if (! $isClickable) disabled @endif
                >
                    @if ($isComplete)
                        <flux:icon name="check" class="w-4 h-4" />
                    @else
                        {{ $stepNum }}
                    @endif
                </button>
                <span class="ml-2 text-sm {{ $isActive ? 'font-medium' : 'text-zinc-500' }} hidden md:inline">{{ $label }}</span>
                @if ($stepNum < $totalSteps)
                    <div class="flex-1 h-px bg-zinc-200 dark:bg-zinc-700 mx-4"></div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Step Content --}}
    @if ($step === 4)
        {{-- Step 4: Details. Rendered as stacked cards (Form-UX handoff) instead
             of the wizard shell's single card. --}}
        <div class="space-y-4">
            {{-- Payment --}}
            <section class="rounded-2xl border border-border bg-white p-6 shadow-xs">
                <h2 class="text-base font-bold text-text-primary">Payment covered by this waiver</h2>
                <p class="mt-0.5 text-sm text-text-secondary">The amount and period this waiver releases.</p>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Payment amount</flux:label>
                        <flux:input.group>
                            <flux:input.group.prefix>$</flux:input.group.prefix>
                            {{-- Text (not number) so the value can carry thousands
                                 separators; updatedAmount() reformats on blur. --}}
                            <flux:input type="text" inputmode="decimal" wire:model.blur="amount" placeholder="0.00" />
                        </flux:input.group>
                        <flux:error name="amount" />
                    </flux:field>

                    @unless ($this->isFinalKind())
                        {{-- "Paid through" only reads right once money is in hand;
                             conditional waivers haven't been paid yet. --}}
                        <flux:field>
                            <flux:label>{{ $this->isConditionalKind() ? 'Through date' : 'Paid through' }}</flux:label>
                            <flux:date-picker wire:model="through_date" />
                            <flux:error name="through_date" />
                        </flux:field>
                    @endunless
                </div>
                @unless ($this->isFinalKind())
                    <p class="mt-2 text-[13px] text-zinc-400">
                        {{ $this->isConditionalKind()
                            ? 'The waiver covers work and materials furnished through this date once the payment clears.'
                            : 'Everything paid up to this date is released by the waiver.' }}
                    </p>
                @endunless

                <flux:field class="mt-4">
                    <flux:label>Invoice or reference # <span class="font-normal text-zinc-400">(optional)</span></flux:label>
                    <flux:input wire:model="invoice_number" placeholder="e.g. INV-2041" />
                    <flux:error name="invoice_number" />
                </flux:field>
            </section>

            {{-- Legal description: only when the state's statutory form is
                 invalid without one (MO residential unconditional final). --}}
            @if ($form?->requiresLegalDescription)
                <section class="rounded-2xl border border-border bg-white p-6 shadow-xs">
                    <h2 class="text-base font-bold text-text-primary">Property legal description</h2>
                    <p class="mt-0.5 text-sm text-text-secondary">
                        This form ({{ $form->statute }}) is only valid with the property's legal
                        description — a street address alone doesn't satisfy it.
                    </p>

                    <flux:field class="mt-4">
                        <flux:textarea wire:model="legal_description" rows="3"
                            placeholder="e.g. Lot 12, Block 3, Sunset Hills Plat Two, recorded in Plat Book 44, Page 7" />
                        <flux:description>
                            Copy it from the deed, title commitment, or county parcel records. The street
                            address still prints separately as "commonly known as."
                        </flux:description>
                        <flux:error name="legal_description" />
                    </flux:field>
                </section>
            @endif

            {{-- Property owner: every waiver form identifies who owns the
                 property, so the project must carry an owner party. --}}
            <section class="rounded-2xl border border-border bg-white p-6 shadow-xs">
                <h2 class="text-base font-bold text-text-primary">Property owner</h2>
                <p class="mt-0.5 text-sm text-text-secondary">Who owns the property this waiver covers.</p>

                @php $ownerParty = $project?->ownerParty(); @endphp
                <div class="mt-4 space-y-2">
                    @if ($ownerParty)
                        <div class="flex items-center gap-3 rounded-xl border border-border bg-bg-light p-4">
                            <flux:icon name="home-modern" class="size-6 shrink-0 text-zinc-400" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-text-primary">{{ $ownerParty->displayName() }}</p>
                                @if ($ownerParty->addressLine() !== '')
                                    <p class="text-xs text-text-secondary">{{ $ownerParty->addressLine() }}</p>
                                @endif
                            </div>
                            <flux:button wire:click="editOwner" size="sm" variant="ghost" icon="pencil-square" class="shrink-0">
                                Edit
                            </flux:button>
                        </div>
                    @else
                        <button
                            type="button"
                            wire:click="openOwnerModal"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-300 p-3 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary/5"
                        >
                            <span class="text-lg leading-none">+</span> Add the property owner
                        </button>
                    @endif
                    <flux:error name="owner" />
                </div>
            </section>

            {{-- Counterparty: contact picker (they sign collect waivers) --}}
            <section class="rounded-2xl border border-border bg-white p-6 shadow-xs">
                @if ($direction === 'collect')
                    <h2 class="text-base font-bold text-text-primary">Who signs this waiver?</h2>
                    <p class="mt-0.5 text-sm text-text-secondary">We'll email them a signature request.</p>
                @else
                    <h2 class="text-base font-bold text-text-primary">Who receives this waiver?</h2>
                @endif

                <div class="mt-4 space-y-2">
                    <flux:select variant="combobox" clearable placeholder="Select a contact..." wire:model.live="contactId">
                        @foreach ($contacts as $contactOption)
                            <flux:select.option value="{{ $contactOption->id }}">{{ $contactOption->displayName() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="contactId" />

                    @php $selectedContactModel = $contactId !== '' ? $this->selectedContact() : null; @endphp
                    @if ($direction === 'collect' && $selectedContactModel && blank($selectedContactModel->email))
                        {{-- Missing email blocks collect waivers; fix it in place. --}}
                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-amber-200 bg-amber-50/70 px-3.5 py-2.5">
                            <p class="text-[13px] text-amber-800">This contact has no email address — we need one to send the signature request.</p>
                            <flux:button wire:click="editSelectedContact" size="sm" variant="primary">Add email</flux:button>
                        </div>
                    @elseif ($selectedContactModel)
                        <flux:button wire:click="editSelectedContact" size="sm" variant="ghost" icon="pencil-square">
                            Edit contact
                        </flux:button>
                    @endif

                    <button
                        type="button"
                        wire:click="openContactModal"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-300 p-3 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary/5"
                    >
                        <span class="text-lg leading-none">+</span> Add a new contact
                    </button>
                </div>
            </section>

            @if ($direction === 'provide')
                {{-- You sign your own provide waiver. --}}
                <section class="rounded-2xl border border-border bg-white p-6 shadow-xs">
                    <h2 class="text-base font-bold text-text-primary">Signature</h2>
                    <div class="mt-3 flex items-center gap-3 rounded-xl border border-border bg-bg-light p-4">
                        <flux:icon name="user-circle" class="size-8 shrink-0 text-zinc-400" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-text-primary">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-text-secondary">{{ auth()->user()->email }} &bull; You sign your own waiver</p>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Optional details: pre-closed disclosure --}}
            <section class="rounded-2xl border border-border bg-white px-6 py-1.5 shadow-xs">
                <flux:accordion>
                    <flux:accordion.item>
                        <flux:accordion.heading>
                            <span class="flex flex-col py-1 text-left">
                                <span class="text-[15px] font-bold text-text-primary">Optional details</span>
                                <span class="mt-0.5 text-[13px] font-normal text-text-secondary">Exceptions{{ $this->isConditionalKind() ? ', check info' : '' }} — fine to skip</span>
                            </span>
                        </flux:accordion.heading>
                        <flux:accordion.content>
                            <div class="space-y-4 border-t border-border pt-4 pb-4">
                                <flux:field>
                                    <flux:label>Exceptions — anything this waiver does <span class="underline">not</span> release</flux:label>
                                    <flux:textarea wire:model="exceptions" rows="3" placeholder="e.g. disputed change order #4, retention, unbilled extras" />
                                    <flux:description>Listed exceptions survive the waiver.</flux:description>
                                    <flux:error name="exceptions" />
                                </flux:field>

                                @if ($this->isConditionalKind())
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <flux:field>
                                            <flux:label>Check from (maker)</flux:label>
                                            <flux:input wire:model="check_maker" placeholder="Who the check is from" />
                                            <flux:error name="check_maker" />
                                        </flux:field>

                                        <flux:field>
                                            <flux:label>Check number</flux:label>
                                            <flux:input wire:model="check_number" placeholder="e.g. 1204" />
                                            <flux:error name="check_number" />
                                        </flux:field>
                                    </div>
                                @endif
                            </div>
                        </flux:accordion.content>
                    </flux:accordion.item>
                </flux:accordion>
            </section>
        </div>
    @else
    <x-ui.card>
        @if ($step === 1)
            {{-- Step 1: Direction fork --}}
            <x-slot:header>What do you need to do?</x-slot:header>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($directions as $dir)
                    <button
                        type="button"
                        wire:key="direction-{{ $dir->value }}"
                        wire:click="selectDirection('{{ $dir->value }}')"
                        @class([
                            'flex flex-col items-start gap-2 rounded-xl border-2 p-5 text-left transition',
                            'border-blue-600 bg-blue-50/60 dark:border-blue-500 dark:bg-blue-900/20' => $direction === $dir->value,
                            'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600' => $direction !== $dir->value,
                        ])
                    >
                        <div class="flex w-full items-center justify-between">
                            <div @class([
                                'flex size-10 items-center justify-center rounded-lg',
                                'bg-blue-100 dark:bg-blue-800/50' => $direction === $dir->value,
                                'bg-zinc-100 dark:bg-zinc-700' => $direction !== $dir->value,
                            ])>
                                <flux:icon :name="$dir->value === 'provide' ? 'arrow-up-tray' : 'inbox-arrow-down'" @class([
                                    'size-5',
                                    'text-blue-600 dark:text-blue-400' => $direction === $dir->value,
                                    'text-zinc-500' => $direction !== $dir->value,
                                ]) />
                            </div>
                            @if ($direction === $dir->value)
                                <flux:icon name="check-circle" class="size-5 text-blue-600 dark:text-blue-400" />
                            @endif
                        </div>
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $dir->label() }}</span>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $dir->description() }}</span>
                    </button>
                @endforeach
            </div>
            <flux:error name="direction" class="mt-3" />

        @elseif ($step === 2)
            {{-- Step 2: Project --}}
            <x-slot:header>Which project is this waiver for?</x-slot:header>

            @if ($projects->isEmpty())
                <div class="py-6 text-center">
                    <flux:icon name="folder-plus" class="mx-auto size-10 text-zinc-400" />
                    <flux:text class="mt-2 text-zinc-500">
                        You don't have any completed projects yet. A waiver needs a project so we know
                        which state's form to use.
                    </flux:text>
                    <flux:button href="{{ route('lien.projects.create') }}" variant="primary" class="mt-4" wire:navigate>
                        Create a project first
                    </flux:button>
                </div>
            @else
                <div class="space-y-4">
                    <flux:field>
                        <flux:label>Project *</flux:label>
                        <flux:select variant="combobox" clearable placeholder="Select project..." wire:model.live="projectId">
                            @foreach ($projects as $projectOption)
                                <flux:select.option value="{{ $projectOption->public_id }}">
                                    {{ $projectOption->name }}{{ $projectOption->jobsiteAddressLine() ? ' ('.$projectOption->jobsiteAddressLine().')' : '' }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="projectId" />
                    </flux:field>

                    {{-- Leaves the wizard (project creation is its own flow); only
                         the direction choice is lost at this step. --}}
                    <a href="{{ route('lien.projects.create') }}" wire:navigate
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-300 p-3 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary/5">
                        <span class="text-lg leading-none">+</span> Add a new project
                    </a>

                    @if ($project)
                        <flux:callout color="blue" icon="map-pin">
                            <flux:callout.heading>Waiver state: {{ \App\Domains\Lien\Waivers\WaiverStateRegistry::STATE_NAMES[$project->jobsite_state] ?? $project->jobsite_state }}</flux:callout.heading>
                            <flux:callout.text>
                                The jobsite is in {{ $project->jobsite_state }}, so we'll use
                                {{ ($stateRules['compliance_standard'] ?? 'generic') === 'generic' ? 'our standard waiver forms' : "the state's statutory waiver forms" }}.
                            </flux:callout.text>
                        </flux:callout>
                    @endif
                </div>
            @endif

        @elseif ($step === 3)
            {{-- Step 3: Waiver type --}}
            <x-slot:header>
                <div>
                    <h2 class="text-lg font-bold text-text-primary">Which waiver do you need?</h2>
                    <p class="mt-1 text-sm text-text-secondary">Answer two questions or pick the exact form directly below.</p>
                </div>
            </x-slot:header>

            <div class="space-y-6">
                {{-- Q1: timing --}}
                <div>
                    <p class="mb-2.5 text-[15px] font-semibold text-text-primary">Is this for a progress payment or the final payment?</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach (['progress' => ['Progress payment', 'One payment along the way: more work or payments are coming.'], 'final' => ['Final payment', 'The last payment on this project.']] as $value => [$label, $hint])
                            @php $selected = $paymentType === $value; @endphp
                            <button
                                type="button"
                                wire:key="payment-type-{{ $value }}"
                                wire:click="$set('paymentType', '{{ $value }}')"
                                @class([
                                    'flex items-start gap-3 rounded-xl border p-4 text-left transition',
                                    'border-primary bg-primary/5' => $selected,
                                    'border-border bg-white hover:border-primary/40' => ! $selected,
                                ])
                            >
                                <span @class([
                                    'mt-0.5 size-[18px] shrink-0 rounded-full bg-white',
                                    'border-[5px] border-primary' => $selected,
                                    'border-[1.5px] border-zinc-300' => ! $selected,
                                ])></span>
                                <span class="min-w-0">
                                    <span class="block text-[15px] font-semibold text-text-primary">{{ $label }}</span>
                                    <span class="mt-0.5 block text-sm text-text-secondary">{{ $hint }}</span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Q2: condition --}}
                <div>
                    <p class="mb-2.5 text-[15px] font-semibold text-text-primary">Has the payment actually been received or cleared?</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach (['no' => ['Not yet', 'Safest: the waiver only takes effect once the money arrives (conditional).'], 'yes' => ['Yes, money in hand', 'The waiver takes effect immediately when signed (unconditional).']] as $value => [$label, $hint])
                            @php $selected = $paymentReceived === $value; @endphp
                            <button
                                type="button"
                                wire:key="payment-received-{{ $value }}"
                                wire:click="$set('paymentReceived', '{{ $value }}')"
                                @class([
                                    'flex items-start gap-3 rounded-xl border p-4 text-left transition',
                                    'border-primary bg-primary/5' => $selected,
                                    'border-border bg-white hover:border-primary/40' => ! $selected,
                                ])
                            >
                                <span @class([
                                    'mt-0.5 size-[18px] shrink-0 rounded-full bg-white',
                                    'border-[5px] border-primary' => $selected,
                                    'border-[1.5px] border-zinc-300' => ! $selected,
                                ])></span>
                                <span class="min-w-0">
                                    <span class="block text-[15px] font-semibold text-text-primary">{{ $label }}</span>
                                    <span class="mt-0.5 block text-sm text-text-secondary">{{ $hint }}</span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Redirected to the state's equivalent form --}}
                @if ($redirectNotice)
                    <flux:callout color="amber" icon="arrow-path">
                        {{ $redirectNotice }}
                    </flux:callout>
                @endif

                {{-- Notary/witness states can't e-sign --}}
                @if ($form && ! $form->esignAllowed)
                    <flux:callout color="purple" icon="pencil-square">
                        <flux:callout.heading>This form must be signed on paper</flux:callout.heading>
                        <flux:callout.text>
                            {{ $form->esignDisabledReason ?? 'This state requires '.($form->notarizationRequired ? 'notarization' : 'a witness').', so e-signing is unavailable.' }}
                            Download the PDF, sign it {{ $form->notarizationRequired ? 'before a notary' : ($form->witnessRequired ? 'with a witness' : 'in person') }},
                            then upload the signed copy to keep everything tracked here.
                        </flux:callout.text>
                    </flux:callout>
                @endif

                <flux:error name="kind" />
            </div>

            {{-- "Your form" strip: the exact form, visually distinct. Stays in sync
                 with the questions above — selecting either updates the other. --}}
            @php
                $formGroups = [
                    'Progress Payment' => ['conditional_progress', 'unconditional_progress'],
                    'Final Payment' => ['conditional_final', 'unconditional_final'],
                ];
            @endphp
            <div class="-mx-6 -mb-6 mt-6 rounded-b-xl border-t border-border bg-bg-light px-6 py-6">
                <div class="mb-3.5 flex flex-wrap items-baseline gap-x-2.5 gap-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-text-secondary">Your form</span>
                    <span class="text-[13px] text-zinc-400">Know exactly what you need? Pick it here instead.</span>
                </div>
                <div class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2">
                    @foreach ($formGroups as $groupHeading => $groupKinds)
                        <div>
                            <p class="mb-2 text-[13px] font-bold text-text-primary">{{ $groupHeading }}</p>
                            <div class="flex flex-col gap-2">
                                @foreach ($groupKinds as $kindValue)
                                    @php $entry = $kinds[$kindValue] ?? null; @endphp
                                    @if ($entry)
                                        @php
                                            $selected = $kind === $kindValue;
                                            $name = $entry['kind']->isConditional() ? 'Conditional Waiver' : 'Unconditional Waiver';
                                        @endphp
                                        @if ($entry['enabled'])
                                            <button
                                                type="button"
                                                wire:key="kind-{{ $kindValue }}"
                                                wire:click="selectKind('{{ $kindValue }}')"
                                                @class([
                                                    'flex items-center gap-3 rounded-xl border p-3.5 text-left transition',
                                                    'border-primary bg-primary/5' => $selected,
                                                    'border-border bg-white hover:border-primary/40' => ! $selected,
                                                ])
                                            >
                                                <span @class([
                                                    'size-[18px] shrink-0 rounded-full bg-white',
                                                    'border-[5px] border-primary' => $selected,
                                                    'border-[1.5px] border-zinc-300' => ! $selected,
                                                ])></span>
                                                <span class="min-w-0 flex-1 text-sm font-semibold text-text-primary">{{ $name }}</span>
                                                @if ($selected)
                                                    <span class="shrink-0 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">Selected</span>
                                                @endif
                                            </button>
                                        @else
                                            {{-- Never hidden: greyed with the state's explanation --}}
                                            <div wire:key="kind-{{ $kindValue }}" class="flex items-start gap-3 rounded-xl border border-border bg-white/50 p-3.5">
                                                <span class="mt-0.5 size-[18px] shrink-0 rounded-full border-[1.5px] border-zinc-200 bg-white"></span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-sm font-semibold text-zinc-400">{{ $name }}</div>
                                                    <div class="mt-0.5 text-xs text-zinc-400">{{ $entry['disabled_reason'] ?? 'Not used in this state.' }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        @elseif ($step === 5)
            {{-- Step 5: Review & actions --}}
            <x-slot:header>Review your waiver</x-slot:header>

            <div class="space-y-6">
                <x-ui.info-list>
                    <x-ui.info-list.item label="Form">
                        {{ $kinds[$kind]['title'] ?? \App\Domains\Lien\Enums\WaiverKind::tryFrom($kind)?->label() }}
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="Direction">
                        {{ \App\Domains\Lien\Enums\WaiverDirection::tryFrom($direction)?->label() }}
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="Project">
                        {{ $project?->name }}
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="State">
                        {{ $project ? (\App\Domains\Lien\Waivers\WaiverStateRegistry::STATE_NAMES[$project->jobsite_state] ?? $project->jobsite_state) : '-' }}
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="Amount">
                        {{ $this->amountFloat() !== null ? '$'.number_format($this->amountFloat(), 2) : '-' }}
                    </x-ui.info-list.item>
                    @unless ($this->isFinalKind())
                        <x-ui.info-list.item label="Through date">
                            {{ $through_date ? \Illuminate\Support\Carbon::parse($through_date)->format('M j, Y') : '-' }}
                        </x-ui.info-list.item>
                    @endunless
                    <x-ui.info-list.item label="Property owner">
                        {{ $project?->ownerParty()?->displayName() ?? '-' }}
                    </x-ui.info-list.item>
                    @if ($form?->requiresLegalDescription)
                        <x-ui.info-list.item label="Legal description">
                            {{ $legal_description ?: '-' }}
                        </x-ui.info-list.item>
                    @endif
                    <x-ui.info-list.item label="Counterparty">
                        {{ $this->selectedContact()?->displayName() ?? '-' }}
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="Signer">
                        {{ $direction === 'provide'
                            ? auth()->user()->name
                            : ($this->selectedContact()?->contact_name ?: $this->selectedContact()?->company_name) }}
                    </x-ui.info-list.item>
                    @if ($this->isConditionalKind() && ($check_maker || $check_number))
                        <x-ui.info-list.item label="Check">
                            {{ implode(', ', array_filter([$check_maker, $check_number ? '#'.$check_number : null])) }}
                        </x-ui.info-list.item>
                    @endif
                    @if ($exceptions)
                        <x-ui.info-list.item label="Exceptions">
                            {{ $exceptions }}
                        </x-ui.info-list.item>
                    @endif
                </x-ui.info-list>

                <flux:error name="kind" />

                {{-- Reaching review auto-saved the waiver as a draft (unless
                     the free allowance ran out); actions work on that draft. --}}
                @if ($savedWaiverId !== null)
                    <div class="flex items-center gap-2 rounded-xl border border-green-200 bg-green-50/70 px-3.5 py-2.5">
                        <flux:icon name="check-circle" class="size-4 shrink-0 text-green-600" />
                        <p class="text-[13px] text-green-800">Saved to the project as a draft — download it or send it for signature below.</p>
                    </div>
                @else
                    <div class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50/70 px-3.5 py-2.5">
                        <flux:icon name="exclamation-triangle" class="size-4 shrink-0 text-amber-600" />
                        <p class="text-[13px] text-amber-800">
                            You've used all {{ $freeSavesLimit }} free waivers this month, so this draft
                            isn't saved. Upgrade to download or send it.
                        </p>
                    </div>
                @endif

                <div class="space-y-3 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                    <div>
                        <flux:button wire:click="downloadPdf" wire:loading.attr="disabled" variant="outline" icon="arrow-down-tray" class="w-full">
                            <span wire:loading.remove wire:target="downloadPdf">Download PDF</span>
                            <span wire:loading wire:target="downloadPdf">Building PDF...</span>
                        </flux:button>
                    </div>

                    <div>
                        @if ($form && ! $form->esignAllowed)
                            <flux:button variant="filled" icon="paper-airplane" class="w-full" disabled>
                                Send for signature
                            </flux:button>
                            <p class="mt-1 text-center text-xs text-zinc-500">
                                {{ $form->esignDisabledReason ?? 'This state requires in-person execution, so e-signing is unavailable. Use Download, sign on paper, then upload the signed copy.' }}
                            </p>
                        @else
                            <flux:button wire:click="saveAndSend" wire:loading.attr="disabled" variant="filled" icon="paper-airplane" class="w-full">
                                <span wire:loading.remove wire:target="saveAndSend">Send for signature</span>
                                <span wire:loading wire:target="saveAndSend">Sending...</span>
                            </flux:button>
                        @endif
                    </div>

                    @if (! $hasPaidAccess)
                        <p class="text-center text-xs text-zinc-500">
                            {{ $remainingFreeSaves }} of {{ $freeSavesLimit }} free waivers left this month.
                        </p>
                    @endif
                </div>
            </div>
        @endif
    </x-ui.card>
    @endif

    {{-- Navigation --}}
    <div class="flex justify-between">
        <div>
            @if ($step > 1)
                <flux:button wire:click="previousStep" variant="ghost" icon="arrow-left">
                    Back
                </flux:button>
            @else
                <flux:button href="{{ route('lien.waivers.index') }}" variant="ghost" wire:navigate>
                    Cancel
                </flux:button>
            @endif
        </div>

        <div>
            @if ($step < $totalSteps)
                <flux:button wire:click="nextStep" wire:loading.attr="disabled" variant="primary" icon-trailing="arrow-right">
                    <span wire:loading.remove wire:target="nextStep">{{ $step === 4 ? 'Continue to review' : 'Continue' }}</span>
                    <span wire:loading wire:target="nextStep">Checking...</span>
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Add/edit-contact modal --}}
    <flux:modal wire:model="showContactModal" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading>{{ $editingContactId ? 'Edit Contact' : 'Add Contact' }}</flux:heading>

            <form wire:submit="saveContact" class="space-y-4">
                {{-- No field is individually required: a contact needs a company
                     or a name. The "one of them" error surfaces on Company. --}}
                <flux:field>
                    <flux:label>Company</flux:label>
                    <flux:input wire:model="contact_company" placeholder="ABC Construction" />
                    <flux:error name="contact_company" />
                </flux:field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>First name</flux:label>
                        <flux:input wire:model="contact_first_name" placeholder="John" />
                        <flux:error name="contact_first_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Last name</flux:label>
                        <flux:input wire:model="contact_last_name" placeholder="Smith" />
                        <flux:error name="contact_last_name" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input type="email" wire:model="contact_email" placeholder="john@abcconstruction.com" />
                    <flux:error name="contact_email" />
                </flux:field>

                <flux:field>
                    <flux:label>Phone</flux:label>
                    <flux:input wire:model="contact_phone" />
                    <flux:error name="contact_phone" />
                </flux:field>

                <flux:separator text="Mailing address (optional)" />

                <flux:field>
                    <flux:label>Street address</flux:label>
                    <flux:input wire:model="contact_address1" placeholder="Start typing to search..."
                        autocomplete="off" data-places-autocomplete data-places-method="updateContactAddressFromAutocomplete" />
                    <flux:error name="contact_address1" />
                </flux:field>

                <flux:field>
                    <flux:label>Address line 2</flux:label>
                    <flux:input wire:model="contact_address2" placeholder="Suite, unit, etc." />
                    <flux:error name="contact_address2" />
                </flux:field>

                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>City</flux:label>
                        <flux:input wire:model="contact_city" />
                        <flux:error name="contact_city" />
                    </flux:field>

                    <flux:field>
                        <flux:label>State</flux:label>
                        <flux:select variant="combobox" clearable placeholder="Select..." wire:model="contact_state">
                            @foreach (config('states') as $code => $name)
                                <flux:select.option value="{{ $code }}">{{ $name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="contact_state" />
                    </flux:field>

                    <flux:field>
                        <flux:label>ZIP</flux:label>
                        <flux:input wire:model="contact_zip" />
                        <flux:error name="contact_zip" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>County</flux:label>
                    <flux:input wire:model="contact_county" placeholder="Fills in from the address" />
                    <flux:error name="contact_county" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button type="button" wire:click="closeContactModal" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingContactId ? 'Save changes' : 'Add Contact' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Add/edit-owner modal: manages the project's owner party in place.
         Only the name is required (a person or an entity goes in the same
         blank); the address autofills via Google Places. --}}
    <flux:modal wire:model="showOwnerModal" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading>{{ $editingOwnerPartyId ? 'Edit Property Owner' : 'Add Property Owner' }}</flux:heading>

            <form wire:submit="saveOwner" class="space-y-4">
                <flux:field>
                    <flux:label>Owner name *</flux:label>
                    <flux:input wire:model="owner_name" placeholder="Person or entity that owns the property" />
                    <flux:error name="owner_name" />
                </flux:field>

                <flux:separator text="Mailing address (optional)" />

                <flux:field>
                    <flux:label>Street address</flux:label>
                    <flux:input wire:model="owner_address1" placeholder="Start typing to search..."
                        autocomplete="off" data-places-autocomplete data-places-method="updateOwnerAddressFromAutocomplete" />
                    <flux:error name="owner_address1" />
                </flux:field>

                <flux:field>
                    <flux:label>Address line 2</flux:label>
                    <flux:input wire:model="owner_address2" placeholder="Suite, unit, etc." />
                    <flux:error name="owner_address2" />
                </flux:field>

                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>City</flux:label>
                        <flux:input wire:model="owner_city" />
                        <flux:error name="owner_city" />
                    </flux:field>

                    <flux:field>
                        <flux:label>State</flux:label>
                        <flux:select variant="combobox" clearable placeholder="Select..." wire:model="owner_state">
                            @foreach (config('states') as $code => $name)
                                <flux:select.option value="{{ $code }}">{{ $name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="owner_state" />
                    </flux:field>

                    <flux:field>
                        <flux:label>ZIP</flux:label>
                        <flux:input wire:model="owner_zip" />
                        <flux:error name="owner_zip" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>County</flux:label>
                    <flux:input wire:model="owner_county" placeholder="Fills in from the address" />
                    <flux:error name="owner_county" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button type="button" wire:click="closeOwnerModal" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingOwnerPartyId ? 'Save changes' : 'Add Owner' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Upsell modal (save limit hit / e-sign gated) --}}
    <flux:modal wire:model="showUpsellModal" class="max-w-md">
        <x-lien.waiver-upsell :heading="$upsellContext === 'esign' ? 'E-sign requires Waiver Pro' : 'You\'ve used all your free saves this month'" />
    </flux:modal>

    @include('livewire.lien._places-autocomplete')
</div>
