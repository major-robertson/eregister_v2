@php
    $app = $state->application;
    $formTypeLabel = $app && \App\Domains\Forms\FormTypeConfig::exists($app->form_type)
        ? \App\Domains\Forms\FormTypeConfig::get($app->form_type)['name']
        : ($app ? \Illuminate\Support\Str::headline((string) $app->form_type) : null);
    $siblings = $app?->states ?? collect();
    $stateConfig = config('states', []);
    $stateName = $stateConfig[$state->state_code] ?? $state->state_code;
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:button
                :href="route('admin.sales-tax.board')"
                variant="subtle"
                icon="arrow-left"
                size="sm"
                wire:navigate
            >
                Back to Board
            </flux:button>
            <flux:heading size="xl" class="mt-3">
                {{ $app?->business?->name ?? 'Unknown Business' }}
                <span class="text-text-secondary">— {{ $stateName }}</span>
            </flux:heading>
            <div class="mt-1 flex flex-wrap items-center gap-2">
                @if ($formTypeLabel)
                    <flux:badge size="sm" color="zinc">{{ $formTypeLabel }}</flux:badge>
                @endif
                <flux:badge size="sm" color="emerald">{{ $state->state_code }}</flux:badge>
                <flux:badge size="sm" color="{{ $state->current_admin_status->color() }}">
                    {{ $state->current_admin_status->label() }}
                </flux:badge>
            </div>
        </div>
    </div>

    @if (session('success'))
        <flux:callout color="green" icon="check-circle">
            {{ session('success') }}
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left column: business + customer + application data, transitions --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Business + customer info --}}
            <div class="rounded-xl border border-border bg-white p-6">
                <flux:heading size="md">Customer</flux:heading>
                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-text-secondary">Business</dt>
                        <dd class="mt-1 text-sm font-medium text-text-primary">
                            @if ($app?->business)
                                <a href="{{ route('admin.businesses.show', $app->business) }}"
                                    class="text-blue-600 hover:underline" wire:navigate>
                                    {{ $app->business->name }}
                                </a>
                            @else
                                Unknown
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-text-secondary">Created By</dt>
                        <dd class="mt-1 text-sm font-medium text-text-primary">
                            @if ($app?->createdBy)
                                {{ $app->createdBy->name }} <span class="text-text-secondary">&middot; {{ $app->createdBy->email }}</span>
                            @else
                                Unknown
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-text-secondary">Paid</dt>
                        <dd class="mt-1 text-sm text-text-primary">
                            {{ $app?->paid_at?->format('M j, Y g:ia') ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-text-secondary">Submitted by Customer</dt>
                        <dd class="mt-1 text-sm text-text-primary">
                            {{ $app?->submitted_at?->format('M j, Y g:ia') ?? '—' }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- All states for this application --}}
            @if ($siblings->count() > 1)
                <div class="rounded-xl border border-border bg-white p-6">
                    <flux:heading size="md">Application States ({{ $siblings->count() }})</flux:heading>
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        @foreach ($siblings as $sibling)
                            @php $isCurrent = $sibling->id === $state->id; @endphp
                            <a href="{{ route('admin.sales-tax.states.show', $sibling) }}"
                                class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-xs transition {{ $isCurrent ? 'border-blue-400 bg-blue-50' : 'border-border bg-white hover:border-blue-300' }}"
                                wire:navigate>
                                <span class="font-medium">{{ $sibling->state_code }}</span>
                                <flux:badge color="{{ $sibling->current_admin_status->color() }}" size="sm">
                                    {{ $sibling->current_admin_status->label() }}
                                </flux:badge>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Application core data summary --}}
            @if (! empty($app?->core_data))
                <div class="rounded-xl border border-border bg-white p-6">
                    <flux:heading size="md">Application Data</flux:heading>
                    <dl class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach ($app->core_data as $key => $value)
                            @if (! is_array($value) && $value !== null && $value !== '')
                                <div>
                                    <dt class="text-xs uppercase tracking-wide text-text-secondary">
                                        {{ \Illuminate\Support\Str::headline((string) $key) }}
                                    </dt>
                                    <dd class="mt-1 text-sm text-text-primary break-words">
                                        {{ is_bool($value) ? ($value ? 'Yes' : 'No') : (string) $value }}
                                    </dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </div>
            @endif

            {{-- Transitions audit log --}}
            <div class="rounded-xl border border-border bg-white p-6">
                <flux:heading size="md">Status History</flux:heading>
                @if ($this->transitions->isEmpty())
                    <flux:text class="mt-3 text-sm text-text-secondary">
                        No status changes yet. The card has been in {{ $state->current_admin_status->label() }} since the application was paid.
                    </flux:text>
                @else
                    <ol class="mt-4 space-y-4">
                        @foreach ($this->transitions as $transition)
                            <li class="flex gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-100">
                                    <flux:icon name="{{ $transition->to_status->icon() }}" class="size-4 text-zinc-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if ($transition->from_status)
                                            <flux:badge color="{{ $transition->from_status->color() }}" size="sm">
                                                {{ $transition->from_status->label() }}
                                            </flux:badge>
                                            <flux:icon name="arrow-right" class="size-3 text-text-secondary" />
                                        @endif
                                        <flux:badge color="{{ $transition->to_status->color() }}" size="sm">
                                            {{ $transition->to_status->label() }}
                                        </flux:badge>
                                    </div>
                                    <flux:text class="mt-1 text-xs text-text-secondary">
                                        {{ $transition->changedBy?->name ?? 'System' }}
                                        &middot; {{ $transition->created_at->format('M j, Y g:ia') }}
                                        ({{ $transition->created_at->diffForHumans() }})
                                    </flux:text>
                                    @if ($transition->comment)
                                        <flux:text class="mt-2 text-sm text-text-primary">
                                            {{ $transition->comment }}
                                        </flux:text>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>

        {{-- Right column: status change panel --}}
        <div>
            <div class="rounded-xl border border-border bg-white p-6">
                <flux:heading size="md">Change Status</flux:heading>

                @can('tax.change_status')
                    @if (empty($this->allowedTransitions))
                        <flux:text class="mt-3 text-sm text-text-secondary">
                            {{ $state->current_admin_status->label() }} is a terminal status. No further transitions allowed.
                        </flux:text>
                    @else
                        <form wire:submit="changeStatus" class="mt-4 space-y-4">
                            <flux:select wire:model="newStatus" label="New Status" placeholder="Select a status...">
                                @foreach ($this->allowedTransitions as $allowed)
                                    <flux:select.option value="{{ $allowed->value }}">
                                        {{ $allowed->label() }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:textarea
                                wire:model="comment"
                                label="Comment (optional)"
                                rows="3"
                                placeholder="Add context for this transition..."
                            />

                            <flux:button type="submit" variant="primary" class="w-full">
                                Change Status
                            </flux:button>
                        </form>
                    @endif
                @else
                    <flux:text class="mt-3 text-sm text-text-secondary">
                        You don't have permission to change status. Contact an admin.
                    </flux:text>
                @endcan
            </div>
        </div>
    </div>
</div>
