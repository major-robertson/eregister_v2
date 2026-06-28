<?php

namespace App\Domains\Lien\Admin\Livewire;

use App\Domains\Lien\Admin\Actions\AddFilingComment;
use App\Domains\Lien\Admin\Actions\ChangeFilingStatus;
use App\Domains\Lien\Admin\Actions\RefundPayment;
use App\Domains\Lien\Admin\Actions\SyncResult;
use App\Domains\Lien\Admin\Actions\UpdateLienFilingDetails;
use App\Domains\Lien\Admin\Actions\UpdateLienParties;
use App\Domains\Lien\Admin\Actions\UpdateLienProjectDetails;
use App\Domains\Lien\Admin\Actions\UpdateRecordingDetails;
use App\Domains\Esign\Actions\VerifySignatureChain;
use App\Domains\Esign\Actions\VoidSignatureRequest;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Lien\Esign\Actions\SendDemandLetterForSignature;
use App\Domains\Lien\Admin\Enums\KanbanColumn;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Enums\RecordingMethod;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienStateRule;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Stripe\Exception\ApiErrorException;

class LienFilingDetail extends Component
{
    use AuthorizesRequests;

    public LienFiling $lienFiling;

    public string $newStatus = '';

    public string $note = '';

    public string $comment = '';

    public bool $showRefundModal = false;

    public bool $showDeleteModal = false;

    public bool $showSendEsignModal = false;

    /**
     * Recording-detail form fields. Bound to the conditional sub-form inside
     * the status-change panel (when transitioning to SubmittedForRecording)
     * AND to the always-visible "Recording details" edit panel that appears
     * once recording_method has been set.
     */
    public string $recordingMethod = '';

    public string $recordingProvider = '';

    public string $recordingReference = '';

    public string $recordingSubmittedAt = '';

    /**
     * Application-edit form state. Each section binds to one of these arrays
     * (e.g. wire:model="projectForm.name"); money fields hold dollar strings and
     * are converted to cents before being handed to the Action.
     *
     * @var array<string, mixed>
     */
    public array $projectForm = [];

    /** @var array<string, mixed> */
    public array $filingForm = [];

    /** @var array<string, mixed> */
    public array $partyForm = [];

    public ?int $editingPartyId = null;

    public ?int $removingPartyId = null;

    public bool $showProjectModal = false;

    public bool $showFilingModal = false;

    public bool $showPartyModal = false;

    public bool $showRemovePartyModal = false;

    /**
     * Event types rendered in the activity timeline. Edit events were added so
     * admin corrections show up with their field-level diffs.
     *
     * @var list<string>
     */
    private const ACTIVITY_EVENT_TYPES = [
        'status_changed', 'note_added', 'payment_refunded', 'recording_details_updated',
        'application_project_updated', 'application_filing_updated', 'application_parties_updated',
        'esign_sent', 'esign_completed',
    ];

    public function mount(string|LienFiling $lienFiling): void
    {
        if (is_string($lienFiling)) {
            $lienFiling = LienFiling::withoutGlobalScope('business')
                ->withTrashed()
                ->where('public_id', $lienFiling)
                ->firstOrFail();
        }

        $this->lienFiling = $lienFiling;
        $this->loadFilingRelations();

        $this->hydrateRecordingFields();
        $this->loadProjectForm();
        $this->loadFilingForm();
    }

    /**
     * (Re)load the relations the detail view renders. Called on mount and after
     * every edit so the page reflects the freshly persisted state.
     */
    private function loadFilingRelations(): void
    {
        $this->lienFiling->load([
            'createdBy',
            'project.business',
            'project.parties',
            'project.deadlines.rule',
            'project.deadlines.documentType',
            'documentType',
            'events' => fn ($q) => $q->whereIn('event_type', self::ACTIVITY_EVENT_TYPES)->latest()->limit(50),
            'events.creator',
        ]);
    }

