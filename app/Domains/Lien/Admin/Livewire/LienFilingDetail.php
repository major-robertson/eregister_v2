<?php

namespace App\Domains\Lien\Admin\Livewire;

use App\Domains\Lien\Admin\Actions\AddFilingComment;
use App\Domains\Lien\Admin\Actions\ChangeFilingStatus;
use App\Domains\Lien\Admin\Actions\RefundPayment;
use App\Domains\Lien\Admin\Enums\KanbanColumn;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienStateRule;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

class LienFilingDetail extends Component
{
    use AuthorizesRequests;

    public LienFiling $lienFiling;

    public string $newStatus = '';

    public string $note = '';

    public string $comment = '';

    public bool $showRefundModal = false;

    public bool $showDeleteModal = false;

    public function mount(string|LienFiling $lienFiling): void
    {
        if (is_string($lienFiling)) {
            $lienFiling = LienFiling::withoutGlobalScope('business')
                ->withTrashed()
                ->where('public_id', $lienFiling)
                ->firstOrFail();
        }

        $this->lienFiling = $lienFiling->load([
            'createdBy',
            'project.business',
            'project.parties',
            'project.deadlines.rule',
            'project.deadlines.documentType',
            'documentType',
            'events' => fn ($q) => $q->whereIn('event_type', ['status_changed', 'note_added', 'payment_refunded'])->latest()->limit(50),
            'events.creator',
        ]);
    }

    public function render(): View
    {
        $project = $this->lienFiling->project;
        $stateRule = $project?->jobsite_state
            ? LienStateRule::find($project->jobsite_state)
            : null;

        $deadlines = $project?->deadlines
            ?->sortBy(fn ($d) => $d->due_date ?? now()->addYears(100))
            ->values() ?? collect();

        $requiredDeadlines = $deadlines->filter(fn ($d) => $d->rule?->is_required);

        $filingDocStatus = $this->computeFilingDocStatus($project, $stateRule, $deadlines);

        $refundablePayment = $this->lienFiling->payments()
            ->latest('paid_at')
            ->first();

        $isDeleted = $this->lienFiling->trashed();

        $canRefund = ! $isDeleted
            && auth()->user()->can('refund', $this->lienFiling)
            && $refundablePayment?->isRefundable();

        return view('lien.admin.filing-detail', [
            'filing' => $this->lienFiling,
            'kanbanColumn' => KanbanColumn::forFiling($this->lienFiling),
            'allowedTransitions' => $this->lienFiling->allowedTransitions(),
            'activityLog' => $this->getActivityLog(),
            'canChangeStatus' => ! $isDeleted && auth()->user()->can('changeStatus', $this->lienFiling),
            'canUpdate' => ! $isDeleted && auth()->user()->can('update', $this->lienFiling),
            'canAddComment' => ! $isDeleted && auth()->user()->can('addComment', $this->lienFiling),
            'canRefund' => $canRefund,
            'canDelete' => auth()->user()->can('delete', $this->lienFiling),
            'isDeleted' => $isDeleted,
            'refundablePayment' => $refundablePayment,
            'requiredDeadlines' => $requiredDeadlines,
            'filingDocStatus' => $filingDocStatus,
        ])->layout('layouts.admin', ['title' => 'Filing Detail']);
    }

