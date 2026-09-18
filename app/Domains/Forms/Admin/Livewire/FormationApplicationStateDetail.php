<?php

namespace App\Domains\Forms\Admin\Livewire;

use App\Domains\Forms\Engine\AnswerFormatter;
use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Enums\FormApplicationStateAdminStatus;
use App\Domains\Forms\Models\FormApplicationState;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class FormationApplicationStateDetail extends Component
{
    public FormApplicationState $state;

    public string $newStatus = '';

    public string $comment = '';

    public string $newComment = '';

    public function mount(FormApplicationState $formApplicationState): void
    {
        Gate::authorize('llc.view');

        $formApplicationState->load([
            'application:id,business_id,form_type,created_by_user_id,paid_at,submitted_at,selected_states,core_data',
            'application.business:id,name',
            // User->name is an accessor; load underlying columns.
            'application.createdBy:id,first_name,last_name,email',
            'application.states:id,form_application_id,state_code,current_admin_status',
        ]);

        $this->state = $formApplicationState;
    }

    /**
     * Field definitions for the answer summaries, so values show by their
     * type and options (e.g. the Management Structure label) rather than
     * as raw keys or "1"/"0".
     *
     * @return array{core: array<string, array<string, mixed>>, state: array<string, array<string, mixed>>}
     */
    #[Computed]
    public function summaryFields(): array
    {
        $registry = app(FormRegistry::class);
        $formatter = app(AnswerFormatter::class);
        $formType = $this->state->application->form_type;

        return [
            'core' => $formatter->fieldsIn($registry->getBase($formType)['core_steps'] ?? []),
            'state' => $formatter->fieldsIn($registry->get($formType, $this->state->state_code)['state_steps'] ?? []),
        ];
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
        Gate::authorize('llc.change_status');

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

    /**
     * Add a comment to the card without changing its status. Allowed on
     * any status, including terminal ones.
     */
    public function addComment(): void
    {
        Gate::authorize('llc.update');

        $this->validate(
            ['newComment' => ['required', 'string', 'max:2000']],
            attributes: ['newComment' => 'comment'],
        );

        $this->state->addAdminComment(trim($this->newComment), Auth::user());

        unset($this->transitions);

        $this->reset('newComment');

        session()->flash('success', 'Comment added.');
    }

    public function render(): View
    {
        return view('forms.admin.formations-detail')
            ->layout('layouts.admin', [
                'title' => 'LLC Formation · '.$this->state->state_code,
            ]);
    }
}
