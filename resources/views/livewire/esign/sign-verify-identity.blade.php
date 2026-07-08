<div class="w-full max-w-md">
    <flux:heading size="xl">Verify your email</flux:heading>
    <flux:text class="mt-2">
        You've been asked to sign <span class="font-medium">{{ $title }}</span>.
        @if ($linkRequired)
            To continue, open the signing link from the email we sent to
            <span class="font-medium">{{ $maskedEmail }}</span>. It starts your secure session
            and sends you a 6-digit code.
        @else
            To keep your signature secure, we sent a 6-digit code to
            <span class="font-medium">{{ $maskedEmail }}</span>.
        @endif
    </flux:text>

    @if (session('esign_code_resent'))
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
            A new code is on its way. It can take a minute to arrive.
        </div>
    @endif

    <form wire:submit="verify" class="mt-6 space-y-4">
        <flux:field>
            <flux:label>Verification code</flux:label>
            <flux:input
                wire:model="code"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                placeholder="123456"
                class="tracking-widest"
            />
            <flux:error name="code" />
        </flux:field>

        <flux:button type="submit" variant="primary" icon="shield-check" class="w-full">
            Verify &amp; continue
        </flux:button>
    </form>

    <flux:text class="mt-4 text-sm">
        Didn't get it? Check spam, or
        <button type="button" wire:click="resend" class="font-medium underline">send a new code</button>.
    </flux:text>

    <flux:text class="mt-6 text-xs text-zinc-500">
        No account is needed to sign. After signing you can create a free account with this email to
        track everything you've signed.
    </flux:text>
</div>
