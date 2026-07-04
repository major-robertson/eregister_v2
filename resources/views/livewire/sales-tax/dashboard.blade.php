<div class="mx-auto max-w-6xl px-6 py-10">
    <x-ui.page-header title="Sales Tax" subtitle="Register for sales and use tax permits across one or more states.">
        <x-slot:actions>
            <x-ui.action-button
                href="{{ $this->workspace->startRouteFor($this->workspace->primaryFormType()) }}"
                icon="plus"
                wire:navigate
            >
                Start New Registration
            </x-ui.action-button>
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
                    <x-ui.action-button
                        href="{{ $this->workspace->startRouteFor($this->workspace->primaryFormType()) }}"
                        icon="plus"
                        wire:navigate
                    >
                        Start New Registration
                    </x-ui.action-button>
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

                            @if ($registration->dashboard_action_url)
                                <flux:button
                                    href="{{ $registration->dashboard_action_url }}"
                                    size="sm"
                                    :variant="$registration->is_editable ? 'filled' : 'ghost'"
                                    wire:navigate
                                >
                                    {{ $registration->dashboard_action_label }}
                                </flux:button>
                            @endif
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

    {{-- Free blank resale certificates + Resale Certificate Generator upsell --}}
    @if ($this->blankFormStates !== [])
        <section class="mb-12">
            <h2 class="mb-6 text-lg font-semibold text-text-primary">Resale Certificates</h2>

            <div class="rounded-xl border border-border bg-white px-6 py-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-500/10">
                        <flux:icon name="document-check" class="size-5 text-blue-600" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-medium text-text-primary">Blank resale certificate forms — free with your registration</div>
                        <p class="mt-1 text-sm text-text-secondary">
                            Download the official blank form for any state you registered in, print it, and fill it out by hand.
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($this->blankFormStates as $state)
                                <flux:button
                                    href="{{ route('sales-tax.blank-resale-certificate', $state) }}"
                                    variant="ghost"
                                    size="sm"
                                    icon="arrow-down-tray"
                                >
                                    {{ $state }}
                                </flux:button>
                            @endforeach
                        </div>

                        @unless ($this->hasResaleCertSubscription)
                            <div class="mt-4 rounded-lg bg-blue-500/5 p-4">
                                <p class="text-sm text-text-primary">
                                    <span class="font-medium">Skip the paperwork:</span>
                                    the Resale Certificate Generator fills your certificates automatically —
                                    every vendor, every state, e-signed and tracked for expiration. Unlimited
                                    certificates for $297/year.
                                </p>
                                <div class="mt-3">
                                    <x-ui.action-button href="{{ route('resale-cert.dashboard') }}" wire:navigate>
                                        Generate Certificates Automatically
                                    </x-ui.action-button>
                                </div>
                            </div>
                        @endunless
                    </div>
                </div>
            </div>
        </section>
    @endif
</div>
