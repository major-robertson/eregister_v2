@extends('layouts.landing')

@section('title', 'Refund Policy - ' . config('app.name', 'eRegister'))

@section('content')
<div class="bg-white py-16 lg:py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold tracking-tight text-zinc-900">Refund Policy</h1>
        <p class="mt-4 text-zinc-500">Last updated: February 28, 2026</p>

        <div class="prose prose-zinc mt-12 max-w-none">

            <h2 class="mt-14 font-bold">IMPORTANT SUMMARY (READ THIS FIRST)</h2>
            <ul>
                <li>Government / state / county filing fees, taxes, shipping, mailing, courier, recording fees,
                    publication fees, and other third-party charges are NON-REFUNDABLE once incurred.</li>
                <li>Our service fees are generally NON-REFUNDABLE because work begins quickly and our
                    services are often time-sensitive.</li>
                <li>If we have not started work and have not incurred third-party costs, you may request cancellation
                    within 1 hour of purchase. After that, all sales are final except where required by
                    law or as we decide in writing.</li>
            </ul>

            <h2 class="mt-14 font-bold">1. Definitions</h2>
            <p>
                This Refund Policy ("Policy") applies to purchases made through https://eregister.com
                and any related checkout pages, applications, or dashboards (collectively, the "Platform"). This Policy
                is part of, and incorporated into, our Terms of Service.
            </p>
            <p>
                In this Policy, "we," "us," and "our" refers to eRegister.
            </p>
            <p>
                "Order" means a purchase for any paid service offered on the Platform, including (without limitation)
                business filings and compliance services, tax registrations, document preparation, and construction
                payment protection services (e.g., notices, liens, releases, and demand letters).
            </p>
            <p>
                "Service Fee" means the fee charged by {{ config('app.name', 'eRegister') }} for time, preparation,
                review, processing, and administration.
            </p>
            <p>
                "Government Fees" means fees charged by federal/state/county agencies (including filing, recording,
                certificate, franchise, or similar fees).
            </p>
            <p>
                "Third-Party Fees" means fees paid to parties other than us, such as couriers, certified mail providers,
                process servers, publication/newspaper services, payment processors, or other vendors.
            </p>
            <p>
                "Start of Work" means the earliest of: (a) you submitting your questionnaire/details, (b) we
                opening/reviewing your order, (c) we preparing forms/documents, (d) we submitting anything to a
                government office or third party, or (e) we incurring any Government Fees or Third-Party Fees related to
                your Order.
            </p>

            <h2 class="mt-14 font-bold">2. General Rule: All Sales Final; Limited Exceptions</h2>
            <p>
                Except where required by law, ALL PAYMENTS ARE FINAL AND NON-REFUNDABLE.
            </p>
            <p>
                We may (but are not obligated to) issue a refund, partial refund, or account credit in our sole
                discretion, on a case-by-case basis. Any exception must be confirmed by us in writing.
            </p>

            <h2 class="mt-14 font-bold">3. Non-Refundable Items</h2>
            <p>
                The following are non-refundable to the fullest extent permitted by law:
            </p>
            <ul>
                <li>Government Fees and Third-Party Fees once incurred, paid, submitted, or committed.</li>
                <li>Any Service Fees after Start of Work.</li>
                <li>Expedited processing fees, rush handling fees, shipping, mailing, courier fees, and publication fees
                    once selected or initiated.</li>
                <li>Digital products/documents/templates once delivered, downloaded, or made available (including,
                    without limitation, operating agreements, certificates, notices, demand letters, and similar
                    documents).</li>
                <li>Services purchased to meet deadlines or preserve rights (including construction notice/lien
                    workflows) once preparation or delivery has begun.</li>
                <li>Subscription charges and renewals (see Section 7), except for duplicate/erroneous charges proven to
                    be our billing error.</li>
            </ul>

            <h2 class="mt-14 font-bold">4. Cancellations (Very Limited)</h2>
            <p>
                Because our services often begin quickly, cancellation requests must be made quickly.
            </p>
            <p>
                If you want to cancel, you must email
                <a href="mailto:contact@eregister.com">contact@eregister.com</a> with:
            </p>
            <ul>
                <li>the account email,</li>
                <li>the Order number, and</li>
                <li>"CANCEL ORDER" in the subject line.</li>
            </ul>
            <p>
                If (and only if) ALL of the following are true, you may be eligible for cancellation:
            </p>
            <ol>
                <li>you request cancellation within 1 hour of purchase,</li>
                <li>Start of Work has not occurred, and</li>
                <li>we have not incurred any Government Fees or Third-Party Fees.</li>
            </ol>
            <p>
                If eligible, we may refund the Service Fee minus a non-refundable processing/administrative amount
                covering payment processing and internal handling costs.
            </p>
            <p>
                If Start of Work has occurred (which may be immediate), cancellation will be treated as a termination of
                the Order and NO refund will be provided. You remain responsible for all incurred
                Government Fees/Third-Party Fees.
            </p>

            <h2 class="mt-14 font-bold">5. Rejections, Deficiencies, and Customer-Provided Information</h2>

            <h3 class="mt-8 font-bold">a) No refunds for customer error</h3>
            <p>
                If your filing/application/document is rejected, delayed, or requires correction due to inaccurate,
                incomplete, or inconsistent information you provided (including
                name/address/ownership/SSN/ITIN/EIN/state records mismatches), you are not entitled to any refund.
            </p>

            <h3 class="mt-8 font-bold">b) Corrections/resubmissions</h3>
            <p>
                If a rejection results from our preparation error, our standard remedy is to correct and resubmit the
                affected paperwork at no additional Service Fee. Government Fees and Third-Party Fees for resubmission
                (if any) are your responsibility unless we explicitly agree otherwise in writing.
            </p>

            <h3 class="mt-8 font-bold">c) Outcome not guaranteed</h3>
            <p>
                We do not guarantee approvals, processing times, or outcomes from government agencies or third parties,
                and those are not grounds for a refund.
            </p>

            <h2 class="mt-14 font-bold">6. Duplicate or Unauthorized Charges</h2>
            <p>
                If you believe you were charged in error (duplicate charge) or have an unauthorized transaction, contact
                <a href="mailto:contact@eregister.com">contact@eregister.com</a> within 7 days of the charge date.
            </p>
            <p>
                If we confirm the charge was our billing error, we will refund the duplicate/erroneous amount to the
                original payment method (or another method we choose, if the original is unavailable).
            </p>

            <h2 class="mt-14 font-bold">7. Subscriptions, Auto-Renewals, and Recurring Services</h2>
            <p>
                Some services may be billed on a recurring basis with varying terms depending on the plan or service
                selected. All subscriptions automatically renew unless canceled.
            </p>
            <ul>
                <li>You may cancel renewal at any time through your account dashboard (if available) or by emailing
                    <a href="mailto:contact@eregister.com">contact@eregister.com</a>.</li>
                <li>Cancellation stops FUTURE renewals only and does not retroactively refund prior
                    charges.</li>
                <li>No prorated refunds are provided for partial billing periods.</li>
                <li>If a renewal charge occurs, it is non-refundable unless it is a proven duplicate/erroneous charge
                    caused by our billing system.</li>
            </ul>

            <h2 class="mt-14 font-bold">8. Chargebacks and Payment Disputes</h2>
            <p>
                Before initiating a chargeback or bank dispute, you agree to contact us at
                <a href="mailto:contact@eregister.com">contact@eregister.com</a> and give us a reasonable opportunity
                to investigate and resolve the issue.
            </p>
            <p>
                If you initiate a chargeback:
            </p>
            <ul>
                <li>we may suspend or terminate your account and pause work on any pending Orders;</li>
                <li>you remain responsible for amounts due for work performed and any Government Fees/Third-Party Fees
                    incurred; and</li>
                <li>you agree to reimburse us for chargeback penalties and fees assessed to us by banks/processors, plus
                    reasonable costs to investigate and respond.</li>
            </ul>

            <h2 class="mt-14 font-bold">9. How Refunds (If Approved) Are Issued</h2>
            <p>
                If we approve a refund:
            </p>
            <ul>
                <li>refunds are issued to the original payment method when possible;</li>
                <li>we may issue account credit instead of a refund where permitted by law and appropriate for the
                    circumstance; and</li>
                <li>processing times depend on your bank/payment provider.</li>
            </ul>

            <h2 class="mt-14 font-bold">10. Policy Changes</h2>
            <p>
                We may update this Policy at any time by posting an updated version on the Platform. The "Last updated"
                date controls. Continued use of the Platform after changes means you accept the updated Policy.
            </p>

            <h2 class="mt-14 font-bold">11. Contact</h2>
            <p>
                Questions about this Policy must be sent to:
            </p>
            <ul>
                <li>Email: <a href="mailto:contact@eregister.com">contact@eregister.com</a></li>
                <li>Address: 4869 Brownsboro Rd Ste 101-R, Louisville, KY 40207</li>
            </ul>

            <h2 class="mt-14 font-bold">12. Governing Law</h2>
            <p>
                This Policy is governed by the laws of Kentucky, without regard to conflict-of-laws
                rules, and you agree that any dispute relating to refunds or this Policy will be brought exclusively in
                the state or federal courts located in Jefferson County, Kentucky, unless applicable
                law requires otherwise.
            </p>

        </div>
    </div>
</div>
@endsection
