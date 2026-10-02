{{-- "More lien tools" box for the lien service pages: the other lien
     services (minus the current page) plus the state rule pages. --}}
@php
    $services = collect([
        'liens.deadline-calculator' => 'Mechanics lien deadline calculator',
        'liens.preliminary-notice' => 'Preliminary notice service',
        'liens.notice-of-intent-to-lien' => 'Notice of intent to lien',
        'liens.payment-demand-letter' => 'Payment demand letter',
        'liens.lien-release' => 'Lien release',
        'liens.lien-waivers' => 'Free lien waiver forms',
        'liens.pricing' => 'Lien filing pricing',
    ])->reject(fn ($label, $name) => request()->routeIs($name));
@endphp
<section {{ $attributes->merge(['class' => 'bg-white py-16']) }}>
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900">More lien tools</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($services as $name => $label)
                    <li><a href="{{ route($name) }}" class="text-zinc-700 underline hover:text-zinc-900">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-zinc-900">Lien rules by state</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="{{ route('liens') }}" class="text-zinc-700 underline hover:text-zinc-900">Mechanics lien rules for every state</a></li>
                    @foreach (['texas' => 'Texas', 'california' => 'California', 'florida' => 'Florida'] as $slug => $state)
                    <li><a href="{{ route('liens.state', ['state' => $slug]) }}" class="text-zinc-700 underline hover:text-zinc-900">{{ $state }} mechanics lien deadlines</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
