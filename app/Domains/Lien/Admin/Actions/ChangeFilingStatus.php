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
     * layer so this action stays reusable for other transitions. Moving to
     * Recorded without a method, a non-null `$recordingReference` alone is
     * written (the instrument number or book and page the county returned).
     *
     * `$customerMessage` (with an optional PDF `$attachment`) is the request
     * for the customer when moving to Awaiting Client / E-Signature / Notary:
     * it is stored on the status_changed event and the filing, and emailed at
     * once (LienFiling::transitionTo). Ignored for any other status. Requiring
     * it for those statuses is the component's job, like the recording fields.
     *
     * Milestone documents (EREG-130): `$recordedCopy` (Recorded, Complete)
     * goes to the 'recorded' media collection, `$proofOfService` (Mailed,
     * Complete) to 'proof_of_service', and `$trackingNumber` (Mailed) to
     * mailing_tracking_number. They are stored before the transition, so the
     * milestone email it sends attaches them, and their ids are kept on the
     * status_changed event for the filing page timeline. Ignored for any
     * other status.
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
        ?UploadedFile $recordedCopy = null,
        ?UploadedFile $proofOfService = null,
        ?string $trackingNumber = null,
    ): void {
        $customerMessage = $newStatus->isWaitingOnCustomer() && filled($customerMessage)
            ? trim($customerMessage)
            : null;

        $recordedCopy = in_array($newStatus, [FilingStatus::Recorded, FilingStatus::Complete], true) ? $recordedCopy : null;
        $proofOfService = in_array($newStatus, [FilingStatus::Mailed, FilingStatus::Complete], true) ? $proofOfService : null;
        $trackingNumber = $newStatus === FilingStatus::Mailed && filled($trackingNumber) ? trim($trackingNumber) : null;
        $recordedReference = $newStatus === FilingStatus::Recorded && $recordingMethod === null && filled($recordingReference)
            ? trim($recordingReference)
            : null;

        // Check before storing any file, so a refused transition leaves no
        // orphaned file behind.
        $storesFiles = $customerMessage !== null || $recordedCopy !== null || $proofOfService !== null;
        if ($storesFiles && ! $filing->canTransitionTo($newStatus)) {
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
            $recordedCopy,
            $proofOfService,
            $trackingNumber,
            $recordedReference,
        ) {
            if ($recordingMethod !== null) {
                $filing->update([
                    'recording_method' => $recordingMethod,
                    'recording_provider' => $recordingProvider,
                    'recording_reference' => $recordingReference,
                    'recording_submitted_at' => $recordingSubmittedAt,
                ]);
            }

            if ($recordedReference !== null) {
                $filing->update(['recording_reference' => $recordedReference]);
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

            if ($recordedReference !== null) {
                $meta['recording_reference'] = $recordedReference;
            }

            if ($trackingNumber !== null) {
                $meta['tracking_number'] = $trackingNumber;
            }

            if ($recordedCopy !== null) {
                $meta['recorded_media_id'] = $this->store($filing, $recordedCopy, 'recorded', ['uploaded_by_user_id' => auth()->id()]);
            }

            if ($proofOfService !== null) {
                $meta['proof_of_service_media_id'] = $this->store($filing, $proofOfService, 'proof_of_service', ['uploaded_by_user_id' => auth()->id()]);
            }

            if ($customerMessage !== null) {
                $meta['customer_message'] = $customerMessage;
                $meta['attachment_media_id'] = $attachment
                    ? $this->store($filing, $attachment, 'requests')
                    : null;
            }

            $filing->transitionTo($newStatus, $meta);
        });
    }

    /**
     * Store an uploaded file on the filing under its original name and
     * return the media id.
     *
     * @param  array<string, mixed>  $properties
     */
    private function store(LienFiling $filing, UploadedFile $file, string $collection, array $properties = []): int
    {
        return $filing->addMedia($file->getRealPath())
            ->usingFileName($file->getClientOriginalName())
            ->withCustomProperties($properties)
            ->toMediaCollection($collection)
            ->id;
    }
}
