<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" icon="arrow-left" :href="route('admin.users.index')" wire:navigate />
        <div>
            <flux:heading size="xl">{{ $user->name }}</flux:heading>
            <flux:text class="mt-1">{{ $user->email }}</flux:text>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-border bg-white p-4">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-100">
                    <flux:icon name="building-office" class="size-5 text-blue-600" />
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Businesses</flux:text>
                    <flux:heading size="lg">{{ $user->businesses->count() }}</flux:heading>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-border bg-white p-4">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-100">
                    <flux:icon name="credit-card" class="size-5 text-green-600" />
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Total Payments</flux:text>
                    <flux:heading size="lg">${{ number_format($totalPaymentsSum / 100, 2) }}</flux:heading>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-border bg-white p-4">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-purple-100">
                    <flux:icon name="arrow-path" class="size-5 text-purple-600" />
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Active Subscriptions</flux:text>
                    <flux:heading size="lg">{{ $activeSubscriptionsCount }}</flux:heading>
                </div>
            </div>
        </div>
    </div>

    <!-- User Details Card -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">User Details</flux:heading>
        </div>
        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <flux:text class="text-sm text-gray-500">Email Verified</flux:text>
                <div class="mt-1">
                    @if ($user->hasVerifiedEmail())
                    <flux:badge size="sm" color="green">Verified</flux:badge>
                    @else
                    <flux:badge size="sm" color="amber">Not Verified</flux:badge>
                    @endif
                </div>
            </div>
            <div>
                <flux:text class="text-sm text-gray-500">Two-Factor Auth</flux:text>
                <div class="mt-1">
                    @if ($user->two_factor_confirmed_at)
                    <flux:badge size="sm" color="green">Enabled</flux:badge>
                    @else
                    <flux:badge size="sm" color="zinc">Disabled</flux:badge>
                    @endif
                </div>
            </div>
            <div>
                <flux:text class="text-sm text-gray-500">Registered</flux:text>
                <flux:text class="mt-1 font-medium">{{ $user->created_at->eastern()->format('M j, Y g:i A') }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-gray-500">User ID</flux:text>
                <flux:text class="mt-1 font-mono text-sm">{{ $user->id }}</flux:text>
            </div>
        </div>
    </div>

    <!-- Businesses Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Businesses</flux:heading>
        </div>

        <div class="px-4 pb-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Business</flux:table.column>
                    <flux:table.column>Role</flux:table.column>
                    <flux:table.column>Onboarding</flux:table.column>
                    <flux:table.column>Lien Onboarding</flux:table.column>
                    <flux:table.column>Subscription</flux:table.column>
                    <flux:table.column>Joined</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($user->businesses as $business)
                    @php
                    $address = $business->business_address ?? [];
                    $activeSubscription = $business->subscriptions->first(fn ($s) => $s->stripe_status === 'active');
                    @endphp
                    <flux:table.row wire:key="business-{{ $business->id }}">
                        <flux:table.cell>
                            <div>
                                <flux:text class="font-medium">
                                    {{ $address['city'] ?? 'Unknown' }}, {{ $address['state'] ?? 'N/A' }}
                                </flux:text>
                                <flux:text class="text-sm text-gray-500">
                                    {{ $address['street'] ?? '' }}
                                </flux:text>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="{{ $business->pivot->role === 'owner' ? 'blue' : 'zinc' }}">
                                {{ ucfirst($business->pivot->role) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($business->onboarding_completed_at)
                            <flux:badge size="sm" color="green">Complete</flux:badge>
                            @else
                            <flux:badge size="sm" color="amber">Incomplete</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($business->lien_onboarding_completed_at)
                            <flux:badge size="sm" color="green">Complete</flux:badge>
                            @else
                            <flux:badge size="sm" color="zinc">Not Started</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($activeSubscription)
                            <flux:badge size="sm" color="green">Active</flux:badge>
                            @else
                            <flux:badge size="sm" color="zinc">None</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text class="text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($business->pivot->created_at)->format('M j, Y') }}
                            </flux:text>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button variant="ghost" size="sm" icon="eye"
                                :href="route('admin.businesses.show', $business)" wire:navigate>
                                View
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="py-12 text-center">
                            <flux:text class="text-gray-400">No businesses found.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <!-- Recent Payments Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Recent Payments</flux:heading>
        </div>

        <div class="px-4 pb-4">
            <flux:table :paginate="$recentPayments">
                <flux:table.columns>
                    <flux:table.column>Amount</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Business</flux:table.column>
                    <flux:table.column>Date</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($recentPayments as $payment)
                    <flux:table.row wire:key="payment-{{ $payment->id }}">
                        <flux:table.cell>
                            <flux:text class="font-medium">{{ $payment->formattedAmount() }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm"
                                color="{{ $payment->status === \App\Enums\PaymentStatus::Succeeded ? 'green' : 'zinc' }}">
                                {{ $payment->status->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text class="text-sm">
                                {{ $payment->stripe_subscription_id ? 'Subscription' : 'One-time' }}
                            </flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($payment->business)
                            @php
                            $addr = $payment->business->business_address ?? [];
                            @endphp
                            <flux:text class="text-sm">
                                {{ $addr['city'] ?? 'Unknown' }}, {{ $addr['state'] ?? 'N/A' }}
                            </flux:text>
                            @else
                            <flux:text class="text-sm text-gray-400">Unknown</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text class="text-sm text-gray-500">
                                {{ $payment->paid_at?->eastern()->format('M j, Y g:i A') ?? $payment->created_at->eastern()->format('M j, Y
                                g:i A') }}
                            </flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-12 text-center">
                            <flux:text class="text-gray-400">No payments found.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>