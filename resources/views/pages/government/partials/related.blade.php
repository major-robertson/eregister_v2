@php
    $allServices = [
        'website-redesign' => ['title' => 'Website Redesign', 'description' => 'Modern, accessible redesigns of legacy agency websites.', 'route' => 'government.website-redesign'],
        'accessibility' => ['title' => 'Accessibility', 'description' => 'WCAG 2.2 AA, Section 508, and ADA audits and remediation.', 'route' => 'government.accessibility'],
        'cms' => ['title' => 'Content Management', 'description' => 'Editor-friendly CMS with workflows and role-based publishing.', 'route' => 'government.cms'],
        'hosting' => ['title' => 'Hosting & Infrastructure', 'description' => 'Hardened, U.S.-based hosting with monitoring and SLAs.', 'route' => 'government.hosting'],
        'maintenance' => ['title' => 'Maintenance & Support', 'description' => 'Patches, updates, and incident response on contract.', 'route' => 'government.maintenance'],
        'portals' => ['title' => 'Citizen & Staff Portals', 'description' => 'Self-service portals for permits, payments, and staff workflows.', 'route' => 'government.portals'],
        'integrations' => ['title' => 'System Integrations', 'description' => 'Connect legacy systems, GIS, and payment processors.', 'route' => 'government.integrations'],
        'implementation' => ['title' => 'Implementation Services', 'description' => 'Procurement-friendly project delivery from kickoff to go-live.', 'route' => 'government.implementation'],
    ];

    $relatedKeys = array_values(array_diff(array_keys($allServices), [$exclude ?? '']));
    shuffle($relatedKeys);
    $related = array_slice($relatedKeys, 0, 3);
@endphp

<section class="bg-slate-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Related Services</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Often paired with this engagement
            </h2>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($related as $key)
                @php($service = $allServices[$key])
                <a href="{{ route($service['route']) }}"
                    class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-lg">
                    <h3 class="text-lg font-semibold text-slate-900">{{ $service['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $service['description'] }}</p>
                    <span
                        class="mt-4 inline-flex items-center text-sm font-semibold text-blue-700 transition group-hover:text-blue-800">
                        Learn more
                        <svg class="ml-1 h-4 w-4 transition group-hover:translate-x-0.5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
