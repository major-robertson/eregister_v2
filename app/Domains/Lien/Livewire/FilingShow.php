<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Mail\ClientResponded;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithFileUploads;

class FilingShow extends Component
{
    use WithFileUploads;

    public LienFiling $filing;

    public ?string $tracking_number = null;

    public $proofFile = null;

    /**
     * The customer's answer to the open request (client_request_json), typed
     * and/or as a file.
     */
    public string $answer = '';

    public $answerFile = null;

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
        ])->layout('components.layouts.portal', ['title' => $this->filing->documentType->name]);
    }
}
