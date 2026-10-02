{{-- Related links for the per-state notice of intent and lien release pages:
     the state's other lien pages, the lien services, and the same page for
     the bordering states that have one. Vars: $page (LienVariantStatePage),
     $variantRoute (this page's route name), $variantLabel ("notice of intent"). --}}
@php
    /** @var \App\Domains\Lien\Seo\LienVariantStatePage $page */
    $name = $page->name;
    $isNotice = $page instanceof \App\Domains\Lien\Seo\NoticeOfIntentStatePage;
@endphp
<section class="bg-white py-16">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900">More {{ $name }} lien tools</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="{{ route('liens.state', ['state' => $page->slug]) }}" class="text-zinc-700 underline hover:text-zinc-900">{{ $name }} mechanics lien deadlines and filing rules</a></li>
                    @if ($isNotice)
                    <li><a href="{{ route('liens.lien-release.state', ['state' => $page->slug]) }}" class="text-zinc-700 underline hover:text-zinc-900">{{ $name }} mechanics lien release</a></li>
                    @else
                    <li><a href="{{ route('liens.notice-of-intent-to-lien.state', ['state' => $page->slug]) }}" class="text-zinc-700 underline hover:text-zinc-900">{{ $name }} notice of intent to lien</a></li>
                    @endif
                    <li><a href="{{ route('liens.lien-waivers.state', ['state' => strtolower($page->code)]) }}" class="text-zinc-700 underline hover:text-zinc-900">Free {{ $name }} lien waiver forms</a></li>
                    <li><a href="{{ route('liens.deadline-calculator') }}" class="text-zinc-700 underline hover:text-zinc-900">Mechanics lien deadline calculator</a></li>
                    <li><a href="{{ route('liens.notice-of-intent-to-lien') }}" class="text-zinc-700 underline hover:text-zinc-900">Notice of intent to lien service</a></li>
                    <li><a href="{{ route('liens.lien-release') }}" class="text-zinc-700 underline hover:text-zinc-900">Lien release service</a></li>
                    <li><a href="{{ route('liens.payment-demand-letter') }}" class="text-zinc-700 underline hover:text-zinc-900">Payment demand letter</a></li>
                    <li><a href="{{ route('liens') }}" class="text-zinc-700 underline hover:text-zinc-900">Mechanics lien rules for every state</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-zinc-900">Bordering states</h2>
                <ul class="mt-4 flex flex-wrap gap-2">
                    @foreach ($page->nearby() as $nearbyName)
                    <li>
                        <a href="{{ route($variantRoute, ['state' => \App\Support\Seo\States::slug($nearbyName)]) }}" class="inline-flex rounded-full border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:border-zinc-300 hover:bg-zinc-50">
                            {{ $nearbyName }} {{ $variantLabel }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <p class="mt-10 text-xs text-zinc-500">
            This page summarizes {{ $name }} law for general information and is not legal advice. Notice and release rules depend on your role, project type and dates. Confirm them against the statute or with counsel before relying on them.
        </p>
    </div>
</section>
