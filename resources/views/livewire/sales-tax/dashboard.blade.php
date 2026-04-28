<div class="mx-auto max-w-6xl px-6 py-10">
    <x-ui.page-header title="Sales Tax" subtitle="Register for sales and use tax permits across one or more states.">
        <x-slot:actions>
            <flux:button
                href="{{ route('forms.start', $this->workspace->formType) }}"
                variant="primary"
                icon="plus"
                wire:navigate
            >
                Start New Registration
            </flux:button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('success'))
        <x-ui.card class="mb-8 border-success/20 bg-success/5">
            <div class="flex items-center gap-3 text-success">
                <flux:icon name="check-circle" class="size-5" />
                {{ session('success') }}
            </div>
        </x-ui.card>
    @endif

    {{-- Past Registrations --}}
    <section class="mb-12">
        <h2 class="mb-6 text-lg font-semibold text-text-primary">Your Registrations</h2>

        @if ($this->registrations->isEmpty())
            <div class="rounded-xl border border-border bg-white px-6 py-16 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500/10">
                    <flux:icon name="receipt-percent" class="size-7 text-emerald-600" />
                </div>
                <p class="font-medium text-text-primary">No sales tax registrations yet</p>
                <p class="mt-1 text-sm text-text-secondary">
                    Register for sales tax permits across one or more states.
                </p>
                <div class="mt-5">
                    <flux:button
                        href="{{ route('forms.start', $this->workspace->formType) }}"
                        variant="primary"
                        icon="plus"
                        wire:navigate
                    >
                        Start New Registration
                    </flux:button>
                </div>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($this->registrations->take(10) as $registration)
                    <div class="flex items-center justify-between rounded-xl border border-border bg-white px-6 py-5 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10">
                                <flux:icon name="document-text" class="size-5 text-emerald-600" />
                            </div>
                            <div>
                                <div class="font-medium text-text-primary">
                                    Sales &amp; Use Tax Permit
                                </div>
                                <div class="mt-0.5 text-sm text-text-secondary">
                                    @php($stateCount = count($registration->selected_states ?? []))
                                    {{ $stateCount }} {{ \Illuminate\Support\Str::plural('state', $stateCount) }}
                                    @if ($stateCount > 0)
                                        &middot; {{ implode(', ', array_slice($registration->selected_states, 0, 5)) }}{{ $stateCount > 5 ? '…' : '' }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            @if ($registration->display_status === 'Submitted')
                                <flux:badge color="green" size="sm">{{ $registration->display_status }}</flux:badge>
                            @elseif ($registration->display_status === 'Paid')
                                <flux:badge color="blue" size="sm">{{ $registration->display_status }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ $registration->display_status }}</flux:badge>
                            @endif

                            <flux:button
                                href="{{ route('forms.application', $registration) }}"
                                size="sm"
                                :variant="$registration->is_editable ? 'filled' : 'ghost'"
                                wire:navigate
                            >
                                {{ $registration->dashboard_action_label }}
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($this->hasMoreRegistrations)
                <p class="mt-4 text-sm text-text-secondary">
                    Showing the 10 most recent registrations.
                </p>
            @endif
        @endif
    </section>

    {{-- Coming soon: monthly filings --}}
    <section>
        <div class="rounded-xl border border-border bg-emerald-50/40 px-6 py-5">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10">
                    <flux:icon name="calendar-days" class="size-5 text-emerald-600" />
                </div>
                <div>
                    <div class="font-medium text-text-primary">Coming soon: monthly filing support</div>
                    <p class="mt-1 text-sm text-text-secondary">
                        File and remit your monthly sales tax returns directly from this dashboard.
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>
