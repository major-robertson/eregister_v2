<?php

namespace App\Domains\Lien\Livewire\Waivers;

use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Lien\Enums\WaiverKind;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Esign\Actions\SendWaiverForSignature;
use App\Domains\Lien\Esign\Actions\VoidWaiverSignatureRequest;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\Actions\GenerateWaiver;
use App\Domains\Lien\Waivers\ResolvedWaiverForm;
use App\Domains\Lien\Waivers\WaiverEntitlements;
use App\Domains\Lien\Waivers\WaiverFormResolver;
use App\Domains\Lien\Waivers\WaiverFormUnavailable;
use App\Domains\Lien\Waivers\WaiverStateRegistry;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Waiver detail page: status-driven actions (download, send for signature,
 * upload a signed copy, void), the deemed-effective countdown for GA/MS, and
 * a merged timeline of waiver milestones + esign audit events.
 *
 * Route binding is business-scoped via BelongsToBusiness, so another tenant's
 * public_id 404s at resolution.
 */
class WaiverShow extends Component
{
    use WithFileUploads;

    public LienWaiver $waiver;

    public $signedFile = null;

    public bool $showVoidModal = false;

    public ?string $voidReason = null;

    public bool $showUpsellModal = false;

    public function mount(LienWaiver $waiver): void
    {
        $this->waiver = $waiver;
    }

    /**
     * Draft-only recovery path: a save normally generates immediately, but a
     * failed generation leaves the row in Draft.
     */
    public function generatePdf(GenerateWaiver $generate): void
    {
        if ($this->waiver->status !== WaiverStatus::Draft) {
            return;
        }

        $this->waiver = $generate->execute($this->waiver);

        Flux::toast(text: 'Waiver PDF generated.', variant: 'success');
    }

    public function sendForSignature(SendWaiverForSignature $send): void
    {
        if (! WaiverEntitlements::canUseEsign(Auth::user()->currentBusiness())) {
            $this->showUpsellModal = true;

            return;
        }

        try {
            $send->execute($this->waiver, Auth::user());
            Flux::toast(text: 'Waiver sent for signature.', variant: 'success');
        } catch (EsignException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
        }

        $this->waiver->refresh();
    }

