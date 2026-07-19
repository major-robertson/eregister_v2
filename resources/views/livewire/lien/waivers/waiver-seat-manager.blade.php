<div class="mx-auto max-w-2xl space-y-6">
    <x-ui.page-header title="Lien Waiver Seats">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Waivers', 'url' => route('lien.waivers.index')],
                ['label' => 'Seats'],
            ]" />
        </x-slot:breadcrumbs>
    </x-ui.page-header>

    <x-ui.card>
        <x-slot:header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg font-bold text-text-primary">Who has a seat?</h2>
                    <p class="mt-0.5 text-sm text-text-secondary">
                        Seat holders get unlimited waivers; everyone else shares the free allowance.
                    </p>
                </div>
                <flux:badge>{{ $assignedSeats }} {{ Str::plural('seat', $assignedSeats) }}</flux:badge>
            </div>
        </x-slot:header>

        <div class="space-y-2">
            @foreach ($members as $member)
                @php $hasSeat = $member->pivot->lien_waiver_seat_at !== null; @endphp
                <div class="flex items-center gap-3 rounded-xl border border-border p-3.5 {{ $hasSeat ? 'bg-green-50/40' : 'bg-white' }}">
                    <flux:icon :name="$hasSeat ? 'check-badge' : 'user'" class="size-5 shrink-0 {{ $hasSeat ? 'text-green-600' : 'text-zinc-400' }}" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-text-primary">{{ $member->name }}</p>
                        <p class="truncate text-xs text-text-secondary">{{ $member->email }} &bull; {{ ucfirst($member->pivot->role) }}</p>
                    </div>
                    @if ($hasSeat)
                        <flux:button wire:click="release({{ $member->id }})" wire:loading.attr="disabled" size="sm" variant="ghost">
                            Remove seat
                        </flux:button>
                    @else
                        <flux:button wire:click="assign({{ $member->id }})" wire:loading.attr="disabled" size="sm" variant="primary">
                            Assign seat
                        </flux:button>
                    @endif
                </div>
            @endforeach
        </div>

        <p class="mt-4 text-xs text-zinc-500">
            Adding a seat bills the prorated difference to your card on file; removing one credits the
            unused time to your next invoice. To drop the last seat, cancel the subscription from your
            billing settings. Team members are managed on your business settings page.
        </p>
    </x-ui.card>
</div>
