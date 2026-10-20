<?php

namespace App\Domains\Lien\Admin\Actions;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\RecordingMethod;
use App\Domains\Lien\Exceptions\InvalidStatusTransitionException;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ChangeFilingStatus
{
    /**
     * Execute the status change action.
     *
     * Recording-detail args are optional. When `$recordingMethod` is non-null,
     * all four recording_* columns are written to the filing in the same
     * transaction as the status transition. The action does NOT enforce
     * "required for SubmittedForRecording" — that lives in the UI/component
     * layer so this action stays reusable for other transitions.
     *
     * `$customerMessage` (with an optional PDF `$attachment`) is the request
     * for the customer when moving to Awaiting Client / E-Signature / Notary:
     * it is stored on the status_changed event and the filing, and emailed at
     * once (LienFiling::transitionTo). Ignored for any other status. Requiring
     * it for those statuses is the component's job, like the recording fields.
     *
     * @throws InvalidStatusTransitionException
     */
    public function execute(
        LienFiling $filing,
        FilingStatus $newStatus,
        ?string $note = null,
        ?RecordingMethod $recordingMethod = null,
        ?string $recordingProvider = null,
        ?string $recordingReference = null,
        ?\DateTimeInterface $recordingSubmittedAt = null,
        ?string $customerMessage = null,
        ?UploadedFile $attachment = null,
    ): void {
        $customerMessage = $newStatus->isWaitingOnCustomer() && filled($customerMessage)
            ? trim($customerMessage)
            : null;

        // Check before storing the attachment, so a refused transition leaves
        // no orphaned file behind.
        if ($customerMessage !== null && ! $filing->canTransitionTo($newStatus)) {
            throw new InvalidStatusTransitionException($filing->status, $newStatus);
        }

        DB::transaction(function () use (
            $filing,
            $newStatus,
            $note,
            $recordingMethod,
            $recordingProvider,
            $recordingReference,
            $recordingSubmittedAt,
            $customerMessage,
            $attachment,
        ) {
            if ($recordingMethod !== null) {
                $filing->update([
                    'recording_method' => $recordingMethod,
                    'recording_provider' => $recordingProvider,
                    'recording_reference' => $recordingReference,
                    'recording_submitted_at' => $recordingSubmittedAt,
                ]);
            }

            $meta = [];

            if ($note) {
                $meta['note'] = $note;
            }

            // Snapshot the recording method onto the status-change event so the
            // activity log can render "Submitted for Recording — Mail to County"
            // historically, even if the recording_method is later edited.
            if ($recordingMethod !== null) {
                $meta['recording_method'] = $recordingMethod->value;
            }

            if ($customerMessage !== null) {
                $meta['customer_message'] = $customerMessage;
                $meta['attachment_media_id'] = $attachment
                    ? $filing->addMedia($attachment->getRealPath())
                        ->usingFileName($attachment->getClientOriginalName())
                        ->toMediaCollection('requests')
                        ->id
                    : null;
            }

            $filing->transitionTo($newStatus, $meta);
        });
    }
}
