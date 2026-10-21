<?php

namespace App\Domains\Lien\Admin\Livewire;

use App\Domains\Esign\Actions\VerifySignatureChain;
use App\Domains\Esign\Actions\VoidSignatureRequest;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Lien\Admin\Actions\AddFilingComment;
use App\Domains\Lien\Admin\Actions\ChangeFilingStatus;
use App\Domains\Lien\Admin\Actions\MarkStillWaiting;
use App\Domains\Lien\Admin\Actions\RefundPayment;
use App\Domains\Lien\Admin\Actions\SendDraftForReview;
use App\Domains\Lien\Admin\Actions\SyncResult;
use App\Domains\Lien\Admin\Actions\UpdateLienDocumentDetails;
use App\Domains\Lien\Admin\Actions\UpdateLienFilingDetails;
use App\Domains\Lien\Admin\Actions\UpdateLienFilingRecipient;
use App\Domains\Lien\Admin\Actions\UpdateLienParties;
use App\Domains\Lien\Admin\Actions\UpdateLienProjectDetails;
use App\Domains\Lien\Admin\Actions\UpdateRecordingDetails;
use App\Domains\Lien\Admin\Enums\KanbanColumn;
use App\Domains\Lien\Admin\Support\BoardCard;
use App\Domains\Lien\Documents\LienCountyKey;
use App\Domains\Lien\Documents\LienDocumentGenerator;
use App\Domains\Lien\Documents\LienDocumentPackage;
use App\Domains\Lien\Documents\LienDocumentUnavailable;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Enums\RecordingMethod;
use App\Domains\Lien\Esign\Actions\SendDemandLetterForSignature;
use App\Domains\Lien\Esign\Actions\SendDemandLetterReminder;
use App\Domains\Lien\Esign\Actions\SendUploadedDocumentForSignature;
use App\Domains\Lien\Esign\UploadedDocumentSignable;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienStateRule;
use App\Mail\FilingMilestone;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Stripe\Exception\ApiErrorException;

