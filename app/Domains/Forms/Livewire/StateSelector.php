<?php

namespace App\Domains\Forms\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\FormTypeConfig;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
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

    public string $stateMode = 'multi';

    public int $maxStates = 40;

    public ?FormApplication $existingDraft = null;

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
        $this->stateMode = $config['state_mode'];
        $this->maxStates = $config['max_states'] ?? ($this->stateMode === 'single' ? 1 : 40);

        $definition = app(FormRegistry::class)->getBase($formType);
        $this->availableStates = $definition['available_states'] ?? array_keys(config('states'));

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

        // Check for existing unpaid/draft application
        $this->existingDraft = FormApplication::where('business_id', $business->id)
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
                if (count($this->selectedStates) < $this->maxStates) {
                    $this->selectedStates[] = $stateCode;
                }
            }
        }
    }

    public function selectAll(): void
    {
        if ($this->stateMode === 'single') {
            return; // Not applicable for single mode
        }

        // Exclude blocked states when selecting all
        $selectableStates = array_diff($this->availableStates, $this->blockedStates);
        $this->selectedStates = array_slice(array_values($selectableStates), 0, $this->maxStates);
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

        $this->redirect(route('portal.checkout', $this->existingDraft));
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
        // Get selectable states (exclude blocked)
        $selectableStates = array_diff($this->availableStates, $this->blockedStates);

        $this->validate([
            'selectedStates' => ['required', 'array', 'min:1', "max:{$this->maxStates}"],
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

            return $application;
        });

        $this->redirect(route('portal.checkout', $application));
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
        ])->layout('layouts.app', ['title' => 'Select States']);
    }
}
