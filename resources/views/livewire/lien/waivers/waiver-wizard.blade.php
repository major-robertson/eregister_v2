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
                $isActive = $step === $stepNum;
                $isComplete = $step > $stepNum;
            @endphp
            <div class="flex items-center {{ $stepNum < $totalSteps ? 'flex-1' : '' }}">
                <button wire:click="goToStep({{ $stepNum }})"
                    @class([
                        'flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium transition',
                        'bg-blue-600 text-white' => $isActive,
                        'bg-green-500 text-white' => $isComplete,
                        'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400' => ! $isActive && ! $isComplete,
                        'cursor-pointer' => $isComplete,
                        'cursor-default' => ! $isComplete,
                    ])
                    @if (! $isComplete) disabled @endif
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
            <x-slot:header>Which waiver do you need?</x-slot:header>

            <div class="space-y-6">
                {{-- State advisories --}}
                @foreach ($stateRules['ui_notes'] ?? [] as $note)
                    <flux:callout color="blue" icon="information-circle" wire:key="ui-note-{{ $loop->index }}">
                        {{ $note }}
                    </flux:callout>
                @endforeach

                @if (! empty($stateRules['advance_waiver_note']))
                    <flux:callout color="amber" icon="information-circle">
                        {{ $stateRules['advance_waiver_note'] }}
                    </flux:callout>
                @endif

                {{-- Guided questions --}}
                <flux:field>
                    <flux:label>Is this for a progress payment or the final payment?</flux:label>
                    <div class="mt-2 grid grid-cols-2 gap-3">
                        @foreach (['progress' => ['Progress payment', 'One payment along the way: more work or payments are coming.'], 'final' => ['Final payment', 'The last payment on this project.']] as $value => [$label, $hint])
                            <button
                                type="button"
                                wire:key="payment-type-{{ $value }}"
                                wire:click="$set('paymentType', '{{ $value }}')"
                                @class([
                                    'rounded-lg border-2 p-4 text-left transition',
                                    'border-blue-600 bg-blue-50/60 dark:border-blue-500 dark:bg-blue-900/20' => $paymentType === $value,
                                    'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600' => $paymentType !== $value,
                                ])
                            >
                                <span class="block font-medium text-zinc-900 dark:text-white">{{ $label }}</span>
                                <span class="mt-1 block text-xs text-zinc-500">{{ $hint }}</span>
                            </button>
                        @endforeach
                    </div>
                </flux:field>

                <flux:field>
                    <flux:label>Has the payment actually been received or cleared?</flux:label>
                    <div class="mt-2 grid grid-cols-2 gap-3">
                        @foreach (['no' => ['Not yet', 'Safest: the waiver only takes effect once the money arrives (conditional).'], 'yes' => ['Yes, money in hand', 'The waiver takes effect immediately when signed (unconditional).']] as $value => [$label, $hint])
                            <button
                                type="button"
                                wire:key="payment-received-{{ $value }}"
                                wire:click="$set('paymentReceived', '{{ $value }}')"
                                @class([
                                    'rounded-lg border-2 p-4 text-left transition',
                                    'border-blue-600 bg-blue-50/60 dark:border-blue-500 dark:bg-blue-900/20' => $paymentReceived === $value,
                                    'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600' => $paymentReceived !== $value,
                                ])
                            >
                                <span class="block font-medium text-zinc-900 dark:text-white">{{ $label }}</span>
                                <span class="mt-1 block text-xs text-zinc-500">{{ $hint }}</span>
                            </button>
                        @endforeach
                    </div>
                </flux:field>

                {{-- Redirected to the state's equivalent form --}}
                @if ($redirectNotice)
                    <flux:callout color="amber" icon="arrow-path">
                        {{ $redirectNotice }}
                    </flux:callout>
                @endif

                {{-- Resolved selection --}}
                @if ($kind !== '' && isset($kinds[$kind]))
                    <flux:callout color="green" icon="check-circle">
                        <flux:callout.heading>{{ $kinds[$kind]['title'] }}</flux:callout.heading>
                        <flux:callout.text>
                            {{ \App\Domains\Lien\Enums\WaiverKind::from($kind)->description() }}
                            @if (! empty($stateRules['statute']))
                                <span class="block mt-1 text-xs">Statutory basis: {{ $stateRules['statute'] }}</span>
                            @endif
                        </flux:callout.text>
                    </flux:callout>
                @endif
                <flux:error name="kind" />

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

                {{-- Power-user grid --}}
                <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <flux:switch wire:model.live="showAllKinds" label="Show all form types" />

                    @if ($showAllKinds)
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($kinds as $kindValue => $entry)
                                @if ($entry['enabled'])
                                    <button
                                        type="button"
                                        wire:key="kind-{{ $kindValue }}"
                                        wire:click="selectKind('{{ $kindValue }}')"
                                        @class([
                                            'rounded-lg border-2 p-4 text-left transition',
                                            'border-blue-600 bg-blue-50/60 dark:border-blue-500 dark:bg-blue-900/20' => $kind === $kindValue,
                                            'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600' => $kind !== $kindValue,
                                        ])
                                    >
                                        <span class="block text-sm font-medium text-zinc-900 dark:text-white">{{ $entry['title'] }}</span>
                                        <span class="mt-1 block text-xs text-zinc-500">{{ $entry['kind']->shortLabel() }}</span>
                                    </button>
                                @else
                                    {{-- Never hidden: greyed out with the state's explanation --}}
                                    <div
                                        wire:key="kind-{{ $kindValue }}"
                                        class="cursor-not-allowed rounded-lg border-2 border-zinc-200 p-4 opacity-60 dark:border-zinc-700"
                                    >
                                        <span class="block text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $entry['title'] }}</span>
                                        <span class="mt-1 block text-xs text-zinc-400">
                                            {{ $entry['disabled_reason'] ?? 'Not used in this state.' }}
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        @elseif ($step === 4)
            {{-- Step 4: Details --}}
            <x-slot:header>Waiver details</x-slot:header>

            <div class="space-y-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Payment amount ($)</flux:label>
                        <flux:input type="number" step="0.01" min="0" wire:model="amount" placeholder="0.00" />
                        <flux:description>The payment this waiver covers. Leaving it blank is allowed.</flux:description>
                        <flux:error name="amount" />
                    </flux:field>

                    @unless ($this->isFinalKind())
                        <flux:field>
                            <flux:label>Through date</flux:label>
                            <flux:date-picker wire:model="through_date" />
                            <flux:description>You waive rights for work/materials furnished through this date.</flux:description>
                            <flux:error name="through_date" />
                        </flux:field>
                    @endunless

                    <flux:field>
                        <flux:label>Invoice number</flux:label>
                        <flux:input wire:model="invoice_number" placeholder="Optional" />
                        <flux:error name="invoice_number" />
                    </flux:field>
                </div>

                @if ($this->isConditionalKind())
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>Check from (maker)</flux:label>
                            <flux:input wire:model="check_maker" placeholder="Who the check is from" />
                            <flux:description>Conditional waivers can identify the expected payment.</flux:description>
                            <flux:error name="check_maker" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Check number</flux:label>
                            <flux:input wire:model="check_number" placeholder="Optional" />
                            <flux:error name="check_number" />
                        </flux:field>
                    </div>
                @endif

                <flux:field>
                    <flux:label>Exceptions</flux:label>
                    <flux:textarea wire:model="exceptions" rows="3" placeholder="e.g., disputed change order #4, retention, unbilled extras..." />
                    <flux:description>
                        Anything this waiver does NOT release: disputed claims, retention, or extras.
                        Listed exceptions survive the waiver.
                    </flux:description>
                    <flux:error name="exceptions" />
                </flux:field>

                {{-- Counterparty --}}
                <div class="border-t border-zinc-200 pt-6 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-3">
                        {{ $direction === 'provide' ? 'Who receives this waiver?' : 'Who is giving you this waiver?' }}
                    </flux:heading>

                    <div class="space-y-3">
                        <flux:field>
                            <flux:label>Contact</flux:label>
                            <flux:select variant="combobox" clearable placeholder="Select a contact..." wire:model="contactId">
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

                {{-- Signer --}}
                <div class="border-t border-zinc-200 pt-6 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-3">Who signs the waiver?</flux:heading>

                    @if ($direction === 'collect')
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <flux:field>
                                <flux:label>Signer name *</flux:label>
                                <flux:input wire:model="signer_name" placeholder="Person at the vendor/sub who signs" />
                                <flux:error name="signer_name" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Signer email *</flux:label>
                                <flux:input type="email" wire:model="signer_email" placeholder="Where the signature request goes" />
                                <flux:error name="signer_email" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Signer title</flux:label>
                                <flux:input wire:model="signer_title" placeholder="e.g., Owner, Project Manager" />
                                <flux:error name="signer_title" />
                            </flux:field>
                        </div>
                    @else
                        <div class="flex items-center gap-3 rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800">
                            <flux:icon name="user-circle" class="size-8 text-zinc-400" />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-zinc-500">{{ auth()->user()->email }} &bull; You sign your own waiver</p>
                            </div>
                        </div>
                        <flux:field class="mt-3 max-w-xs">
                            <flux:label>Your title</flux:label>
                            <flux:input wire:model="signer_title" placeholder="e.g., Owner, President" />
                            <flux:error name="signer_title" />
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
                        {{ $direction === 'provide' ? auth()->user()->name : $signer_name }}
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
