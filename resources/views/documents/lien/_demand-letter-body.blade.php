{{--
    Payment Demand Letter — body content (no <html>/<body>), included by the single
    and batch wrappers. Copy is reproduced from the client's Word template; the
    placeholders that were red there are the populated fields below.

    Rendered via spatie/laravel-pdf's DOMPDF driver: plain HTML + inline CSS only
    (no flex/grid), and kept to a single page.

    Vars: $date, $recipient[name|company|address_lines], $salutation, $amount,
          $start_date, $end_date, $work, $sender[name|company|address_lines|phone|email]
--}}
<div class="letter">
    <p class="date">{{ $date }}</p>

    <div class="recipient">
        @if (($recipient['name'] ?? null))<div>{{ $recipient['name'] }}</div>@endif
        @if (($recipient['company'] ?? null))<div>{{ $recipient['company'] }}</div>@endif
        {{-- New payloads carry block lines; fall back to the legacy single-line address. --}}
        @foreach (($recipient['address_lines'] ?? array_filter([$recipient['address'] ?? null])) as $line)
            <div>{{ $line }}</div>
        @endforeach
    </div>

    <p class="salutation">{{ $salutation }}</p>

    <p>
        This letter serves as a formal demand for payment in the amount of
        <strong>${{ $amount ?: '[amount due]' }}</strong> for construction services completed as
        described below. Despite completion of the agreed-upon work, payment has not been received
        as of the date of this letter.
    </p>

    <table class="details">
        <tr><td><strong>Total Amount Due:</strong></td><td>${{ $amount ?: '[amount due]' }}</td></tr>
        <tr><td><strong>Project Start Date:</strong></td><td>{{ $start_date ?: '—' }}</td></tr>
        <tr><td><strong>Project End Date:</strong></td><td>{{ $end_date ?: '—' }}</td></tr>
    </table>

    <p class="work-label"><strong>Description of Work:</strong></p>
    <p class="work">{{ $work ?: '—' }}</p>

    <p>
        We demand that full payment be remitted within <strong>ten (10)</strong> days of your receipt
        of this letter. If payment is not received within this timeframe, we reserve the right to
        pursue all available legal remedies, which may include but are not limited to
        <strong>filing a mechanics lien against the property,</strong>
        <strong>placing the account with collections,</strong>
        <strong>initiating a lawsuit to recover the amount owed plus interest, attorney fees, and
        court costs.</strong>
    </p>

    <p>
        We hope to resolve this matter promptly without the need for further action.
        <strong>Please contact us immediately upon receipt of this letter to confirm payment
        arrangements or to discuss any questions.</strong>
    </p>

    <p class="closing">Sincerely,</p>

    <div class="signature">
        @if (($sender['name'] ?? null))<div>{{ $sender['name'] }}</div>@endif
        @if (($sender['company'] ?? null))<div>{{ $sender['company'] }}</div>@endif
        @foreach (($sender['address_lines'] ?? []) as $line)
            <div>{{ $line }}</div>
        @endforeach
        @if (($sender['phone'] ?? null))<div>{{ $sender['phone'] }}</div>@endif
        @if (($sender['email'] ?? null))<div>{{ $sender['email'] }}</div>@endif
    </div>
</div>
