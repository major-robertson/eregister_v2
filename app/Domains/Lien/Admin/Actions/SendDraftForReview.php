<?php

namespace App\Domains\Lien\Admin\Actions;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Exceptions\InvalidStatusTransitionException;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Admin "Send for review" (EREG-140): a request of type review through the
 * EREG-128 mechanism. The filing moves to Awaiting Client with the message,
 * which LienFiling::transitionTo stores in client_request_json (with the
 * draft's media id and needs_signature) and emails at once with the draft
 * attached; the reminder sequence starts as for any request. The customer
 * approves the draft or asks for changes on the filing page (FilingShow).
 */
class SendDraftForReview
{
    /**
     * @throws InvalidStatusTransitionException
     */
    public function execute(LienFiling $filing, Media $draft, string $message, bool $needsSignature): void
    {
        if (! $filing->canTransitionTo(FilingStatus::AwaitingClient)) {
            throw new InvalidStatusTransitionException($filing->status, FilingStatus::AwaitingClient);
        }

        DB::transaction(function () use ($filing, $draft, $message, $needsSignature) {
            $filing->transitionTo(FilingStatus::AwaitingClient, [
                'customer_message' => trim($message),
                'attachment_media_id' => null,
                'request_type' => 'review',
                'draft_media_id' => $draft->id,
                'needs_signature' => $needsSignature,
            ]);

            $filing->events()->create([
                'business_id' => $filing->business_id,
                'event_type' => 'draft_sent_for_review',
                'payload_json' => [
                    'media_id' => $draft->id,
                    'version' => $draft->getCustomProperty('version'),
                    'file_name' => $draft->file_name,
                    'needs_signature' => $needsSignature,
                ],
                'created_by' => auth()->id(),
            ]);
        });
    }
}
