<div class="mx-auto max-w-6xl px-6 py-10">
    <x-ui.page-header title="Formations" subtitle="Form your business and manage your formation documents.">
        <x-slot:actions>
            <flux:button
                href="{{ $this->workspace->startRouteFor('llc') }}"
                variant="primary"
                icon="plus"
                wire:navigate
            >
                Form an LLC
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

    {{-- Past Formations --}}
    <section class="mb-12">
        <h2 class="mb-6 text-lg font-semibold text-text-primary">Your Formations</h2>

        @if ($this->formations->isEmpty())
            <div class="rounded-xl border border-border bg-white px-6 py-16 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-indigo-500/10">
                    <flux:icon name="building-office-2" class="size-7 text-indigo-600" />
                </div>
                <p class="font-medium text-text-primary">No formations yet</p>
                <p class="mt-1 text-sm text-text-secondary">
                    Get started by forming your LLC.
                </p>
                <div class="mt-5">
                    <flux:button
                        href="{{ $this->workspace->startRouteFor('llc') }}"
                        variant="primary"
                        icon="plus"
                        wire:navigate
                    >
                        Form an LLC
                    </flux:button>
                </div>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($this->formations->take(10) as $formation)
                    @php
                        // Pull the user-facing label from config/form_types.php
                        // so adding a new formation type is a config-only change.
                        // Falls back to a humanized form_type if the type isn't
                        // registered (defensive — shouldn't happen given the
                        // workspace's form_types constraint).
                        $typeLabel = \App\Domains\Forms\FormTypeConfig::exists($formation->form_type)
                            ? \App\Domains\Forms\FormTypeConfig::get($formation->form_type)['name']
                            : \Illuminate\Support\Str::headline((string) $formation->form_type);
                    @endphp
                    <div class="flex items-center justify-between rounded-xl border border-border bg-white px-6 py-5 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-500/10">
                                <flux:icon name="document-text" class="size-5 text-indigo-600" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2 font-medium text-text-primary">
                                    {{ $typeLabel }}
                                    <flux:badge color="indigo" size="sm">{{ strtoupper($formation->form_type) }}</flux:badge>
                                </div>
                                <div class="mt-0.5 text-sm text-text-secondary">
                                    @php($stateCount = count($formation->selected_states ?? []))
                                    {{ $stateCount }} {{ \Illuminate\Support\Str::plural('state', $stateCount) }}
                                    @if ($stateCount > 0)
                                        &middot; {{ implode(', ', array_slice($formation->selected_states, 0, 5)) }}{{ $stateCount > 5 ? '…' : '' }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            @if ($formation->display_status === 'Submitted')
                                <flux:badge color="green" size="sm">{{ $formation->display_status }}</flux:badge>
                            @elseif ($formation->display_status === 'Paid')
                                <flux:badge color="blue" size="sm">{{ $formation->display_status }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ $formation->display_status }}</flux:badge>
                            @endif

                            @if ($formation->dashboard_action_url)
                                <flux:button
                                    href="{{ $formation->dashboard_action_url }}"
                                    size="sm"
                                    :variant="$formation->is_editable ? 'filled' : 'ghost'"
                                    wire:navigate
                                >
                                    {{ $formation->dashboard_action_label }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($this->hasMoreFormations)
                <p class="mt-4 text-sm text-text-secondary">
                    Showing the 10 most recent formations.
                </p>
            @endif
        @endif
    </section>

    {{-- Coming soon: more formation types --}}
    <section>
        <div class="rounded-xl border border-border bg-indigo-50/40 px-6 py-5">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-500/10">
                    <flux:icon name="sparkles" class="size-5 text-indigo-600" />
                </div>
                <div>
                    <div class="font-medium text-text-primary">Coming soon</div>
                    <p class="mt-1 text-sm text-text-secondary">
                        Additional formation types will be available here as they roll out.
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>
