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

        @elseif ($step === 4)
            {{-- Step 4: Details --}}
            <x-slot:header>
                <div>
                    <h2 class="text-lg font-bold text-text-primary">Waiver details</h2>
                    <p class="mt-1 text-sm text-text-secondary">The payment this waiver covers, and who's involved.</p>
                </div>
            </x-slot:header>

            <div class="space-y-6">
                {{-- Payment: one field per row --}}
                <div>
                    <p class="mb-2.5 text-[15px] font-semibold text-text-primary">Payment</p>
                    <div class="space-y-4">
                        <flux:field class="sm:max-w-sm">
                            <flux:label>Payment amount ($)</flux:label>
                            <flux:input type="number" step="0.01" min="0" wire:model="amount" placeholder="0.00" />
                            <flux:error name="amount" />
                        </flux:field>

                        @unless ($this->isFinalKind())
                            <flux:field class="sm:max-w-sm">
                                <flux:label>Through date</flux:label>
                                <flux:date-picker wire:model="through_date" />
                                <flux:error name="through_date" />
                            </flux:field>
                        @endunless

                        <flux:field class="sm:max-w-sm">
                            <flux:label badge="Optional">Invoice / reference number</flux:label>
                            <flux:input wire:model="invoice_number" />
                            <flux:error name="invoice_number" />
                        </flux:field>
                    </div>
                </div>

                {{-- Counterparty. Signing needs no extra fields: you sign your own
                     provide waivers, and on collect waivers the contact signs. --}}
                <div class="border-t border-border pt-5">
                    <p class="text-[15px] font-semibold text-text-primary">
                        {{ $direction === 'provide' ? 'Who receives this waiver?' : 'Who is giving you this waiver?' }}
                    </p>
                    @if ($direction === 'collect')
                        <p class="mt-0.5 text-[13px] text-text-secondary">They sign it — we email the signature request to this contact.</p>
                    @endif

                    <div class="mt-3 space-y-3">
                        <flux:field>
                            <flux:label>Contact</flux:label>
                            <flux:select variant="combobox" clearable placeholder="Select a contact..." wire:model.live="contactId">
                                @foreach ($contacts as $contact)
                                    <flux:select.option value="{{ $contact->id }}">{{ $contact->displayName() }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="contactId" />
                        </flux:field>

                        <div class="flex flex-wrap gap-2">
                            <flux:button wire:click="openContactModal" size="sm" variant="ghost" icon="plus">
                                Add new contact
                            </flux:button>
                            @if ($direction === 'provide')
                                <flux:button wire:click="useProjectCustomer" size="sm" variant="ghost" icon="user">
                                    Use project customer
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($direction === 'provide')
                    {{-- You sign your own provide waiver. --}}
                    <div class="border-t border-border pt-5">
                        <p class="text-[15px] font-semibold text-text-primary">Signature</p>
                        <div class="mt-3 flex items-center gap-3 rounded-xl border border-border bg-bg-light p-4">
                            <flux:icon name="user-circle" class="size-8 shrink-0 text-zinc-400" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-text-primary">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-text-secondary">{{ auth()->user()->email }} &bull; You sign your own waiver</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Optional details: exceptions + (conditional-only) expected check.
                 Safely skippable, so they live out of the main flow — but always visible. --}}
            <div class="-mx-6 -mb-6 mt-6 rounded-b-xl border-t border-border bg-bg-light px-6 py-5">
                <div class="mb-3.5 flex flex-wrap items-baseline gap-x-2.5 gap-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-text-secondary">Optional details</span>
                    <span class="text-[13px] text-zinc-400">Fine to skip.</span>
                </div>
                <div class="space-y-4">
                    <flux:field>
                        <flux:label>Exceptions</flux:label>
                        <flux:textarea wire:model="exceptions" rows="3" placeholder="e.g., disputed change order #4, retention, unbilled extras..." />
                        <flux:description>
                            Anything this waiver does NOT release: disputed claims, retention, or extras.
                            Listed exceptions survive the waiver.
                        </flux:description>
                        <flux:error name="exceptions" />
                    </flux:field>

                    @if ($this->isConditionalKind())
                        <flux:field class="sm:max-w-sm">
                            <flux:label>Check from (maker)</flux:label>
                            <flux:input wire:model="check_maker" placeholder="Who the check is from" />
                            <flux:error name="check_maker" />
                        </flux:field>

                        <flux:field class="sm:max-w-sm">
                            <flux:label>Check number</flux:label>
                            <flux:input wire:model="check_number" />
                            <flux:error name="check_number" />
                        </flux:field>
                    @endif
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
                        {{ $amount !== null && $amount !== '' ? '$'.number_format((float) $amount, 2) : '-' }}
                    </x-ui.info-list.item>
                    @unless ($this->isFinalKind())
                        <x-ui.info-list.item label="Through date">
                            {{ $through_date ? \Illuminate\Support\Carbon::parse($through_date)->format('M j, Y') : '-' }}
                        </x-ui.info-list.item>
                    @endunless
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

                <div class="space-y-3 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                    {{-- Free: stream without saving --}}
                    <div>
                        <flux:button wire:click="downloadPdf" wire:loading.attr="disabled" variant="outline" icon="arrow-down-tray" class="w-full">
                            <span wire:loading.remove wire:target="downloadPdf">Download PDF</span>
                            <span wire:loading wire:target="downloadPdf">Building PDF...</span>
                        </flux:button>
                        <p class="mt-1 text-center text-xs text-zinc-500">Always free. Nothing is saved to your account.</p>
                    </div>

                    {{-- Metered: save + generate --}}
                    <div>
                        <flux:button wire:click="save" wire:loading.attr="disabled" variant="primary" icon="folder-plus" class="w-full">
                            <span wire:loading.remove wire:target="save">Save to project</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </flux:button>
                        @if (! $hasPaidAccess)
                            <p class="mt-1 text-center text-xs text-zinc-500">
                                {{ $remainingFreeSaves }} of {{ $freeSavesLimit }} free saves left this month.
                            </p>
                        @endif
                    </div>

                    {{-- Paid: save + e-sign --}}
                    <div>
                        @if ($form && ! $form->esignAllowed)
                            <flux:button variant="filled" icon="paper-airplane" class="w-full" disabled>
                                Save &amp; send for signature
                            </flux:button>
                            <p class="mt-1 text-center text-xs text-zinc-500">
                                {{ $form->esignDisabledReason ?? 'This state requires in-person execution, so e-signing is unavailable. Use Download, sign on paper, then upload the signed copy.' }}
                            </p>
                        @else
                            <flux:button wire:click="saveAndSend" wire:loading.attr="disabled" variant="filled" icon="paper-airplane" class="w-full">
                                <span wire:loading.remove wire:target="saveAndSend">Save &amp; send for signature</span>
                                <span wire:loading wire:target="saveAndSend">Sending...</span>
                            </flux:button>
                            @if (! $canEsign)
                                <p class="mt-1 text-center text-xs text-zinc-500">Requires Waiver Pro. We'll show you what's included.</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </x-ui.card>

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
                    <span wire:loading.remove wire:target="nextStep">Continue</span>
                    <span wire:loading wire:target="nextStep">Checking...</span>
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Add-contact modal --}}
    <flux:modal wire:model="showContactModal" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading>Add Contact</flux:heading>

            <form wire:submit="saveContact" class="space-y-4">
                <flux:field>
                    <flux:label>Company *</flux:label>
                    <flux:input wire:model="contact_company" placeholder="ABC Construction" />
                    <flux:error name="contact_company" />
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Contact name</flux:label>
                        <flux:input wire:model="contact_name" placeholder="John Smith" />
                        <flux:error name="contact_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input type="email" wire:model="contact_email" />
                        <flux:error name="contact_email" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Phone</flux:label>
                    <flux:input wire:model="contact_phone" />
                    <flux:error name="contact_phone" />
                </flux:field>

                <flux:field>
                    <flux:label>Address</flux:label>
                    <flux:input wire:model="contact_address1" placeholder="Street address" />
                    <flux:error name="contact_address1" />
                </flux:field>

                <flux:field>
                    <flux:input wire:model="contact_address2" placeholder="Suite, unit, etc." />
                    <flux:error name="contact_address2" />
                </flux:field>

                <div class="grid grid-cols-3 gap-4">
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

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button type="button" wire:click="closeContactModal" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        Add Contact
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Upsell modal (save limit hit / e-sign gated) --}}
    <flux:modal wire:model="showUpsellModal" class="max-w-md">
        <x-lien.waiver-upsell :heading="$upsellContext === 'esign' ? 'E-sign requires Waiver Pro' : 'You\'ve used all your free saves this month'" />
    </flux:modal>
</div>
