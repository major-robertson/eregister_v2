@extends('layouts.landing')

@section('title', 'Terms of Service - ' . config('app.name', 'eRegister'))

@section('content')
<div class="bg-white py-16 lg:py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold tracking-tight text-zinc-900">Terms of Service</h1>
        <p class="mt-4 text-zinc-500">Last updated: February 28, 2026</p>

        <div class="prose prose-zinc mt-12 max-w-none">

            <h2 class="mt-14 font-bold">0. IMPORTANT DISCLAIMERS (PLEASE READ)</h2>
            <p>
                1) WE ARE NOT A LAW FIRM AND DO NOT PROVIDE LEGAL ADVICE. We are not attorneys. Use of
                the Services does not create an attorney-client relationship.
            </p>
            <p>
                2) WE ARE NOT A TAX ADVISOR OR ACCOUNTING FIRM AND DO NOT PROVIDE TAX OR FINANCIAL
                ADVICE.
            </p>
            <p>
                3) WE ARE A PRIVATE COMPANY AND ARE NOT AFFILIATED WITH, ENDORSED BY, OR SPONSORED BY ANY
                GOVERNMENT AGENCY, including but not limited to the IRS, the U.S. Patent and Trademark Office (USPTO), any Secretary of
                State office, any Department of Revenue, any licensing agency, or any court.
            </p>
            <p>
                4) GOVERNMENT FEES ARE SEPARATE. Many Services involve third-party or government fees
                that are not controlled by us and may be non-refundable once submitted.
            </p>

            <h2 class="mt-14 font-bold">1. Agreement to Terms</h2>
            <p>
                These Terms of Service ("Terms") are a binding agreement between you ("you," "your," or "User") and
                eRegister ("we," "us," or "our").
                These Terms govern your access to and use of (a) our website located at https://eregister.com and any
                subdomains (the "Site"), and (b) any products, tools, dashboards,
                document preparation, filing assistance, tracking portals, and related services we provide
                (collectively, the "Services").
            </p>
            <p>
                By accessing or using the Site or Services, you agree to be bound by these Terms and all applicable laws
                and regulations.
                If you do not agree with any of these terms, you are prohibited from using or accessing this site.
            </p>

            <h2 class="mt-14 font-bold">2. Use License</h2>
            <p>
                Permission is granted to temporarily access the materials on eRegister's website for personal,
                non-commercial transitory viewing only.
                This is the grant of a license, not a transfer of title, and under this license you may not:
            </p>
            <ul>
                <li>Modify or copy the materials</li>
                <li>Use the materials for any commercial purpose or for any public display</li>
                <li>Attempt to decompile or reverse engineer any software contained on the website</li>
                <li>Remove any copyright or other proprietary notations from the materials</li>
                <li>Transfer the materials to another person or "mirror" the materials on any other server</li>
            </ul>

            <h2 class="mt-14 font-bold">3. Services Description</h2>
            <p>
                eRegister provides a software-driven workflow and administrative support to help Users prepare and
                submit certain business and compliance filings and related documents ("Filing Services").
                Depending on what you purchase, Services may include business formation filings, sales &amp; use tax
                registration assistance, resale certificate preparation/management, EIN application assistance,
                annual report and renewal tracking and/or filing assistance, payment protection tools (including
                mechanics liens, preliminary notices, notices of intent to lien, lien releases, and demand letters),
                and patent-related administrative services such as provisional patent application preparation/filing
                assistance ("patent pending" service), as well as other related administrative services.
            </p>

            <h3 class="mt-8 font-bold">3.1 What we do NOT do</h3>
            <ul>
                <li>We do not provide legal advice, tax advice, accounting advice, financial advice, investment advice,
                    or insurance advice.</li>
                <li>We do not represent you before any government authority.</li>
                <li>We do not guarantee acceptance, approval, registration, issuance, enforceability, or legal effect of
                    any filing or document.</li>
                <li>We do not guarantee outcomes, including that you will obtain a permit, registration, lien rights,
                    payment, or a patent.</li>
            </ul>

            <h3 class="mt-8 font-bold">3.2 Government processes are outside our control</h3>
            <p>
                Government authorities control their own requirements, forms, rules, processing times, and outcomes, and
                these may change at any time.
                We are not responsible for delays or decisions made by government authorities.
                Your use of the Services is optional and you may complete filings directly with government authorities
                without using eRegister.
            </p>

            <h2 class="mt-14 font-bold">4. User Responsibilities</h2>
            <p>As a user of our Services, you agree to:</p>
            <ul>
                <li>Provide accurate, current, and complete information and keep it updated</li>
                <li>Maintain the confidentiality of your account credentials</li>
                <li>Notify us immediately of any unauthorized use of your account</li>
                <li>Be responsible for all activities that occur under your account</li>
                <li>Review all drafts and final documents for accuracy before submission</li>
                <li>Meet deadlines by providing information and approvals promptly</li>
                <li>Not use the Services for any unlawful, fraudulent, deceptive, or abusive purpose</li>
            </ul>

            <h2 class="mt-14 font-bold">5. Payment Terms</h2>
            <p>
                By using our paid Services, you agree to pay all fees and charges associated with your account on a
                timely basis and with a valid payment method.
                Service fees are separate from government fees. Government fees (and certain
                third-party pass-through costs) are subject to change and are typically non-refundable once
                submitted/paid.
                All fees are non-refundable except as expressly set forth in our Refund Policy.
            </p>

            <h3 class="mt-8 font-bold">5.1 Subscriptions, Auto-Renewal, and Cancellation</h3>
            <p>
                Some Services are offered on a subscription or recurring basis (for example, monthly, annual, or other
                terms). The term and pricing for your subscription will be disclosed at checkout and/or in your account
                dashboard.
            </p>
            <p>
                Auto-renewal: Subscriptions automatically renew unless you cancel
                before the renewal date.
                By purchasing a subscription, you authorize us (and our payment processors) to charge your payment
                method on a recurring basis at the then-current rate disclosed at renewal, unless you cancel.
            </p>
            <p>
                How to cancel: You may cancel through your account dashboard or by emailing
                <a href="mailto:contact@eregister.com">contact@eregister.com</a>.
                Cancellation stops future renewals; it does not retroactively refund amounts already paid unless our
                Refund Policy expressly provides otherwise.
            </p>

            <h2 class="mt-14 font-bold">6. Disclaimer</h2>
            <p>
                The materials on eRegister's website are provided on an 'as is' and 'as available' basis.
                To the maximum extent permitted by law, eRegister makes no warranties, expressed or implied, and hereby
                disclaims and negates all other warranties including, without limitation,
                implied warranties or conditions of merchantability, fitness for a particular purpose, and
                non-infringement of intellectual property or other violation of rights.
                We do not warrant that the Services will be uninterrupted, error-free, or that any filing will be
                approved or achieve a particular result.
            </p>

            <h2 class="mt-14 font-bold">7. Limitations</h2>
            <p>
                In no event shall eRegister or its suppliers be liable for any damages (including, without limitation,
                damages for loss of data or profit, or due to business interruption) arising out of the use or inability
                to use the materials on eRegister's website,
                even if eRegister or an authorized representative has been notified orally or in writing of the
                possibility of such damage.
                To the maximum extent permitted by law, our total liability for all claims related to the Services will
                not exceed the amount of service fees you paid to us for the specific order or service giving rise to
                the claim during the 30 days before the event.
            </p>

            <h2 class="mt-14 font-bold">8. Accuracy of Materials</h2>
            <p>
                The materials appearing on eRegister's website could include technical, typographical, or photographic
                errors.
                eRegister does not warrant that any of the materials on its website are accurate, complete, or current.
                eRegister may make changes to the materials contained on its website at any time without notice.
            </p>

            <h2 class="mt-14 font-bold">9. Links</h2>
            <p>
                eRegister has not reviewed all of the sites linked to its website and is not responsible for the
                contents of any such linked site.
                The inclusion of any link does not imply endorsement by eRegister of the site.
                Use of any such linked website is at the user's own risk.
            </p>

            <h2 class="mt-14 font-bold">10. Modifications</h2>
            <p>
                eRegister may revise these terms of service for its website at any time without notice.
                By using this website you are agreeing to be bound by the then current version of these terms of
                service.
            </p>

            <h2 class="mt-14 font-bold">11. Governing Law; Venue</h2>
            <p>
                These Terms and any dispute arising out of or relating to these Terms or the Services are governed by
                the laws of the Commonwealth of Kentucky,
                without regard to conflict-of-laws principles.
                Any legal action must be brought exclusively in the state or federal courts located in Jefferson
                County, Kentucky, and you consent to personal jurisdiction there.
            </p>

            <h2 class="mt-14 font-bold">12. Contact Information</h2>
            <p>If you have any questions about these Terms of Service, please contact us at:</p>
            <ul>
                <li>Email: <a href="mailto:contact@eregister.com">contact@eregister.com</a></li>
                <li>Address: 4869 Brownsboro Rd Ste 101-R, Louisville, KY 40207</li>
            </ul>

            <h3 class="mt-8 font-bold">12.1 Related Policies</h3>
            <ul>
                <li>Privacy Policy: <a href="https://eregister.com/privacy-policy">https://eregister.com/privacy-policy</a></li>
                <li>Refund Policy: <a href="https://eregister.com/refund-policy">https://eregister.com/refund-policy</a></li>
            </ul>

        </div>
    </div>
</div>
@endsection
