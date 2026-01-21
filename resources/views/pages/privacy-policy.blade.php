@extends('layouts.landing')

@section('title', 'Privacy Policy - ' . config('app.name', 'eRegister'))

@section('content')
<div class="bg-white py-16 lg:py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold tracking-tight text-zinc-900">Privacy Policy</h1>
        <p class="mt-4 text-zinc-500">Last updated: {{ now()->format('F j, Y') }}</p>

        <div class="prose prose-zinc mt-12 max-w-none">
            <h2>1. Introduction</h2>
            <p>
                Welcome to {{ config('app.name', 'eRegister') }}. We respect your privacy and are committed to protecting your personal data.
                This privacy policy will inform you about how we look after your personal data when you visit our website and tell you about your privacy rights and how the law protects you.
            </p>

            <h2>2. Information We Collect</h2>
            <p>
                We may collect, use, store and transfer different kinds of personal data about you which we have grouped together as follows:
            </p>
            <ul>
                <li><strong>Identity Data</strong> includes first name, last name, username or similar identifier, title, and date of birth.</li>
                <li><strong>Contact Data</strong> includes billing address, delivery address, email address and telephone numbers.</li>
                <li><strong>Financial Data</strong> includes bank account and payment card details.</li>
                <li><strong>Transaction Data</strong> includes details about payments to and from you and other details of services you have purchased from us.</li>
                <li><strong>Technical Data</strong> includes internet protocol (IP) address, your login data, browser type and version, time zone setting and location, browser plug-in types and versions, operating system and platform, and other technology on the devices you use to access this website.</li>
                <li><strong>Usage Data</strong> includes information about how you use our website and services.</li>
            </ul>

            <h2>3. How We Use Your Information</h2>
            <p>
                We will only use your personal data when the law allows us to. Most commonly, we will use your personal data in the following circumstances:
            </p>
            <ul>
                <li>To register you as a new customer</li>
                <li>To process and deliver your service orders</li>
                <li>To manage your relationship with us</li>
                <li>To improve our website, products/services, marketing or customer relationships</li>
                <li>To recommend products or services which may be of interest to you</li>
                <li>To comply with legal or regulatory obligations</li>
            </ul>

            <h2>4. Data Security</h2>
            <p>
                We have put in place appropriate security measures to prevent your personal data from being accidentally lost, used or accessed in an unauthorized way, altered or disclosed.
                In addition, we limit access to your personal data to those employees, agents, contractors and other third parties who have a business need to know.
            </p>

            <h2>5. Data Retention</h2>
            <p>
                We will only retain your personal data for as long as reasonably necessary to fulfill the purposes we collected it for, including for the purposes of satisfying any legal, regulatory, tax, accounting or reporting requirements.
            </p>

            <h2>6. Your Legal Rights</h2>
            <p>
                Under certain circumstances, you have rights under data protection laws in relation to your personal data, including the right to:
            </p>
            <ul>
                <li>Request access to your personal data</li>
                <li>Request correction of your personal data</li>
                <li>Request erasure of your personal data</li>
                <li>Object to processing of your personal data</li>
                <li>Request restriction of processing your personal data</li>
                <li>Request transfer of your personal data</li>
                <li>Right to withdraw consent</li>
            </ul>

            <h2>7. Cookies</h2>
            <p>
                Our website uses cookies to distinguish you from other users of our website. This helps us to provide you with a good experience when you browse our website and also allows us to improve our site.
            </p>

            <h2>8. Third-Party Links</h2>
            <p>
                This website may include links to third-party websites, plug-ins and applications. Clicking on those links or enabling those connections may allow third parties to collect or share data about you.
                We do not control these third-party websites and are not responsible for their privacy statements.
            </p>

            <h2>9. Changes to This Privacy Policy</h2>
            <p>
                We may update this privacy policy from time to time. We will notify you of any changes by posting the new privacy policy on this page and updating the "Last updated" date at the top of this policy.
            </p>

            <h2>10. Contact Us</h2>
            <p>
                If you have any questions about this privacy policy or our privacy practices, please contact us at:
            </p>
            <ul>
                <li>Email: privacy@example.com</li>
                <li>Phone: (555) 123-4567</li>
                <li>Address: 123 Business Street, Suite 100, City, State 12345</li>
            </ul>
        </div>
    </div>
</div>
@endsection
