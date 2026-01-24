<x-layouts.lien title="Payment Successful">
    <div class="max-w-lg mx-auto space-y-6">
        <x-ui.page-header title="Payment Successful">
            <x-slot:breadcrumbs>
                <x-ui.breadcrumb :items="[
                    ['label' => 'Lien Projects', 'url' => route('lien.projects.index')],
                    ['label' => $filing->project->name, 'url' => route('lien.projects.show', $filing->project)],
                    ['label' => 'Payment Successful'],
                ]" />
            </x-slot:breadcrumbs>
        </x-ui.page-header>

        <x-ui.card>
            <div class="text-center space-y-4">
                <div class="mx-auto w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                    <flux:icon name="check" class="w-8 h-8 text-green-600 dark:text-green-400" />
                </div>
                
                <flux:heading size="lg">Thank you for your payment!</flux:heading>
                
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    Your payment of {{ $payment->formattedAmount() }} has been processed successfully.
                </flux:text>
            </div>

            <div class="border-t border-zinc-200 dark:border-zinc-700 mt-6 pt-6">
                <x-ui.info-list>
                    <x-ui.info-list.item label="Document">
                        {{ $filing->documentType->name }}
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="Project">
                        {{ $filing->project->name }}
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="Service Level">
                        {{ $filing->service_level->label() }}
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="Amount Paid">
                        {{ $payment->formattedAmount() }}
                    </x-ui.info-list.item>
                </x-ui.info-list>
            </div>
        </x-ui.card>

        <div class="flex justify-center gap-4">
            <flux:button href="{{ route('lien.filings.show', $filing) }}" variant="primary">
                View Filing
            </flux:button>
            <flux:button href="{{ route('lien.projects.show', $filing->project) }}" variant="ghost">
                Back to Project
            </flux:button>
        </div>
    </div>
</x-layouts.lien>
