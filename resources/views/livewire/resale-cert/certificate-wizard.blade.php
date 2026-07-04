<div class="mx-auto max-w-4xl space-y-6">
    <x-ui.page-header title="Generate Certificates">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Certificates', 'url' => route('resale-cert.certificates.index')],
                ['label' => 'Generate'],
            ]" />
        </x-slot:breadcrumbs>
    </x-ui.page-header>

    @if ($step === 1)
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-ui.card>
                    <x-slot:header>
                        <flux:heading size="lg">1. Choose a vendor</flux:heading>
                    </x-slot:header>

                    @if ($this->vendors->isEmpty())
                        <div class="py-6 text-center">
                            <flux:text class="text-zinc-500">You haven't added any vendors yet.</flux:text>
                            <flux:button href="{{ route('resale-cert.vendors.create') }}" variant="primary" class="mt-3" wire:navigate>
                                Add a vendor first
                            </flux:button>
                        </div>
                    @else
                        <flux:field>
                            <flux:label>Vendor *</flux:label>
                            <flux:select variant="combobox" clearable placeholder="Select vendor..." wire:model="vendorId">
                                @foreach ($this->vendors as $vendor)
                                    <flux:select.option value="{{ $vendor->id }}">{{ $vendor->legal_name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="vendorId" />
                        </flux:field>
                    @endif
                </x-ui.card>

                <x-ui.card>
                    <x-slot:header>
                        <flux:heading size="lg">2. Select states to cover</flux:heading>
                    </x-slot:header>

                    <flux:error name="selectedStates" />

                    <div class="grid grid-cols-1 gap-1 sm:grid-cols-2">
                        @foreach ($this->stateOptions as $option)
                            <label
                                wire:key="state-{{ $option['code'] }}"
                                @class([
                                    'flex items-center gap-2 rounded-lg px-3 py-2 text-sm',
                                    'cursor-pointer hover:bg-zinc-50' => $option['selectable'],
                                    'cursor-not-allowed opacity-50' => ! $option['selectable'],
                                ])
                            >
                                <input
                                    type="checkbox"
                                    value="{{ $option['code'] }}"
                                    wire:click="toggleState('{{ $option['code'] }}')"
                                    @checked(in_array($option['code'], $selectedStates, true))
                                    @disabled(! $option['selectable'])
                                    class="h-4 w-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="flex-1 text-text-primary">{{ $option['name'] }}</span>
                                @if ($option['registered'])
                                    <flux:badge color="green" size="sm">Registered</flux:badge>
                                @elseif (! $option['selectable'])
                                    <flux:icon name="lock-closed" class="size-3.5 text-zinc-400" title="{{ $option['reason'] }}" />
                                @endif
                            </label>
                        @endforeach
                    </div>

                    <flux:text class="mt-3 text-xs text-zinc-500">
                        Locked states require a tax registration —
                        <a href="{{ route('resale-cert.settings') }}" class="underline" wire:navigate>add one in Settings</a>.
                    </flux:text>
                </x-ui.card>
            </div>

            <div>
                <x-ui.card>
                    <x-slot:header>
                        <flux:heading size="lg">Selection</flux:heading>
                    </x-slot:header>

                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">States selected</span>
                            <flux:badge color="blue" size="sm">{{ count($selectedStates) }}</flux:badge>
                        </div>
                        @if ($selectedStates !== [])
                            <flux:text class="text-zinc-600">{{ implode(', ', $selectedStates) }}</flux:text>
                        @endif
                    </div>

                    <div class="mt-6">
                        <flux:button wire:click="continueToReview" variant="primary" class="w-full"
                            :disabled="$vendorId === '' || $selectedStates === []">
                            Continue to Review
                        </flux:button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-ui.card>
                    <x-slot:header>
                        <flux:heading size="lg">Minimum Required Forms</flux:heading>
                    </x-slot:header>

                    <flux:text class="mb-4 text-sm text-zinc-500">
                        The fewest forms that cover your selected states — uniform SST/MTC certificates
                        are preferred over individual state forms.
                    </flux:text>

                    <div class="space-y-2">
                        @foreach ($minimumForms as $form)
                            <label class="flex items-start gap-3 rounded-lg border border-border p-3" wire:key="min-{{ $form['state_code'] }}">
                                <input type="checkbox"
                                    wire:model.live="checkedForms.{{ $form['state_code'] }}"
                                    class="mt-0.5 h-4 w-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500" />
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-text-primary">{{ $form['name'] }}</span>
                                        @if ($form['type'] === 'uniform')
                                            <flux:badge color="blue" size="sm">Uniform</flux:badge>
                                        @endif
                                    </div>
                                    <flux:text class="text-sm text-zinc-500">
                                        Covers: {{ implode(', ', $form['covers_states']) }}
                                    </flux:text>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </x-ui.card>

                @if ($optionalForms !== [])
                    <x-ui.card>
                        <x-slot:header>
                            <flux:heading size="lg">Optional Individual Forms</flux:heading>
                        </x-slot:header>

                        <flux:text class="mb-4 text-sm text-zinc-500">
                            These states are already covered by a uniform form, but you can also issue
                            their individual state form for vendors that insist on it.
                        </flux:text>

                        <div class="space-y-2">
                            @foreach ($optionalForms as $form)
                                <label class="flex items-start gap-3 rounded-lg border border-border p-3" wire:key="opt-{{ $form['state_code'] }}">
                                    <input type="checkbox"
                                        wire:model.live="checkedForms.{{ $form['state_code'] }}"
                                        class="mt-0.5 h-4 w-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500" />
                                    <div class="flex-1">
                                        <span class="font-medium text-text-primary">{{ $form['name'] }}</span>
                                        <flux:text class="text-sm text-zinc-500">
                                            Also covered by {{ $form['covered_by'] }}
                                        </flux:text>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </x-ui.card>
                @endif
            </div>

            <div>
                <x-ui.card>
                    <x-slot:header>
                        <flux:heading size="lg">Summary</flux:heading>
                    </x-slot:header>

                    @php $checkedCount = count(array_filter($checkedForms)); @endphp

                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">States to cover</span>
                            <span>{{ count($selectedStates) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Forms to generate</span>
                            <span>{{ $checkedCount }}</span>
                        </div>
                    </div>

                    @if ($this->uncoveredStates !== [])
                        <flux:callout variant="danger" icon="exclamation-triangle" class="mt-4">
                            <flux:callout.heading>Missing coverage</flux:callout.heading>
                            <flux:callout.text>
                                No selected form covers: {{ implode(', ', $this->uncoveredStates) }}
                            </flux:callout.text>
                        </flux:callout>
                    @endif

                    <flux:error name="generate" />

                    <div class="mt-6 space-y-2">
                        <flux:button wire:click="generate" variant="primary" class="w-full"
                            :disabled="$this->uncoveredStates !== [] || $checkedCount === 0">
                            <span wire:loading.remove wire:target="generate">Generate {{ $checkedCount }} Certificate(s)</span>
                            <span wire:loading wire:target="generate">Generating PDFs...</span>
                        </flux:button>
                        <flux:button wire:click="backToSelection" variant="ghost" class="w-full">Back</flux:button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    @endif
</div>
