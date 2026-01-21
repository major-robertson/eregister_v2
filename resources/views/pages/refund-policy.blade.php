@extends('layouts.landing')

@section('title', 'Refund Policy - ' . config('app.name', 'eRegister'))

@section('content')
<div class="bg-white py-16 lg:py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold tracking-tight text-zinc-900">Refund Policy</h1>
        <p class="mt-4 text-zinc-500">Last updated: {{ now()->format('F j, Y') }}</p>

        <div class="prose prose-zinc mt-12 max-w-none">
            <h2>1. Overview</h2>
            <p>
                At {{ config('app.name', 'eRegister') }}, we strive to provide excellent service and ensure customer satisfaction.
                This Refund Policy outlines the circumstances under which refunds may be issued and the process for requesting a refund.
            </p>

            <h2>2. Service Fees vs. State Filing Fees</h2>
            <p>
                It's important to understand the distinction between our fees:
            </p>
            <ul>
                <li><strong>Service Fees:</strong> These are the fees charged by {{ config('app.name', 'eRegister') }} for preparing and processing your applications.</li>
                <li><strong>State Filing Fees:</strong> These are fees charged by state agencies for processing your applications. These fees are paid directly to the state and are non-refundable once submitted.</li>
            </ul>

            <h2>3. Refund Eligibility</h2>
            <h3>Full Refund</h3>
            <p>
                You may be eligible for a full refund of our service fees if:
            </p>
            <ul>
                <li>You cancel your order before we begin processing your application</li>
                <li>We are unable to complete your service due to an error on our part</li>
                <li>You were charged in error or duplicated charges occurred</li>
            </ul>

            <h3>Partial Refund</h3>
            <p>
                A partial refund may be issued if:
            </p>
            <ul>
                <li>You cancel after we have begun processing but before filing with the state</li>
                <li>Only some services in a bundle were completed successfully</li>
            </ul>

            <h3>No Refund</h3>
            <p>
                Refunds will not be issued in the following circumstances:
            </p>
            <ul>
                <li>Your application has already been submitted to the state</li>
                <li>State filing fees have already been paid (these are never refundable)</li>
                <li>Your application was rejected due to inaccurate or incomplete information you provided</li>
                <li>You simply changed your mind after services were rendered</li>
                <li>Dissatisfaction with state processing times (we do not control these)</li>
            </ul>

            <h2>4. Rejected Applications</h2>
            <p>
                If your application is rejected by a state agency:
            </p>
            <ul>
                <li>If rejected due to our error: We will correct and resubmit at no additional charge, or provide a full refund of our service fees.</li>
                <li>If rejected due to information you provided: We will work with you to correct the application for a nominal reprocessing fee. State fees for resubmission are your responsibility.</li>
            </ul>

            <h2>5. How to Request a Refund</h2>
            <p>
                To request a refund, please follow these steps:
            </p>
            <ol>
                <li>Contact our support team within 30 days of your purchase</li>
                <li>Provide your order number and reason for the refund request</li>
                <li>Allow up to 5 business days for our team to review your request</li>
                <li>If approved, refunds will be processed to your original payment method within 7-10 business days</li>
            </ol>

            <h2>6. Refund Processing</h2>
            <p>
                Approved refunds will be processed as follows:
            </p>
            <ul>
                <li><strong>Credit Card:</strong> Refunded to the original card within 7-10 business days</li>
                <li><strong>Bank Transfer:</strong> Refunded to the original bank account within 7-10 business days</li>
                <li><strong>Other Methods:</strong> Refunded via the original payment method or as a credit to your account</li>
            </ul>

            <h2>7. Subscription Services</h2>
            <p>
                For subscription-based services:
            </p>
            <ul>
                <li>You may cancel your subscription at any time</li>
                <li>Cancellation will take effect at the end of your current billing period</li>
                <li>No refunds are provided for partial billing periods</li>
                <li>Any unused credits or services expire upon cancellation</li>
            </ul>

            <h2>8. Disputes</h2>
            <p>
                If you disagree with our refund decision, you may escalate your concern by contacting our management team.
                We are committed to resolving disputes fairly and promptly.
                Please allow up to 10 business days for dispute resolution.
            </p>

            <h2>9. Changes to This Policy</h2>
            <p>
                We reserve the right to modify this Refund Policy at any time.
                Changes will be effective immediately upon posting to our website.
                Your continued use of our services constitutes acceptance of any changes.
            </p>

            <h2>10. Contact Us</h2>
            <p>
                If you have any questions about our Refund Policy, please contact us at:
            </p>
            <ul>
                <li>Email: support@example.com</li>
                <li>Phone: (555) 123-4567</li>
                <li>Address: 123 Business Street, Suite 100, City, State 12345</li>
            </ul>
        </div>
    </div>
</div>
@endsection
