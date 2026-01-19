<div
    class="mx-auto max-w-4xl px-4 py-12"
    x-data="{
        selected: $wire.entangle('selectedStates'),
        blocked: @js($blockedStates),
        toggle(code) {
            if (this.blocked.includes(code)) return;
            if ({{ $stateMode === 'single' ? 'true' : 'false' }}) {
                this.selected = [code];
            } else {
                if (this.selected.includes(code)) {
                    this.selected = this.selected.filter(s => s !== code);
                } else if (this.selected.length < {{ $maxStates }}) {
                    this.selected = [...this.selected, code];
                }
            }
        }
    }"
>
    <flux:heading size="xl" class="mb-2 text-center">
        {{ $stateMode === 'single' ? 'Select State' : 'Select States' }}
    </flux:heading>
    <p class="mb-8 text-center text-zinc-600 dark:text-zinc-400">
        @if ($stateMode === 'single')
            Choose the state for your {{ $formTypeName }}
        @else
            Choose the states where you need a {{ $formTypeName }} (max {{ $maxStates }})
        @endif
    </p>

    @if ($hasExistingDraft)
        <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 p-4 dark:border-yellow-700 dark:bg-yellow-900/20">
            <p class="font-medium text-yellow-800 dark:text-yellow-200">
                You have an existing draft with {{ $existingDraftStateCount }} state{{ $existingDraftStateCount !== 1 ? 's' : '' }} selected.
            </p>
            <div class="mt-3 flex gap-3">
                <flux:button wire:click="resumeExisting" variant="primary" size="sm">Resume Draft</flux:button>
                <flux:button wire:click="startOver" variant="ghost" size="sm">Start Over</flux:button>
            </div>
        </div>
    @endif

    @if (count($blockedStates) > 0)
        <div class="mb-6 rounded-lg border border-zinc-300 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                <flux:icon name="information-circle" class="mr-1 inline size-4" />
                {{ count($blockedStates) }} state{{ count($blockedStates) !== 1 ? 's' : '' }} already
                {{ count($blockedStates) !== 1 ? 'have' : 'has' }} an active application and cannot be selected again.
            </p>
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <div class="text-sm text-zinc-600 dark:text-zinc-400">
            <span x-text="selected.length"></span> state<span x-show="selected.length !== 1">s</span> selected
        </div>
        @if ($stateMode === 'multi')
            <div class="flex gap-2">
                <flux:button wire:click="selectAll" size="sm" variant="ghost">Select All</flux:button>
                <flux:button wire:click="clearAll" size="sm" variant="ghost">Clear All</flux:button>
            </div>
        @endif
    </div>

    <div class="mb-8 grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
        @foreach ($states as $code => $name)
            @if (in_array($code, $availableStates))
                @php
                    $isBlocked = in_array($code, $blockedStates);
                @endphp
                <button
                    wire:key="state-{{ $code }}"
                    x-on:click="toggle('{{ $code }}')"
                    type="button"
                    {{ $isBlocked ? 'disabled' : '' }}
                    @if ($isBlocked) title="Already applied" @endif
                    :class="selected.includes('{{ $code }}')
                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                        : 'border-zinc-200 bg-white hover:border-blue-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-blue-600'"
                    @class([
                        'rounded-lg border p-3 text-left transition',
                        'cursor-not-allowed !border-zinc-200 !bg-zinc-100 opacity-50 dark:!border-zinc-700 dark:!bg-zinc-900' => $isBlocked,
                    ])
                >
                    <div class="flex items-center gap-2">
                        @if ($stateMode === 'single')
                            {{-- Radio style --}}
                            <div
                                :class="selected.includes('{{ $code }}')
                                    ? 'border-blue-500 bg-blue-500'
                                    : 'border-zinc-300 dark:border-zinc-600'"
                                @class([
                                    'flex h-5 w-5 shrink-0 items-center justify-center rounded-full border',
                                    '!border-zinc-300 dark:!border-zinc-600' => $isBlocked,
                                ])
                            >
                                <div x-show="selected.includes('{{ $code }}')" x-cloak class="h-2 w-2 rounded-full bg-white"></div>
                            </div>
                        @else
                            {{-- Checkbox style --}}
                            <div
                                :class="selected.includes('{{ $code }}')
                                    ? 'border-blue-500 bg-blue-500 text-white'
                                    : 'border-zinc-300 dark:border-zinc-600'"
                                @class([
                                    'flex h-5 w-5 shrink-0 items-center justify-center rounded border',
                                    '!border-zinc-300 dark:!border-zinc-600' => $isBlocked,
                                ])
                            >
                                @if ($isBlocked)
                                    <flux:icon name="x-mark" class="size-3 text-zinc-400" />
                                @else
                                    <flux:icon x-show="selected.includes('{{ $code }}')" x-cloak name="check" class="size-3" />
                                @endif
                            </div>
                        @endif
                        <div>
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $code }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $name }}
                                @if ($isBlocked)
                                    <span class="text-yellow-600 dark:text-yellow-400">(Applied)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </button>
            @endif
        @endforeach
    </div>

    @error('selectedStates')
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-red-700 dark:bg-red-900/20 dark:text-red-400">
            {{ $message }}
        </div>
    @enderror

    <div class="flex justify-center">
        <flux:button wire:click="proceed" variant="primary" x-bind:disabled="selected.length === 0">
            Continue to Checkout (<span x-text="selected.length"></span> state<span x-show="selected.length !== 1">s</span>)
        </flux:button>
    </div>
</div>