    /**
     * Compute the traffic-light status for the filing's document type.
     *
     * @return array{color: string, label: string, docName: string, dueDate: ?string, daysRemaining: ?int, isRequired: bool}
     */
    protected function computeFilingDocStatus($project, ?LienStateRule $stateRule, Collection $deadlines): array
    {
        $docType = $this->lienFiling->documentType;
        $docName = $docType?->name ?? 'Unknown';

        $default = ['color' => 'zinc', 'label' => 'No project data', 'docName' => $docName, 'dueDate' => null, 'daysRemaining' => null, 'isRequired' => false];

        if (! $project || ! $docType) {
            return $default;
        }

        // Find the matching project deadline for this filing's document type
        $deadline = $deadlines->first(
            fn ($d) => $d->document_type_id === $docType->id
        );

        $isRequired = $deadline?->rule?->is_required ?? false;

        if (! $deadline) {
            return [
                'color' => 'zinc',
                'label' => 'No deadline rule found',
                'docName' => $docName,
                'dueDate' => null,
                'daysRemaining' => null,
                'isRequired' => false,
            ];
        }

        // Warn if filing an NOI or prelim when lien rights have already expired
        $slug = $docType->slug;
        if (in_array($slug, ['noi', 'prelim_notice'], true)) {
            $lienDeadline = $deadlines->first(
                fn ($d) => $d->documentType?->slug === 'mechanics_lien'
            );

            if ($lienDeadline && ($lienDeadline->status === DeadlineStatus::Missed || $lienDeadline->isOverdue())) {
                return [
                    'color' => 'yellow',
                    'label' => 'Lien rights expired — consider a demand letter instead',
                    'docName' => $docName,
                    'dueDate' => $deadline->due_date?->format('M j, Y'),
                    'daysRemaining' => $deadline->daysRemaining(),
                    'isRequired' => $isRequired,
                ];
            }
        }

        // Optional document — always green, no urgency
        if (! $isRequired) {
            return [
                'color' => 'green',
                'label' => 'Optional — file anytime',
                'docName' => $docName,
                'dueDate' => $deadline->due_date?->format('M j, Y'),
                'daysRemaining' => null,
                'isRequired' => false,
            ];
        }

        // Not applicable for this claimant/state
        if ($deadline->status === DeadlineStatus::NotApplicable) {
            return [
                'color' => 'green',
                'label' => 'Not required'.($stateRule ? ' in '.$project->jobsite_state : ''),
                'docName' => $docName,
                'dueDate' => null,
                'daysRemaining' => null,
                'isRequired' => false,
            ];
        }

        $dueDate = $deadline->due_date?->format('M j, Y');
        $daysRemaining = $deadline->daysRemaining();

        // Completed
        if ($deadline->status === DeadlineStatus::Completed) {
            return [
                'color' => 'green',
                'label' => 'Filed',
                'docName' => $docName,
                'dueDate' => $dueDate,
                'daysRemaining' => null,
                'isRequired' => $isRequired,
            ];
        }

        // Overdue / Missed
        if ($deadline->status === DeadlineStatus::Missed || $deadline->isOverdue()) {
            $pastDays = $daysRemaining !== null ? abs($daysRemaining) : null;

            return [
                'color' => 'red',
                'label' => 'OVERDUE'.($pastDays !== null ? " — {$pastDays} days past deadline" : ''),
                'docName' => $docName,
                'dueDate' => $dueDate,
                'daysRemaining' => $daysRemaining,
                'isRequired' => $isRequired,
            ];
        }

        // Deadline unknown
        if ($deadline->status === DeadlineStatus::DeadlineUnknown) {
            return [
                'color' => 'yellow',
                'label' => ($isRequired ? 'Required' : 'Optional').' — deadline unknown (missing dates)',
                'docName' => $docName,
                'dueDate' => null,
                'daysRemaining' => null,
                'isRequired' => $isRequired,
            ];
        }

        // Has a due date in the future
        if ($daysRemaining !== null && $daysRemaining >= 0) {
            $urgency = $daysRemaining <= 7 ? 'yellow' : 'green';

            return [
                'color' => $urgency,
                'label' => ($isRequired ? 'Required' : 'Optional')." — {$daysRemaining} days left to file",
                'docName' => $docName,
                'dueDate' => $dueDate,
                'daysRemaining' => $daysRemaining,
                'isRequired' => $isRequired,
            ];
        }

        return [
            'color' => 'yellow',
            'label' => 'Status unknown — verify timeline',
            'docName' => $docName,
            'dueDate' => $dueDate,
            'daysRemaining' => $daysRemaining,
            'isRequired' => $isRequired,
        ];
    }