    /**
     * The print-sign-upload path: required in notary/witness states where
     * e-signing is unavailable, and handy when a counterparty signs on paper.
     * An outstanding e-sign request is voided first so the signer's link
     * can't produce a second executed copy.
     */
    public function uploadSigned(VoidWaiverSignatureRequest $void): void
    {
        // E-sign features are included on every tier (the free tier is only
        // limited by its monthly save allowance, consumed when the waiver was
        // saved); the gate stays as a single switch should that ever change.
        if (! WaiverEntitlements::canUseEsign(Auth::user()->currentBusiness())) {
            $this->showUpsellModal = true;

            return;
        }

        $this->validate([
            'signedFile' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        if (! in_array($this->waiver->status, [WaiverStatus::Generated, WaiverStatus::AwaitingSignature], true)) {
            Flux::toast(text: 'This waiver cannot accept a signed copy in its current status.', variant: 'warning');

            return;
        }

        // Voiding the outstanding request nulls sent_at; keep the original so
        // the "Sent for signature" milestone survives on the timeline.
        $originalSentAt = $this->waiver->sent_at;

        if ($this->waiver->status === WaiverStatus::AwaitingSignature) {
            $void->execute($this->waiver, Auth::user(), 'Signed copy uploaded outside e-sign.');
            $this->waiver->refresh();
        }

        $this->waiver->addMedia($this->signedFile->getRealPath())
            ->usingFileName($this->signedFile->getClientOriginalName())
            ->toMediaCollection('signed');

        $signedAt = now();

        // GA/MS: a signed waiver becomes conclusively effective N days after
        // execution unless payment arrives or an Affidavit of Nonpayment is
        // filed. The statutory countdown is a calendar-date rule, so anchor it
        // on the Eastern date we display signed_at in (not raw UTC), or an
        // evening-Eastern signing lands a day late.
        $deemedDays = WaiverStateRegistry::for($this->waiver->state)['deemed_effective_days'];

        $this->waiver->update([
            'status' => WaiverStatus::Signed,
            'signed_at' => $signedAt,
            'sent_at' => $originalSentAt ?? $this->waiver->sent_at,
            'deemed_effective_at' => $deemedDays !== null
                ? $signedAt->copy()->eastern()->addDays($deemedDays)->toDateString()
                : null,
        ]);

        $this->signedFile = null;

        Flux::toast(text: 'Signed copy uploaded.', variant: 'success');
    }

    public function voidSignatureRequest(VoidWaiverSignatureRequest $void): void
    {
        $void->execute($this->waiver, Auth::user(), $this->voidReason ?: null);

        $this->showVoidModal = false;
        $this->voidReason = null;
        $this->waiver->refresh();

        Flux::toast(text: 'Signature request voided. The waiver is back to Generated and can be re-sent.', variant: 'success');
    }

    private function resolvedForm(): ?ResolvedWaiverForm
    {
        try {
            return app(WaiverFormResolver::class)->resolve(
                $this->waiver->state,
                $this->waiver->kind,
                $this->waiver->project?->property_class,
            );
        } catch (WaiverFormUnavailable) {
            return null;
        }
    }

    public function render(): View
    {
        $this->waiver->load(['project', 'contact', 'createdBy']);

        $form = $this->resolvedForm();

        // Prefer the frozen snapshot's title: that's what the PDF says.
        $formTitle = $this->waiver->render_snapshot_json['form']['title']
            ?? $form?->title
            ?? $this->waiver->kind->label();

        $latestRequest = $this->waiver->latestSignatureRequest();
        $activeRequest = $this->waiver->activeSignatureRequest();

        $signedMedia = $this->waiver->getFirstMedia('signed')
            ?: $this->waiver->latestSignedDocument()?->signedMedia();

        return view('livewire.lien.waivers.waiver-show', [
            'formTitle' => $formTitle,
            'form' => $form,
            'latestRequest' => $latestRequest,
            'activeRequest' => $activeRequest,
            'hasPaidAccess' => WaiverEntitlements::hasPaidAccess(Auth::user()->currentBusiness()),
            'hasGeneratedPdf' => $this->waiver->getFirstMedia('generated') !== null,
            'hasSignedCopy' => $signedMedia !== null,
            'timeline' => $this->buildTimeline($latestRequest !== null ? $latestRequest->events()->with('actor:id,name')->get() : collect()),
            'kindShortLabel' => $this->waiver->kind instanceof WaiverKind ? $this->waiver->kind->shortLabel() : (string) $this->waiver->kind,
        ])->layout('components.layouts.portal', ['title' => $formTitle]);
    }

    /**
     * Waiver milestones + the signature request's hash-chained audit events,
     * sorted chronologically.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Domains\Esign\Models\SignatureEvent>  $events
     * @return \Illuminate\Support\Collection<int, array{label: string, at: \Illuminate\Support\Carbon, ip: ?string}>
     */
    private function buildTimeline($events)
    {
        $milestones = collect([
            ['label' => 'Waiver created', 'at' => $this->waiver->created_at, 'ip' => null],
            ['label' => 'PDF generated', 'at' => $this->waiver->generated_at, 'ip' => null],
            ['label' => 'Sent for signature', 'at' => $this->waiver->sent_at, 'ip' => null],
            ['label' => 'Signed', 'at' => $this->waiver->signed_at, 'ip' => null],
            ['label' => 'Voided', 'at' => $this->waiver->voided_at, 'ip' => null],
        ])->filter(fn (array $item) => $item['at'] !== null);

        $auditEvents = $events->map(fn ($event) => [
            'label' => $event->event_type->label(),
            'at' => $event->occurred_at,
            'ip' => $event->ip_address,
        ]);

        return $milestones->concat($auditEvents)
            ->sortBy('at')
            ->values();
    }
}
