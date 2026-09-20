@props(['heading' => 'Do more with Waiver Pro', 'waiver' => null])

@php
    $monthly = '$'.number_format(config('lien_waivers.prices.monthly.amount_cents', 4900) / 100);
    $yearly = '$'.number_format(config('lien_waivers.prices.yearly.amount_cents', 49000) / 100);

    // The waiver the user was acting on rides along, so checkout can bring
    // them straight back to it to sign.
    $subscribeUrl = route('lien.waivers.subscribe', array_filter(['waiver' => $waiver]));
@endphp

<div {{ $attributes->class(['space-y-4']) }}>
    <div>
        <flux:heading size="lg">{{ $heading }}</flux:heading>
        <flux:text class="mt-1 text-sm text-zinc-500">
            Creating and downloading waivers is always free. Pro lets you sign and send them online.
        </flux:text>
    </div>

    <ul class="space-y-2">
        @foreach ([
            'Sign your own waivers online',
            'Collect signatures from subs and vendors',
            'Automatic reminders until they sign',
            'Signed copies saved to your project',
            'Unlimited waivers',
        ] as $feature)
            <li class="flex items-start gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                <flux:icon name="check-circle" class="mt-0.5 size-4 shrink-0 text-green-600 dark:text-green-400" />
                <span>{{ $feature }}</span>
            </li>
        @endforeach
    </ul>

    <p class="text-sm font-medium text-zinc-900 dark:text-white">
        {{ $monthly }}/month or {{ $yearly }}/year per seat <span class="font-normal text-zinc-500">(2 months free). Cancel anytime.</span>
    </p>

    <flux:button href="{{ $subscribeUrl }}" variant="primary" class="w-full">
        Upgrade now
    </flux:button>
</div>
