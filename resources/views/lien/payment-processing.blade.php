<x-layouts.lien title="Processing Payment">
    <div class="max-w-lg mx-auto space-y-6">
        <x-ui.page-header title="Processing Payment">
            <x-slot:breadcrumbs>
                <x-ui.breadcrumb :items="[
                    ['label' => 'Lien Projects', 'url' => route('lien.projects.index')],
                    ['label' => $filing->project->name, 'url' => route('lien.projects.show', $filing->project)],
                    ['label' => 'Processing Payment'],
                ]" />
            </x-slot:breadcrumbs>
        </x-ui.page-header>

        <x-ui.card>
            <div 
                class="text-center space-y-4"
                x-data="paymentPoller(@js(route('lien.api.payment-status', $filing)), @js(route('lien.filings.payment-confirmation', $filing)))"
                x-init="startPolling()"
            >
                <div class="mx-auto w-16 h-16 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
                    <flux:icon name="arrow-path" class="w-8 h-8 text-amber-600 dark:text-amber-400 animate-spin" />
                </div>
                
                <flux:heading size="lg">Processing your payment...</flux:heading>
                
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    Please wait while we confirm your payment. This usually takes just a few seconds.
                </flux:text>
                
                <flux:text class="text-sm text-zinc-500">
                    You'll be redirected automatically once payment is confirmed.
                </flux:text>
            </div>
        </x-ui.card>

        <div class="text-center">
            <flux:button href="{{ route('lien.filings.show', $filing) }}" variant="ghost">
                Check Filing Status
            </flux:button>
        </div>
    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('paymentPoller', (statusUrl, confirmationUrl) => ({
            polling: false,
            attempts: 0,
            maxAttempts: 30, // 30 attempts * 2 seconds = 1 minute max
            
            startPolling() {
                this.polling = true;
                this.poll();
            },
            
            async poll() {
                if (!this.polling || this.attempts >= this.maxAttempts) {
                    return;
                }
                
                this.attempts++;
                
                try {
                    const response = await fetch(statusUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        
                        if (data.status === 'succeeded' || data.paid) {
                            // Payment confirmed - redirect to success page
                            window.location.href = confirmationUrl;
                            return;
                        }
                        
                        if (data.status === 'failed' || data.status === 'canceled') {
                            // Payment failed - redirect to checkout to retry
                            window.location.href = '{{ route('lien.filings.checkout', $filing) }}';
                            return;
                        }
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
                
                // Continue polling after 2 seconds
                setTimeout(() => this.poll(), 2000);
            },
            
            stopPolling() {
                this.polling = false;
            }
        }));
    });
    </script>
</x-layouts.lien>