class LienFilingDetail extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public LienFiling $lienFiling;

    public string $newStatus = '';

    public string $note = '';

    /**
     * The request emailed to the customer when moving to Awaiting Client /
     * E-Signature / Notary (required for those three), with an optional PDF.
     */
    public string $customerMessage = '';

    public $customerAttachment = null;

    /**
     * Milestone documents on the status form (EREG-130), emailed to the
     * customer with the milestone email: the county's recorded copy
     * (Recorded, Complete), our proof of service (Mailed, Complete), and the
     * certified mail tracking number (Mailed). All optional.
     */
    public $recordedCopy = null;

    public $proofOfService = null;

    public string $trackingNumber = '';

    public string $comment = '';

    /** The optional one-line note on "Still waiting" (the customer emailed us). */
    public string $stillWaitingNote = '';

    public bool $showRefundModal = false;

    public bool $showDeleteModal = false;

    public bool $showSendEsignModal = false;

    /**
     * "Draft for review" card (EREG-140): a PDF draft to upload as a new
     * version, and the Send for review dialog (the customer message and
     * whether the document needs their signature afterwards).
     */
    public $draftFile = null;

    public bool $showSendDraftModal = false;

    public string $draftMessage = '';

    public bool $draftNeedsSignature = false;

    /**
     * Parties checked in the Send for E-Sign dialog. Only these get a letter.
     *
     * @var list<string>
     */
    public array $esignPartyIds = [];

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

    /**
     * Document details form (lien_filings.document_details_json): the facts
     * the generated documents need that the application never asked for.
     *
     * @var array<string, mixed>
     */
    public array $documentForm = [];

    /** @var array<string, mixed> */
    public array $partyForm = [];

    /**
     * Recipient service form (delivery method, tracking, sent / delivered
     * times) for the recipient being edited.
     *
     * @var array<string, mixed>
     */
    public array $recipientForm = [];

    public ?int $editingRecipientId = null;

    public bool $showRecipientModal = false;

    public ?int $editingPartyId = null;

    public ?int $removingPartyId = null;

    public bool $showProjectModal = false;

    public bool $showFilingModal = false;

    public bool $showDocumentModal = false;

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
        'document_details_updated', 'recipient_added', 'recipient_updated',
        'esign_sent', 'esign_reminder_sent', 'esign_completed',
        'client_request_sent', 'client_response', 'document_uploaded',
        'draft_sent_for_review', 'draft_approved', 'draft_changes_requested',
        'customer_emailed', 'reply_dismissed',
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
            'recipients' => fn ($q) => $q->withoutGlobalScope('business')->with('party'),
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

        // Every party except the claimant (the sender) can get its own letter.
        $demandRecipients = $this->lienFiling->isDemandLetter()
            ? ($this->lienFiling->project?->nonClaimantParties() ?? collect())
            : collect();

        // The generated document package for the other kinds (prelim notice,
        // NOI, lien, release): the state and county rules, pre-flight
        // warnings and download links, or why there is nothing to download.
        $documentPackage = $this->lienFiling->hasGeneratedDocuments()
            ? LienDocumentPackage::forFiling($this->lienFiling)
            : null;

        $canChangeStatus = ! $isDeleted && auth()->user()->can('changeStatus', $this->lienFiling);

        // While the signer hasn't signed, admins can email a reminder or copy
        // the signing link to share it directly (text, chat, a phone call).
        $esignAwaiting = $esignRequest?->status === SignatureRequestStatus::AwaitingSignature;

        $esignReminders = $esignRequest?->events
            ->filter(fn ($event) => $event->event_type === SignatureEventType::ReminderSent)
            ->values() ?? collect();

        // Requests we emailed and the customer's answers (FilingShow),
        // including approvals of and changes to a draft sent for review.
        $clientResponses = $this->lienFiling->events()
            ->whereIn('event_type', ['client_response', 'draft_approved', 'draft_changes_requested'])
            ->with('creator')
            ->latest()
            ->orderByDesc('id')
            ->get();

        // Files on both sides, keyed by media id for the links.
        $requestFiles = $this->lienFiling->media()
            ->whereIn('collection_name', ['requests', 'customer_uploads', 'drafts'])
            ->get()
            ->keyBy('id');

        // Draft versions, newest first, each with what last happened to it
        // (sent for review, approved, changes requested).
        $drafts = $requestFiles
            ->where('collection_name', 'drafts')
            ->sortByDesc('id')
            ->values();

        // Send for E-Sign: a demand letter signs one letter per party; any
        // other filing signs its newest draft (EREG-141), where e-sign is
        // enabled for the document and state (else the reason shows).
        $isDemandLetter = $this->lienFiling->isDemandLetter();
        $esignDraft = $isDemandLetter ? null : $drafts->first();
        $esignBlockedReason = UploadedDocumentSignable::notAllowedReason($this->lienFiling);

        $canSendEsign = ! $isDeleted
            && ($isDemandLetter ? $demandRecipients->isNotEmpty() : ($esignDraft !== null && $esignBlockedReason === null))
            && $esignRequest?->isActive() !== true
            && $this->lienFiling->canTransitionTo(FilingStatus::AwaitingEsign)
            && auth()->user()->can('changeStatus', $this->lienFiling);

        $draftOutcomes = $drafts->isEmpty() ? collect() : $this->lienFiling->events()
            ->whereIn('event_type', ['draft_sent_for_review', 'draft_approved', 'draft_changes_requested'])
            ->oldest('id')
            ->get()
            ->mapWithKeys(fn ($event) => [
                (int) ($event->payload_json[$event->event_type === 'draft_changes_requested' ? 'draft_media_id' : 'media_id'] ?? 0) => $event->event_type,
            ]);

        $draftUploaders = $drafts->isEmpty() ? collect() : User::query()
            ->whereIn('id', $drafts->map(fn ($draft) => $draft->getCustomProperty('uploaded_by_user_id'))->filter()->unique())
            ->get()
            ->keyBy('id');

        // Every file the customer sent from the filing page (answers' files
        // and signed documents), newest first.
        $customerUploads = $requestFiles
            ->where('collection_name', 'customer_uploads')
            ->sortByDesc('id')
            ->values();

        // Recorded copies and proofs of service uploaded on the status form
        // (EREG-130), newest first; also keyed by id for the activity log.
        $milestoneFiles = $this->lienFiling->media()
            ->whereIn('collection_name', ['recorded', 'proof_of_service'])
            ->orderByDesc('id')
            ->get()
            ->keyBy('id');

        return view('lien.admin.filing-detail', [
            'filing' => $this->lienFiling,
            // The column the board shows (an untouched full-service order is
            // New there, see BoardCard). Null for statuses the board leaves
            // out (Draft, Complete, ...): the header shows the status then.
            'kanbanColumn' => BoardCard::forFiling($this->lienFiling)?->column
                ?? KanbanColumn::tryForFiling($this->lienFiling),
            'allowedTransitions' => $this->lienFiling->allowedTransitions(),
            'activityLog' => $this->getActivityLog(),
            'canChangeStatus' => $canChangeStatus,
            'canUpdate' => ! $isDeleted && auth()->user()->can('update', $this->lienFiling),
            'canAddComment' => ! $isDeleted && auth()->user()->can('addComment', $this->lienFiling),
            'canRefund' => $canRefund,
            'canDelete' => auth()->user()->can('delete', $this->lienFiling),
            'isDeleted' => $isDeleted,
            'refundablePayment' => $refundablePayment,
            'requiredDeadlines' => $requiredDeadlines,
            'filingDocStatus' => $filingDocStatus,
            'esignRequest' => $esignRequest,
            'hasPriorEsign' => $signedEsignRequest !== null,
            'demandRecipients' => $demandRecipients,
            'documentPackage' => $documentPackage,
            'signedLetters' => $signedEsignRequest?->documents->whereNotNull('signed_at')->values() ?? collect(),
            'canSendEsign' => $canSendEsign,
            'esignDraft' => $esignDraft,
            'esignDraftUploader' => $esignDraft ? $draftUploaders->get($esignDraft->getCustomProperty('uploaded_by_user_id')) : null,
            'esignBlockedReason' => $esignBlockedReason,
            'esignAwaiting' => $esignAwaiting,
            'esignReminders' => $esignReminders,
            'clientRequest' => $this->lienFiling->client_request_json,
            'clientRequestOpen' => $this->lienFiling->openClientRequest() !== null,
            'clientResponses' => $clientResponses,
            'requestFiles' => $requestFiles,
            'customerUploads' => $customerUploads,
            'milestoneFiles' => $milestoneFiles,
            'drafts' => $drafts,
            'draftOutcomes' => $draftOutcomes,
            'draftUploaders' => $draftUploaders,
            // Send for review moves the filing to Awaiting Client, so it needs
            // a draft and that transition (not while a request is open).
            'canSendDraft' => $canChangeStatus
                && $drafts->isNotEmpty()
                && $this->lienFiling->canTransitionTo(FilingStatus::AwaitingClient),
            'canUseGeneratedDraft' => $canChangeStatus && ($documentPackage?->isAvailable() ?? false),
            'reviewOpen' => $this->lienFiling->openReviewRequest() !== null,
            'canRemindSigner' => $esignAwaiting && $canChangeStatus && $esignRequest->invitation_bounced_at === null,
            'signingUrl' => $esignAwaiting && $canChangeStatus && ! $esignRequest->isExpired()
                ? $esignRequest->signingUrl()
                : null,
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

        // Awaiting Client / E-Signature / Notary: the customer gets the
        // message by email at once, so it is required.
        $isCustomerRequest = FilingStatus::tryFrom($this->newStatus)?->isWaitingOnCustomer() ?? false;

        // Milestone documents, emailed with the milestone email.
        $takesRecordedCopy = in_array($this->newStatus, [FilingStatus::Recorded->value, FilingStatus::Complete->value], true);
        $takesProofOfService = in_array($this->newStatus, [FilingStatus::Mailed->value, FilingStatus::Complete->value], true);
        $isRecorded = $this->newStatus === FilingStatus::Recorded->value;
        $isMailed = $this->newStatus === FilingStatus::Mailed->value;

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

        if ($isCustomerRequest) {
            $rules['customerMessage'] = ['required', 'string', 'max:2000'];
            $rules['customerAttachment'] = ['nullable', 'file', 'mimes:pdf', 'max:10240'];
        }

        if ($isRecorded) {
            $rules['recordingReference'] = ['nullable', 'string', 'max:255'];
        }

        if ($takesRecordedCopy) {
            $rules['recordedCopy'] = ['nullable', 'file', 'mimes:pdf', 'max:10240'];
        }

        if ($takesProofOfService) {
            $rules['proofOfService'] = ['nullable', 'file', 'mimes:pdf', 'max:10240'];
        }

        if ($isMailed) {
            $rules['trackingNumber'] = ['nullable', 'string', 'max:100'];
        }

        $this->validate($rules, [
            'customerMessage.required' => 'Write the message for the customer. It is emailed to them when you save.',
            'customerMessage.max' => 'Keep the message under 2,000 characters.',
            'customerAttachment.file' => 'The attachment did not upload. Please try again.',
            'customerAttachment.mimes' => 'The attachment must be a PDF.',
            'customerAttachment.max' => 'The attachment must be 10 MB or smaller.',
            'recordedCopy.file' => 'The recorded copy did not upload. Please try again.',
            'recordedCopy.mimes' => 'The recorded copy must be a PDF.',
            'recordedCopy.max' => 'The recorded copy must be 10 MB or smaller.',
            'proofOfService.file' => 'The proof of service did not upload. Please try again.',
            'proofOfService.mimes' => 'The proof of service must be a PDF.',
            'proofOfService.max' => 'The proof of service must be 10 MB or smaller.',
            'trackingNumber.max' => 'Keep the tracking number under 100 characters.',
        ]);

        $newStatus = FilingStatus::from($this->newStatus);
        $sendsMilestone = FilingMilestone::sendsFor($this->lienFiling, $newStatus);

        $action = app(ChangeFilingStatus::class);
        $action->execute(
            filing: $this->lienFiling,
            newStatus: $newStatus,
            note: $this->note ?: null,
            recordingMethod: $isRecordingTransition && $this->recordingMethod
                ? RecordingMethod::from($this->recordingMethod)
                : null,
            recordingProvider: $isRecordingTransition ? ($this->recordingProvider ?: null) : null,
            recordingReference: $isRecordingTransition || $isRecorded ? ($this->recordingReference ?: null) : null,
            recordingSubmittedAt: $isRecordingTransition && $this->recordingSubmittedAt
                ? Carbon::parse($this->recordingSubmittedAt, config('app.display_timezone'))->utc()
                : null,
            customerMessage: $isCustomerRequest ? $this->customerMessage : null,
            attachment: $isCustomerRequest ? $this->customerAttachment : null,
            recordedCopy: $takesRecordedCopy ? $this->recordedCopy : null,
            proofOfService: $takesProofOfService ? $this->proofOfService : null,
            trackingNumber: $isMailed ? ($this->trackingNumber ?: null) : null,
        );

        $this->lienFiling->refresh();
        $this->hydrateRecordingFields();
        $this->reset(['newStatus', 'note', 'customerMessage', 'customerAttachment', 'recordedCopy', 'proofOfService', 'trackingNumber']);

        $recipient = $this->lienFiling->createdBy?->email;

        session()->flash('success', match (true) {
            $isCustomerRequest && $recipient !== null => "Status updated. The message was emailed to {$recipient}.",
            $sendsMilestone && $recipient !== null => "Status updated. We emailed {$recipient}.",
            default => 'Status updated successfully.',
        });
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
     * "Still waiting": the customer's email (Front) did not give us what we
     * need. Clears the flag with an optional note and, while the filing waits
     * on the customer, restarts the reminders. The status stays.
     */
    public function stillWaiting(): void
    {
        $this->authorize('changeStatus', $this->lienFiling);

        $this->validate([
            'stillWaitingNote' => ['nullable', 'string', 'max:255'],
        ], [
            'stillWaitingNote.max' => 'Keep the note to one line (255 characters).',
        ]);

        $waiting = $this->lienFiling->status->isWaitingOnCustomer();
        $done = app(MarkStillWaiting::class)->execute($this->lienFiling, $this->stillWaitingNote ?: null);

        $this->lienFiling->refresh();
        $this->reset('stillWaitingNote');

        if ($done) {
            session()->flash('success', $waiting
                ? 'Marked still waiting. The reminders start again from the first one.'
                : 'Marked still waiting.');
        }
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

        // Demand letters: start with every party checked; the admin unchecks
        // any to skip. Other filings sign their newest draft, no party picker.
        $this->esignPartyIds = $this->lienFiling->isDemandLetter()
            ? ($this->lienFiling->project?->nonClaimantParties() ?? collect())
                ->map(fn ($party) => (string) $party->id)
                ->all()
            : [];

        $this->showSendEsignModal = true;
    }

    /**
     * Lock the demand letters (or, for any other filing, the newest draft,
     * EREG-141), email the signer, and move the filing to AwaitingEsign.
     */
    public function sendForEsign(): void
    {
        $this->authorize('changeStatus', $this->lienFiling);

        $isDemandLetter = $this->lienFiling->isDemandLetter();

        if ($isDemandLetter && $this->esignPartyIds === []) {
            $this->addError('esignPartyIds', 'Pick at least one party to send a demand letter to.');

            return;
        }

        try {
            if ($isDemandLetter) {
                app(SendDemandLetterForSignature::class)->execute(
                    $this->lienFiling,
                    auth()->user(),
                    array_map('intval', $this->esignPartyIds),
                );
            } else {
                app(SendUploadedDocumentForSignature::class)->execute($this->lienFiling, auth()->user());
            }

            $this->showSendEsignModal = false;
            $this->afterEdit();

            session()->flash('success', 'Sent for e-signature. The signer has been emailed a signing link.');
        } catch (EsignException $e) {
            $this->showSendEsignModal = false;

            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Email the signer a reminder with their signing link, renewing the link
     * for another TTL window.
     */
    public function sendSignerReminder(): void
    {
        $this->authorize('changeStatus', $this->lienFiling);

        try {
            $request = app(SendDemandLetterReminder::class)->execute($this->lienFiling, auth()->user());

            $this->afterEdit();

            session()->flash('success', "Reminder emailed to {$request->signer_email_snapshot}.");
        } catch (EsignException $e) {
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
    // Draft for review (EREG-140): versioned PDFs in the drafts
    // collection, sent to the customer to approve or send back.
    // ------------------------------------------------------------------

    /**
     * Upload a PDF as the next draft version. Every version is kept.
     */
    public function uploadDraft(): void
    {
        $this->authorize('changeStatus', $this->lienFiling);

        $this->validate([
            'draftFile' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ], [
            'draftFile.required' => 'Choose the PDF to upload.',
            'draftFile.file' => 'The draft did not upload. Please try again.',
            'draftFile.mimes' => 'The draft must be a PDF.',
            'draftFile.max' => 'The draft must be 20 MB or smaller.',
        ]);

        $version = $this->lienFiling->nextDraftVersion();

        $this->lienFiling->addMedia($this->draftFile->getRealPath())
            ->usingFileName($this->draftFile->getClientOriginalName())
            ->withCustomProperties($this->draftProperties($version, 'upload'))
            ->toMediaCollection('drafts');

        $this->reset('draftFile');

        session()->flash('success', "Draft v{$version} uploaded.");
    }

    /**
     * Copy the generated document (prelim notice, NOI, lien, release), as it
     * renders right now, into the drafts as the next version, so generated
     * and hand-built drafts go through the same review.
     */
    public function useGeneratedDraft(): void
    {
        $this->authorize('changeStatus', $this->lienFiling);

        if (! $this->lienFiling->hasGeneratedDocuments()) {
            session()->flash('error', 'This filing has no generated document.');

            return;
        }

        // The relations the document renders from, scoped as the page loads them.
        $this->loadFilingRelations();
        $generator = app(LienDocumentGenerator::class);

        try {
            $form = $generator->resolve($this->lienFiling);
        } catch (LienDocumentUnavailable $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        if (! view()->exists($form->body)) {
            session()->flash('error', "The {$form->stateName} {$form->title} template isn't built yet.");

            return;
        }

        $version = $this->lienFiling->nextDraftVersion();

        $this->lienFiling->addMediaFromString(base64_decode($generator->render($this->lienFiling, $form)->base64()))
            ->usingFileName($generator->filename($this->lienFiling, $form))
            ->withCustomProperties($this->draftProperties($version, 'generated'))
            ->toMediaCollection('drafts');

        session()->flash('success', "The generated document was saved as draft v{$version}.");
    }

    /**
     * Open the Send for review dialog with the message prefilled.
     */
    public function confirmSendDraftForReview(): void
    {
        $this->authorize('changeStatus', $this->lienFiling);

        $documentName = $this->lienFiling->documentType?->name ?: 'filing';

        $this->draftMessage = "Please review the attached draft of your {$documentName}. If everything is correct, approve it. If something needs to change, tell us what.";
        $this->draftNeedsSignature = false;
        $this->resetErrorBag();
        $this->showSendDraftModal = true;
    }

    /**
     * Send the newest draft for review: a review request through the usual
     * request mechanism (Awaiting Client, emailed at once with the draft
     * attached, reminders as for any request).
     */
    public function sendDraftForReview(): void
    {
        $this->authorize('changeStatus', $this->lienFiling);

        $this->validate([
            'draftMessage' => ['required', 'string', 'max:2000'],
            'draftNeedsSignature' => ['boolean'],
        ], [
            'draftMessage.required' => 'Write the message for the customer. It is emailed to them with the draft.',
            'draftMessage.max' => 'Keep the message under 2,000 characters.',
        ]);

        $draft = $this->lienFiling->draftVersions()->first();

        if ($draft === null || ! $this->lienFiling->canTransitionTo(FilingStatus::AwaitingClient)) {
            $this->showSendDraftModal = false;
            session()->flash('error', $draft === null
                ? 'Upload a draft first.'
                : "A draft can't be sent for review from the “{$this->lienFiling->status->label()}” status.");

            return;
        }

        app(SendDraftForReview::class)->execute(
            $this->lienFiling,
            $draft,
            $this->draftMessage,
            $this->draftNeedsSignature,
        );

        $this->showSendDraftModal = false;
        $this->reset(['draftMessage', 'draftNeedsSignature']);
        $this->afterEdit();

        $recipient = $this->lienFiling->createdBy?->email;
        $version = $draft->getCustomProperty('version');

        session()->flash('success', $recipient
            ? "Draft v{$version} sent for review. We emailed {$recipient}."
            : "Draft v{$version} sent for review.");
    }

    /**
     * @return array{version: int, uploaded_by_user_id: int|null, source: string}
     */
    private function draftProperties(int $version, string $source): array
    {
        return [
            'version' => $version,
            'uploaded_by_user_id' => auth()->id(),
            'source' => $source,
        ];
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

    public function editDocumentDetails(): void
    {
        $this->authorize('update', $this->lienFiling);
        $this->loadDocumentForm();
        $this->resetErrorBag();
        $this->showDocumentModal = true;
    }

    /**
     * Save the document details. No snapshot re-sync: nothing here is part
     * of payload_json; the documents render from the live filing.
     */
    public function updateDocumentDetails(): void
    {
        $this->authorize('update', $this->lienFiling);
        $this->validate($this->documentRules());

        $input = $this->documentForm;
        $input['estimated_price_cents'] = $this->dollarsToCents($this->documentForm['estimated_price'] ?? null);
        $input['original_lien'] = $this->documentForm['original_lien'] ?? [];
        $input['original_lien']['amount_received_cents'] = $this->dollarsToCents($this->documentForm['original_lien']['amount_received'] ?? null);

        app(UpdateLienDocumentDetails::class)->execute($this->lienFiling, $input);

        $this->afterEdit();
        $this->showDocumentModal = false;
        session()->flash('success', 'Document details updated.');
    }

    // ------------------------------------------------------------------
    // Recipients: who the document is served on, added one at a time by
    // an admin (never automatically), with the address snapshotted as of
    // that moment, plus the service facts once it has gone out.
    // ------------------------------------------------------------------

    public function addRecipient(int $partyId): void
    {
        $this->authorize('update', $this->lienFiling);

        $party = $this->lienFiling->project?->parties->firstWhere('id', $partyId);
        abort_if($party === null, 404);

        try {
            app(UpdateLienFilingRecipient::class)->add($this->lienFiling, $party);
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->afterEdit();
        session()->flash('success', ($party->displayName() ?: 'Party').' added as a recipient.');
    }

    public function editRecipient(int $recipientId): void
    {
        $this->authorize('update', $this->lienFiling);

        $recipient = $this->lienFiling->recipients->firstWhere('id', $recipientId);
        abort_if($recipient === null, 404);

        $this->editingRecipientId = $recipient->id;
        // Shown and edited in the display timezone; parsed back from it on save.
        $this->recipientForm = [
            'delivery_method' => $recipient->delivery_method,
            'tracking_number' => $recipient->tracking_number,
            'sent_at' => $recipient->sent_at?->eastern()->format('Y-m-d\TH:i'),
            'delivered_at' => $recipient->delivered_at?->eastern()->format('Y-m-d\TH:i'),
        ];
        $this->resetErrorBag();
        $this->showRecipientModal = true;
    }

    public function updateRecipient(): void
    {
        $this->authorize('update', $this->lienFiling);
        $this->validate($this->recipientRules());

        $recipient = $this->lienFiling->recipients->firstWhere('id', $this->editingRecipientId);
        abort_if($recipient === null, 404);

        $timestamp = fn (?string $value) => filled($value)
            ? Carbon::parse($value, config('app.display_timezone'))->utc()
            : null;

        app(UpdateLienFilingRecipient::class)->update($this->lienFiling, $recipient, [
            'delivery_method' => $this->recipientForm['delivery_method'] ?? null,
            'tracking_number' => $this->recipientForm['tracking_number'] ?? null,
            'sent_at' => $timestamp($this->recipientForm['sent_at'] ?? null),
            'delivered_at' => $timestamp($this->recipientForm['delivered_at'] ?? null),
        ]);

        $this->afterEdit();
        $this->showRecipientModal = false;
        session()->flash('success', 'Recipient updated.');
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
     * Stored details first; the signer, license and (for a release) the
     * recorded lien prefill from the business and the project so the usual
     * case is a one-click confirm. Contract type and notice date stay blank
     * because the documents already fall back to the project's values.
     */
    private function loadDocumentForm(): void
    {
        $filing = $this->lienFiling;
        $details = $filing->document_details_json ?? [];
        $original = $details['original_lien'] ?? [];
        $business = $filing->project?->business;

        $signer = collect($business?->responsible_people ?? [])
            ->first(fn (array $person) => ! empty($person['can_sign_liens']) && ! empty($person['name']));

        $recordedLien = $filing->documentKind() === 'lien_release' && empty($original['recording_reference']) && empty($original['recorded_at'])
            ? $filing->project?->latestRecordedLien($filing)
            : null;

        $this->documentForm = [
            'signer_name' => $details['signer_name'] ?? $signer['name'] ?? null,
            'signer_title' => $details['signer_title'] ?? $signer['title'] ?? null,
            'license_number' => $details['license_number'] ?? $business?->contractor_license_number,
            'contract_date' => $details['contract_date'] ?? null,
            'contract_type' => $details['contract_type'] ?? null,
            'estimated_price' => $this->centsToDollars($details['estimated_price_cents'] ?? null),
            'owner_interest' => $details['owner_interest'] ?? null,
            'block' => $details['block'] ?? null,
            'lot' => $details['lot'] ?? null,
            'notice_served_at' => $details['notice_served_at'] ?? null,
            'notice_served_method' => $details['notice_served_method'] ?? null,
            'months_of_work' => $details['months_of_work'] ?? null,
            'attachments_note' => $details['attachments_note'] ?? null,
            'original_lien' => [
                'recording_reference' => $original['recording_reference'] ?? $recordedLien?->recording_reference,
                'book' => $original['book'] ?? null,
                'page' => $original['page'] ?? null,
                'county' => $original['county'] ?? LienCountyKey::displayName($recordedLien?->jurisdiction_county),
                'recorded_at' => $original['recorded_at'] ?? $recordedLien?->recorded_at?->eastern()->format('Y-m-d'),
                'amount_received' => $this->centsToDollars($original['amount_received_cents'] ?? null),
                'other_info' => $original['other_info'] ?? null,
            ],
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
    private function documentRules(): array
    {
        return [
            'documentForm.signer_name' => ['nullable', 'string', 'max:255'],
            'documentForm.signer_title' => ['nullable', 'string', 'max:255'],
            'documentForm.license_number' => ['nullable', 'string', 'max:100'],
            'documentForm.contract_date' => ['nullable', 'date', 'before_or_equal:today'],
            'documentForm.contract_type' => ['nullable', Rule::in(UpdateLienDocumentDetails::CONTRACT_TYPES)],
            'documentForm.estimated_price' => ['nullable', 'numeric', 'min:0'],
            'documentForm.owner_interest' => ['nullable', 'string', 'max:255'],
            'documentForm.block' => ['nullable', 'string', 'max:50'],
            'documentForm.lot' => ['nullable', 'string', 'max:50'],
            'documentForm.notice_served_at' => ['nullable', 'date', 'before_or_equal:today'],
            'documentForm.notice_served_method' => ['nullable', Rule::in(UpdateLienDocumentDetails::NOTICE_METHODS)],
            'documentForm.months_of_work' => ['nullable', 'string', 'max:255'],
            'documentForm.attachments_note' => ['nullable', 'string', 'max:1000'],
            'documentForm.original_lien.recording_reference' => ['nullable', 'string', 'max:255'],
            'documentForm.original_lien.book' => ['nullable', 'string', 'max:50'],
            'documentForm.original_lien.page' => ['nullable', 'string', 'max:50'],
            'documentForm.original_lien.county' => ['nullable', 'string', 'max:255'],
            'documentForm.original_lien.recorded_at' => ['nullable', 'date', 'before_or_equal:today'],
            'documentForm.original_lien.amount_received' => ['nullable', 'numeric', 'min:0'],
            'documentForm.original_lien.other_info' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, list<mixed>>
     */
    private function recipientRules(): array
    {
        return [
            'recipientForm.delivery_method' => ['nullable', Rule::in(UpdateLienFilingRecipient::DELIVERY_METHODS)],
            'recipientForm.tracking_number' => ['nullable', 'string', 'max:100'],
            'recipientForm.sent_at' => ['nullable', 'date'],
            'recipientForm.delivered_at' => ['nullable', 'date', 'after_or_equal:recipientForm.sent_at'],
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
