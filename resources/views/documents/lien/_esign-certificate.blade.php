{{--
    Certificate of Completion — the audit page appended to a signed demand letter.
    DOMPDF: plain HTML + inline CSS (no flex/grid). Starts on its own page.

    Vars: $certificate[...]
--}}
<div class="esign-cert">
    <h2 class="esign-cert-title">Certificate of Completion</h2>
    <p class="esign-cert-sub">
        Electronic record for <strong>{{ $certificate['document_label'] }}</strong>
        (Document ID {{ $certificate['document_identifier'] }}).
        Envelope reference {{ $certificate['request_public_id'] }}.
    </p>

    <table class="esign-cert-table">
        <tr><td>Signer</td><td>{{ $certificate['signer_name'] ?: '—' }}</td></tr>
        <tr><td>Adopted signature</td><td>{{ $certificate['adopted_name'] ?: '—' }}</td></tr>
        <tr><td>Email</td><td>{{ $certificate['signer_email'] ?: '—' }}</td></tr>
        @if ($certificate['signer_phone'])
        <tr><td>Phone</td><td>{{ $certificate['signer_phone'] }}</td></tr>
        @endif
        <tr><td>Email verified</td><td>{{ $certificate['email_verified_at'] ?: 'Not verified' }}</td></tr>
        <tr><td>Signature method</td><td>{{ $certificate['signature_method'] }}</td></tr>
        <tr><td>Consent</td><td>
            @if ($certificate['consent_version'])
                Scope “{{ $certificate['consent_scope'] }}”, version {{ $certificate['consent_version'] }}, accepted {{ $certificate['consent_at'] }}
            @else
                —
            @endif
        </td></tr>
        <tr><td>Locked document hash (SHA-256)</td><td class="esign-cert-hash">{{ $certificate['locked_hash'] ?: '—' }}</td></tr>
    </table>

    <p class="esign-cert-intent"><strong>Intent:</strong> {{ $certificate['intent'] ?: '—' }}</p>

    <h3 class="esign-cert-h3">Audit trail</h3>
    <table class="esign-cert-events">
        <thead>
            <tr><th>Event</th><th>When</th><th>IP</th><th>Actor</th><th>Document</th></tr>
        </thead>
        <tbody>
            @foreach ($certificate['events'] as $event)
            <tr>
                <td>{{ $event['label'] }}</td>
                <td>{{ $event['at'] ?: '—' }}</td>
                <td>{{ $event['ip'] ?: '—' }}</td>
                <td>{{ $event['actor'] ?: '—' }}</td>
                <td>{{ $event['document'] ?: '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="esign-cert-foot">
        This certificate records the electronic signing of the document above under the U.S. ESIGN
        Act and applicable Uniform Electronic Transactions Act (UETA). The signer consented to use
        electronic records and signatures and adopted the signature shown with intent to be bound.
    </p>
</div>
