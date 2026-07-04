<div class="space-y-6">
    <x-ui.page-header title="Resale Certificates" subtitle="Generate resale certificates for every state you buy in.">
        @if ($this->isSubscribed && $this->profileComplete)
            <x-slot:actions>
                <flux:button href="{{ route('resale-cert.certificates.create') }}" variant="primary" icon="plus" wire:navigate>
                    Generate Certificate
                </flux:button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if (session('success'))
        <flux:callout color="green" icon="check-circle">{{ session('success') }}</flux:callout>
    @endif
    @if (session('info'))
        <flux:callout color="blue" icon="information-circle">{{ session('info') }}</flux:callout>
    @endif

    @if (! $this->isSubscribed)
        {{-- Not subscribed: full pricing pitch with a direct path to checkout --}}
        <div class="mx-auto max-w-lg">
            <x-ui.card>
                <div class="space-y-6 py-4 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-500/10">
                        <flux:icon name="document-check" class="size-7 text-blue-600" />
                    </div>

                    <div>
                        <flux:heading size="xl">$297<span class="text-base font-normal text-zinc-500">/year</span></flux:heading>
                        <flux:text class="text-zinc-500">Unlimited certificates · Less than $25/month</flux:text>
                    </div>

                    <ul class="mx-auto max-w-xs space-y-2 text-left text-sm text-zinc-700">
                        @foreach ([
                            'Unlimited certificate generation',
                            'All supported states',
                            'MTC & SST uniform certificates',
                            'E-signature applied automatically',
                            'Unlimited vendor management',
                            'Expiration tracking & alerts',
                        ] as $feature)
                            <li class="flex items-center gap-2">
                                <flux:icon name="check" class="size-4 shrink-0 text-green-600" />
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    {{-- Full page load: the checkout page mounts the Stripe Payment Element. --}}
                    <x-ui.action-button href="{{ route('resale-cert.checkout') }}">
                        Subscribe Now
                    </x-ui.action-button>

                    <flux:text class="text-xs text-zinc-500">
                        Secure checkout powered by Stripe. Cancel anytime.
                    </flux:text>
                </div>
            </x-ui.card>
        </div>
    @elseif (! $this->profileComplete)
        {{-- Subscribed but profile incomplete: onboarding prompt --}}
        <x-ui.card>
            <div class="mx-auto max-w-xl space-y-4 py-8 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-500/10">
                    <flux:icon name="clipboard-document-list" class="size-7 text-blue-600" />
                </div>
                <flux:heading size="lg">Set up your resale profile</flux:heading>
                <flux:text class="text-zinc-600">
                    Three quick steps — what you sell, your state tax registrations, and your e-signature.
                    Takes about 5 minutes, then you can generate certificates instantly.
                </flux:text>
                <div class="pt-2">
                    <x-ui.action-button href="{{ route('resale-cert.onboarding') }}" wire:navigate>
                        Get Started
                    </x-ui.action-button>
                </div>
            </div>
        </x-ui.card>
    @else
        @if ($this->subscription?->onGracePeriod())
            <flux:callout color="amber" icon="exclamation-triangle">
                Your subscription is canceled and access ends
                {{ $this->subscription->ends_at->eastern()->format('F j, Y') }}.
            </flux:callout>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <a href="{{ route('resale-cert.vendors.index') }}" wire:navigate>
                <x-ui.stat-card :value="$this->stats['vendors']" label="Vendors" icon="building-storefront" color="primary" />
            </a>
            <a href="{{ route('resale-cert.certificates.index') }}" wire:navigate>
                <x-ui.stat-card :value="$this->stats['certificates']" label="Certificates" icon="document-check" color="success" />
            </a>
            <a href="{{ route('resale-cert.certificates.index', ['statusFilter' => 'expiring']) }}" wire:navigate>
                <x-ui.stat-card :value="$this->stats['expiring']" label="Expiring within 90 days" icon="clock" color="warning" />
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-ui.card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <flux:heading size="lg">Recent Certificates</flux:heading>
                        <flux:button href="{{ route('resale-cert.certificates.index') }}" variant="ghost" size="sm" wire:navigate>
                            View all
                        </flux:button>
                    </div>
                </x-slot:header>

                @if ($this->recentCertificates->isEmpty())
                    <div class="py-8 text-center">
                        <flux:icon name="document-check" class="mx-auto h-10 w-10 text-zinc-300" />
                        <flux:text class="mt-2 text-zinc-500">No certificates yet.</flux:text>
                        <flux:button href="{{ route('resale-cert.certificates.create') }}" variant="primary" size="sm" class="mt-3" wire:navigate>
                            Generate your first certificate
                        </flux:button>
                    </div>
                @else
                    <div class="divide-y divide-border">
                        @foreach ($this->recentCertificates as $certificate)
                            <a href="{{ route('resale-cert.certificates.show', $certificate) }}" wire:navigate
                                class="flex items-center justify-between gap-3 py-3 hover:bg-zinc-50">
                                <div>
                                    <flux:text class="font-medium text-text-primary">{{ $certificate->displayName() }}</flux:text>
                                    <flux:text class="text-sm text-zinc-500">{{ $certificate->vendor?->legal_name }}</flux:text>
                                </div>
                                <flux:badge :color="$certificate->statusColor()" size="sm">{{ $certificate->statusLabel() }}</flux:badge>
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card>
                <x-slot:header>
                    <flux:heading size="lg">Expiring Soon</flux:heading>
                </x-slot:header>

                @if ($this->expiringCertificates->isEmpty())
                    <div class="py-8 text-center">
                        <flux:icon name="check-circle" class="mx-auto h-10 w-10 text-green-400" />
                        <flux:text class="mt-2 text-zinc-500">Nothing expires in the next 90 days.</flux:text>
                    </div>
                @else
                    <div class="divide-y divide-border">
                        @foreach ($this->expiringCertificates as $certificate)
                            @php $days = (int) now()->diffInDays($certificate->expiration_date); @endphp
                            <a href="{{ route('resale-cert.certificates.show', $certificate) }}" wire:navigate
                                class="flex items-center justify-between gap-3 py-3 hover:bg-zinc-50">
                                <div>
                                    <flux:text class="font-medium text-text-primary">{{ $certificate->displayName() }}</flux:text>
                                    <flux:text class="text-sm text-zinc-500">{{ $certificate->vendor?->legal_name }}</flux:text>
                                </div>
                                <flux:badge :color="$days <= 30 ? 'red' : ($days <= 60 ? 'amber' : 'zinc')" size="sm">
                                    {{ $days }} days
                                </flux:badge>
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </div>
    @endif
</div>
