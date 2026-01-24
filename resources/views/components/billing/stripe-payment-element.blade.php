@props(['clientSecret', 'paymentIntentId', 'paymentId', 'returnUrl', 'formattedAmount', 'isReady' => false])

{{-- wire:ignore prevents Livewire from morphing this DOM, which breaks Stripe Elements --}}
<div wire:ignore>
    <div 
        x-data="stripePayment(@js($clientSecret), @js($paymentIntentId), @js($returnUrl), @js($isReady))" 
        x-init="init()"
    >
        {{-- Unique ID per payment prevents "already mounted" errors --}}
        <div id="payment-element-{{ $paymentId }}" class="mb-4"></div>
        
        <div x-show="errorMessage" x-cloak class="text-red-600 text-sm mb-4">
            <span x-text="errorMessage"></span>
        </div>
        
        <flux:button 
            @click="submit()" 
            x-bind:disabled="!canSubmit"
            variant="primary" 
            class="w-full justify-center"
        >
            <span x-show="!isProcessing">Pay {{ $formattedAmount }}</span>
            <span x-show="isProcessing">Processing...</span>
        </flux:button>
    </div>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('stripePayment', (clientSecret, paymentIntentId, returnUrl, isReady) => ({
        stripe: null,
        elements: null,
        isProcessing: false,
        isInitialized: false,
        errorMessage: '',
        
        get canSubmit() {
            return isReady && this.isInitialized && !this.isProcessing;
        },
        
        init() {
            if (!clientSecret) {
                this.errorMessage = 'Payment not ready. Please refresh the page.';
                return;
            }
            
            this.stripe = Stripe('{{ config("cashier.key") }}');
            this.elements = this.stripe.elements({ clientSecret });
            const paymentElement = this.elements.create('payment');
            
            paymentElement.on('ready', () => {
                this.isInitialized = true;
            });
            
            // Mount to unique ID per payment
            paymentElement.mount('#payment-element-{{ $paymentId }}');
        },
        
        async submit() {
            if (!this.canSubmit) return;
            
            this.isProcessing = true;
            this.errorMessage = '';
            
            const { error } = await this.stripe.confirmPayment({
                elements: this.elements,
                confirmParams: { return_url: returnUrl },
                redirect: 'if_required', // Cards won't redirect, 3DS will
            });
            
            if (error) {
                this.errorMessage = error.message;
                this.isProcessing = false;
            } else {
                // Payment succeeded without redirect - use server-provided PI id
                window.location.href = returnUrl + '?payment_intent=' + paymentIntentId;
            }
        }
    }));
});
</script>
@endpush