    /**
     * Populate recording-detail form fields from the underlying filing so
     * the edit panel always reflects current persisted values.
     */
    protected function hydrateRecordingFields(): void
    {
        $this->recordingMethod = $this->lienFiling->recording_method?->value ?? '';
        $this->recordingProvider = (string) ($this->lienFiling->recording_provider ?? '');
        $this->recordingReference = (string) ($this->lienFiling->recording_reference ?? '');
        // Shown/edited in the display timezone; parsed back from it on save.
        $this->recordingSubmittedAt = $this->lienFiling->recording_submitted_at
            ? $this->lienFiling->recording_submitted_at->eastern()->format('Y-m-d\TH:i')
            : '';
    }

    /**
     * Livewire hook fired when the admin picks a new target status. When the
     * pick is SubmittedForRecording and no submitted-at value has been entered
     * yet, prefill it with "now" so the typical case (admin just submitted it)
     * requires zero typing.
     */
    public function updatedNewStatus(string $value): void
    {
        if ($value === FilingStatus::SubmittedForRecording->value && $this->recordingSubmittedAt === '') {
            $this->recordingSubmittedAt = now()->eastern()->format('Y-m-d\TH:i');
        }
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

        $esignRequest = $this->lienFiling->signatureRequests()
            ->with(['documents' => fn ($q) => $q->orderBy('sort_order'), 'documents.media', 'events.actor'])
            ->latest('id')
            ->first();

        // The most recently completed session — its signed PDFs stay downloadable
        // by admins even after a later re-send starts a fresh (unsigned) session.
        $signedEsignRequest = $this->lienFiling->signatureRequests()
            ->where('status', SignatureRequestStatus::Completed->value)
            ->with(['documents' => fn ($q) => $q->orderBy('sort_order'), 'documents.media'])
            ->latest('id')
            ->first();

        $recipientCount = $this->lienFiling->isDemandLetter()
            ? ($this->lienFiling->project?->nonClaimantParties()->count() ?? 0)
            : 0;

        $canSendEsign = ! $isDeleted
            && $this->lienFiling->isDemandLetter()
            && $recipientCount > 0
            && $esignRequest?->isActive() !== true
            && $this->lienFiling->canTransitionTo(FilingStatus::AwaitingEsign)
            && auth()->user()->can('changeStatus', $this->lienFiling);

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
            'esignRequest' => $esignRequest,
            'signedEsignRequest' => $signedEsignRequest,
            'hasPriorEsign' => $signedEsignRequest !== null,
            'recipientCount' => $recipientCount,
            'canSendEsign' => $canSendEsign,
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
            ->whereIn('event_type', self::ACTIVITY_EVENT_TYPES)
            ->with('creator')
            ->latest()
            ->limit(50)
            ->get();
    }

