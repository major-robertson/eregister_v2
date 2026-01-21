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
            $steps = ['Project', 'Parties', 'Details', 'Service', 'Review'];
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
                <span class="ml-2 text-sm {{ $isActive ? 'font-medium' : 'text-zinc-500' }}">{{ $label }}</span>
                @if($index < count($steps) - 1)
                    <div class="flex-1 h-px bg-zinc-200 dark:bg-zinc-700 mx-4"></div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Step Content --}}
    <x-ui.card>
        @if($step === 1)
            {{-- Step 1: Project Confirmation --}}
            <x-slot:header>Confirm Project & Jurisdiction</x-slot:header>

            <x-ui.info-list>
                <x-ui.info-list.item label="Project">
                    {{ $project->name }}
                </x-ui.info-list.item>
                <x-ui.info-list.item label="Jobsite Address">
                    {{ $project->jobsiteAddressLine() }}
                </x-ui.info-list.item>
                <x-ui.info-list.item label="State">
                    {{ $project->jobsite_state }}
                </x-ui.info-list.item>
                <x-ui.info-list.item label="County">
                    {{ $project->jobsite_county ?? 'Not specified' }}
                </x-ui.info-list.item>
                <x-ui.info-list.item label="Document Type">
                    {{ $documentType->name }}
                </x-ui.info-list.item>
            </x-ui.info-list>

            @if($deadline->isPlaceholder())
                <flux:callout color="amber" icon="exclamation-triangle" class="mt-4">
                    Deadline rules for {{ $project->jobsite_state }} are estimates. Please verify requirements.
                </flux:callout>
            @endif

        @elseif($step === 2)
            {{-- Step 2: Parties --}}
            <x-slot:header>Confirm Parties</x-slot:header>

            @if($parties->isEmpty())
                <flux:callout color="amber" icon="exclamation-triangle">
                    No parties have been added to this project. Please add at least the property owner.
                </flux:callout>
                <div class="mt-4">
                    <flux:button href="{{ route('lien.projects.show', $project) }}" variant="primary">
                        Add Parties
                    </flux:button>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($parties as $party)
                        <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ $party->displayName() }}</span>
                                        <flux:badge>{{ $party->role->label() }}</flux:badge>
                                    </div>
                                    @if($party->addressLine())
                                        <div class="text-sm text-zinc-500 mt-1">{{ $party->addressLine() }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if(!$parties->where('role.value', 'owner')->count())
                    <flux:callout color="amber" icon="exclamation-triangle" class="mt-4">
                        No property owner has been added. This is typically required for lien filings.
                    </flux:callout>
                @endif
            @endif

        @elseif($step === 3)
            {{-- Step 3: Filing Details --}}
            <x-slot:header>Filing Details</x-slot:header>

            <div class="space-y-4">
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
            </div>

        @elseif($step === 4)
            {{-- Step 4: Service Choice --}}
            <x-slot:header>Choose Your Service Level</x-slot:header>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($serviceLevels as $level)
                    <label
                        @class([
                            'relative flex flex-col p-6 border-2 rounded-xl cursor-pointer transition',
                            'border-blue-500 bg-blue-50 dark:bg-blue-900/20' => $service_level === $level->value,
                            'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300' => $service_level !== $level->value,
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
                            </ul>
                        @endif
                    </label>
                @endforeach
            </div>

        @elseif($step === 5)
            {{-- Step 5: Review --}}
            <x-slot:header>Review & Submit</x-slot:header>

            <div class="space-y-6">
                <x-ui.info-list>
                    <x-ui.info-list.item label="Document Type">
                        {{ $documentType->name }}
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="Project">
                        {{ $project->name }}
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="Amount Claimed">
                        ${{ number_format($amount_claimed, 2) }}
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="Service Level">
                        {{ $service_level === 'full_service' ? 'Full Service' : 'Self-Serve' }}
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="Total">
                        <span class="text-lg font-bold">
                            ${{ number_format($pricing[$service_level] / 100, 2) }}
                        </span>
                    </x-ui.info-list.item>
                </x-ui.info-list>

                <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    <flux:checkbox wire:model="disclaimerAccepted">
                        I understand that this service provides document preparation assistance only and
                        does not constitute legal advice. I have verified that the information provided
                        is accurate and that I am authorized to file this document.
                    </flux:checkbox>
                    <flux:error name="disclaimerAccepted" />
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
                <flux:button wire:click="nextStep" variant="primary">
                    Continue
                </flux:button>
            @else
                <flux:button wire:click="proceedToCheckout" variant="primary">
                    Proceed to Payment
                </flux:button>
            @endif
        </div>
    </div>
</div>
