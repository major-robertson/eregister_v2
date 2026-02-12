<?php

namespace App\Domains\Lien\Admin\Actions;

use App\Domains\Lien\Models\LienFiling;

class AddFilingComment
{
    /**
     * Add a comment to a filing without changing its status.
     */
    public function execute(LienFiling $filing, string $comment): void
    {
        $filing->events()->create([
            'business_id' => $filing->business_id,
            'event_type' => 'note_added',
            'payload_json' => ['comment' => $comment],
            'created_by' => auth()->id(),
        ]);
    }
}
