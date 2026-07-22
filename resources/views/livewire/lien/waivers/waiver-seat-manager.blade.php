<div class="mx-auto max-w-2xl space-y-6">
    <x-ui.page-header title="Lien Waiver Seats">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Waivers', 'url' => route('lien.waivers.index')],
                ['label' => 'Seats'],
            ]" />
        </x-slot:breadcrumbs>
    </x-ui.page-header>

    @if ($onGracePeriod)
        <flux:callout color="amber" icon="clock">
            <flux:callout.heading>Subscription ends {{ $endsAt?->format('M j, Y') }}</flux:callout.heading>
            <flux:callout.text>
                Cancellation is scheduled — every seat keeps working until then, and nothing further
                is billed.{{ $canManageBilling ? ' Changed your mind?' : '' }}
            </flux:callout.text>
            @if ($canManageBilling)
                <flux:callout.link href="#" wire:click.prevent="resumeSubscription">Resume subscription</flux:callout.link>
            @endif
        </flux:callout>
    @endif

    <x-ui.card>
        <x-slot:header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg font-bold text-text-primary">Who has a seat?</h2>
                    <p class="mt-0.5 text-sm text-text-secondary">
                        Seat holders get unlimited waivers; everyone else shares the free allowance.
                        {{ $canManageSeats ? '' : 'You can manage your own seat here; an owner or admin manages the rest of the team.' }}
                    </p>
                </div>
                <flux:badge>{{ $assignedSeats }} {{ Str::plural('seat', $assignedSeats) }}</flux:badge>
            </div>
        </x-slot:header>

        <div class="space-y-2">
            @foreach ($members as $member)
                @php
                    $hasSeat = $member->pivot->lien_waiver_seat_at !== null;
                    $isSelf = $member->id === auth()->id();
                    $canManageRow = $canManageSeats || $isSelf;
                @endphp
                <div class="flex items-center gap-3 rounded-xl border border-border p-3.5 {{ $hasSeat ? 'bg-green-50/40' : 'bg-white' }}">
                    <flux:icon :name="$hasSeat ? 'check-badge' : 'user'" class="size-5 shrink-0 {{ $hasSeat ? 'text-green-600' : 'text-zinc-400' }}" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-text-primary">
                            {{ $member->name }}@if ($isSelf) <span class="font-normal text-zinc-400">(you)</span>@endif
                        </p>
                        <p class="truncate text-xs text-text-secondary">{{ $member->email }} &bull; {{ ucfirst($member->pivot->role) }}</p>
                    </div>

                    @if ($hasSeat)
                        @if ($canManageSeats && $seatlessMembers->isNotEmpty())
                            {{-- Reassign (admin/owner) moves the seat without touching the bill. --}}
                            <flux:dropdown>
                                <flux:button size="sm" variant="ghost" icon-trailing="chevron-down">Reassign</flux:button>
                                <flux:menu>
                                    @foreach ($seatlessMembers as $target)
                                        <flux:menu.item wire:click="reassign({{ $member->id }}, {{ $target->id }})">
                                            To {{ $target->name }}
                                        </flux:menu.item>
                                    @endforeach
                                </flux:menu>
                            </flux:dropdown>
                        @endif
                        @if ($canManageRow)
                            <flux:button wire:click="confirmRelease({{ $member->id }})" size="sm" variant="ghost">
                                Remove seat
                            </flux:button>
                        @endif
                    @elseif ($canManageRow)
                        <flux:button wire:click="confirmAssign({{ $member->id }})" size="sm" variant="primary">
                            {{ $isSelf ? 'Get a seat' : 'Assign seat' }}
                        </flux:button>
                    @endif
                </div>
            @endforeach
        </div>

        <p class="mt-4 text-xs text-zinc-500">
            Adding a seat bills the prorated difference to your card on file; removing one credits the
            unused time to your next invoice; reassigning moves a seat without billing anything. Team
            members are managed on your business settings page.
        </p>
    </x-ui.card>

    @if ($canManageBilling && ! $onGracePeriod)
        <x-ui.card class="border-red-100">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-text-primary">Cancel subscription</h3>
                    <p class="mt-0.5 text-sm text-text-secondary">
                        Seats keep working until the end of the period you've paid for; nothing further is billed.
                    </p>
                </div>
                <flux:button wire:click="$set('showCancelModal', true)" variant="danger" size="sm">
                    Cancel subscription
                </flux:button>
            </div>
        </x-ui.card>
    @endif

    {{-- Assign confirm: spells out the prorated per-seat charge. --}}
    <flux:modal wire:model="assignUserId" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">Add a seat{{ $assignTarget ? ' for '.$assignTarget->name : '' }}?</flux:heading>
            <flux:text class="text-sm text-zinc-600">
                This adds one seat to your Lien Waiver Pro subscription at
                <span class="font-semibold text-zinc-900">{{ $perSeatPrice['formatted'] }}/{{ $perSeatPrice['per_label'] }}</span>,
                prorated for the rest of the current billing period and charged to the card on file.
                Unlimited waivers start right away.
            </flux:text>
            <div class="flex justify-end gap-3">
                <flux:button wire:click="$set('assignUserId', null)" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="assign" variant="primary">Add seat &middot; {{ $perSeatPrice['formatted'] }}/{{ $perSeatPrice['per_label'] }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Remove confirm. --}}
    <flux:modal wire:model="releaseUserId" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">Remove {{ $releaseTarget ? $releaseTarget->name."'s" : 'this' }} seat?</flux:heading>
            <flux:text class="text-sm text-zinc-600">
                {{ $releaseTarget && $releaseTarget->id === auth()->id() ? 'You' : ($releaseTarget?->name ?? 'They') }}
                will move back to the free tier (3 waivers/month, shared with the team). The unused time
                on this seat credits your next invoice.
            </flux:text>
            <div class="flex justify-end gap-3">
                <flux:button wire:click="$set('releaseUserId', null)" variant="ghost">Keep seat</flux:button>
                <flux:button wire:click="release" variant="danger">Remove seat</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Cancel-subscription confirm. --}}
    <flux:modal wire:model="showCancelModal" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">Cancel Lien Waiver Pro?</flux:heading>
            <flux:text class="text-sm text-zinc-600">
                Every seat keeps working until the end of the period you've already paid for — nothing
                further is billed. After that, the whole team moves to the free tier. You can resume any
                time before it ends.
            </flux:text>
            <div class="flex justify-end gap-3">
                <flux:button wire:click="$set('showCancelModal', false)" variant="ghost">Keep subscription</flux:button>
                <flux:button wire:click="cancelSubscription" variant="danger">Cancel subscription</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
