@extends('layouts.government')

@php
    // Researched and verified 2026-10-03 (SEO Phase E, EREG-14). Primary sources only.
    $updated = '2026-10-03';
    $headline = 'North Carolina Government Website Accessibility and Procurement';
    $pageTitle = 'North Carolina Government Website Accessibility Rules';
    $description = 'How NCDIT\'s WCAG 2.1 AA standard, the ADA Title II deadlines, G.S. 143-129 bidding and North Carolina\'s records and meeting laws shape agency websites.';
    $sources = [
        ['name' => 'NCDIT, State of North Carolina Digital Accessibility and Usability Standard (version 1.1, January 16, 2025)', 'url' => 'https://it.nc.gov/documents/digital-accessibility-usability-standard/open'],
        ['name' => 'NCDIT, FAQ on partnering with NCDIT for digital accessibility compliance', 'url' => 'https://it.nc.gov/faq-partnering-for-compliance'],
        ['name' => 'NCDIT, Statewide IT Contracts', 'url' => 'https://it.nc.gov/services/statewide-it-contracts'],
        ['name' => 'N.C.G.S. 143B-1320 (definitions, including "state agency" and "local government entity")', 'url' => 'https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_143B/GS_143B-1320.html'],
        ['name' => 'N.C.G.S. 143B-1350 (procurement of information technology)', 'url' => 'https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_143B/GS_143B-1350.html'],
        ['name' => '28 CFR part 35, subpart H (DOJ Title II web accessibility rule, current text)', 'url' => 'https://www.ecfr.gov/current/title-28/chapter-I/part-35/subpart-H'],
        ['name' => 'DOJ interim final rule extending the compliance dates, 91 FR 20902 (April 20, 2026)', 'url' => 'https://www.federalregister.gov/documents/2026/04/20/2026-07663/extension-of-compliance-dates-for-nondiscrimination-on-the-basis-of-disability-accessibility-of-web'],
        ['name' => 'N.C.G.S. 143-129 (formal bidding for public contracts)', 'url' => 'https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_143/GS_143-129.html'],
        ['name' => 'N.C.G.S. 143-131 (informal bids for local governments)', 'url' => 'https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_143/GS_143-131.html'],
        ['name' => 'N.C.G.S. 143-49 (Department of Administration purchasing services for local governments)', 'url' => 'https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_143/GS_143-49.html'],
        ['name' => 'N.C. Department of Administration, Division of Purchase and Contract', 'url' => 'https://www.doa.nc.gov/divisions/purchase-contract'],
        ['name' => 'NC eProcurement', 'url' => 'https://eprocurement.nc.gov/'],
        ['name' => 'N.C.G.S. 132-1 (public records defined)', 'url' => 'https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_132/GS_132-1.html'],
        ['name' => 'N.C.G.S. 132-3 (destruction of records regulated)', 'url' => 'https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_132/GS_132-3.html'],
        ['name' => 'N.C.G.S. 143-318.9 (open meetings public policy)', 'url' => 'https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_143/GS_143-318.9.html'],
        ['name' => 'N.C.G.S. 143-318.12 (public notice of official meetings, including website posting)', 'url' => 'https://www.ncleg.gov/EnactedLegislation/Statutes/HTML/BySection/Chapter_143/GS_143-318.12.html'],
    ];
    $faq = [
        ['q' => 'Does North Carolina have a website accessibility law for local governments?', 'a' => 'We found no North Carolina statute that sets a web accessibility standard for cities, counties or school districts. NCDIT\'s Digital Accessibility and Usability Standard requires WCAG 2.1 AA, but it applies to state agencies. Local governments are covered by federal ADA Title II, which requires the same WCAG 2.1 AA standard. Confirm with your attorney.'],
        ['q' => 'When must a North Carolina city website meet ADA Title II?', 'a' => 'It depends on the city\'s total population from the most recent Census. A city of 50,000 or more must comply by April 26, 2027. A city under 50,000 has until April 26, 2028. Special district governments have until April 26, 2028 regardless of size. These are the dates after the Justice Department\'s April 2026 extension.'],
        ['q' => 'What is the formal bid threshold for North Carolina local governments?', 'a' => 'Under G.S. 143-129, formal bidding applies to construction or repair work of $500,000 or more and to purchases of apparatus, supplies, materials or equipment of $90,000 or more. G.S. 143-131 requires informal bids from $30,000 up to those limits. These sections do not list services by name, so ask your attorney how they apply to a website project.'],
        ['q' => 'Can a North Carolina county use a state IT contract?', 'a' => 'Often, yes. G.S. 143-129(e) exempts purchases of information technology through contracts established by NCDIT, and purchases from state contracts when the vendor extends the same or better terms. NCDIT lists its statewide IT term contracts, including an Accessibility Services contract. Check each contract\'s terms before you buy.'],
        ['q' => 'Does a North Carolina town have to post meeting notices on its website?', 'a' => 'If the town\'s public body has a website and a schedule of regular meetings, G.S. 143-318.12 requires it to post that schedule on the site. If its own employees maintain the website, it must also post notice of special meetings before they happen. Separately, special meeting notices go on the bulletin board and to those who filed a written request, at least 48 hours ahead.'],
    ];
