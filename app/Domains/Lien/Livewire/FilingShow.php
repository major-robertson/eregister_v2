<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\RecordingMethod;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingEvent;
use App\Mail\ClientResponded;
use App\Mail\FilingMilestone;
use App\Mail\UploadReceived;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class FilingShow extends Component
{
    use WithFileUploads;

    /**
     * What the signed-document upload box takes, checked against the file's
     * content rather than its name or the type the browser reported.
     *
     * @var list<string>
     */
    private const SIGNED_DOCUMENT_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/heic',
        'image/heif',
        'image/heic-sequence',
        'image/heif-sequence',
    ];

    public LienFiling $filing;

    public ?string $tracking_number = null;

    public $proofFile = null;

    /**
     * The customer's answer to the open request (client_request_json), typed
     * and/or as a file.
     */
    public string $answer = '';

    public $answerFile = null;

    /**
     * The signed (or notarized) document the customer sends back while the
     * filing waits on them (Awaiting Client / E-Signature / Notary).
     */
    public $signedDocument = null;

    /**
     * A draft sent for review (EREG-140): the customer ticks the box and
     * approves it, or says what to change (text, optional file).
     */
    public bool $approveConfirmed = false;

    public string $changes = '';

    public $changesFile = null;

    public function mount(LienFiling $filing): void
    {
        Gate::authorize('view', $filing);
        $this->filing = $filing;
        $this->tracking_number = $filing->mailing_tracking_number;
    }

    public function markMailed(): void
    {
        $this->validate([
            'tracking_number' => ['nullable', 'string', 'max:100'],
        ]);

        if ($this->filing->status === FilingStatus::Paid) {
            $this->filing->transitionTo(FilingStatus::Mailed, [
                'tracking_number' => $this->tracking_number,
            ]);
        }

        session()->flash('message', 'Filing marked as mailed.');
    }

    public function markRecorded(): void
    {
        if ($this->filing->status === FilingStatus::Mailed) {
            $this->filing->transitionTo(FilingStatus::Recorded);
        }

        session()->flash('message', 'Filing marked as recorded.');
    }

    public function markComplete(): void
    {
        if (in_array($this->filing->status, [FilingStatus::Paid, FilingStatus::Mailed, FilingStatus::Recorded])) {
            $this->filing->transitionTo(FilingStatus::Complete);
        }

        session()->flash('message', 'Filing marked as complete.');
    }

    public function uploadProof(): void
    {
        $this->validate([
            'proofFile' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $this->filing->addMedia($this->proofFile->getRealPath())
            ->usingFileName($this->proofFile->getClientOriginalName())
            ->toMediaCollection('proofs');

        $this->proofFile = null;

        session()->flash('message', 'Proof document uploaded.');
    }

    /**
     * Answer the request we emailed: log it as a client_response event, move
     * the filing to Needs Review (which closes the request and stops the
     * reminders) and tell lien ops.
     */
    public function submitAnswer(): void
    {
        Gate::authorize('answerRequest', $this->filing);

        $request = $this->filing->openClientRequest();

        if ($request === null) {
            $this->reset(['answer', 'answerFile']);
            session()->flash('message', 'This request is already closed.');

            return;
        }

        $this->validate([
            'answer' => ['required_without:answerFile', 'nullable', 'string', 'max:5000'],
            'answerFile' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,heic,heif', 'max:20480'],
        ], [
            'answer.required_without' => 'Type your answer or attach a file.',
            'answer.max' => 'Keep your answer under 5,000 characters.',
            'answerFile.file' => 'The file did not upload. Please try again.',
            'answerFile.mimes' => 'Attach a PDF, JPG, PNG or HEIC file.',
            'answerFile.max' => 'The file must be 20 MB or smaller.',
        ]);

        $text = trim($this->answer) ?: null;

        $response = DB::transaction(function () use ($text) {
            $media = $this->answerFile
                ? $this->filing->addMedia($this->answerFile->getRealPath())
                    ->usingFileName($this->answerFile->getClientOriginalName())
                    ->toMediaCollection('customer_uploads')
                : null;

            $response = $this->filing->events()->create([
                'business_id' => $this->filing->business_id,
                'event_type' => 'client_response',
                'payload_json' => [
                    'text' => $text,
                    'media_id' => $media?->id,
                    'file_name' => $media?->file_name,
                    'request_status' => $this->filing->status->value,
                ],
                'created_by' => auth()->id(),
            ]);

            // Allowed from all three waiting statuses.
            $this->filing->transitionTo(FilingStatus::NeedsReview, ['client_response_id' => $response->id]);

            return $response;
        });

        // The answer is saved; a queue hiccup must not show the customer an error.
        $ops = config('lien.notifications.ops_email');

        if (filled($ops)) {
            try {
                Mail::to($ops)->queue(new ClientResponded($this->filing, $response, $request));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->reset(['answer', 'answerFile']);

        session()->flash('message', 'Thanks. We have your answer and will keep going.');
    }

    /**
     * Take the signed (or notarized) document back while the filing waits on
     * the customer: store it in customer_uploads, log a document_uploaded
     * event, move the filing to Needs Review (which closes any open request
     * and stops the reminders), tell lien ops and confirm to the customer.
     */
    public function uploadSignedDocument(): void
    {
        Gate::authorize('answerRequest', $this->filing);

        if (! $this->filing->status->isWaitingOnCustomer()) {
            $this->reset('signedDocument');
            session()->flash('message', 'We are not waiting on a document for this filing anymore.');

            return;
        }

        $this->validate([
            'signedDocument' => [
                'bail',
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,heic,heif',
                'max:20480',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! in_array(self::detectMimeType($value), self::SIGNED_DOCUMENT_TYPES, true)) {
                        $fail('We could not open this file as a PDF, JPG, PNG or HEIC. Please upload the scan or photo itself.');
                    }
                },
            ],
        ], [
            'signedDocument.required' => 'Choose the file to upload.',
            'signedDocument.file' => 'The file did not upload. Please try again.',
            'signedDocument.mimes' => 'Upload a PDF, JPG, PNG or HEIC file.',
            'signedDocument.max' => 'The file must be 20 MB or smaller.',
        ]);

        $request = $this->filing->openClientRequest();
        $originalName = $this->signedDocument->getClientOriginalName();

        $upload = DB::transaction(function () use ($originalName) {
            $media = $this->filing->addMedia($this->signedDocument->getRealPath())
                ->usingFileName($originalName)
                ->withCustomProperties(['kind' => 'signed_document'])
                ->toMediaCollection('customer_uploads');

            $upload = $this->filing->events()->create([
                'business_id' => $this->filing->business_id,
                'event_type' => 'document_uploaded',
                'payload_json' => [
                    'kind' => 'signed_document',
                    'media_id' => $media->id,
                    'original_name' => $originalName,
                    'file_name' => $media->file_name,
                    'size' => $media->size,
                    'request_status' => $this->filing->status->value,
                ],
                'created_by' => auth()->id(),
            ]);

            // Allowed from all three waiting statuses.
            $this->filing->transitionTo(FilingStatus::NeedsReview, ['document_uploaded_id' => $upload->id]);

            return $upload;
        });

        // The document is saved; a queue hiccup must not show the customer an error.
        $ops = config('lien.notifications.ops_email');

        if (filled($ops)) {
            try {
                Mail::to($ops)->queue(new ClientResponded($this->filing, $upload, $request));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        try {
            Mail::to(auth()->user())->queue(new UploadReceived($this->filing, $originalName, auth()->user()));
        } catch (\Throwable $e) {
            report($e);
        }

        $this->reset('signedDocument');

        session()->flash('upload_received', $originalName);
    }

    /**
     * Approve the draft we sent for review: log a draft_approved event, move
     * the filing on (which closes the request and stops the reminders) and
     * tell lien ops.
     */
    public function approveDraft(): void
    {
        Gate::authorize('answerRequest', $this->filing);

        $request = $this->filing->openReviewRequest();

        if ($request === null) {
            $this->reset(['approveConfirmed', 'changes', 'changesFile']);
            session()->flash('message', 'This review is already closed.');

            return;
        }

        $this->validate([
            'approveConfirmed' => ['accepted'],
        ], [
            'approveConfirmed.accepted' => 'Tick the box to confirm you have read the whole document.',
        ]);

        $draft = $this->filing->reviewDraft();

        if ($draft === null) {
            $this->addError('approveConfirmed', 'We could not find the draft. Please reply to our email and we will send it again.');

            return;
        }

        $needsSignature = (bool) ($request['needs_signature'] ?? false);

        $approval = DB::transaction(function () use ($draft, $needsSignature) {
            $approval = $this->filing->events()->create([
                'business_id' => $this->filing->business_id,
                'event_type' => 'draft_approved',
                'payload_json' => [
                    'media_id' => $draft->id,
                    'version' => $draft->getCustomProperty('version'),
                    'file_name' => $draft->file_name,
                    'approved_by_user_id' => auth()->id(),
                    'approved_at' => now()->toIso8601String(),
                    'needs_signature' => $needsSignature,
                ],
                'created_by' => auth()->id(),
            ]);

            $this->filing->transitionTo(self::approvedDraftStatus($needsSignature), ['draft_approved_id' => $approval->id]);

            return $approval;
        });

        $this->notifyOps($approval, $request);

        $this->reset(['approveConfirmed', 'changes', 'changesFile']);

        session()->flash('draft_approved', $needsSignature ? 'needs_signature' : 'ready');
    }

    /**
     * Where an approved draft goes: Ready to File, or Needs Review when the
     * admin said it needs the customer's signature afterwards, so staff send
     * the signature request next (status form, or Send for E-Sign for a
     * demand letter). Not Awaiting E-Signature: that would start the "Please
     * sign" reminders with nothing to sign yet, and Send for E-Sign can only
     * move a filing into that status, not start from it.
     */
    public static function approvedDraftStatus(bool $needsSignature): FilingStatus
    {
        return $needsSignature ? FilingStatus::NeedsReview : FilingStatus::ReadyToFile;
    }

    /**
     * Ask for changes to the draft we sent for review: log a
     * draft_changes_requested event with the text (and file), move the filing
     * to Needs Review (which closes the request and stops the reminders) and
     * tell lien ops.
     */
    public function requestDraftChanges(): void
    {
        Gate::authorize('answerRequest', $this->filing);

        $request = $this->filing->openReviewRequest();

        if ($request === null) {
            $this->reset(['approveConfirmed', 'changes', 'changesFile']);
            session()->flash('message', 'This review is already closed.');

            return;
        }

        $this->validate([
            'changes' => ['required', 'string', 'max:5000'],
            'changesFile' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,heic,heif', 'max:20480'],
        ], [
            'changes.required' => 'Tell us what needs to change.',
            'changes.max' => 'Keep it under 5,000 characters.',
            'changesFile.file' => 'The file did not upload. Please try again.',
            'changesFile.mimes' => 'Attach a PDF, JPG, PNG or HEIC file.',
            'changesFile.max' => 'The file must be 20 MB or smaller.',
        ]);

        $draft = $this->filing->reviewDraft();
        $text = trim($this->changes);

        $changeRequest = DB::transaction(function () use ($request, $draft, $text) {
            $media = $this->changesFile
                ? $this->filing->addMedia($this->changesFile->getRealPath())
                    ->usingFileName($this->changesFile->getClientOriginalName())
                    ->withCustomProperties(['kind' => 'draft_changes'])
                    ->toMediaCollection('customer_uploads')
                : null;

            $changeRequest = $this->filing->events()->create([
                'business_id' => $this->filing->business_id,
                'event_type' => 'draft_changes_requested',
                'payload_json' => [
                    'text' => $text,
                    'media_id' => $media?->id,
                    'file_name' => $media?->file_name,
                    'draft_media_id' => $request['draft_media_id'] ?? null,
                    'version' => $draft?->getCustomProperty('version'),
                    'draft_file_name' => $draft?->file_name,
                ],
                'created_by' => auth()->id(),
            ]);

            $this->filing->transitionTo(FilingStatus::NeedsReview, ['draft_changes_requested_id' => $changeRequest->id]);

            return $changeRequest;
        });

        $this->notifyOps($changeRequest, $request);

        $this->reset(['approveConfirmed', 'changes', 'changesFile']);

        session()->flash('draft_changes', true);
    }

    /**
     * Queue the ops email about the customer's answer. The answer is saved;
     * a queue hiccup must not show the customer an error.
     */
    private function notifyOps(LienFilingEvent $event, ?array $request): void
    {
        $ops = config('lien.notifications.ops_email');

        if (! filled($ops)) {
            return;
        }

        try {
            Mail::to($ops)->queue(new ClientResponded($this->filing, $event, $request));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * The filing's history for the customer, oldest first (EREG-130): each
     * user-visible status change (FilingStatus::isUserMilestone) with its
     * description, plus for the recording and mailing milestones the method,
     * county, recording reference, tracking link and documents; and our
     * requests, their answers, uploads and draft reviews.
     *
     * @return list<array{at: \Carbon\CarbonInterface, icon: string, title: string, text: ?string, quote: ?string, details: list<string>, tracking: ?array{number: string, url: string}, links: list<array{label: string, url: string}>}>
     */
    private function timeline(): array
    {
        $files = $this->filing->media()
            ->whereIn('collection_name', ['recorded', 'proof_of_service'])
            ->get()
            ->keyBy('id');

        $items = [];

        foreach ($this->filing->events->sortBy([['created_at', 'asc'], ['id', 'asc']]) as $event) {
            $payload = $event->payload_json ?? [];
            $version = isset($payload['version']) ? " (version {$payload['version']})" : '';

            $item = match ($event->event_type) {
                'status_changed' => $this->statusTimelineItem($payload, $files),
                // A draft sent for review shows as draft_sent_for_review below.
                'client_request_sent' => ($payload['type'] ?? null) === 'review' ? null : [
                    'icon' => 'chat-bubble-left-ellipsis',
                    'title' => 'We asked you:',
                    'quote' => Str::limit((string) ($payload['text'] ?? ''), 500) ?: null,
                ],
                'draft_sent_for_review' => [
                    'icon' => 'document-magnifying-glass',
                    'title' => "We sent you a draft to review{$version}",
                ],
                'client_response' => [
                    'icon' => 'chat-bubble-left-right',
                    'title' => 'You answered',
                    'quote' => Str::limit((string) ($payload['text'] ?? ''), 500) ?: null,
                    'details' => filled($payload['file_name'] ?? null) ? ["You attached {$payload['file_name']}."] : [],
                ],
                'document_uploaded' => [
                    'icon' => 'arrow-up-tray',
                    'title' => 'You uploaded '.($payload['original_name'] ?? $payload['file_name'] ?? 'a document'),
                ],
                'draft_approved' => [
                    'icon' => 'check',
                    'title' => "You approved the draft{$version}",
                ],
                'draft_changes_requested' => [
                    'icon' => 'pencil-square',
                    'title' => "You asked for changes to the draft{$version}",
                    'quote' => Str::limit((string) ($payload['text'] ?? ''), 500) ?: null,
                ],
                default => null,
            };

            if ($item !== null) {
                $items[] = array_merge([
                    'at' => $event->created_at,
                    'icon' => 'information-circle',
                    'text' => null,
                    'quote' => null,
                    'details' => [],
                    'tracking' => null,
                    'links' => [],
                ], $item);
            }
        }

        return $items;
    }

    /**
     * A status change on the timeline, or null for Draft / Awaiting Payment
     * and for a change that opened a request (the request is its own item).
     *
     * @param  array<string, mixed>  $payload
     * @param  \Illuminate\Support\Collection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media>  $files
     * @return array<string, mixed>|null
     */
    private function statusTimelineItem(array $payload, $files): ?array
    {
        $status = FilingStatus::tryFrom($payload['to'] ?? '');
        $meta = $payload['meta'] ?? [];

        if ($status === null || ! $status->isUserMilestone()) {
            return null;
        }

        if ($status->isWaitingOnCustomer() && filled($meta['customer_message'] ?? null)) {
            return null;
        }

        $details = [];
        $tracking = null;

        if ($status === FilingStatus::SubmittedForRecording) {
            $method = RecordingMethod::tryFrom($meta['recording_method'] ?? '') ?? $this->filing->recording_method;
            $how = FilingMilestone::isInPerson($this->filing->recording_provider) ? 'In person' : $method?->label();
            $county = FilingMilestone::countyName($this->filing);

            if ($how) {
                $details[] = "Method: {$how}";
            }

            if ($county) {
                $details[] = "County: {$county}";
            }
        }

        if ($status === FilingStatus::Recorded) {
            $reference = ($meta['recording_reference'] ?? null) ?: $this->filing->recording_reference;

            if (filled($reference)) {
                $details[] = "Recording reference: {$reference}";
            }
        }

        if ($status === FilingStatus::Mailed) {
            $number = ($meta['tracking_number'] ?? null) ?: $this->filing->mailing_tracking_number;

            if (filled($number)) {
                $tracking = ['number' => $number, 'url' => FilingMilestone::trackingUrl($number)];
            }
        }

        $links = [];

        foreach (['recorded_media_id' => 'Recorded copy', 'proof_of_service_media_id' => 'Proof of service'] as $key => $label) {
            $file = isset($meta[$key]) ? $files->get($meta[$key]) : null;

            if ($file !== null) {
                $links[] = ['label' => "{$label} ({$file->file_name})", 'url' => $file->getTemporaryUrl(now()->addMinutes(30))];
            }
        }

        return [
            'icon' => $status->icon(),
            'title' => $status->label(),
            'text' => $status->userDescription(),
            'details' => $details,
            'tracking' => $tracking,
            'links' => $links,
        ];
    }

    /**
     * The file's type read from its content (magic bytes), or null when the
     * file cannot be read.
     */
    private static function detectMimeType(mixed $file): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        $path = $file->getRealPath();

        if (! is_string($path) || ! is_file($path)) {
            return null;
        }

        return (new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: null;
    }

    public function render(): View
    {
        $this->filing->load(['project', 'documentType', 'recipients', 'events' => fn ($q) => $q->latest(), 'payment']);

        // Surface a "Review & Sign" link when this filing is awaiting the current
        // user's e-signature.
        $signUrl = null;
        if ($this->filing->status === FilingStatus::AwaitingEsign) {
            $active = $this->filing->activeSignatureRequest();

            if ($active !== null && $active->signer_user_id === auth()->id()) {
                $ttlDays = (int) config('esign.signing.invitation_link_ttl_days', 14);
                $signUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'esign.sign',
                    now()->addDays($ttlDays),
                    ['request' => $active->public_id],
                );
            }
        }

        $clientRequest = $this->filing->openClientRequest();

        // A filing moved to Awaiting Client / Notary before requests were
        // stored has no request on this page; it went out by email, so say
        // that (the generic reminder's wording) instead of "below".
        $whatsNext = $clientRequest === null && in_array($this->filing->status, [FilingStatus::AwaitingClient, FilingStatus::AwaitingNotary], true)
            ? 'Please check the request we emailed you and reply to it.'
            : $this->filing->status->whatsNext();

        // A draft sent for review gets its own panel at the top (the PDF,
        // Approve, Request changes) in place of the request panel below.
        $reviewRequest = $this->filing->openReviewRequest();
        $reviewDraft = $reviewRequest ? $this->filing->reviewDraft() : null;
        $statusDescription = $this->filing->status->userDescription();

        if ($reviewRequest !== null) {
            $statusDescription = 'Your draft is ready for review.';
            $whatsNext = 'Read the draft above. Approve it, or tell us what to change.';
        }

        // The upload box shows while the filing waits on the customer. With an
        // open request it sits in the request panel with the answer form:
        // after the form for Awaiting Client (usually a question), before it
        // for Awaiting Notary / E-Signature (usually a document to return).
        $showUploadBox = $this->filing->status->isWaitingOnCustomer() && $reviewRequest === null;

        return view('livewire.lien.filing-show', [
            'events' => $this->filing->events,
            'recipients' => $this->filing->recipients,
            'proofs' => $this->filing->getMedia('proofs'),
            'canDownload' => $this->filing->isPaid() && $this->filing->getFirstMedia('generated'),
            'signUrl' => $signUrl,
            'whatsNext' => $whatsNext,
            'clientRequest' => $clientRequest,
            'clientRequestSentAt' => $clientRequest ? $this->filing->clientRequestSentAt() : null,
            'clientRequestSender' => $clientRequest ? $this->filing->clientRequestSender()?->first_name : null,
            'clientRequestAttachment' => $clientRequest ? $this->filing->clientRequestAttachment() : null,
            'showUploadBox' => $showUploadBox,
            'uploadAfterAnswer' => $clientRequest !== null && $this->filing->status === FilingStatus::AwaitingClient,
            'statusDescription' => $statusDescription,
            'reviewRequest' => $reviewRequest,
            'reviewDraft' => $reviewDraft,
            // Signed for this draft only (FilingDraftController), so the
            // versions not sent yet can't be opened by changing the id.
            'reviewDraftUrl' => $reviewDraft
                ? URL::temporarySignedRoute('lien.filings.drafts.show', now()->addHours(2), ['filing' => $this->filing, 'media' => $reviewDraft->id])
                : null,
            'timeline' => $this->timeline(),
        ])->layout('components.layouts.portal', ['title' => $this->filing->documentType->name]);
    }
}
