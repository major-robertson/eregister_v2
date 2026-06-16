<?php

namespace App\Domains\Forms\Admin\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Admin\Enums\SalesTaxKanbanColumn;
use App\Domains\Forms\Models\FormApplicationState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Default sales tax admin kanban board. Shows the 5 active-work
 * columns (New, NeedsReview, SubmittedToState, Rejected, Hold).
 *
 * AwaitingClient (parked on customer) and Approved (terminal) are
 * excluded by default to keep the board focused on active admin work.
 * Switch to the /board/all route (SalesTaxBoardAll) to see everything.
 */
class SalesTaxBoard extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage('businessesPage');
        $this->resetPage('cardsPage');
    }

    public function render(): View
    {
        $columns = $this->columns();

        $data = [
            'columns' => $columns,
            'showAll' => $this->showAll(),
        ];

        if ($this->search !== '') {
            $data['businessResults'] = $this->getBusinessResults();
            $data['resultCount'] = $data['businessResults']->total();
        }

        $data['cards'] = $this->getCards($columns);
        $data['totalCount'] = $data['cards']->flatten()->count();

        return view('forms.admin.sales-tax-board', $data)
            ->layout('layouts.admin', ['title' => 'Sales Tax Board']);
    }

    /**
     * Whether this is the all-statuses board variant. Subclassed by
     * SalesTaxBoardAll which overrides to true.
     */
    protected function showAll(): bool
    {
        return false;
    }

    /**
     * @return array<int, SalesTaxKanbanColumn>
     */
    protected function columns(): array
    {
        return $this->showAll()
            ? SalesTaxKanbanColumn::cases()
            : SalesTaxKanbanColumn::defaultBoardCases();
    }

    /**
     * Cards = FormApplicationState rows for paid sales-tax applications,
     * grouped by current_admin_status. Eager-loads sibling states (id
     * + form_application_id + current_admin_status only) so the per-card
     * sibling-progress strip doesn't issue N+1 queries.
     *
     * @param  array<int, SalesTaxKanbanColumn>  $columns
     * @return Collection<string, Collection<int, FormApplicationState>>
     */
    public function getCards(array $columns): Collection
    {
        $columnValues = array_map(fn (SalesTaxKanbanColumn $c): string => $c->value, $columns);
        $formTypes = (array) config('workspaces.sales_tax.form_types', []);

        return FormApplicationState::query()
            ->whereIn('current_admin_status', $columnValues)
            ->whereHas('application', fn ($q) => $q
                ->whereNotNull('paid_at')
                ->whereIn('form_type', $formTypes))
            ->with([
                'application:id,business_id,form_type,created_by_user_id,paid_at,selected_states',
                'application.business:id,name',
                // `name` is an accessor on User composed from first_name + last_name,
                // so we load both columns explicitly here.
                'application.createdBy:id,first_name,last_name,email',
                // Lightweight sibling states for the parent-application progress
                // strip on each card. No N+1 because the same parent's siblings
                // are loaded once and reused across cards.
                'application.states:id,form_application_id,state_code,current_admin_status',
                'transitions' => fn ($q) => $q->latest()->limit(1),
            ])
            ->latest('current_admin_status_changed_at')
            ->latest('updated_at')
            ->get()
            ->groupBy(fn (FormApplicationState $state) => $state->current_admin_status->value);
    }

    /**
     * Search businesses with associated counts. Mirrors the lien board's
     * business-search panel for visual + workflow consistency.
     */
    public function getBusinessResults(): LengthAwarePaginator
    {
        return Business::query()
            ->when($this->search, fn ($query) => $query->adminSearch($this->search))
            ->withCount(['lienProjects', 'formApplications'])
            ->with(['users' => fn ($q) => $q->limit(4)])
            ->orderBy('name')
            ->paginate(15, pageName: 'businessesPage');
    }
}
