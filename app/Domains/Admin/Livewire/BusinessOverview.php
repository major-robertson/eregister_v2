<?php

namespace App\Domains\Admin\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Lien\Models\LienProject;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BusinessOverview extends Component
{
    use WithPagination;

    public Business $business;

    public function mount(Business $business): void
    {
        $this->business = $business;
    }

    public function render(): View
    {
        // Eager load users with pivot data
        $this->business->load([
            'users' => fn ($q) => $q->withPivot(['role', 'created_at']),
        ]);

        // Summary counts
        $usersCount = $this->business->users->count();

        $activeSubscriptionsCount = DB::table('subscriptions')
            ->where('business_id', $this->business->id)
            ->where('stripe_status', 'active')
            ->count();

        $paymentsCount = Payment::query()
            ->where('business_id', $this->business->id)
            ->where('status', PaymentStatus::Succeeded)
            ->count();

        $totalPaymentsSum = Payment::query()
            ->where('business_id', $this->business->id)
            ->where('status', PaymentStatus::Succeeded)
            ->sum('amount_cents');

        $formApplicationsCount = FormApplication::query()
            ->where('business_id', $this->business->id)
            ->count();

        $inProgressApplicationsCount = FormApplication::query()
            ->where('business_id', $this->business->id)
            ->whereNull('submitted_at')
            ->count();

        $lienProjectsCount = LienProject::forBusiness($this->business)->count();

        // Get subscriptions
        $subscriptions = DB::table('subscriptions')
            ->where('business_id', $this->business->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Paginated tables
        $payments = Payment::query()
            ->where('business_id', $this->business->id)
            ->latest('paid_at')
            ->paginate(15, pageName: 'payments');

        $formApplications = FormApplication::query()
            ->where('business_id', $this->business->id)
            ->with('states')
            ->latest()
            ->paginate(15, pageName: 'applications');

        $lienProjects = LienProject::forBusiness($this->business)
            ->withCount('filings')
            ->latest()
            ->paginate(15, pageName: 'lien_projects');

        return view('admin.business-overview', [
            'usersCount' => $usersCount,
            'activeSubscriptionsCount' => $activeSubscriptionsCount,
            'paymentsCount' => $paymentsCount,
            'totalPaymentsSum' => $totalPaymentsSum,
            'formApplicationsCount' => $formApplicationsCount,
            'inProgressApplicationsCount' => $inProgressApplicationsCount,
            'lienProjectsCount' => $lienProjectsCount,
            'subscriptions' => $subscriptions,
            'payments' => $payments,
            'formApplications' => $formApplications,
            'lienProjects' => $lienProjects,
        ])->layout('layouts.admin', ['title' => 'Business Overview']);
    }

    /**
     * Format subscription status for display.
     */
    public function formatSubscriptionStatus(string $status): string
    {
        return match ($status) {
            'active' => 'Active',
            'canceled' => 'Canceled',
            'incomplete' => 'Incomplete',
            'incomplete_expired' => 'Expired',
            'past_due' => 'Past Due',
            'trialing' => 'Trialing',
            'unpaid' => 'Unpaid',
            default => ucfirst($status),
        };
    }

    /**
     * Get badge color for subscription status.
     */
    public function getSubscriptionStatusColor(string $status): string
    {
        return match ($status) {
            'active' => 'green',
            'trialing' => 'blue',
            'canceled' => 'zinc',
            'incomplete', 'incomplete_expired' => 'amber',
            'past_due', 'unpaid' => 'red',
            default => 'zinc',
        };
    }

    /**
     * Get Stripe dashboard URL for a customer.
     */
    public function getStripeCustomerUrl(): ?string
    {
        if (! $this->business->stripe_id) {
            return null;
        }

        $mode = Payment::isLiveMode() ? '' : '/test';

        return "https://dashboard.stripe.com{$mode}/customers/{$this->business->stripe_id}";
    }
}
