<?php

namespace App\Domains\Formations\Livewire;

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
     * The Formations workspace metadata. Resolved once per request via
     * Livewire's #[Computed] memoization; access in Blade as
     * `$this->workspace->...`, NOT `$this->workspace()`.
     */
    #[Computed]
    public function workspace(): Workspace
    {
        $workspace = app(WorkspaceRegistry::class)->find('formations');

        if (! $workspace) {
            throw new \RuntimeException('Formations workspace is not registered in config/workspaces.php');
        }

        return $workspace;
    }

    /**
     * Most recent formations for the current business across every form
     * type the workspace claims (LLC today; corporation/dba/etc. later).
     * Fetches one extra row so the view can detect "more than 10" without
     * issuing a second count query.
     *
     * @return Collection<int, FormApplication>
     */
    #[Computed]
    public function formations(): Collection
    {
        return FormApplication::query()
            ->with('states')
            ->where('business_id', $this->business->id)
            ->whereIn('form_type', $this->workspace->formTypes)
            ->latest()
            ->limit(11)
            ->get();
    }

    #[Computed]
    public function hasMoreFormations(): bool
    {
        return $this->formations->count() > 10;
    }

    public function render(): View
    {
        return view('livewire.formations.dashboard')
            ->layout('layouts.workspace', [
                'key' => 'formations',
                'title' => 'Formations',
            ]);
    }
}
