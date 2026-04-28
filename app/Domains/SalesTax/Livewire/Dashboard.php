<?php

namespace App\Domains\SalesTax\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Support\Workspaces\Workspace;
use App\Support\Workspaces\WorkspaceRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    public Business $business;

    public function mount(): void
    {
        $business = Auth::user()?->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return;
        }

        $this->business = $business;
    }

    /**
     * The Sales Tax workspace metadata. Resolved once per request via
     * Livewire's #[Computed] memoization; access in Blade as
     * `$this->workspace->...`, NOT `$this->workspace()`.
     */
    #[Computed]
    public function workspace(): Workspace
    {
        $workspace = app(WorkspaceRegistry::class)->find('sales_tax');

        if (! $workspace) {
            throw new \RuntimeException('Sales Tax workspace is not registered in config/workspaces.php');
        }

        return $workspace;
    }

    /**
     * Most recent registrations for the current business. Fetches one
     * extra row so the view can detect "more than 10 exist" without
     * issuing a second count query.
     *
     * @return Collection<int, FormApplication>
     */
    #[Computed]
    public function registrations(): Collection
    {
        return FormApplication::query()
            ->with('states')
            ->where('business_id', $this->business->id)
            ->where('form_type', $this->workspace->formType)
            ->latest()
            ->limit(11)
            ->get();
    }

    #[Computed]
    public function hasMoreRegistrations(): bool
    {
        return $this->registrations->count() > 10;
    }

    public function render(): View
    {
        return view('livewire.sales-tax.dashboard')
            ->layout('layouts.workspace', [
                'key' => 'sales_tax',
                'title' => 'Sales Tax',
            ]);
    }
}