    /**
     * Get activity log including status changes and comments.
     */
    public function getActivityLog(): Collection
    {
        return $this->lienFiling->events()
            ->whereIn('event_type', ['status_changed', 'note_added', 'payment_refunded'])
            ->with('creator')
            ->latest()
            ->limit(50)
            ->get();
    }

    /**
     * Update the filing status.
     */
    public function updateStatus(): void
    {
        $this->authorize('changeStatus', $this->lienFiling);

        $this->validate([
            'newStatus' => ['required', Rule::enum(FilingStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $action = app(ChangeFilingStatus::class);
        $action->execute(
            filing: $this->lienFiling,
            newStatus: FilingStatus::from($this->newStatus),
            note: $this->note ?: null,
        );

        $this->lienFiling->refresh();
        $this->reset(['newStatus', 'note']);

        session()->flash('success', 'Status updated successfully.');
    }

    /**
     * Add a comment to the filing without changing status.
     */
    public function addComment(): void
    {
        $this->authorize('addComment', $this->lienFiling);

        $this->validate([
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $action = app(AddFilingComment::class);
        $action->execute(
            filing: $this->lienFiling,
            comment: $this->comment,
        );

        $this->reset('comment');

        session()->flash('success', 'Comment added successfully.');
    }

    /**
     * Show the refund confirmation modal.
     */
    public function confirmRefund(): void
    {
        $this->authorize('refund', $this->lienFiling);

        $this->showRefundModal = true;
    }

    /**
     * Process the refund via Stripe.
     */
    public function refundPayment(): void
    {
        $this->authorize('refund', $this->lienFiling);

        $payment = $this->lienFiling->payments()
            ->latest('paid_at')
            ->first();

        if (! $payment?->isRefundable()) {
            session()->flash('error', 'This payment cannot be refunded.');
            $this->showRefundModal = false;

            return;
        }

        try {
            app(RefundPayment::class)->execute($payment, auth()->user());

            $this->lienFiling->refresh();
            $this->showRefundModal = false;

            session()->flash('success', "Payment of {$payment->formattedAmount()} has been refunded.");
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->showRefundModal = false;

            session()->flash('error', 'Stripe error: '.$e->getMessage());
        }
    }

    /**
     * Show the delete confirmation modal.
     */
    public function confirmDelete(): void
    {
        $this->authorize('delete', $this->lienFiling);

        $this->showDeleteModal = true;
    }

    /**
     * Soft-delete the filing. Email sequences are suppressed by the model's
     * deleting hook. The filing remains visible to admins via withTrashed().
     */
    public function deleteFiling()
    {
        $this->authorize('delete', $this->lienFiling);

        $this->lienFiling->events()->create([
            'business_id' => $this->lienFiling->business_id,
            'event_type' => 'filing_deleted',
            'payload_json' => [
                'status_at_delete' => $this->lienFiling->status->value,
            ],
            'created_by' => auth()->id(),
        ]);

        $this->lienFiling->delete();

        session()->flash('success', 'Filing deleted. Customer can no longer see it and automated emails have stopped.');

        return $this->redirectRoute('admin.liens.board', navigate: true);
    }

    /**
     * Toggle the needs_review flag.
     */
    public function toggleNeedsReview(): void
    {
        $this->authorize('update', $this->lienFiling);

        $this->lienFiling->update([
            'needs_review' => ! $this->lienFiling->needs_review,
        ]);

        // Log the change
        $this->lienFiling->events()->create([
            'business_id' => $this->lienFiling->business_id,
            'event_type' => 'needs_review_changed',
            'payload_json' => [
                'needs_review' => $this->lienFiling->needs_review,
            ],
            'created_by' => auth()->id(),
        ]);

        $this->lienFiling->refresh();

        session()->flash('success', $this->lienFiling->needs_review
            ? 'Filing flagged for manager review.'
            : 'Review flag removed.');
    }
}
