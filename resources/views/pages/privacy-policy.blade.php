@extends('layouts.landing')

@section('title', 'Privacy Policy - ' . config('app.name', 'eRegister'))

@section('content')
<div class="bg-white py-16 lg:py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold tracking-tight text-zinc-900">Privacy Policy</h1>
        <p class="mt-4 text-zinc-500">Last Updated: February 28, 2026</p>

        <div class="prose prose-zinc mt-12 max-w-none">

            <p>
                This Privacy Policy explains how eRegister ("we," "us," or "our") collects, uses, discloses, and
                protects information when you visit or use our website,
                <a href="https://eregister.com" rel="noopener noreferrer">https://eregister.com</a> (the "Site"), and
                any related products or services we provide (collectively, the "Services").
            </p>

            <p>
                Because eRegister helps users prepare and submit business and compliance filings (and related documents
                such as lien notices and other payment protection documents),
                some information you provide may be submitted to government agencies or recorded in public records.
                Please read the "Public Records and Filings" section carefully.
            </p>

            <h2 class="mt-14 font-bold">1. Scope</h2>
            <p>This Privacy Policy applies to information we collect:</p>
            <ul>
                <li>on the Site,</li>
                <li>through your eRegister account,</li>
                <li>through forms you complete (including contact/support requests),</li>
                <li>through transactions and filings you request us to prepare and submit,</li>
                <li>and through communications with us (email/chat/SMS if applicable).</li>
            </ul>
            <p>
                This Privacy Policy does not apply to third-party websites, services, or platforms that may be linked
                from our Site.
            </p>

            <h2 class="mt-14 font-bold">2. Information We Collect</h2>
            <p>
                We collect information in three main ways: (A) information you provide, (B) information collected
                automatically, and (C) information from third parties.
            </p>

            <h3 class="mt-8 font-bold">A) Information you provide to us</h3>
            <p>Depending on which Services you use, you may provide:</p>

            <p>Account and Profile Information</p>
            <ul>
                <li>Name</li>
                <li>Email address</li>
                <li>Password (stored in encrypted/hashed form, where applicable)</li>
                <li>Account preferences and settings</li>
            </ul>

            <p>Business and Filing Information</p>
            <p>
                When you request business formation, compliance, or registration services, you may provide:
            </p>
            <ul>
                <li>Business or entity name</li>
                <li>Business address(es) (mailing, principal office, etc.)</li>
                <li>Names and contact information for owners, members, managers, officers, directors, or other
                    authorized persons</li>
                <li>Registered agent details (if applicable)</li>
                <li>State(s) and filing details</li>
                <li>Business identifiers (if applicable)</li>
                <li>Supporting documents you upload or provide</li>
            </ul>

            <p>Tax-Related Information</p>
            <p>
                When you request EIN or sales/use tax registration services, you may provide:
            </p>
            <ul>
                <li>Business tax identifiers (EIN, state tax IDs)</li>
                <li>Responsible party information (which may include a Social Security Number or ITIN if required for
                    certain submissions)</li>
                <li>Business activity details and other information needed to prepare submissions</li>
            </ul>

            <p>Payment Protection / Lien and Notice Information</p>
            <p>
                If you use lien tracking, lien filing, preliminary notices, intent-to-lien notices, lien releases, or
                demand letters, you may provide:
            </p>
            <ul>
                <li>Project details and job information</li>
                <li>Property address and related property details</li>
                <li>Owner / general contractor / customer contact information</li>
                <li>Invoice amounts, payment status, and relevant dates</li>
                <li>Documents and evidence you choose to upload or include (for example contracts or invoices)</li>
            </ul>

            <p>Intellectual Property Filing Information (if offered)</p>
            <p>
                If you use any intellectual property-related filing service, you may provide:
            </p>
            <ul>
                <li>Applicant name(s) and contact details</li>
                <li>Submission content you provide, such as descriptions and supporting materials</li>
            </ul>

            <p>Communications</p>
            <p>
                If you contact us (for example via our contact form, email, chat, or SMS), we collect the content of
                your message and related information
                (such as name, business name, email address, and any attachments you send).
            </p>

            <h3 class="mt-8 font-bold">B) Information collected automatically</h3>
            <p>When you use the Site, we may automatically collect:</p>
            <ul>
                <li>IP address</li>
                <li>Device identifiers and general device information</li>
                <li>Browser type, operating system, and language</li>
                <li>Pages viewed, links clicked, and time spent</li>
                <li>Approximate location (derived from IP address)</li>
                <li>Referring/exit pages and URLs</li>
                <li>Log and security event data</li>
            </ul>

            <h3 class="mt-8 font-bold">C) Information from third parties</h3>
            <p>We may receive information from:</p>
            <ul>
                <li>Payment processors (for example confirmation that a payment succeeded; limited billing details)</li>
                <li>Service providers that help us operate the Services (for example hosting, analytics, customer
                    support tools, and email delivery)</li>
                <li>Government/public sources where relevant to the Services (for example business entity status
                    lookups), to the extent permitted by law</li>
            </ul>

            <h2 class="mt-14 font-bold">3. How We Use Information</h2>
            <p>We use information to:</p>
            <ul>
                <li>Provide, operate, and maintain the Services</li>
                <li>Create and manage your account</li>
                <li>Prepare, submit, and track filings or documents you request</li>
                <li>Communicate with you about your account, orders, filings, deadlines, and updates</li>
                <li>Provide customer support and respond to requests</li>
                <li>Process payments and prevent fraud</li>
                <li>Improve the Site and Services (including troubleshooting, analytics, and testing)</li>
                <li>Send marketing communications where permitted by law (you can opt out)</li>
                <li>Protect the security and integrity of the Services</li>
                <li>Comply with legal obligations and enforce our terms</li>
            </ul>

            <h2 class="mt-14 font-bold">4. How We Disclose Information</h2>
            <p>We may disclose information as follows:</p>

            <h3 class="mt-8 font-bold">A) Disclosures to complete filings and services you request</h3>
            <p>
                If you request that we prepare or submit filings or documents, we may disclose necessary information to:
            </p>
            <ul>
                <li>State or local government agencies (for example Secretaries of State and tax agencies)</li>
                <li>The IRS (for EIN-related submissions, if applicable)</li>
                <li>County recorders or similar offices (for lien-related filings, where applicable, including Jefferson
                    County, Kentucky when relevant)</li>
                <li>The USPTO or similar authorities (for intellectual property-related submissions, if applicable)</li>
                <li>Print/mail vendors or couriers (if documents must be mailed or served)</li>
            </ul>

            <h3 class="mt-8 font-bold">B) Service providers</h3>
            <p>
                We may share information with vendors that perform services for us, such as hosting and infrastructure
                providers, payment processors,
                customer support and communication providers, analytics providers, security/fraud prevention providers,
                document storage providers, and e-signature providers (if applicable).
            </p>

            <h3 class="mt-8 font-bold">C) Legal and safety</h3>
            <p>We may disclose information if we believe it is necessary to:</p>
            <ul>
                <li>Comply with law, regulation, legal process, or governmental request</li>
                <li>Protect the rights, property, and safety of eRegister, our users, or the public</li>
                <li>Detect, prevent, or address fraud, security, or technical issues</li>
            </ul>

            <h3 class="mt-8 font-bold">D) Business transfers</h3>
            <p>
                If we are involved in a merger, acquisition, financing, reorganization, bankruptcy, or sale of some or
                all assets,
                information may be transferred as part of that transaction.
            </p>

            <h2 class="mt-14 font-bold">5. Public Records and Filings</h2>
            <p>
                Some filings and documents you request may become part of public records, depending on the jurisdiction
                and the type of filing
                (for example certain business formation documents and recorded liens). We cannot control how government
                agencies or third parties use
                information that is included in public records.
            </p>
            <p>
                You are responsible for reviewing what information you submit and understanding which details may become
                public.
            </p>

            <h2 class="mt-14 font-bold">6. Cookies and Similar Technologies</h2>
            <p>
                We use cookies and similar technologies to help the Site function and to improve user experience.
                Cookies may be used for:
            </p>
            <ul>
                <li>Essential site functionality (for example login sessions)</li>
                <li>Preferences</li>
                <li>Analytics/performance</li>
                <li>Marketing/advertising (where applicable)</li>
            </ul>
            <p>
                You can control cookies through your browser settings. Some browsers offer "Do Not Track" signals. We do
                not guarantee the Site will respond to all such signals.
            </p>

            <h2 class="mt-14 font-bold">7. Marketing Communications</h2>
            <p>
                If you sign up for marketing emails or if permitted by law, we may send you updates about products,
                services, and promotions.
                You can opt out at any time by using the "unsubscribe" link in our emails or contacting us at
                contact@eregister.com.
            </p>

            <h2 class="mt-14 font-bold">8. Subscriptions, Auto-Renewal, and Cancellation</h2>
            <p>
                Some Services may be offered on a subscription basis with different available terms. Unless otherwise
                disclosed at checkout or in your account,
                subscriptions automatically renew until you cancel.
            </p>
            <p>
                You can cancel at any time through your account dashboard or by emailing contact@eregister.com. If you
                cancel, your subscription will remain active
                until the end of the current billing period unless otherwise required by law or stated at checkout.
            </p>
            <p>
                Refunds (if any) and other billing-related terms are described in our Refund Policy:
                <a href="https://eregister.com/refund-policy"
                    rel="noopener noreferrer">https://eregister.com/refund-policy</a>.
            </p>

            <h2 class="mt-14 font-bold">9. Data Security</h2>
            <p>
                We use administrative, technical, and physical safeguards designed to protect information. However, no
                method of transmission or storage is 100% secure,
                and we cannot guarantee absolute security.
            </p>

            <h2 class="mt-14 font-bold">10. Data Retention</h2>
            <p>
                We retain information for as long as reasonably necessary to:
            </p>
            <ul>
                <li>provide the Services,</li>
                <li>maintain your account,</li>
                <li>comply with legal and accounting obligations,</li>
                <li>resolve disputes,</li>
                <li>enforce agreements.</li>
            </ul>
            <p>
                Retention periods may vary depending on the type of data, the nature of the filing or transaction, and
                operational needs.
                Where permitted by law, we may retain certain records for longer periods when advantageous for
                legitimate business purposes
                such as fraud prevention, compliance, dispute resolution, and enforcement.
            </p>

            <h2 class="mt-14 font-bold">11. Your Privacy Choices and Rights</h2>
            <p>
                Depending on where you live, you may have rights to access, correct, delete, or obtain a copy of certain
                information we hold,
                and to opt out of certain processing (such as certain marketing or targeted advertising, where
                applicable).
            </p>

            <p>To make a request, contact us at:</p>
            <ul>
                <li>Email: contact@eregister.com</li>
                <li>Mail: 4869 Brownsboro Rd Ste 101-R, Louisville, KY 40207</li>
            </ul>
            <p>
                We may need to verify your identity before fulfilling certain requests.
            </p>

            <h3 class="mt-8 font-bold">US State Privacy Disclosures (if applicable)</h3>
            <p>
                If you are a resident of certain US states (for example California, Colorado, Connecticut, Virginia, or
                Utah), you may have additional rights,
                including the right to opt out of targeted advertising and certain profiling, where applicable.
            </p>
            <p>
                We do not sell personal information for money.
            </p>
            <p>
                If your state provides an appeal right and we deny your request, you may appeal by contacting
                contact@eregister.com with the subject line "Privacy Appeal."
            </p>

            <h2 class="mt-14 font-bold">12. Children's Privacy</h2>
            <p>
                The Site and Services are not directed to children under 13 (or under 16 where applicable), and we do
                not knowingly collect personal information from children.
                If you believe a child has provided information to us, contact us at contact@eregister.com.
            </p>

            <h2 class="mt-14 font-bold">13. International Users</h2>
            <p>
                If you access the Site from outside the United States, you understand that information may be processed
                and stored in the United States or other locations
                where our service providers operate, and that data protection laws may differ from those in your
                jurisdiction.
            </p>

            <h2 class="mt-14 font-bold">14. Changes to This Privacy Policy</h2>
            <p>
                We may update this Privacy Policy from time to time. We will post the updated version on this page and
                update the "Last Updated" date above.
            </p>

            <h2 class="mt-14 font-bold">15. Contact Us</h2>
            <p>
                If you have questions about this Privacy Policy or our privacy practices, contact us:
            </p>
            <p>
                eRegister<br>
                4869 Brownsboro Rd Ste 101-R, Louisville, KY 40207<br>
                contact@eregister.com
            </p>
            <p>
                Privacy Policy URL: <a href="https://eregister.com/privacy-policy"
                    rel="noopener noreferrer">https://eregister.com/privacy-policy</a>
            </p>

        </div>
    </div>
</div>
@endsection