    /**
     * Update the filing status.
     *
     * When transitioning to SubmittedForRecording, recording_method is required
     * (the other three recording_* fields stay optional). The action persists
     * the recording fields in the same DB transaction as the status change.
     */
    public function updateStatus(): void
    {
        $this->authorize('changeStatus', $this->lienFiling);

        $isRecordingTransition = $this->newStatus === FilingStatus::SubmittedForRecording->value;

        $rules = [
            'newStatus' => ['required', Rule::enum(FilingStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ];

        if ($isRecordingTransition) {
            $rules['recordingMethod'] = ['required', Rule::enum(RecordingMethod::class)];
            $rules['recordingProvider'] = ['nullable', 'string', 'max:255'];
            $rules['recordingReference'] = ['nullable', 'string', 'max:255'];
            $rules['recordingSubmittedAt'] = ['required', 'date'];
        }

        $this->validate($rules);

        $action = app(ChangeFilingStatus::class);
        $action->execute(
            filing: $this->lienFiling,
            newStatus: FilingStatus::from($this->newStatus),
            note: $this->note ?: null,
            recordingMethod: $isRecordingTransition && $this->recordingMethod
                ? RecordingMethod::from($this->recordingMethod)
                : null,
            recordingProvider: $isRecordingTransition ? ($this->recordingProvider ?: null) : null,
            recordingReference: $isRecordingTransition ? ($this->recordingReference ?: null) : null,
            recordingSubmittedAt: $isRecordingTransition && $this->recordingSubmittedAt
                ? Carbon::parse($this->recordingSubmittedAt, config('app.display_timezone'))->utc()
                : null,
        );

        $this->lienFiling->refresh();
        $this->hydrateRecordingFields();
        $this->reset(['newStatus', 'note']);

        session()->flash('success', 'Status updated successfully.');
    }

    /**
     * Update recording details on a filing without changing status.
     *
     * Used after the SubmittedForRecording transition when the admin later
     * gets a tracking/confirmation number from the e-recording portal or
     * mail carrier, or needs to correct the originally entered values.
     */
    public function updateRecordingDetails(): void
    {
        $this->authorize('update', $this->lienFiling);

        $this->validate([
            'recordingMethod' => ['required', Rule::enum(RecordingMethod::class)],
            'recordingProvider' => ['nullable', 'string', 'max:255'],
            'recordingReference' => ['nullable', 'string', 'max:255'],
            'recordingSubmittedAt' => ['required', 'date'],
        ]);

        app(UpdateRecordingDetails::class)->execute(
            filing: $this->lienFiling,
            recordingMethod: RecordingMethod::from($this->recordingMethod),
            recordingProvider: $this->recordingProvider ?: null,
            recordingReference: $this->recordingReference ?: null,
            recordingSubmittedAt: $this->recordingSubmittedAt
                ? Carbon::parse($this->recordingSubmittedAt, config('app.display_timezone'))->utc()
                : null,
        );

        $this->lienFiling->refresh();
        $this->hydrateRecordingFields();

        session()->flash('success', 'Recording details updated.');
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
        } catch (ApiErrorException $e) {
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

    // ------------------------------------------------------------------
    // E-signature
    // ------------------------------------------------------------------

    /**
     * Open the "Send for E-Sign" confirmation modal.
     */
    public function confirmSendForEsign(): void
    {
        $this->authorize('changeStatus', $this->lienFiling);
        $this->showSendEsignModal = true;
    }

    /**
     * Generate + lock the demand letters, email the signer, and move the filing
     * to AwaitingEsign.
     */
    public function sendForEsign(): void
    {
        $this->authorize('changeStatus', $this->lienFiling);

        try {
            app(SendDemandLetterForSignature::class)->execute($this->lienFiling, auth()->user());

            $this->showSendEsignModal = false;
            $this->afterEdit();

            session()->flash('success', 'Sent for e-signature. The signer has been emailed a signing link.');
        } catch (EsignException $e) {
            $this->showSendEsignModal = false;

            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Void the active signing session (e.g. to resend with corrected recipients),
     * moving the filing into review so it stops chasing the signer.
     */
    public function voidEsign(): void
    {
        $this->authorize('changeStatus', $this->lienFiling);

        $request = $this->lienFiling->activeSignatureRequest();

        if ($request === null) {
            session()->flash('error', 'There is no active signature request to void.');

            return;
        }

        app(VoidSignatureRequest::class)->execute($request, auth()->user(), 'Voided by admin.');

        if ($this->lienFiling->status === FilingStatus::AwaitingEsign
            && $this->lienFiling->canTransitionTo(FilingStatus::NeedsReview)) {
            app(ChangeFilingStatus::class)->execute(
                filing: $this->lienFiling,
                newStatus: FilingStatus::NeedsReview,
                note: 'E-signature request voided.',
            );
        }

        $this->afterEdit();

        session()->flash('success', 'Signature request voided.');
    }

    /**
     * Verify the integrity of the audit-trail hash chain for the latest session.
     */
    public function verifyChain(): void
    {
        $request = $this->lienFiling->latestSignatureRequest();

        if ($request === null) {
            session()->flash('error', 'There is no signature request to verify.');

            return;
        }

        $result = app(VerifySignatureChain::class)->execute($request);

        if ($result->valid) {
            session()->flash('success', "Audit chain verified — {$result->eventCount} events, intact.");
        } else {
            session()->flash('error', "Audit chain INVALID at event #{$result->brokenAtEventId}: {$result->reason}");
        }
    }

    // ------------------------------------------------------------------
    // Application editing (project / filing / parties)
    // Edits update the live records, write a field-level audit event, and
    // re-sync every eligible fulfillment snapshot via SyncFilingSnapshot.
    // ------------------------------------------------------------------

    public function editProjectDetails(): void
    {
        $this->authorize('update', $this->lienFiling);
        $this->loadProjectForm();
        $this->resetErrorBag();
        $this->showProjectModal = true;
    }

    public function updateProjectDetails(): void
    {
        $this->authorize('update', $this->lienFiling);
        $this->validate($this->projectRules());

        $input = $this->projectForm;
        $input['base_contract_amount_cents'] = $this->dollarsToCents($this->projectForm['base_contract_amount'] ?? null);
        $input['change_orders_cents'] = $this->dollarsToCents($this->projectForm['change_orders'] ?? null);
        $input['credits_deductions_cents'] = $this->dollarsToCents($this->projectForm['credits_deductions'] ?? null);
        $input['payments_received_cents'] = $this->dollarsToCents($this->projectForm['payments_received'] ?? null);
        $input['uncompleted_work_cents'] = $this->dollarsToCents($this->projectForm['uncompleted_work'] ?? null);

        $result = app(UpdateLienProjectDetails::class)->execute($this->lienFiling, $input);

        $this->afterEdit();
        $this->showProjectModal = false;
        $this->flashSyncResult($result, 'Project details updated.');
    }

    public function editFilingDetails(): void
    {
        $this->authorize('update', $this->lienFiling);
        $this->loadFilingForm();
        $this->resetErrorBag();
        $this->showFilingModal = true;
    }

    public function updateFilingDetails(): void
    {
        $this->authorize('update', $this->lienFiling);
        $this->validate($this->filingRules());

        $input = $this->filingForm;
        $input['amount_claimed_cents'] = $this->dollarsToCents($this->filingForm['amount_claimed'] ?? null);

        $result = app(UpdateLienFilingDetails::class)->execute($this->lienFiling, $input);

        $this->afterEdit();
        $this->showFilingModal = false;
        $this->flashSyncResult($result, 'Filing details updated.');
    }

    public function addParty(): void
    {
        $this->authorize('update', $this->lienFiling);
        $this->editingPartyId = null;
        $this->partyForm = $this->blankPartyForm();
        $this->resetErrorBag();
        $this->showPartyModal = true;
    }

    public function editParty(int $partyId): void
    {
        $this->authorize('update', $this->lienFiling);

        $party = $this->lienFiling->project?->parties->firstWhere('id', $partyId);
        abort_if($party === null, 404);

        $this->editingPartyId = $party->id;
        $this->partyForm = [
            'role' => $party->role?->value,
            'name' => $party->name,
            'company_name' => $party->company_name,
            'address1' => $party->address1,
            'address2' => $party->address2,
            'city' => $party->city,
            'state' => $party->state,
            'zip' => $party->zip,
            'email' => $party->email,
            'phone' => $party->phone,
        ];
        $this->resetErrorBag();
        $this->showPartyModal = true;
    }

    public function saveParty(): void
    {
        $this->authorize('update', $this->lienFiling);
        $this->validate($this->partyRules());

        $wasEdit = $this->editingPartyId !== null;

        $result = app(UpdateLienParties::class)
            ->saveParty($this->lienFiling, $this->editingPartyId, $this->partyForm);

        $this->afterEdit();
        $this->showPartyModal = false;
        $this->flashSyncResult($result, $wasEdit ? 'Party updated.' : 'Party added.');
    }

    public function confirmRemoveParty(int $partyId): void
    {
        $this->authorize('update', $this->lienFiling);
        $this->removingPartyId = $partyId;
        $this->showRemovePartyModal = true;
    }

    public function removeParty(): void
    {
        $this->authorize('update', $this->lienFiling);
        abort_if($this->removingPartyId === null, 400);

        $result = app(UpdateLienParties::class)
            ->removeParty($this->lienFiling, $this->removingPartyId);

        $this->removingPartyId = null;
        $this->showRemovePartyModal = false;
        $this->afterEdit();
        $this->flashSyncResult($result, 'Party removed.');
    }

    /**
     * Reload relations + re-hydrate the edit forms after a successful edit.
     */
    private function afterEdit(): void
    {
        $this->lienFiling->refresh();
        $this->loadFilingRelations();
        $this->hydrateRecordingFields();
        $this->loadProjectForm();
        $this->loadFilingForm();
    }

    private function loadProjectForm(): void
    {
        $p = $this->lienFiling->project;

        if ($p === null) {
            $this->projectForm = [];

            return;
        }

        $this->projectForm = [
            'name' => $p->name,
            'job_number' => $p->job_number,
            'provided_type' => $p->provided_type,
            'hired_by' => $p->hired_by,
            'property_context' => $p->property_context,
            'property_class' => $p->property_class,
            'jobsite_address1' => $p->jobsite_address1,
            'jobsite_address2' => $p->jobsite_address2,
            'jobsite_city' => $p->jobsite_city,
            'jobsite_state' => $p->jobsite_state,
            'jobsite_zip' => $p->jobsite_zip,
            'jobsite_county' => $p->jobsite_county,
            'legal_description' => $p->legal_description,
            'apn' => $p->apn,
            'first_furnish_date' => $p->first_furnish_date?->format('Y-m-d'),
            'last_furnish_date' => $p->last_furnish_date?->format('Y-m-d'),
            'completion_date' => $p->completion_date?->format('Y-m-d'),
            'noc_status' => $p->noc_status?->value,
            'noc_recorded_at' => $p->noc_recorded_at?->format('Y-m-d'),
            'base_contract_amount' => $this->centsToDollars($p->base_contract_amount_cents),
            'change_orders' => $this->centsToDollars($p->change_orders_cents),
            'credits_deductions' => $this->centsToDollars($p->credits_deductions_cents),
            'payments_received' => $this->centsToDollars($p->payments_received_cents),
            'uncompleted_work' => $this->centsToDollars($p->uncompleted_work_cents),
            'owner_is_tenant' => (bool) $p->owner_is_tenant,
            'has_written_contract' => (bool) $p->has_written_contract,
        ];
    }

    private function loadFilingForm(): void
    {
        $f = $this->lienFiling;

        $this->filingForm = [
            'amount_claimed' => $this->centsToDollars($f->amount_claimed_cents),
            'description_of_work' => $f->description_of_work,
            'jurisdiction_state' => $f->jurisdiction_state,
            'jurisdiction_county' => $f->jurisdiction_county,
            'service_level' => $f->service_level?->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function blankPartyForm(): array
    {
        return [
            'role' => PartyRole::Owner->value,
            'name' => '',
            'company_name' => '',
            'address1' => '',
            'address2' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'email' => '',
            'phone' => '',
        ];
    }

    /**
     * @return array<string, list<mixed>>
     */
    private function projectRules(): array
    {
        return [
            'projectForm.name' => ['required', 'string', 'max:255'],
            'projectForm.job_number' => ['nullable', 'string', 'max:100'],
            'projectForm.provided_type' => ['required', 'in:labor,materials_only,both'],
            'projectForm.hired_by' => ['required', 'in:owner,direct_contractor,subcontractor'],
            'projectForm.property_context' => ['nullable', 'in:unknown,tenant_improvement,owner_occupied'],
            'projectForm.property_class' => ['nullable', 'in:residential,commercial,government'],
            'projectForm.jobsite_address1' => ['nullable', 'string', 'max:255'],
            'projectForm.jobsite_address2' => ['nullable', 'string', 'max:255'],
            'projectForm.jobsite_city' => ['nullable', 'string', 'max:255'],
            'projectForm.jobsite_state' => ['required', 'string', 'size:2'],
            'projectForm.jobsite_zip' => ['nullable', 'string', 'max:10'],
            'projectForm.jobsite_county' => ['nullable', 'string', 'max:255'],
            'projectForm.legal_description' => ['nullable', 'string', 'max:2000'],
            'projectForm.apn' => ['nullable', 'string', 'max:255'],
            'projectForm.first_furnish_date' => ['nullable', 'date', 'before_or_equal:today'],
            'projectForm.last_furnish_date' => ['nullable', 'date', 'before_or_equal:today', 'after_or_equal:projectForm.first_furnish_date'],
            'projectForm.completion_date' => ['nullable', 'date', 'before_or_equal:today'],
            'projectForm.noc_status' => ['nullable', 'in:yes,no,unknown'],
            'projectForm.noc_recorded_at' => ['nullable', 'date'],
            'projectForm.base_contract_amount' => ['nullable', 'numeric', 'min:0'],
            'projectForm.change_orders' => ['nullable', 'numeric'],
            'projectForm.credits_deductions' => ['nullable', 'numeric'],
            'projectForm.payments_received' => ['nullable', 'numeric'],
            'projectForm.uncompleted_work' => ['nullable', 'numeric'],
            'projectForm.owner_is_tenant' => ['boolean'],
            'projectForm.has_written_contract' => ['boolean'],
        ];
    }

    /**
     * @return array<string, list<mixed>>
     */
    private function filingRules(): array
    {
        return [
            'filingForm.amount_claimed' => ['nullable', 'numeric', 'min:0'],
            'filingForm.description_of_work' => ['nullable', 'string', 'max:5000'],
            'filingForm.jurisdiction_state' => ['nullable', 'string', 'size:2'],
            'filingForm.jurisdiction_county' => ['nullable', 'string', 'max:255'],
            'filingForm.service_level' => ['required', 'in:self_serve,full_service'],
        ];
    }

    /**
     * @return array<string, list<mixed>>
     */
    private function partyRules(): array
    {
        return [
            'partyForm.role' => ['required', Rule::enum(PartyRole::class)],
            'partyForm.name' => ['required', 'string', 'max:255'],
            'partyForm.company_name' => ['nullable', 'string', 'max:255'],
            'partyForm.address1' => ['nullable', 'string', 'max:255'],
            'partyForm.address2' => ['nullable', 'string', 'max:255'],
            'partyForm.city' => ['nullable', 'string', 'max:255'],
            'partyForm.state' => ['nullable', 'string', 'size:2'],
            'partyForm.zip' => ['nullable', 'string', 'max:10'],
            'partyForm.email' => ['nullable', 'email', 'max:255'],
            'partyForm.phone' => ['nullable', 'string', 'max:50'],
        ];
    }

    private function flashSyncResult(SyncResult $result, string $message): void
    {
        if ($result->skipReasonFor($this->lienFiling->id) === 'finalized') {
            $siblings = $result->siblingSyncedCount($this->lienFiling->id);
            $message .= ' This filing is finalized, so its fulfillment snapshot was not changed'
                .($siblings > 0 ? "; {$siblings} other filing(s) were updated." : '.');
        }

        session()->flash('success', $message);

        if ($result->staleSentRecipientWarnings !== []) {
            session()->flash('warning', implode(' ', $result->staleSentRecipientWarnings));
        }
    }

    private function dollarsToCents(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return (int) round((float) $value * 100);
    }

    private function centsToDollars(?int $cents): ?string
    {
        if ($cents === null) {
            return null;
        }

        return number_format($cents / 100, 2, '.', '');
    }
}