@endphp

@section('title', $pageTitle)
@section('description', $description)
@section('og_type', 'article')

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <x-seo.breadcrumbs class="mb-6 text-slate-400" center :items="[
                ['name' => 'Government', 'url' => route('government.home')],
                ['name' => 'North Carolina'],
            ]" />
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-300">State guide</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl">{{ $headline }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-300 sm:text-xl">
                NCDIT's standard for state agencies, the Title II dates for local governments, and how NC agencies buy web work.
            </p>
        </div>
    </div>
</section>

<x-government.article :headline="$headline" :description="$description" path="/government/north-carolina" :updated="$updated" :sources="$sources" :faq="$faq">
    <p>North Carolina's statewide digital accessibility standard, issued by the Department of Information Technology (NCDIT), requires state agency websites to meet WCAG 2.1 Level AA. North Carolina's cities, counties, school districts and special districts fall under the federal ADA Title II web rule, which requires the same standard by April 26, 2027 or April 26, 2028, depending on population. State bidding, public records and open meetings laws then shape how those sites are bought and what they must post.</p>
    <h2>NCDIT's standard covers state agencies</h2>
    <p>NCDIT's State of North Carolina Digital Accessibility and Usability Standard, version 1.1, is dated January 16, 2025. It applies to state agencies' websites and digital services that are meant for the public, whether the agency runs them or a contractor does. It says all agency websites and digital services must meet WCAG 2.1 Level AA, in line with ADA Title II. It encourages agencies to apply the standard to internal sites too. It does not apply to third-party content posted without a contract or other arrangement with the agency.</p>
    <p>The standard points to N.C.G.S. 143B-1320(a)(17), 143B-1376 and 75-61(10). Section 143B-1320(a)(17) defines &quot;state agency.&quot; That definition leaves out the legislative and judicial branches, the Community Colleges System Office and The University of North Carolina. The same section separately defines a &quot;local government entity&quot; as a city, county, local school administrative unit or community college. We found no North Carolina statute that sets a web accessibility standard for local governments.</p>
    <p>NCDIT's compliance FAQ tells state agencies to comply by April 26, 2027 and to name a Digital Accessibility Coordinator. It also says NCDIT's Digital Commons, a Drupal-based content management system with built-in accessibility standards and monitoring, is free for all state and local agencies.</p>
    <h2>How the NC standard and the Title II deadline fit together</h2>
    <p>For local governments, ADA Title II sets the requirement and the date. Each entity's date depends on its own total population:</p>
    <ul>
    <li><strong>Cities and counties</strong> of 50,000 or more: April 26, 2027. Under 50,000: April 26, 2028.</li>
    <li><strong>School districts</strong> use the Census Bureau's school district estimates to find their population, then follow the same split.</li>
    <li><strong>Special district governments</strong>: April 26, 2028, whatever their size, if they meet the federal definition.</li>
    </ul>
    <p>Both dates are still ahead as of October 2026. State agencies are on the April 26, 2027 date, which matches NCDIT's FAQ. See the <a href="/government/accessibility">ADA Title II deadline guide</a> for the exceptions and the rest of the rule.</p>
    <div class="mt-6 overflow-x-auto rounded-2xl border border-zinc-200">
        <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
            <thead>
                <tr>
                    <th scope="col">Agency</th>
                    <th scope="col">What it does for agency websites</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                <tr>
                    <th scope="row">N.C. Department of Information Technology (NCDIT)</th>
                    <td>Sets the digital accessibility standard for state agencies; buys IT for participating agencies; runs statewide IT term contracts and Digital Commons</td>
                </tr>
                <tr>
                    <th scope="row">Department of Administration, Division of Purchase and Contract</th>
                    <td>Runs state procurement, statewide term contracts and NC eProcurement</td>
                </tr>
                <tr>
                    <th scope="row">Department of Natural and Cultural Resources</th>
                    <td>Gives the consent G.S. 132-3 requires before public records are destroyed</td>
                </tr>
                <tr>
                    <th scope="row">U.S. Department of Justice</th>
                    <td>Issues and enforces the ADA Title II web rule</td>
                </tr>
            </tbody>
        </table>
    </div>
    <h2>How North Carolina agencies buy website work</h2>
    <p><strong>Bidding thresholds.</strong> G.S. 143-129 requires formal bidding for construction or repair work of $500,000 or more, and for purchases of apparatus, supplies, materials or equipment of $90,000 or more. G.S. 143-131 requires local governments to get informal bids from $30,000 up to those limits. These sections do not name professional services. Whether a website project falls under them depends on what you are buying, so check with your attorney and purchasing officer.</p>
    <p><strong>State IT contracts.</strong> Under G.S. 143B-1350, NCDIT procures IT for participating state agencies and approves IT purchases by separate agencies. It must also set procedures that let state agencies and local government entities use federal GSA schedules and other cooperative purchasing agreements. G.S. 143-129(e)(7) exempts purchases of IT made through contracts NCDIT establishes. G.S. 143-129(e)(9) exempts purchases from state contracts when the vendor extends the same or better prices and terms to the local government. NCDIT's Statewide IT Contracts page lists an Accessibility Services contract (8116A), running October 1, 2026 to September 30, 2028, and an IT Services contract (920S).</p>
    <p><strong>Piggybacking.</strong> G.S. 143-129(g) lets a local governing board skip bidding to buy apparatus, supplies, materials or equipment from a vendor that won a similar formal bid with another government within the previous 12 months. The board must approve the purchase at a regular meeting at least 10 days after publishing notice.</p>
    <p><strong>State purchasing.</strong> G.S. 143-49(6) makes the Department of Administration's purchasing services available to counties, cities, towns and local school administrative units. NC eProcurement serves state agencies, community colleges, school systems and local governments.</p>
    <h2>Public records and meeting notice duties</h2>
    <ul>
    <li><strong>Public records.</strong> G.S. 132-1 defines public records to include electronic data-processing records made or received by any state or local agency, including special districts. The people own them, and copies must be free or at minimal cost unless a law says otherwise. Your website content and the files behind it can be public records.</li>
    <li><strong>Retention.</strong> G.S. 132-3 bars a public official from destroying a public record without the consent of the Department of Natural and Cultural Resources, except in accordance with G.S. 130A-99 and G.S. 121-5. Plan for this before a migration deletes old pages or files.</li>
    <li><strong>Open meetings.</strong> G.S. 143-318.9 states the policy that public bodies act openly. Under G.S. 143-318.12, a public body with a website and a regular meeting schedule must post that schedule on the site. If its own employees maintain the site, it must also post notice of special meetings before they happen. Notice of those meetings must also go on the body's bulletin board, and to anyone who filed a written request, at least 48 hours ahead.</li>
    </ul>
    <h2>What a North Carolina redesign has to handle</h2>
    <p>A redesign for a North Carolina agency has to meet WCAG 2.1 AA by your Title II date, which also satisfies NCDIT's standard if you are a state agency. It must post meeting schedules and notices where the statute requires. It must keep public records reachable, and it must not delete anything without lawful authority. New PDFs, such as agenda packets and budgets, must be accessible from your compliance date on. Older files can move to a clearly labeled archive if they meet the archive test.</p>
    <p>We found no North Carolina rule that requires agency websites to be offered in Spanish or another language, so this page does not cover language access. Ask your attorney whether other laws apply to your programs.</p>
    <h2>What to do next</h2>
    <p>Confirm your Title II date, check whether a state IT contract fits your project, and list every page the statutes above require. A <a href="/government/website-redesign">website redesign</a>, <a href="/government/maintenance">ongoing maintenance</a> and a <a href="/government/portals">resident portal</a> can each be scoped to those duties. eRegister is a private vendor, not a government agency, and can take on this work under your agency's purchasing rules. <a href="/contact">Tell us about your project</a> or read our <a href="/government/capabilities">capabilities statement</a>.</p>
</x-government.article>

@include('pages.government.partials.cta')
@endsection
