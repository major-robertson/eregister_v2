<?php

namespace App\Domains\Lien\Livewire\Waivers;

use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\WaiverEntitlements;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Waiver home: status tiles, free-tier meter, the GA/MS deemed-effective
 * countdown, and the most recent waivers. All queries are pinned to the
 * current business by LienWaiver's BelongsToBusiness global scope.
 */
class WaiverDashboard extends Component
{
    public function render(): View
    {
        $business = Auth::user()->currentBusiness();

        // Status tiles. "Drafts" includes Generated: details entered and/or
        // PDF built, but nothing sent or signed yet.
        $draftCount = LienWaiver::query()
            ->whereIn('status', [WaiverStatus::Draft, WaiverStatus::Generated])
            ->count();

        $awaitingCount = LienWaiver::query()
            ->where('status', WaiverStatus::AwaitingSignature)
            ->count();

        // Month boundaries in the product's Eastern display timezone; the
        // tile must agree with the Eastern dates rendered next to it.
        $now = now()->eastern();

        $signedThisMonthCount = LienWaiver::query()
            ->where('status', WaiverStatus::Signed)
            ->whereBetween('signed_at', [$now->copy()->startOfMonth()->utc(), $now->copy()->endOfMonth()->utc()])
            ->count();

        // GA/MS deemed-effective countdown: a signed conditional waiver in
        // those states becomes conclusively effective 90/60 days after
        // execution even if payment never arrived, unless an Affidavit of
        // Nonpayment is filed first. Only surface the card when the business
        // actually has SIGNED waivers carrying a deemed-effective date; a
        // voided one-off must not pin the card to the dashboard forever.
        $tracksDeemedEffective = LienWaiver::query()
            ->where('status', WaiverStatus::Signed)
            ->whereNotNull('deemed_effective_at')
            ->exists();

        $deemedEffectiveSoon = LienWaiver::query()
            ->with(['project:id,public_id,name'])
            ->where('status', WaiverStatus::Signed)
            ->whereBetween('deemed_effective_at', [today(), today()->addDays(30)])
            ->orderBy('deemed_effective_at')
            ->get();

        $recentWaivers = LienWaiver::query()
            ->with(['project:id,public_id,name'])
            ->latest()
            ->limit(8)
            ->get();

        return view('livewire.lien.waivers.waiver-dashboard', [
            'draftCount' => $draftCount,
            'awaitingCount' => $awaitingCount,
            'signedThisMonthCount' => $signedThisMonthCount,
            'tracksDeemedEffective' => $tracksDeemedEffective,
            'deemedEffectiveSoon' => $deemedEffectiveSoon,
            'recentWaivers' => $recentWaivers,
            'hasPaidAccess' => WaiverEntitlements::hasPaidAccess($business),
            'savedThisMonth' => WaiverEntitlements::savedThisMonth($business),
            'freeSavesLimit' => WaiverEntitlements::freeSavesLimit(),
        ])->layout('components.layouts.portal', ['title' => 'Lien Waivers']);
    }
}
