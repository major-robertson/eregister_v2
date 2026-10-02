{{-- Links to every sales tax registration state page (/sales-tax-registration/{state}). --}}
@php($states = \App\Domains\SalesTax\Seo\SalesTaxStatePage::availableStates())
<section {{ $attributes->merge(['class' => 'bg-white py-24']) }}>
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Sales tax registration by state</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">Each state names the registration differently and sets its own fee, timing and filing rules. Pick yours to see what applies.</p>
        </div>
        <ul class="mt-12 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($states as $name)
            <li>
                <a href="{{ route('sales-tax-registration.state', ['state' => \App\Support\Seo\States::slug($name)]) }}" class="flex h-full items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 py-3 text-center text-sm font-medium text-zinc-900 transition hover:border-zinc-300 hover:text-emerald-700 hover:shadow-sm">{{ $name }} sales tax registration</a>
            </li>
            @endforeach
        </ul>
    </div>
</section>
