<div>
    @if ($submitted)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-6 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-emerald-900">Message sent!</h3>
            <p class="mt-2 text-emerald-700">Thank you for contacting us. We'll get back to you as soon as possible.</p>
            <button wire:click="$set('submitted', false)" class="mt-4 text-sm font-medium text-emerald-600 hover:text-emerald-700">
                Send another message
            </button>
        </div>
    @else
        <form wire:submit="submit" class="space-y-6">
            <div>
                <flux:input
                    wire:model="name"
                    label="Name"
                    placeholder="Your name"
                    required
                />
            </div>

            <div>
                <flux:input
                    wire:model="business_name"
                    label="Business Name"
                    placeholder="Your business name (optional)"
                />
            </div>

            <div>
                <flux:input
                    wire:model="email"
                    type="email"
                    label="Email"
                    placeholder="you@example.com"
                    required
                />
            </div>

            <div>
                <flux:textarea
                    wire:model="message"
                    label="Message"
                    placeholder="How can we help you?"
                    rows="5"
                    required
                />
            </div>

            <div>
                <flux:button type="submit" variant="primary" class="w-full justify-center">
                    <span wire:loading.remove wire:target="submit">Send Message</span>
                    <span wire:loading wire:target="submit">Sending...</span>
                </flux:button>
            </div>
        </form>
    @endif
</div>
