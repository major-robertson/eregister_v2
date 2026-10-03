@extends('layouts.government')

@php
    // Researched and verified 2026-10-03 (SEO Phase E, EREG-14). Primary sources only.
    $updated = '2026-10-03';
    $headline = 'Florida Government Website Accessibility and Procurement';
    $pageTitle = 'Florida Government Website Accessibility Rules';
    $description = 'How Florida\'s accessibility law, the ADA Title II deadlines, state term contracts and Florida\'s public records and posting rules shape agency websites.';
    $sources = [
        ['name' => 'Fla. Stat. 282.601 (accessibility of electronic information and information technology)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/282.601'],
        ['name' => 'Fla. Stat. 282.602 (definitions, including "state agency")', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/282.602'],
        ['name' => 'Fla. Stat. 282.603 (state agencies must conform to Section 508 standards)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/282.603'],
        ['name' => 'Fla. Stat. 282.604 (DMS rulemaking on accessible technology)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/282.604'],
        ['name' => 'Fla. Admin. Code R. 60-8.002 (standards applicable to electronic and information technology)', 'url' => 'https://flrules.org/gateway/RuleNo.asp?id=60-8.002'],
        ['name' => 'Fla. Stat. 282.0051 (Florida Digital Service powers and duties)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/282.0051'],
        ['name' => '28 CFR part 35, subpart H (DOJ Title II web accessibility rule, current text)', 'url' => 'https://www.ecfr.gov/current/title-28/chapter-I/part-35/subpart-H'],
        ['name' => 'DOJ interim final rule extending the compliance dates, 91 FR 20902 (April 20, 2026)', 'url' => 'https://www.federalregister.gov/documents/2026/04/20/2026-07663/extension-of-compliance-dates-for-nondiscrimination-on-the-basis-of-disability-accessibility-of-web'],
        ['name' => 'Fla. Stat. 287.012 (definitions of "agency" and "eligible user")', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/287.012'],
        ['name' => 'Fla. Stat. 287.017 (purchasing categories and threshold amounts)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/287.017'],
        ['name' => 'Fla. Stat. 287.056 (purchases from state term contracts; requests for quote)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/287.056'],
        ['name' => 'Fla. Stat. 287.057 (competitive solicitation above Category Two)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/287.057'],
        ['name' => 'Fla. Admin. Code R. 60A-1.001 (definitions, including eligible users)', 'url' => 'https://flrules.org/gateway/RuleNo.asp?id=60A-1.001'],
        ['name' => 'Florida DMS, MyFloridaMarketPlace', 'url' => 'https://www.dms.myflorida.com/business_operations/state_purchasing/myfloridamarketplace'],
        ['name' => 'Florida DMS, State Term Contracts', 'url' => 'https://www.dms.myflorida.com/business_operations/state_purchasing/state_contracts_and_agreements/state_term_contracts'],
        ['name' => 'Fla. Stat. 119.01 (general state policy on public records)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/119.01'],
        ['name' => 'Fla. Stat. 257.36 (records retention schedules)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/257.36'],
        ['name' => 'Florida Department of State, General Records Schedules (GS1-SL for state and local government agencies)', 'url' => 'https://dos.fl.gov/library-archives/records-management/general-records-schedules/'],
        ['name' => 'Fla. Stat. 286.011 (public meetings; reasonable notice)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/286.011'],
        ['name' => 'Fla. Stat. 50.0311 (legal notices on a publicly accessible website)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/50.0311'],
        ['name' => 'Fla. Stat. 166.241 (municipal budget website posting)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/166.241'],
        ['name' => 'Fla. Stat. 129.03 (county budget website posting)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/129.03'],
        ['name' => 'Fla. Stat. 200.065 (millage and budget hearings; school district advertising)', 'url' => 'https://www.flsenate.gov/Laws/Statutes/2026/200.065'],
    ];
    $faq = [
        ['q' => 'Does Florida have its own website accessibility law for cities and counties?', 'a' => 'Florida\'s accessibility law, Part II of Chapter 282, is written for state agencies in the executive, legislative and judicial branches. It requires them to meet Section 508 standards. We found no Florida statute that sets a separate web standard for cities, counties or school districts. They are covered by federal ADA Title II and its WCAG 2.1 AA rule. Confirm with your counsel.'],
        ['q' => 'When must a Florida county website meet ADA Title II?', 'a' => 'It depends on the county\'s total population from the most recent Census. A county of 50,000 or more must comply by April 26, 2027. A county under 50,000 has until April 26, 2028. Special districts have until April 26, 2028 regardless of size. These are the dates after the Justice Department\'s April 2026 extension.'],
        ['q' => 'Can a Florida city buy website services from a state term contract?', 'a' => 'Florida law lets eligible users buy from state term contracts, and the DMS rule lists counties, cities, towns, districts and school districts as eligible users. Whether a contract covers website work depends on what it was procured for. Check the contract\'s scope on the DMS State Term Contracts page and your own purchasing rules.'],
        ['q' => 'Do budget PDFs a Florida city posts online need to be accessible?', 'a' => 'Under the federal rule, PDFs posted after your compliance date must meet WCAG 2.1 AA unless an exception applies. The exception for older documents only covers files posted before that date. Florida requires cities and counties to post budgets online in PDF or a similar downloadable format, so each new budget file should be accessible.'],
        ['q' => 'How long must a Florida city keep its budget on its website?', 'a' => 'Section 166.241 says the tentative budget must be posted at least 5 days before the budget hearing and stay up at least 45 days. The final adopted budget must be posted within 30 days after adoption and stay up at least 5 years. Section 129.03 sets the same posting periods for counties.'],
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
                ['name' => 'Florida'],
            ]" />
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-300">State guide</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl">{{ $headline }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-300 sm:text-xl">
                Florida's Section 508 law for state agencies, the Title II dates for local governments, and how agencies buy web work.
            </p>
        </div>
    </div>
</section>

<x-government.article :headline="$headline" :description="$description" path="/government/florida" :updated="$updated" :sources="$sources" :faq="$faq">
    <p>Florida's own accessibility law covers state agencies and requires them to follow federal Section 508 standards. Florida's cities, counties, school districts and special districts fall under the federal ADA Title II web rule, which requires WCAG 2.1 Level AA by April 26, 2027 or April 26, 2028, depending on population. Florida's public records, meeting notice and budget posting laws then decide much of what those sites must publish.</p>
    <h2>Florida's accessibility law covers state agencies</h2>
    <p>Part II of Chapter 282 of the Florida Statutes, sections 282.601 to 282.606, deals with accessible electronic information and technology. Section 282.603 requires each state agency to develop, buy, maintain and use technology that conforms to Section 508 of the Rehabilitation Act and its federal regulations at 36 CFR part 1194, unless that would be an undue burden. If it would, the agency must still give people the information another way. Section 282.602 defines &quot;state agency&quot; as an agency of the executive, legislative or judicial branch of state government. &quot;Internet websites&quot; are named in its definition of covered technology.</p>
    <p>Section 282.604 tells the Department of Management Services (DMS) to adopt rules on accessible technology. DMS did so in Florida Administrative Code chapter 60-8. Rule 60-8.002, last amended in 2017, lists technical standards for software and for web pages. Those web standards follow the older Section 508 checklist. They do not mention WCAG.</p>
    <p>The Florida Digital Service sits inside DMS. Under section 282.0051, it develops and publishes IT policy and enterprise architecture for state agencies. We found no Florida Digital Service accessibility standard that applies to local governments.</p>
    <h2>How Florida law and the Title II deadline fit together</h2>
    <p>The state's own agencies are also public entities under ADA Title II. The State of Florida's total population is far above 50,000, so its agencies face the April 26, 2027 date. They should plan to meet both Section 508, under Florida law, and WCAG 2.1 AA, under the federal rule.</p>
    <p>For local governments, Title II sets the requirement and the date. Each entity's date depends on its own total population:</p>
    <ul>
    <li><strong>Cities and counties</strong> of 50,000 or more: April 26, 2027. Under 50,000: April 26, 2028.</li>
    <li><strong>School districts</strong> use the Census Bureau's school district estimates to find their population, then follow the same split.</li>
    <li><strong>Special district governments</strong>: April 26, 2028, whatever their size. An independent water, fire or community development district may qualify, but check it against the federal definition first.</li>
    </ul>
    <p>Both dates are still ahead as of October 2026. See the <a href="/government/accessibility">ADA Title II deadline guide</a> for the full rule and its exceptions.</p>
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
                    <th scope="row">Department of Management Services</th>
                    <td>Runs state purchasing, MyFloridaMarketPlace and state term contracts; adopts the chapter 60-8 accessibility rules</td>
                </tr>
                <tr>
                    <th scope="row">Florida Digital Service (within DMS)</th>
                    <td>Sets IT policy and enterprise architecture for state agencies</td>
                </tr>
                <tr>
                    <th scope="row">Department of State, Division of Library and Information Services</th>
                    <td>Sets records retention schedules, including GS1-SL for state and local government agencies</td>
                </tr>
                <tr>
                    <th scope="row">U.S. Department of Justice</th>
                    <td>Issues and enforces the ADA Title II web rule</td>
                </tr>
            </tbody>
        </table>
    </div>
    <h2>How Florida agencies buy website work</h2>
    <p><strong>State agencies.</strong> Chapter 287 governs purchasing by executive branch agencies. Section 287.057 requires a competitive solicitation, meaning an invitation to bid, request for proposals or invitation to negotiate, for purchases above the Category Two threshold. Section 287.017 sets Category Two at $35,000.</p>
    <p><strong>MyFloridaMarketPlace.</strong> DMS describes MyFloridaMarketPlace (MFMP) as the State of Florida's eProcurement system, the source for centralized purchasing between vendors and state government entities.</p>
    <p><strong>State term contracts.</strong> Under section 287.056, state agencies must, and eligible users may, buy from state term contracts that DMS procures. Eligible users can also send a request for quote to contract vendors to look for better prices or terms. DMS rule 60A-1.001 lists political subdivisions, including counties, cities, towns, villages and districts, and school districts as eligible users. That is the main route for a Florida local government to piggyback on a state contract. Whether a given contract covers web design or development depends on its scope, so read the contract first.</p>
    <p>Chapter 287's definition of &quot;agency&quot; covers the executive branch, not local governments. Counties, cities and school districts usually buy under their own purchasing rules, so check your local code and your attorney.</p>
    <h2>Public records and posting duties that shape the site</h2>
    <ul>
    <li><strong>Public records.</strong> Section 119.01 says state, county and city records are open to inspection and copying. As agencies rely on electronic records, they must keep reasonable public access to them. An agency may not sign a contract for a public records database that impairs public access. Keep this in mind when a vendor hosts your content.</li>
    <li><strong>Retention.</strong> Under section 257.36(6), public records may be destroyed only under retention schedules set by the Division of Library and Information Services. Its GS1-SL schedule covers state and local government agencies. Plan for this before a migration deletes old pages or files.</li>
    <li><strong>Meeting notice.</strong> Section 286.011, the Sunshine Law, requires reasonable notice of public meetings and prompt minutes.</li>
    <li><strong>Legal notices.</strong> Under section 50.0311, a local government may publish legal notices on a publicly accessible website. Notices must be searchable and show the date first published.</li>
    <li><strong>Budgets.</strong> Sections 166.241 (cities) and 129.03 (counties) require posting the tentative budget at least 5 days before the hearing, for at least 45 days, and the final budget within 30 days after adoption, for at least 5 years. Budgets must be in PDF or a similar downloadable format. Section 200.065 lets school districts advertise their tentative budget on a publicly accessible website.</li>
    </ul>
    <h2>What a Florida redesign has to handle</h2>
    <p>A redesign for a Florida agency has to do several things at once. It must meet WCAG 2.1 AA by your Title II date, and Section 508 too if you are a state agency. It must keep budgets, notices and agendas posted for the periods the statutes set. It must keep records reachable, and it must not delete anything outside a retention schedule. New PDFs, especially budgets, must be accessible from your compliance date on. Old ones can move to a clearly labeled archive if they meet the archive test.</p>
    <p>We found no Florida rule that requires agency websites to be offered in Spanish or another language, so this page does not cover language access. Ask your counsel whether other laws apply to your programs.</p>
    <h2>What to do next</h2>
    <p>Confirm your Title II date, then list every page and file the statutes above require you to post. A <a href="/government/website-redesign">website redesign</a> on an accessible <a href="/government/cms">CMS</a>, with <a href="/government/hosting">U.S.-based hosting</a>, can build those posting duties into the site. eRegister is a private vendor, not a government agency, and can take on this work under your agency's purchasing rules. <a href="/contact">Tell us about your project</a> or read our <a href="/government/capabilities">capabilities statement</a>.</p>
</x-government.article>

@include('pages.government.partials.cta')
@endsection
