@extends('layouts.landing')

@section('title', 'Terms of Service - ' . config('app.name', 'eRegister'))

@section('content')
<div class="bg-white py-16 lg:py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold tracking-tight text-zinc-900">Terms of Service</h1>
        <p class="mt-4 text-zinc-500">Last updated: {{ now()->format('F j, Y') }}</p>

        <div class="prose prose-zinc mt-12 max-w-none">
            <h2>1. Agreement to Terms</h2>
            <p>
                By accessing or using {{ config('app.name', 'eRegister') }}'s website and services, you agree to be bound by these Terms of Service and all applicable laws and regulations.
                If you do not agree with any of these terms, you are prohibited from using or accessing this site.
            </p>

            <h2>2. Use License</h2>
            <p>
                Permission is granted to temporarily access the materials on {{ config('app.name', 'eRegister') }}'s website for personal, non-commercial transitory viewing only.
                This is the grant of a license, not a transfer of title, and under this license you may not:
            </p>
            <ul>
                <li>Modify or copy the materials</li>
                <li>Use the materials for any commercial purpose or for any public display</li>
                <li>Attempt to decompile or reverse engineer any software contained on the website</li>
                <li>Remove any copyright or other proprietary notations from the materials</li>
                <li>Transfer the materials to another person or "mirror" the materials on any other server</li>
            </ul>

            <h2>3. Services Description</h2>
            <p>
                {{ config('app.name', 'eRegister') }} provides business registration services including but not limited to:
            </p>
            <ul>
                <li>Sales tax permit applications</li>
                <li>LLC formation and registration</li>
                <li>Use tax registration</li>
                <li>Foreign qualification filings</li>
                <li>Annual report filings</li>
                <li>Business license applications</li>
            </ul>
            <p>
                We act as a filing service and do not provide legal, tax, or financial advice. You should consult with appropriate professionals for such advice.
            </p>

            <h2>4. User Responsibilities</h2>
            <p>
                As a user of our services, you agree to:
            </p>
            <ul>
                <li>Provide accurate and complete information</li>
                <li>Maintain the confidentiality of your account credentials</li>
                <li>Notify us immediately of any unauthorized use of your account</li>
                <li>Be responsible for all activities that occur under your account</li>
                <li>Not use the services for any unlawful purpose</li>
            </ul>

            <h2>5. Payment Terms</h2>
            <p>
                By using our paid services, you agree to pay all fees and charges associated with your account on a timely basis and with a valid payment method.
                All fees are non-refundable except as expressly set forth in our Refund Policy.
                State filing fees are separate from our service fees and are subject to change based on state requirements.
            </p>

            <h2>6. Disclaimer</h2>
            <p>
                The materials on {{ config('app.name', 'eRegister') }}'s website are provided on an 'as is' basis.
                {{ config('app.name', 'eRegister') }} makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.
            </p>

            <h2>7. Limitations</h2>
            <p>
                In no event shall {{ config('app.name', 'eRegister') }} or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on {{ config('app.name', 'eRegister') }}'s website, even if {{ config('app.name', 'eRegister') }} or a {{ config('app.name', 'eRegister') }} authorized representative has been notified orally or in writing of the possibility of such damage.
            </p>

            <h2>8. Accuracy of Materials</h2>
            <p>
                The materials appearing on {{ config('app.name', 'eRegister') }}'s website could include technical, typographical, or photographic errors.
                {{ config('app.name', 'eRegister') }} does not warrant that any of the materials on its website are accurate, complete or current.
                {{ config('app.name', 'eRegister') }} may make changes to the materials contained on its website at any time without notice.
            </p>

            <h2>9. Links</h2>
            <p>
                {{ config('app.name', 'eRegister') }} has not reviewed all of the sites linked to its website and is not responsible for the contents of any such linked site.
                The inclusion of any link does not imply endorsement by {{ config('app.name', 'eRegister') }} of the site. Use of any such linked website is at the user's own risk.
            </p>

            <h2>10. Modifications</h2>
            <p>
                {{ config('app.name', 'eRegister') }} may revise these terms of service for its website at any time without notice.
                By using this website you are agreeing to be bound by the then current version of these terms of service.
            </p>

            <h2>11. Governing Law</h2>
            <p>
                These terms and conditions are governed by and construed in accordance with the laws of the United States and you irrevocably submit to the exclusive jurisdiction of the courts in that location.
            </p>

            <h2>12. Contact Information</h2>
            <p>
                If you have any questions about these Terms of Service, please contact us at:
            </p>
            <ul>
                <li>Email: legal@example.com</li>
                <li>Phone: (555) 123-4567</li>
                <li>Address: 123 Business Street, Suite 100, City, State 12345</li>
            </ul>
        </div>
    </div>
</div>
@endsection
