<?php

namespace App\Domains\Business\Livewire;

use App\Domains\Business\Models\Business;
use App\Support\Workspaces\Workspace;
use App\Support\Workspaces\WorkspaceRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class BusinessDropdown extends Component
{
    public ?Business $currentBusiness = null;

    /** @var Collection<int, Business> */
    public Collection $businesses;

    /**
     * Route name of the page hosting this dropdown, captured at mount so
     * switchBusiness() can keep the user in the workspace they're standing
     * in (Livewire update requests themselves hit livewire.update).
     */
    public string $pageRouteName = '';

    /**
     * Whether the hosting page's route has parameters (e.g. a bound
     * model). Such pages show a record of the *old* business and would
     * 403/404 if reloaded after a switch.
     */
    public bool $pageHasRouteParameters = false;

    public string $newBusinessName = '';

    public function mount(): void
    {
        $this->loadBusinesses();
        $this->pageRouteName = Route::currentRouteName() ?? '';
        $this->pageHasRouteParameters = filled(Route::current()?->parameters());
    }

    public function loadBusinesses(): void
    {
        $user = Auth::user();
        $this->businesses = $user->businesses()->orderBy('name')->get();
        $this->currentBusiness = $user->currentBusiness();
    }

    public function switchBusiness(int $businessId): mixed
    {
        $business = Auth::user()->businesses()->find($businessId);

        if (! $business) {
            $this->addError('business', 'Business not found.');

            return null;
        }

        session(['current_business_id' => $business->id]);

        if (! $business->isOnboardingComplete()) {
            return $this->redirect(route('portal.onboarding'), navigate: true);
        }

        // Keep the user's place: inside a workspace, land on that
        // workspace's dashboard for the new business; elsewhere, reload
        // the page they're on.
        if ($workspace = $this->workspaceForRoute($this->pageRouteName)) {
            return $this->redirect(route($workspace->dashboardRoute), navigate: true);
        }

        // Pages with route parameters (e.g. portal.checkout/{application})
        // show a record scoped to the previous business — reloading them
        // after the switch would 403. Send those to the hub instead.
        if (! $this->pageHasRouteParameters) {
            // Same-origin check requires a path separator after the host so
            // e.g. https://our-host.evil.com can't pass a prefix match.
            $referer = request()->header('Referer');
            $origin = request()->getSchemeAndHttpHost();

            if (is_string($referer) && ($referer === $origin || str_starts_with($referer, "{$origin}/"))) {
                return $this->redirect($referer, navigate: true);
            }
        }

        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function createBusiness(): mixed
    {
        $this->validate([
            'newBusinessName' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        $business = Business::create([
            'name' => $this->newBusinessName,
        ]);

        Auth::user()->businesses()->attach($business->id, ['role' => 'owner']);

        session(['current_business_id' => $business->id]);

        // A freshly created business always has incomplete onboarding.
        return $this->redirect(route('portal.onboarding'), navigate: true);
    }

    private function workspaceForRoute(string $routeName): ?Workspace
    {
        if ($routeName === '') {
            return null;
        }

        foreach (app(WorkspaceRegistry::class)->all() as $workspace) {
            $prefix = str($workspace->dashboardRoute)->beforeLast('.');

            if (str_starts_with($routeName, "{$prefix}.")) {
                return $workspace;
            }
        }

        return null;
    }

    public function render(): View
    {
        return view('livewire.business.business-dropdown');
    }
}
