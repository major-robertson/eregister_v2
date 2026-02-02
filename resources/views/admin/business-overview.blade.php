<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" icon="arrow-left" :href="route('admin.businesses.index')" wire:navigate />
        <div class="flex-1">
            @php
            $address = $business->business_address ?? [];
            @endphp
            <flux:heading size="xl">{{ $address['city'] ?? 'Unknown' }}, {{ $address['state'] ?? 'N/A' }}</flux:heading>
            <flux:text class="mt-1">{{ $address['street'] ?? 'No address' }}</flux:text>
        </div>
        @if ($this->getStripeCustomerUrl())
        <flux:button variant="ghost" size="sm" icon="arrow-top-right-on-square"
            href="{{ $this->getStripeCustomerUrl() }}" target="_blank">
            View in Stripe
        </flux:button>
        @endif
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-6">
        <div class="rounded-lg border border-border bg-white p-4">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-100">
                    <flux:icon name="users" class="size-5 text-blue-600" />
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Users</flux:text>
                    <flux:heading size="lg">{{ $usersCount }}</flux:heading>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-border bg-white p-4">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-purple-100">
                    <flux:icon name="arrow-path" class="size-5 text-purple-600" />
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Subscriptions</flux:text>
                    <flux:heading size="lg">{{ $activeSubscriptionsCount }}</flux:heading>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-border bg-white p-4">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-100">
                    <flux:icon name="credit-card" class="size-5 text-green-600" />
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Total Paid</flux:text>
                    <flux:heading size="lg">${{ number_format($totalPaymentsSum / 100, 2) }}</flux:heading>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-border bg-white p-4">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-100">
                    <flux:icon name="document-text" class="size-5 text-amber-600" />
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Applications</flux:text>
                    <flux:heading size="lg">{{ $formApplicationsCount }}</flux:heading>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-border bg-white p-4">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-indigo-100">
                    <flux:icon name="clipboard-document-list" class="size-5 text-indigo-600" />
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Lien Projects</flux:text>
                    <flux:heading size="lg">{{ $lienProjectsCount }}</flux:heading>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-border bg-white p-4">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-zinc-100">
                    <flux:icon name="banknotes" class="size-5 text-zinc-600" />
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Payments</flux:text>
                    <flux:heading size="lg">{{ $paymentsCount }}</flux:heading>
                </div>
            </div>
        </div>
    </div>

    <!-- Business Details & Onboarding Status -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Business Details Card -->
        <div class="rounded-lg border border-border bg-white">
            <div class="border-b border-border px-4 py-3">
                <flux:heading size="sm">Business Details</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4 p-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Business ID</flux:text>
                    <flux:text class="mt-1 font-mono text-sm">{{ $business->id }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Created</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $business->created_at->format('M j, Y g:i A') }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Phone</flux:text>
                    <flux:text class="mt-1">{{ $business->phone ?? 'Not set' }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Contractor License</flux:text>
                    <flux:text class="mt-1">{{ $business->contractor_license_number ?? 'Not set' }}</flux:text>
                </div>
                <div class="col-span-2">
                    <flux:text class="text-sm text-gray-500">Stripe Customer ID</flux:text>
                    <div class="mt-1 flex items-center gap-2">
                        @if ($business->stripe_id)
                        <flux:text class="font-mono text-sm">{{ $business->stripe_id }}</flux:text>
                        <button type="button" class="text-gray-400 hover:text-gray-600"
                            onclick="navigator.clipboard.writeText('{{ $business->stripe_id }}'); $dispatch('toast', {message: 'Copied to clipboard', type: 'success'})"
                            title="Copy to clipboard">
                            <flux:icon name="clipboard" class="size-4" />
                        </button>
                        @else
                        <flux:text class="text-gray-400">Not set</flux:text>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Onboarding Status Card -->
        <div class="rounded-lg border border-border bg-white">
            <div class="border-b border-border px-4 py-3">
                <flux:heading size="sm">Onboarding Status</flux:heading>
            </div>
            <div class="space-y-4 p-4">
                <div class="flex items-center justify-between rounded-lg border border-border p-3">
                    <div>
                        <flux:text class="font-medium">Main Onboarding</flux:text>
                        <flux:text class="text-sm text-gray-500">Business profile setup</flux:text>
                    </div>
                    @if ($business->onboarding_completed_at)
                    <div class="text-right">
                        <flux:badge size="sm" color="green">Complete</flux:badge>
                        <flux:text class="mt-1 text-xs text-gray-500">
                            {{ $business->onboarding_completed_at->format('M j, Y') }}
                        </flux:text>
                    </div>
                    @else
                    <flux:badge size="sm" color="amber">Incomplete</flux:badge>
                    @endif
                </div>

                <div class="flex items-center justify-between rounded-lg border border-border p-3">
                    <div>
                        <flux:text class="font-medium">Lien Onboarding</flux:text>
                        <flux:text class="text-sm text-gray-500">Lien service setup</flux:text>
                    </div>
                    @if ($business->lien_onboarding_completed_at)
                    <div class="text-right">
                        <flux:badge size="sm" color="green">Complete</flux:badge>
                        <flux:text class="mt-1 text-xs text-gray-500">
                            {{ $business->lien_onboarding_completed_at->format('M j, Y') }}
                        </flux:text>
                    </div>
                    @else
                    <flux:badge size="sm" color="zinc">Not Started</flux:badge>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Users</flux:heading>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-left text-sm text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">User</th>
                        <th class="px-4 py-3 font-medium">Role</th>
                        <th class="px-4 py-3 font-medium">Email Verified</th>
                        <th class="px-4 py-3 font-medium">2FA</th>
                        <th class="px-4 py-3 font-medium">Joined</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($business->users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <flux:avatar :initials="$user->initials()" size="sm" />
                                <div>
                                    <flux:text class="font-medium">{{ $user->name }}</flux:text>
                                    <flux:text class="text-sm text-gray-500">{{ $user->email }}</flux:text>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" color="{{ $user->pivot->role === 'owner' ? 'blue' : 'zinc' }}">
                                {{ ucfirst($user->pivot->role) }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            @if ($user->hasVerifiedEmail())
                            <flux:badge size="sm" color="green">Verified</flux:badge>
                            @else
                            <flux:badge size="sm" color="amber">Pending</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($user->two_factor_confirmed_at)
                            <flux:badge size="sm" color="green">Enabled</flux:badge>
                            @else
                            <flux:badge size="sm" color="zinc">Disabled</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <flux:text class="text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($user->pivot->created_at)->format('M j, Y') }}
                            </flux:text>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button variant="ghost" size="sm" icon="eye" :href="route('admin.users.show', $user)"
                                wire:navigate>
                                View
                            </flux:button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <flux:text class="text-gray-400">No users found.</flux:text>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Subscriptions Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Subscriptions</flux:heading>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-left text-sm text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Plan</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Started</th>
                        <th class="px-4 py-3 font-medium">Ends At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($subscriptions as $subscription)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <flux:text class="font-medium">{{ ucfirst($subscription->type ?? 'default') }}</flux:text>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm"
                                color="{{ $this->getSubscriptionStatusColor($subscription->stripe_status) }}">
                                {{ $this->formatSubscriptionStatus($subscription->stripe_status) }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            <flux:text class="text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($subscription->created_at)->format('M j, Y') }}
                            </flux:text>
                        </td>
                        <td class="px-4 py-3">
                            @if ($subscription->ends_at)
                            <flux:text class="text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($subscription->ends_at)->format('M j, Y') }}
                            </flux:text>
                            @else
                            <flux:text class="text-sm text-gray-400">-</flux:text>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center">
                            <flux:text class="text-gray-400">No subscriptions found.</flux:text>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Payments</flux:heading>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-left text-sm text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Amount</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Type</th>
                        <th class="px-4 py-3 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <flux:text class="font-medium">{{ $payment->formattedAmount() }}</flux:text>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm"
                                color="{{ $payment->status === \App\Enums\PaymentStatus::Succeeded ? 'green' : 'zinc' }}">
                                {{ $payment->status->label() }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            <flux:text class="text-sm">
                                {{ $payment->stripe_subscription_id ? 'Subscription' : 'One-time' }}
                            </flux:text>
                        </td>
                        <td class="px-4 py-3">
                            <flux:text class="text-sm text-gray-500">
                                {{ $payment->paid_at?->format('M j, Y g:i A') ?? $payment->created_at->format('M j, Y
                                g:i A') }}
                            </flux:text>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center">
                            <flux:text class="text-gray-400">No payments found.</flux:text>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
        <div class="border-t border-border px-4 py-3">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

    <!-- Form Applications Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Form Applications</flux:heading>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-left text-sm text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Form Type</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Phase</th>
                        <th class="px-4 py-3 font-medium">States</th>
                        <th class="px-4 py-3 font-medium">Progress</th>
                        <th class="px-4 py-3 font-medium">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($formApplications as $application)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <flux:text class="font-medium">{{ ucfirst(str_replace('_', ' ', $application->form_type)) }}
                            </flux:text>
                        </td>
                        <td class="px-4 py-3">
                            @if ($application->submitted_at)
                            <flux:badge size="sm" color="green">Submitted</flux:badge>
                            @elseif ($application->paid_at)
                            <flux:badge size="sm" color="blue">Paid</flux:badge>
                            @else
                            <flux:badge size="sm" color="zinc">Draft</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <flux:text class="text-sm">{{ ucfirst($application->current_phase ?? 'N/A') }}</flux:text>
                        </td>
                        <td class="px-4 py-3">
                            <flux:text class="text-sm">
                                {{ implode(', ', $application->selected_states ?? []) ?: 'None' }}
                            </flux:text>
                        </td>
                        <td class="px-4 py-3">
                            <flux:text class="text-sm">
                                {{ $application->completedStateCount() }}/{{ $application->stateCount() }} states
                            </flux:text>
                        </td>
                        <td class="px-4 py-3">
                            <flux:text class="text-sm text-gray-500">
                                {{ $application->created_at->format('M j, Y') }}
                            </flux:text>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <flux:text class="text-gray-400">No form applications found.</flux:text>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($formApplications->hasPages())
        <div class="border-t border-border px-4 py-3">
            {{ $formApplications->links() }}
        </div>
        @endif
    </div>

    <!-- Lien Projects Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Lien Projects</flux:heading>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-left text-sm text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Project</th>
                        <th class="px-4 py-3 font-medium">Wizard Status</th>
                        <th class="px-4 py-3 font-medium">Filings</th>
                        <th class="px-4 py-3 font-medium">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($lienProjects as $project)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div>
                                <flux:text class="font-medium">{{ $project->name ?? 'Unnamed Project' }}</flux:text>
                                <flux:text class="text-sm text-gray-500">
                                    {{ $project->jobsite_city ?? 'Unknown' }}, {{ $project->jobsite_state ?? 'N/A' }}
                                </flux:text>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if ($project->wizard_completed_at)
                            <flux:badge size="sm" color="green">Complete</flux:badge>
                            @else
                            <flux:badge size="sm" color="amber">Draft</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" color="zinc">{{ $project->filings_count }}</flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            <flux:text class="text-sm text-gray-500">
                                {{ $project->created_at->format('M j, Y') }}
                            </flux:text>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center">
                            <flux:text class="text-gray-400">No lien projects found.</flux:text>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lienProjects->hasPages())
        <div class="border-t border-border px-4 py-3">
            {{ $lienProjects->links() }}
        </div>
        @endif
    </div>
</div>