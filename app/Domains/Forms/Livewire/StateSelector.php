<?php

namespace App\Domains\Forms\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\FormTypeConfig;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\Price;
use App\Support\SignupIntent;
use App\Support\Workspaces\WorkspaceRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class StateSelector extends Component
{
    public Business $business;

    public string $formType;

    /** @var array<int, string> */
    public array $selectedStates = [];

    /** @var array<int, string> */
    public array $availableStates = [];

    /** @var array<int, string> States with existing paid/submitted applications */
    public array $blockedStates = [];

    /** @var array<string, string> States excluded from selection with reason */
    public array $excludedStates = [];

    public string $stateMode = 'multi';

    public ?int $maxStates = null;

    public ?FormApplication $existingDraft = null;

    /** Price per selected state in cents, when the form type is billed per state. */
    public ?int $perStateCents = null;

    public function mount(string $formType): void
    {
        $business = Auth::user()->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return;
        }

        Gate::authorize('view', $business);

        $this->business = $business;
        $this->formType = $formType;

        // Load form type configuration
        $config = FormTypeConfig::get($formType);

        // One-per-business form types (e.g. LLC): a company can form only one.
        // Block starting a second once one is paid/submitted.
        if (($config['one_per_business'] ?? false) && $this->businessAlreadyHasFormation()) {
            session()->flash('error', 'This company already has an LLC. A company can form only one LLC.');
            $this->redirect($this->workspaceDashboardUrl());

            return;
        }

        $this->stateMode = $config['state_mode'];
        $this->maxStates = $config['max_states'] ?? ($this->stateMode === 'single' ? 1 : null);

        $definition = app(FormRegistry::class)->getBase($formType);
        $this->availableStates = $definition['available_states'] ?? array_keys(config('states'));
        $this->excludedStates = $definition['excluded_states'] ?? [];

        // Query blocked states from normalized FormApplicationState table
        $this->blockedStates = FormApplicationState::query()
            ->whereHas('application', fn ($q) => $q
                ->where('business_id', $business->id)
                ->where('form_type', $formType)
                ->where(fn ($qq) => $qq
                    ->whereNotNull('paid_at')
                    ->orWhere('status', 'submitted')
                )
            )
            ->pluck('state_code')
            ->unique()
            ->values()
            ->toArray();

        // Check for existing unpaid/draft application. forList() keeps the
        // multi-MB snapshot columns out of the ORDER BY (error 1038).
        $this->existingDraft = FormApplication::forList()
            ->where('business_id', $business->id)
            ->where('form_type', $formType)
            ->whereNull('paid_at')
            ->where('status', 'draft')
            ->latest()
            ->first();

        if ($this->existingDraft) {
            // Filter out any blocked states from existing draft selection
            $this->selectedStates = array_values(
                array_diff($this->existingDraft->selected_states, $this->blockedStates)
            );
        } else {
            $this->preselectState();
        }

        $this->perStateCents = $this->resolvePerStateCents($config);
    }

    /**
     * Start with the state in the link (a reminder email carries the one
     * they picked on the marketing page), the state the visitor picked on
     * the marketing page, or the business's own state. One state is what
     * most customers need; "Select All" used to send two dozen people into
     * a 46-state application they never finished.
     */
    private function preselectState(): void
    {
        $linkState = request()->query('state');

        $candidates = [
            is_string($linkState) ? strtoupper($linkState) : null,
            SignupIntent::state(),
            strtoupper((string) ($this->business->business_address['state'] ?? '')),
        ];

        foreach ($candidates as $code) {
            if ($code && in_array($code, $this->availableStates, true)
                && ! in_array($code, $this->blockedStates, true)
                && ! array_key_exists($code, $this->excludedStates)) {
                $this->selectedStates = [$code];

                return;
            }
        }
    }

    /**
     * The per-state price shown next to the selection, for form types billed
     * per state. Null when the form type is billed another way.
     *
     * @param  array<string, mixed>  $config
     */
    private function resolvePerStateCents(array $config): ?int
    {
        if (($config['billing_type'] ?? null) !== 'one_time_per_state') {
            return null;
        }

        try {
            return Price::resolve('tax', $this->formType, 'per_state', 'one_time')->amount_cents;
        } catch (\Throwable) {
            return null;
        }
    }

    public function toggleState(string $stateCode): void
    {
        // Prevent toggling blocked states
        if (in_array($stateCode, $this->blockedStates)) {
            return;
        }

        if ($this->stateMode === 'single') {
            // Radio behavior - replace selection
            $this->selectedStates = [$stateCode];
        } else {
            // Checkbox behavior - toggle
            if (in_array($stateCode, $this->selectedStates)) {
                $this->selectedStates = array_values(array_diff($this->selectedStates, [$stateCode]));
            } else {
                if ($this->maxStates === null || count($this->selectedStates) < $this->maxStates) {
                    $this->selectedStates[] = $stateCode;
                }
            }
        }
    }

    public function clearAll(): void
    {
        $this->selectedStates = [];
    }

    public function resumeExisting(): void
    {
        if (! $this->existingDraft) {
            $this->redirect(url()->previous());

            return;
        }

        $this->redirect($this->nextUrlFor($this->existingDraft));
    }

    public function startOver(): void
    {
        if ($this->existingDraft) {
            DB::transaction(function (): void {
                $this->existingDraft->states()->delete();
                $this->existingDraft->delete();
            });
            $this->existingDraft = null;
        }

        $this->selectedStates = [];
    }

    public function proceed(): void
    {
        // Safety re-check for one-per-business types (e.g. a second tab).
        if ((FormTypeConfig::get($this->formType)['one_per_business'] ?? false)
            && $this->businessAlreadyHasFormation()) {
            session()->flash('error', 'This company already has an LLC. A company can form only one LLC.');
            $this->redirect($this->workspaceDashboardUrl());

            return;
        }

        // Get selectable states (exclude blocked)
        $selectableStates = array_diff($this->availableStates, $this->blockedStates);

        $rules = ['required', 'array', 'min:1'];
        if ($this->maxStates !== null) {
            $rules[] = "max:{$this->maxStates}";
        }

        $this->validate([
            'selectedStates' => $rules,
            'selectedStates.*' => ['required', 'string', Rule::in($selectableStates)],
        ]);

        $application = DB::transaction(function () {
            // Delete any existing draft first
            if ($this->existingDraft) {
                $this->existingDraft->states()->delete();
                $this->existingDraft->delete();
            }

            $application = FormApplication::create([
                'business_id' => $this->business->id,
                'form_type' => $this->formType,
                'definition_version' => app(FormRegistry::class)->getBase($this->formType)['version'] ?? 1,
                'selected_states' => $this->selectedStates,
                'status' => 'draft',
                'current_phase' => 'core',
                'current_step_key' => null,
                'current_state_index' => 0,
                'core_data' => [],
                'created_by_user_id' => auth()->id(),
            ]);

            foreach ($this->selectedStates as $stateCode) {
                FormApplicationState::create([
                    'form_application_id' => $application->id,
                    'state_code' => $stateCode,
                    'status' => 'pending',
                    'data' => [],
                ]);
            }

            $application->update([
                'definition_snapshot' => $this->buildDefinitionSnapshot($application),
            ]);

            return $application;
        });

        $this->redirect($this->nextUrlFor($application));
    }

    /**
     * Where a new or resumed draft goes next. Pay-first types (sales tax)
     * order and pay before the questions, so an unpaid draft opens the
     * workspace's order screen; everything else opens the wizard.
     */
    private function nextUrlFor(FormApplication $application): string
    {
        $workspace = app(WorkspaceRegistry::class)->findByFormType($application->form_type);

        if ($application->paysFirst() && ! $application->isPaid() && $workspace?->checkoutRouteName) {
            return route($workspace->checkoutRouteName, $application);
        }

        return $this->applicationUrlFor($application);
    }

    /**
     * Resolve the workspace-owned application URL for the given form
     * application. Throws if no workspace claims the form_type — this
     * is a config bug that should fail loudly rather than fall back to
     * a removed generic route.
     */
    private function applicationUrlFor(FormApplication $application): string
    {
        $workspace = app(WorkspaceRegistry::class)->findByFormType($application->form_type);
        $url = $workspace?->applicationRouteFor($application);

        if (! $url) {
            throw new \LogicException(
                "No application route configured for form type [{$application->form_type}]. ".
                'Add the form_type to a workspace in config/workspaces.php.'
            );
        }

        return $url;
    }

    /**
     * Whether this business already has a paid/submitted application of the
     * current form type (used to enforce one-per-business form types).
     */
    private function businessAlreadyHasFormation(): bool
    {
        return FormApplication::where('business_id', $this->business->id)
            ->where('form_type', $this->formType)
            ->where(fn ($q) => $q->whereNotNull('paid_at')->orWhere('status', 'submitted'))
            ->exists();
    }

    private function workspaceDashboardUrl(): string
    {
        $workspace = app(WorkspaceRegistry::class)->findByFormType($this->formType);

        return $workspace ? route($workspace->dashboardRoute) : route('dashboard');
    }

    private function buildDefinitionSnapshot(FormApplication $application): array
    {
        $registry = app(FormRegistry::class);
        $snapshots = ['base' => $registry->getBase($application->form_type)];

        foreach ($application->selected_states as $stateCode) {
            $snapshots['states'][$stateCode] = $registry->get($application->form_type, $stateCode);
        }

        return $snapshots;
    }

    public function render(): View
    {
        $config = FormTypeConfig::get($this->formType);

        return view('livewire.forms.state-selector', [
            'stateCount' => count($this->selectedStates),
            'hasExistingDraft' => $this->existingDraft !== null,
            'existingDraftStateCount' => $this->existingDraft?->stateCount(),
            'states' => config('states'),
            'stateMode' => $this->stateMode,
            'maxStates' => $this->maxStates,
            'blockedStates' => $this->blockedStates,
            'formTypeName' => $config['name'],
            'excludedStates' => $this->excludedStates,
            'perStateCents' => $this->perStateCents,
        ])->layout('components.layouts.portal', ['title' => 'Select States']);
    }
}
