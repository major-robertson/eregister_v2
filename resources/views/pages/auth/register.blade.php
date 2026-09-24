@php
    // A visitor who picked a waiver on a marketing page lands here next. Keep
    // talking about that waiver instead of greeting them with a blank form.
    // An invitation has its own explanation, so it wins.
    $forWaiver = ($waiverIntent ?? null) !== null && ($invitation ?? null) === null;
    $waiverStateName = $forWaiver
        ? (\App\Domains\Lien\Waivers\WaiverStateRegistry::STATE_NAMES[$waiverIntent['state'] ?? ''] ?? null)
        : null;

    // A visitor sent here by a product page (?product=sales-tax&state=TX)
    // gets a heading about that product instead of a blank "Create an
    // account". The waiver starter and invitations keep their own copy.
    $intentProduct = (! $forWaiver && ($invitation ?? null) === null) ? ($signupIntent['product'] ?? null) : null;
    $intentStateName = $intentProduct && ($signupIntent['state'] ?? null) ? config('states.'.$signupIntent['state']) : null;
    $statePrefix = $intentStateName ? $intentStateName.' ' : '';
    $productHeading = match ($intentProduct) {
        'sales-tax' => "Create your account to start your {$statePrefix}sales tax registration",
        'resale-cert' => "Create your account to generate {$statePrefix}resale certificates",
        'llc' => 'Create your account to start your LLC',
        'liens' => 'Create your account to protect your payment',
        default => null,
    };
@endphp

<x-layouts::auth title="Create an Account">
    <div class="flex flex-col gap-6">
        @if ($forWaiver)
            {{-- Account, business, the job, the waiver details. --}}
            <div class="flex w-full flex-col text-center">
                <p class="text-sm font-medium text-text-secondary">Step 1 of 4</p>
                <flux:heading size="xl" class="mt-1">Create your free account to finish your {{ $waiverStateName ? $waiverStateName.' ' : '' }}lien waiver</flux:heading>
                <flux:subheading>Free. No credit card.</flux:subheading>
            </div>
        @elseif ($productHeading)
            <div class="flex w-full flex-col text-center">
                <p class="text-sm font-medium text-text-secondary">Step 1 of 3</p>
                <flux:heading size="xl" class="mt-1">{{ $productHeading }}</flux:heading>
                <flux:subheading>Free to create. Nothing to pay until you order.</flux:subheading>
            </div>
        @else
            <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />
        @endif

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        @if ($invitation ?? null)
            <flux:callout color="blue" icon="user-plus">
                {{ __("You've been invited to join :business — use this email address to accept the invitation.", ['business' => $invitation->business->name]) }}
            </flux:callout>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- First Name -->
            <flux:input
                name="first_name"
                :label="__('First name')"
                :value="old('first_name')"
                type="text"
                required
                autofocus
                autocomplete="given-name"
                :placeholder="__('First name')"
            />

            <!-- Last Name -->
            <flux:input
                name="last_name"
                :label="__('Last name')"
                :value="old('last_name')"
                type="text"
                required
                autocomplete="family-name"
                :placeholder="__('Last name')"
            />

            <!-- Email Address: prefilled from a team invitation or the
                 ?email= handoff (e.g. the guest signer's create-account CTA,
                 which claims their signed documents by matching email). -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email', ($invitation ?? null)?->email ?? request('email'))"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                viewable
            />

            <!-- Honeypot -->
            <div aria-hidden="true" style="position: absolute; left: -9999px;">
                <label for="website">Website</label>
                <input type="text" name="website" id="website" tabindex="-1" autocomplete="off" />
            </div>

            <x-recaptcha />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ $forWaiver ? 'Create my free account' : __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-text-secondary">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
