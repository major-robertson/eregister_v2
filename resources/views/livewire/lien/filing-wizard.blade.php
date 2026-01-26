<div class="max-w-4xl mx-auto space-y-6">
    <x-ui.page-header :title="'Create ' . $documentType->name">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Lien Projects', 'url' => route('lien.projects.index')],
                ['label' => $project->name, 'url' => route('lien.projects.show', $project)],
                ['label' => $documentType->name],
            ]" />
        </x-slot:breadcrumbs>
    </x-ui.page-header>

    {{-- Progress Steps --}}
    <div class="flex items-center justify-between mb-8">
        @php
            $steps = ['Property', 'Parties', 'Amount', 'Service & Review'];
        @endphp
        @foreach($steps as $index => $label)
            @php
                $stepNum = $index + 1;
                $isActive = $step === $stepNum;
                $isComplete = $step > $stepNum;
            @endphp
            <div class="flex items-center {{ $index < count($steps) - 1 ? 'flex-1' : '' }}">
                <button
                    wire:click="goToStep({{ $stepNum }})"
                    @class([
                        'flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium transition',
                        'bg-blue-600 text-white' => $isActive,
                        'bg-green-500 text-white' => $isComplete,
                        'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400' => !$isActive && !$isComplete,
                        'cursor-pointer' => $isComplete,
                        'cursor-default' => !$isComplete,
                    ])
                    @if(!$isComplete) disabled @endif
                >
                    @if($isComplete)
                        <flux:icon name="check" class="w-4 h-4" />
                    @else
                        {{ $stepNum }}
                    @endif
                </button>
                <span class="ml-2 text-sm {{ $isActive ? 'font-medium' : 'text-zinc-500' }} hidden md:inline">{{ $label }}</span>
                @if($index < count($steps) - 1)
                    <div class="flex-1 h-px bg-zinc-200 dark:bg-zinc-700 mx-4"></div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Step Content --}}
    <x-ui.card>
        @if($step === 1)
            {{-- Step 1: Property Details --}}
            <x-slot:header>Property Details</x-slot:header>

            <div class="space-y-6">
                {{-- Property Address (Read-Only) --}}
                <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="flex items-center gap-2 mb-1">
                        <flux:icon name="map-pin" class="w-4 h-4 text-zinc-500" />
                        <span class="text-sm font-medium text-zinc-500">Property Address</span>
                    </div>
                    <div class="text-base font-medium">{{ $project->jobsiteAddressLine() }}</div>
                </div>

                {{-- Property Type --}}
                <flux:field>
                    <flux:label>Property Type *</flux:label>
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="project_type_category" value="residential" class="h-4 w-4 border-zinc-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Residential</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="project_type_category" value="commercial" class="h-4 w-4 border-zinc-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Commercial</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="project_type_category" value="government" class="h-4 w-4 border-zinc-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Government</span>
                        </label>
                    </div>
                    <flux:error name="project_type_category" />
                </flux:field>

                @if($project_type_category === 'government')
                    <flux:callout color="amber" icon="exclamation-triangle">
                        Mechanics liens typically don't apply to government-owned projects.
                        You may need to file a bond claim instead.
                    </flux:callout>
                @endif

                {{-- Legal Description --}}
                <flux:field>
                    <flux:label>Do you have the Legal Description?</flux:label>
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="has_legal_description" value="yes" class="h-4 w-4 border-zinc-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Yes</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="has_legal_description" value="no" class="h-4 w-4 border-zinc-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">No</span>
                        </label>
                    </div>
                </flux:field>

                @if($has_legal_description === 'yes')
                    <flux:field>
                        <flux:label>Legal Description</flux:label>
                        <flux:textarea wire:model="legal_description" rows="3" placeholder="As it appears on the deed..." />
                        <flux:error name="legal_description" />
                    </flux:field>
                @endif

                {{-- APN Question --}}
                <flux:field>
                    <flux:label>Do you have the Assessor Parcel Number (APN)?</flux:label>
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="has_apn" value="yes" class="h-4 w-4 border-zinc-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Yes</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="has_apn" value="no" class="h-4 w-4 border-zinc-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">No</span>
                        </label>
                    </div>
                </flux:field>

                @if($has_apn === 'yes')
                    <flux:field>
                        <flux:label>Assessor Parcel Number (APN)</flux:label>
                        <flux:input wire:model="apn" placeholder="e.g., 123-456-789" />
                        <flux:error name="apn" />
                    </flux:field>
                @endif

                {{-- Multiple Parcels --}}
                <flux:field>
                    <flux:label>Is there more than one parcel?</flux:label>
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="multiple_parcels" value="yes" class="h-4 w-4 border-zinc-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Yes</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="multiple_parcels" value="no" class="h-4 w-4 border-zinc-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">No</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="multiple_parcels" value="unknown" class="h-4 w-4 border-zinc-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Not sure</span>
                        </label>
                    </div>
                </flux:field>

                @if($multiple_parcels === 'yes')
                    <flux:callout color="blue" icon="information-circle">
                        You may need additional legal descriptions and APNs for each parcel.
                    </flux:callout>
                @endif

                {{-- Owner is Tenant --}}
                <flux:field>
                    <flux:checkbox wire:model="owner_is_tenant" label="The property owner is a tenant" />
                    <flux:description>Select Yes only if your contract is with a tenant, not the property owner.</flux:description>
                </flux:field>
            </div>

        @elseif($step === 2)
            {{-- Step 2: Parties --}}
            <x-slot:header>Parties</x-slot:header>

            @php
                $ownerParty = $parties->firstWhere('role.value', 'owner');
                $otherParties = $parties->filter(fn($p) => $p->role->value !== 'owner');
            @endphp

            <div class="space-y-3">
                {{-- Claimant (Auto-filled) --}}
                @if($claimantInfo)
                    <div class="flex items-start justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ $claimantInfo['company_name'] }}</span>
                                <flux:badge size="sm" color="blue">Claimant</flux:badge>
                            </div>
                            @if($claimantInfo['address'])
                                <div class="text-sm text-zinc-500 mt-1">{{ $claimantInfo['address'] }}</div>
                            @endif
                        </div>
                        <flux:button href="{{ route('business.edit') }}" size="sm" variant="ghost" icon="pencil" />
                    </div>
                @endif

                {{-- Property Owner --}}
                @if($ownerParty)
                    <div class="flex items-start justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ $ownerParty->displayName() }}</span>
                                <flux:badge size="sm" color="green">Property Owner</flux:badge>
                            </div>
                            @if($ownerParty->name !== $ownerParty->displayName())
                                <div class="text-xs text-zinc-500">{{ $ownerParty->name }}</div>
                            @endif
                            @if($ownerParty->addressLine())
                                <div class="text-sm text-zinc-500 mt-1">{{ $ownerParty->addressLine() }}</div>
                            @endif
                            @if($ownerParty->email || $ownerParty->phone)
                                <div class="text-xs text-zinc-500 mt-1">
                                    {{ implode(' | ', array_filter([$ownerParty->email, $ownerParty->phone])) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex gap-1">
                            <flux:button wire:click="openPartyModal({{ $ownerParty->id }})" size="sm" variant="ghost" icon="pencil" />
                            <flux:button
                                wire:click="deleteParty({{ $ownerParty->id }})"
                                wire:confirm="Delete this party?"
                                size="sm"
                                variant="ghost"
                                icon="trash"
                            />
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-between py-6 px-5 border-2 border-dashed border-zinc-400 dark:border-zinc-500 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-zinc-700 dark:text-zinc-300">Property Owner</span>
                            <flux:badge size="sm" color="red">Required</flux:badge>
                        </div>
                        <flux:button wire:click="openPartyModal" size="sm" variant="primary" icon="plus">
                            Add
                        </flux:button>
                    </div>
                    <flux:error name="owner_required" />
                @endif

                {{-- Other Parties --}}
                @foreach($otherParties as $party)
                    <div class="flex items-start justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ $party->displayName() }}</span>
                                <flux:badge size="sm">{{ $party->role->label() }}</flux:badge>
                            </div>
                            @if($party->name !== $party->displayName())
                                <div class="text-xs text-zinc-500">{{ $party->name }}</div>
                            @endif
                            @if($party->addressLine())
                                <div class="text-sm text-zinc-500 mt-1">{{ $party->addressLine() }}</div>
                            @endif
                            @if($party->email || $party->phone)
                                <div class="text-xs text-zinc-500 mt-1">
                                    {{ implode(' | ', array_filter([$party->email, $party->phone])) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex gap-1">
                            <flux:button wire:click="openPartyModal({{ $party->id }})" size="sm" variant="ghost" icon="pencil" />
                            <flux:button
                                wire:click="deleteParty({{ $party->id }})"
                                wire:confirm="Delete this party?"
                                size="sm"
                                variant="ghost"
                                icon="trash"
                            />
                        </div>
                    </div>
                @endforeach

                {{-- Add Party Button --}}
                <div class="flex items-center justify-between py-2 px-4 border border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg">
                    <span class="text-sm text-zinc-500">Add another party (optional)</span>
                    <flux:button wire:click="openPartyModal" size="sm" variant="ghost" icon="plus" class="border border-zinc-200 dark:border-zinc-600">
                        Add
                    </flux:button>
                </div>
            </div>

        @elseif($step === 3)
            {{-- Step 3: Amount & Contract --}}
            <x-slot:header>Amount & Contract</x-slot:header>

            <div class="space-y-6">
                {{-- Written Contract Question - Required --}}
                <flux:field>
                    <flux:label>Was the work done pursuant to a written contract? *</flux:label>
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="has_written_contract" value="1" class="h-4 w-4 border-zinc-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Yes</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="has_written_contract" value="0" class="h-4 w-4 border-zinc-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">No</span>
                        </label>
                    </div>
                    <flux:error name="has_written_contract" />
                </flux:field>

                <flux:field>
                    <flux:label>Amount Claimed *</flux:label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500">$</span>
                        <flux:input
                            type="number"
                            step="0.01"
                            min="0"
                            wire:model="amount_claimed"
                            class="pl-7"
                            placeholder="0.00"
                        />
                    </div>
                    <flux:error name="amount_claimed" />
                </flux:field>

                <flux:field>
                    <flux:label>Description of Work/Materials *</flux:label>
                    <flux:textarea
                        wire:model="description_of_work"
                        rows="4"
                        placeholder="Describe the labor, services, or materials you provided..."
                    />
                    <flux:error name="description_of_work" />
                </flux:field>

                {{-- Collapsible Amount Breakdown --}}
                <details class="group">
                    <summary class="flex items-center justify-between cursor-pointer p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">Optional: Amount Breakdown (advanced)</span>
                        </div>
                        <flux:icon name="chevron-down" class="w-5 h-5 transition-transform group-open:rotate-180" />
                    </summary>
                    <div class="p-4 border border-t-0 border-zinc-200 dark:border-zinc-700 rounded-b-lg space-y-4">
                        <flux:field>
                            <flux:label>A. Base Contract Amount</flux:label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500">$</span>
                                <flux:input type="number" step="0.01" min="0" wire:model.live="base_contract_amount" class="pl-7" placeholder="0.00" />
                            </div>
                        </flux:field>

                        <flux:field>
                            <flux:label>B. Net Change Orders</flux:label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500">$</span>
                                <flux:input type="number" step="0.01" wire:model.live="change_orders" class="pl-7" placeholder="0.00" />
                            </div>
                            <flux:description>Can be positive or negative</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>C. Value of Uncompleted Work</flux:label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500">$</span>
                                <flux:input type="number" step="0.01" min="0" wire:model.live="uncompleted_work" class="pl-7" placeholder="0.00" />
                            </div>
                        </flux:field>

                        <flux:field>
                            <flux:label>D. Credits or Deductions</flux:label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500">$</span>
                                <flux:input type="number" step="0.01" min="0" wire:model.live="credits_deductions" class="pl-7" placeholder="0.00" />
                            </div>
                        </flux:field>

                        <flux:field>
                            <flux:label>E. Payments Received</flux:label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500">$</span>
                                <flux:input type="number" step="0.01" min="0" wire:model.live="payments_received" class="pl-7" placeholder="0.00" />
                            </div>
                        </flux:field>

                        @if($this->calculatedBalanceDue !== null)
                            <div class="p-4 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Balance Due (A + B - C - D - E)</span>
                                    <span class="text-xl font-bold">${{ $this->calculatedBalanceDue }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </details>
            </div>

        @elseif($step === 4)
            {{-- Step 4: Checkout (Service + Review combined) - 2-column layout --}}
            <x-slot:header class="lg:hidden">Service & Review</x-slot:header>

            <div class="lg:flex lg:gap-8">
                {{-- Left Column: Service Selection (60-65%) --}}
                <div class="lg:w-3/5 space-y-6">
                    <flux:heading size="lg">Choose Your Service Level</flux:heading>

                    {{-- Validation Errors --}}
                    @if($errors->any())
                        <flux:callout color="red" icon="exclamation-circle">
                            <div class="space-y-1">
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        </flux:callout>
                    @endif

                    {{-- Warnings --}}
                    @if(count($warnings) > 0)
                        @foreach($warnings as $warning)
                            <flux:callout color="amber" icon="exclamation-triangle">
                                {{ $warning }}
                            </flux:callout>
                        @endforeach
                    @endif

                    {{-- Service Level Cards --}}
                    <div class="space-y-4">
                        @foreach($serviceLevels as $level)
                            <div>
                                @if($level->value === 'full_service')
                                    <div class="flex items-center gap-2 mb-2 text-sm text-zinc-500 dark:text-zinc-400">
                                        <flux:icon name="star" class="w-4 h-4" />
                                        Most contractors choose Full Service
                                    </div>
                                @endif
                                <label
                                    @class([
                                        'relative flex flex-col p-6 border-2 rounded-xl cursor-pointer transition',
                                        'border-blue-500 ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20' => $service_level === $level->value,
                                        'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600' => $service_level !== $level->value,
                                    ])
                                >
                                    <input
                                        type="radio"
                                        name="service_level"
                                        value="{{ $level->value }}"
                                        wire:model.live="service_level"
                                        class="sr-only"
                                    >
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-semibold text-lg">{{ $level->label() }}</span>
                                        <span class="text-xl font-bold">
                                            ${{ number_format($pricing[$level->value] / 100, 2) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ $level->description() }}
                                    </p>
                                    @if($level->value === 'full_service')
                                        <ul class="mt-3 text-sm text-zinc-600 dark:text-zinc-400 space-y-1">
                                            <li class="flex items-center gap-2">
                                                <flux:icon name="check" class="w-4 h-4 text-green-500" />
                                                Professional verification
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <flux:icon name="check" class="w-4 h-4 text-green-500" />
                                                Certified mailing
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <flux:icon name="check" class="w-4 h-4 text-green-500" />
                                                Recording with county
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <flux:icon name="check" class="w-4 h-4 text-green-500" />
                                                Deadline compliance review
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <flux:icon name="check" class="w-4 h-4 text-green-500" />
                                                Error correction & resubmission
                                            </li>
                                        </ul>
                                    @else
                                        <ul class="mt-3 text-sm text-zinc-600 dark:text-zinc-400 space-y-1">
                                            <li class="flex items-center gap-2">
                                                <flux:icon name="check" class="w-4 h-4 text-green-500" />
                                                Download completed document
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <flux:icon name="check" class="w-4 h-4 text-green-500" />
                                                Filing instructions included
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <flux:icon name="information-circle" class="w-4 h-4 text-zinc-400" />
                                                <span class="text-zinc-400">No verification</span>
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <flux:icon name="information-circle" class="w-4 h-4 text-zinc-400" />
                                                <span class="text-zinc-400">No mailing</span>
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <flux:icon name="information-circle" class="w-4 h-4 text-zinc-400" />
                                                <span class="text-zinc-400">No recording</span>
                                            </li>
                                        </ul>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>

                    {{-- Confirmation Checkbox --}}
                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6">
                        <flux:checkbox
                            wire:model="disclaimerAccepted"
                            label="I confirm this information is accurate and I'm authorized to file this document."
                        />
                        <flux:error name="disclaimerAccepted" />
                    </div>
                </div>

                {{-- Right Column: Order Summary (35-40%) --}}
                <div class="mt-8 lg:mt-0 lg:w-2/5">
                    @php
                        $ownerParty = $parties->firstWhere('role.value', 'owner');
                    @endphp

                    {{-- Mobile: Collapsible summary --}}
                    <details class="lg:hidden group rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <summary class="flex items-center justify-between cursor-pointer p-4 font-medium">
                            <span>Review your info</span>
                            <flux:icon name="chevron-down" class="w-5 h-5 transition-transform group-open:rotate-180" />
                        </summary>
                        <div class="p-4 pt-0">
                            <x-ui.info-list>
                                <x-ui.info-list.item label="Document">
                                    {{ $documentType->name }}
                                </x-ui.info-list.item>
                                <x-ui.info-list.item label="Property">
                                    {{ $project->jobsiteAddressLine() }}
                                </x-ui.info-list.item>
                                <x-ui.info-list.item label="Amount Claimed">
                                    ${{ number_format($amount_claimed, 2) }}
                                </x-ui.info-list.item>
                                <x-ui.info-list.item label="Owner">
                                    {{ $ownerParty?->displayName() ?? '—' }}
                                </x-ui.info-list.item>
                                <x-ui.info-list.item label="Claimant">
                                    {{ $claimantInfo['company_name'] ?? '—' }}
                                </x-ui.info-list.item>
                            </x-ui.info-list>
                        </div>
                    </details>

                    {{-- Desktop: Sticky sidebar --}}
                    <div class="hidden lg:block lg:sticky lg:top-4">
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                                <h3 class="font-semibold">Order Summary</h3>
                            </div>
                            <div class="p-4 space-y-3">
                                <div class="text-sm">
                                    <span class="text-zinc-500">Document</span>
                                    <div class="font-medium">{{ $documentType->name }}</div>
                                </div>

                                <div class="text-sm">
                                    <span class="text-zinc-500">Property</span>
                                    <div class="font-medium">{{ $project->jobsiteAddressLine() }}</div>
                                </div>

                                <div class="text-sm">
                                    <span class="text-zinc-500">Amount Claimed</span>
                                    <div class="font-medium">${{ number_format($amount_claimed, 2) }}</div>
                                </div>

                                <div class="border-t border-zinc-200 dark:border-zinc-700 pt-3 text-sm">
                                    <span class="text-zinc-500">Owner</span>
                                    <div class="font-medium">{{ $ownerParty?->displayName() ?? '—' }}</div>
                                </div>

                                <div class="text-sm">
                                    <span class="text-zinc-500">Claimant</span>
                                    <div class="font-medium">{{ $claimantInfo['company_name'] ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-ui.card>

    {{-- Navigation --}}
    <div class="flex justify-between">
        <div>
            @if($step > 1)
                <flux:button wire:click="previousStep" variant="ghost">
                    Back
                </flux:button>
            @endif
        </div>

        <div class="flex gap-3">
            @if($step < $totalSteps)
                <flux:button wire:click="nextStep" variant="primary" :disabled="$step === 2 && !$ownerParty">
                    Continue
                </flux:button>
            @else
                <flux:button wire:click="proceedToCheckout" variant="primary">
                    Proceed to Payment
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Party Modal --}}
    <flux:modal wire:model="showPartyModal" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading>{{ $editingPartyId ? 'Edit Party' : 'Add Party' }}</flux:heading>

            <form wire:submit="saveParty" class="space-y-4">
                <flux:field>
                    <flux:label>Role *</flux:label>
                    <flux:select wire:model="partyRole">
                        @foreach($partyRoles as $roleOption)
                            <option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="partyRole" />
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Name *</flux:label>
                        <flux:input wire:model="partyName" placeholder="John Smith" />
                        <flux:error name="partyName" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Company Name</flux:label>
                        <flux:input wire:model="partyCompanyName" placeholder="ABC Construction" />
                        <flux:error name="partyCompanyName" />
                    </flux:field>
                </div>

                <flux:separator />

                @if($partyRole === 'owner' && $ownerAddressSource === 'copied_from_jobsite')
                    {{-- Read-only address display when copied from jobsite --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <flux:label>Address</flux:label>
                            <flux:button type="button" wire:click="enableManualAddressEdit" size="sm" variant="ghost" icon="pencil" class="border border-zinc-200 dark:border-zinc-600">
                                Edit Address
                            </flux:button>
                        </div>
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <div class="text-sm text-zinc-700 dark:text-zinc-300">
                                <div>{{ $partyAddress1 }}</div>
                                @if($partyAddress2)
                                    <div>{{ $partyAddress2 }}</div>
                                @endif
                                <div>{{ $partyCity }}, {{ $partyState }} {{ $partyZip }}</div>
                            </div>
                            <div class="mt-2 text-xs text-zinc-500 flex items-center gap-1">
                                <flux:icon name="map-pin" class="w-3 h-3" />
                                Copied from jobsite address
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Editable address fields --}}
                    <flux:field>
                        <div class="flex items-center justify-between">
                            <flux:label>Address</flux:label>
                            @if($partyRole === 'owner' && !$ownerAddressSource)
                                <flux:button type="button" wire:click="useJobsiteAddress" size="sm" variant="primary" icon="map-pin">
                                    Use Jobsite Address
                                </flux:button>
                            @endif
                        </div>
                        <flux:input wire:model="partyAddress1" placeholder="Street address" />
                        <flux:error name="partyAddress1" />
                    </flux:field>

                    <flux:field>
                        <flux:input wire:model="partyAddress2" placeholder="Suite, unit, etc." />
                        <flux:error name="partyAddress2" />
                    </flux:field>

                    <flux:field>
                        <flux:label>City</flux:label>
                        <flux:input wire:model="partyCity" />
                        <flux:error name="partyCity" />
                    </flux:field>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>State</flux:label>
                            <flux:input wire:model="partyState" maxlength="2" placeholder="CA" />
                            <flux:error name="partyState" />
                        </flux:field>

                        <flux:field>
                            <flux:label>ZIP</flux:label>
                            <flux:input wire:model="partyZip" />
                            <flux:error name="partyZip" />
                        </flux:field>
                    </div>
                @endif

                <flux:separator />

                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input type="email" wire:model="partyEmail" />
                    <flux:error name="partyEmail" />
                </flux:field>

                <flux:field>
                    <flux:label>Phone</flux:label>
                    <flux:input wire:model="partyPhone" />
                    <flux:error name="partyPhone" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button type="button" wire:click="closePartyModal" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingPartyId ? 'Save Changes' : 'Add Party' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

</div>
