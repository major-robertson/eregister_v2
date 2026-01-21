<div class="w-full max-w-lg">
    {{-- Progress dots --}}
    <div class="mb-16 flex justify-center gap-2">
        @for ($i = 1; $i <= $totalSteps; $i++)
            <div @class([
                'h-2 w-2 rounded-full transition-colors',
                'bg-primary' => $i <= $step,
                'bg-zinc-300 dark:bg-zinc-600' => $i > $step,
            ])></div>
        @endfor
    </div>

    @if ($step === 1)
        {{-- Step 1: Entity Information --}}
        <div class="text-center">
            <h1 class="text-3xl font-bold tracking-tight text-text-primary sm:text-4xl">
                Tell us about your business
            </h1>
            <p class="mt-4 text-lg text-text-secondary">
                This information helps us prepare accurate lien documents.
            </p>
        </div>

        <div class="mt-12 space-y-6">
            <div>
                <label class="mb-2 block text-sm font-medium text-text-secondary">What type of entity is your business? *</label>
                <select
                    wire:model.live="entityType"
                    class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary focus:border-primary focus:outline-none focus:ring-0"
                >
                    <option value="">Select entity type...</option>
                    @foreach ($entityTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('entityType')
                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            @if ($this->requiresStateOfIncorporation())
                <div>
                    <label class="mb-2 block text-sm font-medium text-text-secondary">State of incorporation/registration *</label>
                    <select
                        wire:model="stateOfIncorporation"
                        class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary focus:border-primary focus:outline-none focus:ring-0"
                    >
                        <option value="">Select state...</option>
                        @foreach ($states as $code => $name)
                            <option value="{{ $code }}">{{ $code }} - {{ $name }}</option>
                        @endforeach
                    </select>
                    @error('stateOfIncorporation')
                        <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="flex justify-end pt-6">
                <flux:button wire:click="nextStep" variant="primary" icon-trailing="arrow-right">
                    Continue
                </flux:button>
            </div>
        </div>

    @elseif ($step === 2)
        {{-- Step 2: Business Contact --}}
        <div class="text-center">
            <h1 class="text-3xl font-bold tracking-tight text-text-primary sm:text-4xl">
                Business Contact
            </h1>
            <p class="mt-4 text-lg text-text-secondary">
                We'll include this on your lien documents.
            </p>
        </div>

        <div class="mt-12 space-y-6">
            <div
                x-data="{
                    phone: @js($this->formatPhoneForDisplay($phone)),
                    formatPhone() {
                        let digits = this.phone.replace(/\D/g, '').substring(0, 10);
                        if (digits.length === 0) {
                            this.phone = '';
                        } else if (digits.length <= 3) {
                            this.phone = '(' + digits;
                        } else if (digits.length <= 6) {
                            this.phone = '(' + digits.substring(0, 3) + ') ' + digits.substring(3);
                        } else {
                            this.phone = '(' + digits.substring(0, 3) + ') ' + digits.substring(3, 6) + '-' + digits.substring(6);
                        }
                        $wire.set('phone', digits);
                    }
                }"
            >
                <label class="mb-2 block text-sm font-medium text-text-secondary">Phone Number *</label>
                <input
                    type="tel"
                    x-model="phone"
                    x-on:input="formatPhone()"
                    placeholder="(555) 123-4567"
                    class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
                />
                @error('phone')
                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-text-secondary">
                    Contractor License Number <span class="text-text-tertiary">(optional)</span>
                </label>
                <input
                    type="text"
                    wire:model="contractorLicenseNumber"
                    placeholder="e.g., 12345678"
                    class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
                />
                @error('contractorLicenseNumber')
                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-between pt-6">
                <flux:button wire:click="previousStep" variant="ghost" icon="arrow-left">
                    Back
                </flux:button>
                <flux:button wire:click="nextStep" variant="primary" icon-trailing="arrow-right">
                    Continue
                </flux:button>
            </div>
        </div>

    @elseif ($step === 3)
        {{-- Step 3: Authorized Signer --}}
        <div class="text-center">
            <h1 class="text-3xl font-bold tracking-tight text-text-primary sm:text-4xl">
                Authorized Signer
            </h1>
            <p class="mt-4 text-lg text-text-secondary">
                This person will be listed as the authorized signer on all lien filings.
            </p>
        </div>

        <div class="mt-12 space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-text-secondary">First Name *</label>
                    <input
                        type="text"
                        wire:model="signerFirstName"
                        placeholder="John"
                        class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
                    />
                    @error('signerFirstName')
                        <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-text-secondary">Last Name *</label>
                    <input
                        type="text"
                        wire:model="signerLastName"
                        placeholder="Smith"
                        class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
                    />
                    @error('signerLastName')
                        <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-text-secondary">Title *</label>
                <input
                    type="text"
                    wire:model="signerTitle"
                    placeholder="President, Owner, Manager, etc."
                    class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
                />
                @error('signerTitle')
                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-between pt-6">
                <flux:button wire:click="previousStep" variant="ghost" icon="arrow-left">
                    Back
                </flux:button>
                <flux:button wire:click="complete" variant="primary" icon-trailing="check">
                    Complete Setup
                </flux:button>
            </div>
        </div>
    @endif
</div>
