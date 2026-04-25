<?php

namespace App\Domains\Lien\Admin\Actions;

use App\Domains\Lien\Enums\RecordingMethod;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Support\Facades\DB;

class UpdateRecordingDetails
{
    /**
     * Update the recording_* columns on a filing without changing its status.
     *
     * Logs a `recording_details_updated` event capturing the before/after
     * values of every changed field, so the activity timeline shows the
     * audit trail when admins correct or fill in details after the fact.
     */
    public function execute(
        LienFiling $filing,
        ?RecordingMethod $recordingMethod,
        ?string $recordingProvider,
        ?string $recordingReference,
        ?\DateTimeInterface $recordingSubmittedAt,
    ): void {
        DB::transaction(function () use (
            $filing,
            $recordingMethod,
            $recordingProvider,
            $recordingReference,
            $recordingSubmittedAt,
        ) {
            $before = [
                'recording_method' => $filing->recording_method?->value,
                'recording_provider' => $filing->recording_provider,
                'recording_reference' => $filing->recording_reference,
                'recording_submitted_at' => $filing->recording_submitted_at?->toIso8601String(),
            ];

            $filing->update([
                'recording_method' => $recordingMethod,
                'recording_provider' => $recordingProvider,
                'recording_reference' => $recordingReference,
                'recording_submitted_at' => $recordingSubmittedAt,
            ]);

            $after = [
                'recording_method' => $filing->recording_method?->value,
                'recording_provider' => $filing->recording_provider,
                'recording_reference' => $filing->recording_reference,
                'recording_submitted_at' => $filing->recording_submitted_at?->toIso8601String(),
            ];

            $changes = [];
            foreach ($after as $field => $value) {
                if ($before[$field] !== $value) {
                    $changes[$field] = ['from' => $before[$field], 'to' => $value];
                }
            }

            if ($changes === []) {
                return;
            }

            $filing->events()->create([
                'business_id' => $filing->business_id,
                'event_type' => 'recording_details_updated',
                'payload_json' => ['changes' => $changes],
                'created_by' => auth()->id(),
            ]);
        });
    }
}
