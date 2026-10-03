@extends('layouts.government')

@php
    // Researched and verified 2026-10-03 (SEO Phase E, EREG-14). Primary sources only.
    $updated = '2026-10-03';
    $headline = 'ADA Title II Website Accessibility Deadlines';
    $pageTitle = 'ADA Title II Website Deadlines: 2027 and 2028 Dates';
    $description = 'The DOJ moved the ADA Title II web deadlines to April 26, 2027 and April 26, 2028. See which date applies to your agency and what the rule requires.';
    $sources = [
        ['name' => '28 CFR part 35, subpart H (DOJ Title II web accessibility rule, current text)', 'url' => 'https://www.ecfr.gov/current/title-28/chapter-I/part-35/subpart-H'],
        ['name' => '28 CFR 35.104 (definitions of archived web content, special district government, total population)', 'url' => 'https://www.ecfr.gov/current/title-28/chapter-I/part-35/subpart-A/section-35.104'],
        ['name' => 'DOJ final rule, 89 FR 31320 (April 24, 2024)', 'url' => 'https://www.federalregister.gov/documents/2024/04/24/2024-07758/nondiscrimination-on-the-basis-of-disability-accessibility-of-web-information-and-services-of-state'],
        ['name' => 'DOJ interim final rule extending the compliance dates, 91 FR 20902 (April 20, 2026)', 'url' => 'https://www.federalregister.gov/documents/2026/04/20/2026-07663/extension-of-compliance-dates-for-nondiscrimination-on-the-basis-of-disability-accessibility-of-web'],
        ['name' => 'ADA.gov fact sheet on the Title II web rule (updated for the 2026 interim final rule)', 'url' => 'https://www.ada.gov/resources/2024-03-08-web-rule/'],
        ['name' => 'ADA.gov, First Steps Toward Complying with the Title II Web and Mobile Application Accessibility Rule', 'url' => 'https://www.ada.gov/resources/web-rule-first-steps/'],
        ['name' => 'W3C, WCAG 2.1 (June 5, 2018 Recommendation, the version the rule incorporates)', 'url' => 'https://www.w3.org/TR/2018/REC-WCAG21-20180605/'],
        ['name' => 'W3C, WCAG 2.2', 'url' => 'https://www.w3.org/TR/WCAG22/'],
        ['name' => 'HHS Section 504 final rule, 89 FR 40066 (May 9, 2024)', 'url' => 'https://www.federalregister.gov/documents/2024/05/09/2024-09237/nondiscrimination-on-the-basis-of-disability-in-programs-or-activities-receiving-federal-financial'],
        ['name' => 'HHS interim final rule extending the Section 504 web compliance dates, 91 FR 25496 (May 11, 2026)', 'url' => 'https://www.federalregister.gov/documents/2026/05/11/2026-09266/extension-of-compliance-dates-for-nondiscrimination-on-the-basis-of-disability-accessibility-of-web'],
        ['name' => '45 CFR 84.84 (HHS Section 504 web and mobile accessibility requirements, current text)', 'url' => 'https://www.ecfr.gov/current/title-45/subtitle-A/subchapter-A/part-84/subpart-I/section-84.84'],
        ['name' => 'Section508.gov, IT Accessibility Laws and Policies', 'url' => 'https://www.section508.gov/manage/laws-and-policies/'],
        ['name' => 'U.S. Access Board, ICT accessibility standards (Section 508)', 'url' => 'https://www.access-board.gov/ict/'],
    ];
    $faq = [
        ['q' => 'What is the ADA Title II website compliance deadline?', 'a' => 'As of October 2026, it is April 26, 2027 for state and local governments with a total population of 50,000 or more. It is April 26, 2028 for those under 50,000 and for all special district governments. The Justice Department set these dates in an interim final rule on April 20, 2026. The original dates were April 24, 2026 and April 26, 2027.'],
        ['q' => 'Was the April 24, 2026 deadline cancelled?', 'a' => 'It was moved, not cancelled. The Justice Department pushed it back one year before it arrived. The accessibility requirements, the WCAG 2.1 AA standard and the exceptions did not change. The Department says it may propose changes to the substance later. Until a new rule is published, plan for the 2027 and 2028 dates.'],
        ['q' => 'Does the rule require WCAG 2.1 or WCAG 2.2?', 'a' => 'The rule requires WCAG 2.1, Level A and Level AA, using the version W3C published in June 2018. WCAG 2.2 is newer and is not what the rule names. W3C says content that conforms to WCAG 2.2 also conforms to WCAG 2.1, so building to 2.2 is fine, but still test the one 2.1 criterion that 2.2 removed, 4.1.1 Parsing.'],
        ['q' => 'Do old PDFs on our website have to be fixed?', 'a' => 'Not always. PDFs, word processing files, presentations and spreadsheets that were on your site before your compliance date are exempt, unless people still use them to apply for, access or take part in a service. A permit application form is an example. Documents you post after your compliance date must conform.'],
        ['q' => 'Does Section 508 apply to state and local governments?', 'a' => 'Not by its own terms. Section 508 covers technology that federal agencies develop, buy, maintain or use. State and local governments fall under ADA Title II. Some states adopt Section 508 standards for their own agencies by state law. Florida does this for state agencies in Chapter 282 of the Florida Statutes.'],
        ['q' => 'Does the rule cover websites a vendor runs for us?', 'a' => 'Yes. The rule covers web content and mobile apps your agency provides directly or through contractual, licensing or other arrangements. A hosted payment page, permit portal or agenda system you buy from a vendor is your agency\'s content under the rule. Write the WCAG 2.1 AA requirement into your contracts.'],
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
                ['name' => 'Accessibility'],
            ]" />
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-300">Accessibility guide</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl">{{ $headline }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-300 sm:text-xl">
                Current Title II web compliance dates, the WCAG 2.1 AA standard, the five exceptions, and what to do now.
            </p>
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('contact') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-blue-500">
                    Request an audit
                </a>
                <a href="#dates"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-900/50 px-8 py-4 text-base font-semibold text-white transition hover:bg-slate-800">
                    See the dates
                </a>
            </div>
        </div>
    </div>
</section>

<x-government.article :headline="$headline" :description="$description" path="/government/accessibility" :updated="$updated" :sources="$sources" :faq="$faq">
    <p>The Justice Department's ADA Title II web rule requires state and local government websites and mobile apps to meet WCAG 2.1 Level AA. As of October 2026, the deadline is <strong>April 26, 2027</strong> for governments with a total population of 50,000 or more, and <strong>April 26, 2028</strong> for smaller governments and all special district governments. Both dates are still ahead: an April 2026 interim final rule moved each original date back one year.</p>
    <h2 id="dates">The current compliance dates</h2>
    <p>The rule was published on April 24, 2024, at 89 FR 31320, and added a new subpart H to 28 CFR part 35. On April 20, 2026, four days before the first deadline, the Justice Department published an interim final rule (91 FR 20902) that pushed both compliance dates back one year. It took effect the day it was published. The current text of 28 CFR 35.200(b) carries the new dates, and ADA.gov's fact sheet shows them too.</p>
    <div class="mt-6 overflow-x-auto rounded-2xl border border-zinc-200">
        <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
            <thead>
                <tr>
                    <th scope="col">Your agency</th>
                    <th scope="col">Original date (2024 rule)</th>
                    <th scope="col">Current date (2026 rule)</th>
                    <th scope="col">Status on October 3, 2026</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                <tr>
                    <th scope="row">State or local government with a total population of 50,000 or more</th>
                    <td>April 24, 2026</td>
                    <td>April 26, 2027</td>
                    <td>Ahead. The original date never took effect.</td>
                </tr>
                <tr>
                    <th scope="row">State or local government with a total population under 50,000</th>
                    <td>April 26, 2027</td>
                    <td>April 26, 2028</td>
                    <td>Ahead</td>
                </tr>
                <tr>
                    <th scope="row">Special district government, any size</th>
                    <td>April 26, 2027</td>
                    <td>April 26, 2028</td>
                    <td>Ahead</td>
                </tr>
            </tbody>
        </table>
    </div>
    <p>No compliance date has passed yet. The old April 24, 2026 date, which many web pages still show, no longer applies.</p>
    <p>&quot;Total population&quot; means your Census Bureau population from the most recent decennial Census. Independent school districts use the Census Bureau's Small Area Income and Poverty Estimates instead. A &quot;special district government&quot; is a public entity, other than a county, city, township or independent school district, that state law sets up to perform one function or a few, with enough fiscal and administrative independence to count as a separate government, and whose population the Census does not calculate. The definitions are in 28 CFR 35.104.</p>
    <p>The interim final rule took comments through June 22, 2026. In it, the Department said it will consider proposing changes to the rule's substance during the extension period. As of October 3, 2026, the Federal Register shows no later amendment, proposal or stay. The Department also wrote that public entities have an ongoing duty under Title II to make their online services accessible, whatever the compliance dates say.</p>
    <h2>What the rule requires</h2>
    <p>Your agency must make sure its web content and mobile apps meet the Level A and Level AA success criteria of WCAG 2.1. The rule covers content you provide directly and content you provide &quot;through contractual, licensing, or other arrangements.&quot; A vendor's payment page, permit portal or meeting-agenda tool counts as your content.</p>
    <p>The rule incorporates the June 2018 version of WCAG 2.1. W3C has since published WCAG 2.2, which the rule does not name. W3C says pages that conform to WCAG 2.2 also conform to WCAG 2.1. One caveat: WCAG 2.2 dropped success criterion 4.1.1 (Parsing), so if you build to 2.2, still test 4.1.1.</p>
    <p>The rule allows some flexibility:</p>
    <ul>
    <li><strong>Conforming alternate versions.</strong> You may offer a separate accessible version only when making the content itself accessible is not possible because of technical or legal limits.</li>
    <li><strong>Equivalent facilitation.</strong> You may use another method if it gives substantially equal or greater accessibility.</li>
    <li><strong>Undue burden or fundamental alteration.</strong> You must comply unless you can show that doing so would fundamentally change a program or create undue financial and administrative burdens. Only the head of your agency, or a designee, can make that call, after considering all available resources, and the reasons must be written down. You must still provide as much access as you can.</li>
    <li><strong>Minimal-impact noncompliance.</strong> A small failure that does not keep people with disabilities from getting the same information, interactions and transactions, with substantially equal timeliness, privacy, independence and ease of use, is treated as compliant. This is narrow. Do not plan around it.</li>
    </ul>
    <h2>The five exceptions</h2>
    <p>The rule lists five kinds of content that do not have to meet WCAG 2.1:</p>
    <ol>
    <li><strong>Archived web content.</strong> It must meet all four tests: created before your compliance date (or a copy of older paper or media), kept only for reference, research or recordkeeping, not changed after archiving, and stored in an area clearly labeled as an archive.</li>
    <li><strong>Preexisting conventional electronic documents.</strong> PDFs, word processing files, presentations and spreadsheets posted before your compliance date. The exception does not apply to documents people still use to apply for, gain access to or take part in a service.</li>
    <li><strong>Content posted by a third party.</strong> Public comments on a forum, for example. It does not cover a third party that posts because of a contract or other arrangement with your agency.</li>
    <li><strong>Individualized, password-protected documents.</strong> Documents about a specific person, property or account, like a utility bill, that are secured behind a login.</li>
    <li><strong>Preexisting social media posts.</strong> Posts made before your compliance date.</li>
    </ol>
    <p>The pattern matters: these exceptions protect what is already there. New documents, new posts and new pages after your date must conform.</p>
    <h2>Section 504 and Section 508</h2>
    <p><strong>Section 504 (HHS).</strong> If your agency receives financial assistance from the U.S. Department of Health and Human Services, a separate rule also applies. HHS adopted the same WCAG 2.1 AA standard on May 9, 2024 (89 FR 40066), in 45 CFR 84.84. An HHS interim final rule (91 FR 25496, May 11, 2026) moved its dates to May 11, 2027 for recipients with 15 or more employees and May 10, 2028 for recipients with fewer than 15. These dates are a few weeks off the Title II dates, so track both.</p>
    <p><strong>Section 508.</strong> Section 508 of the Rehabilitation Act applies to federal agencies when they develop, procure, maintain or use information technology. It does not by itself apply to cities, counties or school districts. Some states apply Section 508 standards to their own agencies by state law. See the <a href="/government/florida">Florida page</a> for one example and the <a href="/government/north-carolina">North Carolina page</a> for a state whose standard points to WCAG 2.1 AA.</p>
    <h2>A plan for the months ahead</h2>
    <p>If your date is April 26, 2027, you have less than a year. ADA.gov's First Steps guide suggests this order:</p>
    <ol>
    <li>Name who owns accessibility, and train the staff who publish content.</li>
    <li>Inventory your websites, mobile apps and documents.</li>
    <li>Sort out what falls under an exception and what must conform.</li>
    <li>Test what must conform against WCAG 2.1 AA.</li>
    <li>Fix the most-used and most important services first: payments, applications, permits, meeting agendas and emergency information.</li>
    <li>Review vendor contracts, and require WCAG 2.1 AA in new ones.</li>
    <li>Write an accessibility policy, and keep it current.</li>
    </ol>
    <p>PDFs are often the slowest part. Each budget, agenda packet and form you post after your date has to conform, so fix the template, not just the file.</p>
    <h2>What to do next</h2>
    <p>Check your Census population to confirm your date, then run the inventory. If your site needs more than fixes, a <a href="/government/website-redesign">website redesign</a> on an accessible <a href="/government/cms">CMS</a> can stop editors from publishing new problems. eRegister audits and remediates agency sites to WCAG 2.1 AA and keeps them conformant through <a href="/government/maintenance">ongoing maintenance</a>, so <a href="/contact">tell us about your site</a>. We are a private vendor, not a government agency, and nothing here is legal advice, so confirm your obligations with your counsel.</p>
    <p>See the full list of <a href="/government">government services</a>, including <a href="/government/hosting">hosting</a>, <a href="/government/portals">portals</a>, <a href="/government/integrations">integrations</a>, <a href="/government/implementation">implementation</a> and our <a href="/government/capabilities">capabilities statement</a>.</p>
</x-government.article>

@include('pages.government.partials.cta')
@endsection
