{{--
    Google-side tracking for a payment success page. Include it inside the
    page's @push('scripts') block, BEFORE the Google Ads 'conversion' event.

    1. Enhanced conversions: hands the Google tag the customer's email, which
       the tag SHA-256 hashes in the browser before anything is sent. It lets
       Google match the sale to an ad click when cookies alone can't. Set only
       here (and on the sign-up conversion), not on every page.
    2. GA4 'purchase': until now only the Google Ads tag heard about a sale, so
       Analytics had no revenue at all. The transaction id is the payment id,
       which GA4 uses to drop duplicates on a reload.

    Vars: $payment (App\Models\Payment), $itemName (string).
--}}
@auth
<script data-navigate-once>
    window.gtag && gtag('set', 'user_data', { email: @js(auth()->user()->email) });
</script>
@endauth
<script data-navigate-once>
    window.gtag && gtag('event', 'purchase', {
        transaction_id: @js((string) $payment->id),
        value: {{ number_format($payment->amount_cents / 100, 2, '.', '') }},
        currency: "USD",
        items: [{
            item_name: @js($itemName),
            price: {{ number_format($payment->amount_cents / 100, 2, '.', '') }},
            quantity: 1
        }]
    });
</script>
