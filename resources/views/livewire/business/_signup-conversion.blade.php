{{--
    The sign-up conversion scripts, rendered once on the first screen after
    registration: the address screen for most products, the one-screen
    business setup for waiver sign-ups. The component pulls (not reads) the
    just_registered flag, so a refresh never fires them twice.
--}}
@push('scripts')
<!-- Enhanced conversions: the new user's email, hashed in the browser by the
     Google tag, so the sign-up can be matched to its ad click. -->
<script data-navigate-once>
    window.gtag && gtag('set', 'user_data', { email: @js(auth()->user()->email) });
</script>
<!-- Google Ads Conversion Tracking - Create Account -->
<script data-navigate-once>
    gtag('event', 'conversion', {
        send_to: "AW-984288380/XDg5CMWk_7oZEPyYrNUD"
    });
</script>
<!-- GA4 funnel: sign_up (landing_path says which product page brought them) -->
<script data-navigate-once>
    window.gtag && gtag('event', 'sign_up', {
        method: 'email',
        landing_path: @js(auth()->user()->signup_landing_path)
    });
</script>
<!-- Reddit Pixel Conversion - Create Account -->
<script data-navigate-once>
    rdt('track', 'SignUp', {
        conversionId: @js('signup-' . auth()->id())
    });
</script>
<!-- OpenAI Ads Conversion - Create Account -->
<script data-navigate-once>
    oaiq("measure", "registration_completed", {
        type: "customer_action"
    }, { event_id: @js('signup-' . auth()->id()) });
</script>
@endpush
