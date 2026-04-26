<?php

namespace App\Domains\Lien\Admin\Actions;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\RecordingMethod;
use App\Domains\Lien\Models\LienFiling;
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
     * @throws \App\Domains\Lien\Exceptions\InvalidStatusTransitionException
     */
    public function execute(
        LienFiling $filing,
        FilingStatus $newStatus,
        ?string $note = null,
        ?RecordingMethod $recordingMethod = null,
        ?string $recordingProvider = null,
        ?string $recordingReference = null,
        ?\DateTimeInterface $recordingSubmittedAt = null,
    ): void {
        DB::transaction(function () use (
            $filing,
            $newStatus,
            $note,
            $recordingMethod,
            $recordingProvider,
            $recordingReference,
            $recordingSubmittedAt,
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

            $filing->transitionTo($newStatus, $meta);
        });
    }
}
