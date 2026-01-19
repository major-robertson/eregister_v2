<x-layouts.app :title="$business->name ?? 'Dashboard'">
    <div class="mx-auto max-w-6xl px-6 py-8">
        <x-ui.page-header :title="$business->name ?? 'Dashboard'" subtitle="Manage your business and applications">
            <x-slot:actions>
                <a href="{{ route('forms.start', ['formType' => 'sales_tax_permit']) }}" class="btn-action">
                    <flux:icon name="plus-circle" class="size-5" />
                    New Application
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        @if (session('success'))
            <x-ui.card class="mb-6 border-success/20 bg-success/5">
                <div class="flex items-center gap-3 text-success">
                    <flux:icon name="check-circle" class="size-5" />
                    {{ session('success') }}
                </div>
            </x-ui.card>
        @endif

        @if (session('error'))
            <x-ui.card class="mb-6 border-danger/20 bg-danger/5">
                <div class="flex items-center gap-3 text-danger">
                    <flux:icon name="x-circle" class="size-5" />
                    {{ session('error') }}
                </div>
            </x-ui.card>
        @endif

        {{-- Quick Actions --}}
        <div class="mb-8">
            <h2 class="mb-4 text-lg font-semibold text-text-primary">Quick Actions</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('forms.start', ['formType' => 'sales_tax_permit']) }}"
                   class="flex items-center gap-4 rounded-lg border border-border bg-white p-6 transition hover:border-primary hover:shadow-md">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <flux:icon name="clipboard-document" class="size-6" />
                    </div>
                    <div>
                        <div class="font-medium text-text-primary">Sales Tax Permit</div>
                        <div class="text-sm text-text-secondary">Apply for multiple states</div>
                    </div>
                </a>
                <a href="{{ route('forms.start', ['formType' => 'llc']) }}"
                   class="flex items-center gap-4 rounded-lg border border-border bg-white p-6 transition hover:border-primary hover:shadow-md">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-accent/10 text-accent">
                        <flux:icon name="building-office" class="size-6" />
                    </div>
                    <div>
                        <div class="font-medium text-text-primary">LLC Formation</div>
                        <div class="text-sm text-text-secondary">Form an LLC in any state</div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Recent Applications --}}
        <x-ui.card :padding="false" class="mb-8">
            <x-slot:header>
                <h3 class="text-lg font-semibold text-text-primary">Recent Applications</h3>
            </x-slot:header>
            @php
                $applications = $business->formApplications()->latest()->limit(5)->get();
            @endphp

            @forelse ($applications as $application)
                <div class="flex items-center justify-between border-b border-border px-6 py-4 last:border-b-0">
                    <div>
                        <div class="font-medium text-text-primary">
                            {{ str_replace('_', ' ', ucwords($application->form_type)) }}
                        </div>
                        <div class="text-sm text-text-secondary">
                            {{ count($application->selected_states) }} state(s) &bull; {{ ucfirst($application->status) }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($application->status === 'submitted')
                            <flux:badge color="green" size="sm">Submitted</flux:badge>
                        @elseif ($application->isPaid())
                            <flux:button href="{{ route('forms.application', $application) }}" size="sm" variant="ghost">
                                Continue
                            </flux:button>
                        @else
                            <flux:button href="{{ route('portal.checkout', $application) }}" size="sm" variant="primary">
                                Pay Now
                            </flux:button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-text-secondary">
                    No applications yet. Get started above!
                </div>
            @endforelse
        </x-ui.card>

        {{-- Business Info --}}
        <x-ui.card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-text-primary">Business Information</h3>
                    <flux:button href="{{ route('portal.onboarding') }}" size="sm" variant="ghost">
                        Edit
                    </flux:button>
                </div>
            </x-slot:header>
            @php
                $address = $business->business_address;
                $addressStr = $address
                    ? ($address['line1'] ?? '') .
                      (isset($address['line2']) && $address['line2'] ? ', ' . $address['line2'] : '') .
                      (isset($address['city']) ? ', ' . $address['city'] : '') .
                      (isset($address['state']) ? ', ' . $address['state'] : '') .
                      (isset($address['zip']) ? ' ' . $address['zip'] : '')
                    : 'Not provided';
            @endphp
            <x-ui.info-list :items="[
                'Business Name' => $business->name ?? $business->legal_name ?? 'Not provided',
                'Legal Name' => $business->legal_name ?? 'Not provided',
                'Address' => $addressStr,
            ]" />
        </x-ui.card>
    </div>
</x-layouts.app>
