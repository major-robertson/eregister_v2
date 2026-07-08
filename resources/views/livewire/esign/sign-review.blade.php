<div class="w-full max-w-2xl">
    @if (session('error'))
    <flux:callout variant="danger" icon="x-circle" class="mb-4">{{ session('error') }}</flux:callout>
    @endif

    <flux:heading size="xl">Review &amp; sign your {{ $title }}</flux:heading>
    <flux:text class="mt-2">{{ $intent }}</flux:text>

    <div class="mt-6 space-y-2">
        @foreach ($documents as $document)
        <div wire:key="review-{{ $document->id }}" class="flex items-center justify-between rounded-lg border border-border bg-white p-3">
            <div class="min-w-0">
                <flux:text class="font-mono text-xs text-gray-500">{{ $document->document_identifier }}</flux:text>
                <flux:text class="font-medium">{{ $loop->iteration }}. {{ $document->label }}</flux:text>
            </div>
            <flux:badge size="sm" color="zinc">Ready</flux:badge>
        </div>
        @endforeach
    </div>

    <div
        x-data="{
            // x-ref on an x-data element registers in the CHILD scope, so the
            // parent finds the capture component by querying its own subtree.
            get capture() {
                const el = this.$root.querySelector('[x-ref=\'signatureCapture\']');
                return el ? Alpine.$data(el) : null;
            },
            signing: false,
            guest: @js($isGuest ?? false),
            async submit() {
                if (this.signing) return;

                let sig = null;
                if (! this.guest && ! $wire.useSaved) {
                    sig = this.capture?.export();
                    if (! sig) return;
                }

                this.signing = true;
                try {
                    await $wire.signAll(sig?.dataUrl ?? null, sig?.strokesJson ?? null, sig?.method ?? null, sig?.typedFont ?? null);
                } finally {
                    this.signing = false;
                }
            },
        }"
        class="mt-6 space-y-4 rounded-lg border border-border bg-white p-6"
    >
        <flux:field>
            <flux:label>{{ $typedNameLabel }}</flux:label>
            <flux:input wire:model.live.debounce.300ms="adoptedName" placeholder="Your full legal name" autocomplete="name" />
            <flux:error name="adoptedName" />
        </flux:field>

        @if (($isGuest ?? false))
            <flux:text class="text-xs text-zinc-500">
                Your typed legal name is adopted as your electronic signature and applied to the document.
            </flux:text>
        @endif

        @if (! ($isGuest ?? false) && $this->savedSignatureDataUri)
            <div class="rounded border border-gray-200 bg-gray-50 p-3">
                <label class="flex items-start gap-2">
                    <input type="checkbox" wire:model.live="useSaved" class="mt-0.5 h-4 w-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500" />
                    <span class="min-w-0">
                        <span class="block text-sm font-medium text-text-primary">Use my saved signature</span>
                        <img src="{{ $this->savedSignatureDataUri }}" alt="Your saved signature" class="mt-1 h-12" />
                    </span>
                </label>
            </div>
        @endif

        @if (! ($isGuest ?? false) && (! $useSaved || ! $this->savedSignatureDataUri))
            <div class="rounded border border-gray-200 bg-gray-50 p-3">
                <div class="mb-2 text-xs text-gray-500">Your signature</div>
                <x-esign.signature-capture :default-name="$adoptedName" />
                <flux:text class="mt-2 text-xs text-zinc-500">
                    This signature is saved to your account and reused for future documents.
                </flux:text>
            </div>
        @endif

        <flux:button type="button" x-on:click="submit()" x-bind:disabled="signing || (! guest && ! $wire.useSaved && ! capture?.hasSignature)"
            variant="primary" icon="pencil-square" class="w-full">
            <span x-show="!signing">{{ $signButton }}</span>
            <span x-show="signing" x-cloak>Signing...</span>
        </flux:button>
        <flux:text class="text-center text-xs text-gray-500">
            By clicking “{{ $signButton }}”, you confirm you intend to be legally bound by your electronic
            signature on each listed document.
        </flux:text>
    </div>
</div>
