<div
    x-data="{
        drawing: false,
        agreed: false,
        saving: false,
        // x-ref on an x-data element registers in the CHILD scope, so the
        // parent finds the capture component by querying its own subtree.
        get capture() {
            const el = this.$root.querySelector('[x-ref=\'signatureCapture\']');
            return el ? Alpine.$data(el) : null;
        },
        async save() {
            const sig = this.capture?.export();
            if (! sig || ! this.agreed || this.saving) return;

            this.saving = true;
            try {
                await $wire.save(sig.dataUrl, sig.strokesJson, this.agreed, sig.method, sig.typedName, sig.typedFont);
                this.drawing = false;
                this.agreed = false;
            } finally {
                this.saving = false;
            }
        },
    }"
    class="space-y-4"
>
    @if ($this->currentSignatureDataUri)
        <div class="space-y-2">
            <flux:text class="text-sm font-medium text-text-primary">Your current signature</flux:text>
            <div class="inline-block rounded-lg border border-border bg-white p-3">
                <img src="{{ $this->currentSignatureDataUri }}" alt="Your signature" class="h-16" />
            </div>
            <flux:text class="text-xs text-zinc-500">
                Used across eRegister — resale certificates and e-signed documents alike.
            </flux:text>
        </div>
    @endif

    @if (! $this->emailVerified)
        <flux:callout color="amber" icon="envelope">
            <flux:callout.heading>Verify your email to sign</flux:callout.heading>
            <flux:callout.text>
                Electronic signatures require a verified email address. Check your inbox for the
                verification link, or resend it below.
            </flux:callout.text>
            <div class="mt-3">
                <flux:button type="button" variant="primary" size="sm" wire:click="resendVerification">
                    Resend verification email
                </flux:button>
            </div>
        </flux:callout>
    @else
        <div x-show="drawing" x-cloak class="space-y-3">
            <div>
                <flux:text class="text-sm font-medium text-text-primary">
                    Adopt your signature
                </flux:text>
                <flux:text class="text-sm text-zinc-500">
                    Type your name and pick a style, or draw it. It's applied to every document you sign on eRegister.
                </flux:text>
            </div>

            <x-esign.signature-capture :default-name="auth()->user()->name" />

            {{-- ESIGN/UETA disclosures — snapshotted verbatim onto the consent record on save. --}}
            <details class="rounded-lg border border-border bg-zinc-50 p-3 text-sm text-zinc-600">
                <summary class="cursor-pointer font-medium text-text-primary">
                    {{ $consent['heading'] }}
                </summary>
                <div class="mt-2 space-y-2">
                    <p>{{ $consent['agreement'] }}</p>
                    @foreach ($consent['disclosures'] as $disclosure)
                        <p>{{ $disclosure }}</p>
                    @endforeach
                </div>
            </details>

            <label class="flex items-start gap-2 text-sm text-zinc-700">
                <input type="checkbox" x-model="agreed" class="mt-0.5 h-4 w-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500" />
                <span>{{ $consent['checkbox'] }}</span>
            </label>

            <div class="flex items-center gap-3">
                <flux:button type="button" variant="primary" size="sm" x-on:click="save()"
                    x-bind:disabled="!capture?.hasSignature || !agreed || saving">
                    <span x-show="!saving">Adopt Signature</span>
                    <span x-show="saving" x-cloak>Saving...</span>
                </flux:button>
                <flux:button type="button" variant="ghost" size="sm" x-on:click="drawing = false">Cancel</flux:button>
            </div>
        </div>

        <div x-show="!drawing">
            <flux:button type="button" variant="{{ $this->currentSignature ? 'ghost' : 'primary' }}" size="sm" x-on:click="drawing = true">
                {{ $this->currentSignature ? 'Replace signature' : 'Add your signature' }}
            </flux:button>
        </div>
    @endif

    <flux:error name="signature" />
</div>
