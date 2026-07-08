@props(['heading' => 'Do more with Waiver Pro'])

@php
    $monthly = '$'.number_format(config('lien_waivers.prices.monthly.amount_cents', 9900) / 100);
    $yearly = '$'.number_format(config('lien_waivers.prices.yearly.amount_cents', 99000) / 100);
@endphp

<div {{ $attributes->class(['space-y-4']) }}>
    <div>
        <flux:heading size="lg">{{ $heading }}</flux:heading>
        <flux:text class="mt-1 text-sm text-zinc-500">
            Generating and downloading waivers is always free. Upgrade to run the whole exchange in one place.
        </flux:text>
    </div>

    <ul class="space-y-2">
        @foreach ([
            'Unlimited saved waivers on every project',
            'E-sign — send waivers and collect signatures',
            'Automatic signer reminders until it\'s signed',
            'Signed-copy storage with a full audit trail',
        ] as $feature)
            <li class="flex items-start gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                <flux:icon name="check-circle" class="mt-0.5 size-4 shrink-0 text-green-600 dark:text-green-400" />
                <span>{{ $feature }}</span>
            </li>
        @endforeach
    </ul>

    <p class="text-sm font-medium text-zinc-900 dark:text-white">
        {{ $monthly }}/month or {{ $yearly }}/year <span class="font-normal text-zinc-500">(2 months free)</span>
    </p>

    <flux:button href="{{ route('lien.waivers.subscribe') }}" variant="primary" class="w-full">
        Upgrade now
    </flux:button>
</div>
