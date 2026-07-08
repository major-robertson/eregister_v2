<?php

namespace App\Domains\Lien\Waivers\Actions;

use App\Domains\Lien\Documents\WaiverGenerator;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienWaiver;
use Illuminate\Support\Carbon;

/**
 * Freezes a waiver's render payload, renders the PDF, and stores it on the
 * waiver's media. Everything downstream (esign locking, re-downloads, the
 * signed variant) renders from the frozen snapshot, never live project data.
 */
class GenerateWaiver
{
    public function __construct(private readonly WaiverGenerator $generator) {}

    public function execute(LienWaiver $waiver): LienWaiver
    {
        $payload = $this->generator->data($waiver);
        $form = $this->generator->resolveForm($waiver);

        $waiver->update([
            'render_snapshot_json' => $payload,
            'template_key' => $form->template,
            'template_version' => $form->templateVersion,
            'status' => WaiverStatus::Generated,
            'generated_at' => Carbon::now(),
        ]);

        $bytes = $this->generator->renderFromSnapshot($payload)->generatePdfContent();

        // Regeneration replaces the previous unsigned PDF, but only after the
        // new one is safely stored, so a failed store can't leave a Generated
        // waiver with no PDF at all.
        $media = $waiver->addMediaFromString($bytes)
            ->usingFileName($this->generator->filename($waiver))
            ->toMediaCollection('generated');
        $waiver->clearMediaCollectionExcept('generated', $media);

        return $waiver->refresh();
    }
}
