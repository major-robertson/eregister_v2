<x-layouts.app :title="$business->name ?? 'Dashboard'">
    <div class="mx-auto max-w-6xl px-6 py-10">
        <x-ui.page-header :title="$business->name ?? 'Dashboard'" subtitle="Manage your business and applications" />

        @if (session('success'))
            <x-ui.card class="mb-8 border-success/20 bg-success/5">
                <div class="flex items-center gap-3 text-success">
                    <flux:icon name="check-circle" class="size-5" />
                    {{ session('success') }}
                </div>
            </x-ui.card>
        @endif

        @if (session('error'))
            <x-ui.card class="mb-8 border-danger/20 bg-danger/5">
                <div class="flex items-center gap-3 text-danger">
                    <flux:icon name="x-circle" class="size-5" />
                    {{ session('error') }}
                </div>
            </x-ui.card>
        @endif

        {{-- Quick Actions --}}
        <section class="mb-12">
            <h2 class="mb-6 text-lg font-semibold text-text-primary">Quick Actions</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                {{-- Sales Tax Permit Card --}}
                <div class="group rounded-xl border border-border bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <flux:icon name="clipboard-document" class="size-6" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-text-primary">Sales Tax Permit</h3>
                            <p class="mt-1 text-sm text-text-secondary">Apply for multiple states at once</p>
                        </div>
                    </div>
                    <div class="mt-5 pt-5 border-t border-border">
                        <flux:button href="{{ route('forms.start', ['formType' => 'sales_tax_permit']) }}" variant="primary" class="w-full justify-center">
                            Get Started
                        </flux:button>
                    </div>
                </div>

                {{-- LLC Formation Card --}}
                <div class="group rounded-xl border border-border bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-accent/10 text-accent">
                            <flux:icon name="building-office" class="size-6" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-text-primary">LLC Formation</h3>
                            <p class="mt-1 text-sm text-text-secondary">Form an LLC in any state</p>
                        </div>
                    </div>
                    <div class="mt-5 pt-5 border-t border-border">
                        <flux:button href="{{ route('forms.start', ['formType' => 'llc']) }}" variant="primary" class="w-full justify-center">
                            Get Started
                        </flux:button>
                    </div>
                </div>
            </div>
        </section>

        {{-- Recent Applications --}}
        <section class="mb-12">
            <h2 class="mb-6 text-lg font-semibold text-text-primary">Recent Applications</h2>
            @php
                $applications = $business->formApplications()->latest()->limit(5)->get();
            @endphp

            @if($applications->count() > 0)
                <div class="space-y-4">
                    @foreach ($applications as $application)
                        <div class="flex items-center justify-between rounded-xl border border-border bg-white px-6 py-5 shadow-sm">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100">
                                    <flux:icon name="document-text" class="size-5 text-text-secondary" />
                                </div>
                                <div>
                                    <div class="font-medium text-text-primary">
                                        {{ str_replace('_', ' ', ucwords($application->form_type)) }}
                                    </div>
                                    <div class="mt-0.5 text-sm text-text-secondary">
                                        {{ count($application->selected_states) }} state(s)
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                @if ($application->status === 'submitted')
                                    <flux:badge color="green" size="sm">Submitted</flux:badge>
                                @elseif ($application->isPaid())
                                    <span class="text-sm text-text-secondary">Draft</span>
                                    <flux:button href="{{ route('forms.application', $application) }}" size="sm" variant="filled">
                                        Continue
                                    </flux:button>
                                @else
                                    <span class="text-sm text-text-secondary">Unpaid</span>
                                    <flux:button href="{{ route('portal.checkout', $application) }}" size="sm" variant="primary">
                                        Pay Now
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-border bg-white px-6 py-16 text-center shadow-sm">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100">
                        <flux:icon name="document-text" class="size-7 text-text-tertiary" />
                    </div>
                    <p class="font-medium text-text-primary">No applications yet</p>
                    <p class="mt-1 text-sm text-text-secondary">Get started by choosing a quick action above!</p>
                </div>
            @endif
        </section>

        {{-- Business Info --}}
        <section>
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-text-primary">Business Information</h2>
                <flux:button href="{{ route('portal.onboarding') }}" size="sm" variant="ghost" icon="pencil">
                    Edit
                </flux:button>
            </div>
            <div class="rounded-xl border border-border bg-white p-6 shadow-sm">
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
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm text-text-secondary">Business Name</dt>
                        <dd class="mt-1 font-medium text-text-primary">{{ $business->name ?? $business->legal_name ?? 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-text-secondary">Legal Name</dt>
                        <dd class="mt-1 font-medium text-text-primary">{{ $business->legal_name ?? 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-text-secondary">Address</dt>
                        <dd class="mt-1 font-medium text-text-primary">{{ $addressStr }}</dd>
                    </div>
                </dl>
            </div>
        </section>
    </div>
</x-layouts.app>
