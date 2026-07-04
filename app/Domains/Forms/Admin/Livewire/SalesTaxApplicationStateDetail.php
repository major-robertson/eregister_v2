<?php

namespace App\Domains\Forms\Admin\Livewire;

use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Engine\SensitiveDataProtector;
use App\Domains\Forms\Enums\FormApplicationStateAdminStatus;
use App\Domains\Forms\Models\FormApplicationState;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SalesTaxApplicationStateDetail extends Component
{
    public FormApplicationState $state;

    public string $newStatus = '';

    public string $comment = '';

    /**
     * Decrypted core (shared) application data. Sensitive fields (FEIN,
     * SSN, etc.) are stored encrypted-at-rest; admins processing an
     * application need to read them, so this decrypts them for display
     * using the same protector the form runner uses on load.
     *
     * @var array<string, mixed>
     */
    public array $decryptedCoreData = [];

    /**
     * Decrypted state-specific data for the current state card.
     *
     * @var array<string, mixed>
     */
    public array $decryptedStateData = [];

    public function mount(FormApplicationState $formApplicationState): void
    {
        Gate::authorize('tax.view');

        $formApplicationState->load([
            'application:id,business_id,form_type,created_by_user_id,paid_at,submitted_at,selected_states,core_data',
            'application.business:id,name',
            // User->name is an accessor; load underlying columns.
            'application.createdBy:id,first_name,last_name,email',
            'application.states:id,form_application_id,state_code,current_admin_status',
        ]);

        $this->state = $formApplicationState;

        $protector = app(SensitiveDataProtector::class);
        $registry = app(FormRegistry::class);
        $formType = $this->state->application?->form_type;

        if ($formType) {
            $base = $registry->getBase($formType);
            $this->decryptedCoreData = $protector->decryptCoreData(
                $this->state->application->core_data ?? [],
                $base
            );

            $stateDef = $registry->get($formType, $this->state->state_code);
            $this->decryptedStateData = $protector->decryptStateData(
                $this->state->data ?? [],
                $stateDef
            );
        }
    }

    /**
     * @return array<int, FormApplicationStateAdminStatus>
     */
    #[Computed]
    public function allowedTransitions(): array
    {
        return $this->state->current_admin_status->allowedTransitions();
    }

    #[Computed]
    public function transitions()
    {
        return $this->state
            ->transitions()
            ->with('changedBy:id,first_name,last_name,email')
            ->get();
    }

    public function changeStatus(): void
    {
        Gate::authorize('tax.change_status');

        $this->validate([
            'newStatus' => [
                'required',
                'string',
                Rule::in(array_map(
                    fn (FormApplicationStateAdminStatus $s): string => $s->value,
                    $this->allowedTransitions()
                )),
            ],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $next = FormApplicationStateAdminStatus::from($this->newStatus);

        $this->state->transitionAdminStatusTo(
            next: $next,
            by: Auth::user(),
            comment: $this->comment !== '' ? $this->comment : null,
        );

        // Refresh to pick up the new denormalized status + transitions list.
        $this->state->refresh();
        unset($this->allowedTransitions, $this->transitions);

        $this->reset(['newStatus', 'comment']);

        session()->flash('success', "Status changed to {$next->label()}.");
    }

    public function render(): View
    {
        return view('forms.admin.sales-tax-detail')
            ->layout('layouts.admin', [
                'title' => 'Sales Tax Registration · '.$this->state->state_code,
            ]);
    }
}
