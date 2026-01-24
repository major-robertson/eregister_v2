<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\DTOs\ActivityItem;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienNotificationLog;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(): View
    {
        $business = Auth::user()->currentBusiness();
        $businessId = $business->id;

        // Continue where you left off - most recent draft
        $continueDraft = LienFiling::query()
            ->select(['id', 'public_id', 'project_id', 'document_type_id', 'updated_at'])
            ->with(['project:id,name,public_id', 'documentType:id,name'])
            ->whereHas('project', fn ($q) => $q->where('business_id', $businessId))
            ->where('status', FilingStatus::Draft)
            ->latest('updated_at')
            ->first();

        // Overdue deadlines - count + peek
        $overdueCount = LienProjectDeadline::query()
            ->whereHas('project', fn ($q) => $q->where('business_id', $businessId))
            ->where('due_date', '<', today())
            ->whereIn('status', [DeadlineStatus::Pending, DeadlineStatus::Missed])
            ->count();

        $overdueDeadlines = LienProjectDeadline::query()
            ->select(['id', 'project_id', 'document_type_id', 'due_date', 'status'])
            ->with(['project:id,name,public_id', 'documentType:id,name'])
            ->whereHas('project', fn ($q) => $q->where('business_id', $businessId))
            ->where('due_date', '<', today())
            ->whereIn('status', [DeadlineStatus::Pending, DeadlineStatus::Missed])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // Upcoming deadlines (7 days) - count + peek
        $upcomingCount = LienProjectDeadline::query()
            ->whereHas('project', fn ($q) => $q->where('business_id', $businessId))
            ->where('status', DeadlineStatus::Pending)
            ->whereBetween('due_date', [today(), today()->addDays(7)])
            ->count();

        $upcomingDeadlines = LienProjectDeadline::query()
            ->select(['id', 'project_id', 'document_type_id', 'due_date', 'status'])
            ->with(['project:id,name,public_id', 'documentType:id,name'])
            ->whereHas('project', fn ($q) => $q->where('business_id', $businessId))
            ->where('status', DeadlineStatus::Pending)
            ->whereBetween('due_date', [today(), today()->addDays(7)])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // Pending payments - count + peek
        $pendingPaymentsCount = LienFiling::query()
            ->whereHas('project', fn ($q) => $q->where('business_id', $businessId))
            ->where('status', FilingStatus::AwaitingPayment)
            ->count();

        $pendingPayments = LienFiling::query()
            ->select(['id', 'public_id', 'project_id', 'document_type_id', 'status', 'created_at'])
            ->with(['project:id,name,public_id', 'documentType:id,name'])
            ->whereHas('project', fn ($q) => $q->where('business_id', $businessId))
            ->where('status', FilingStatus::AwaitingPayment)
            ->latest()
            ->limit(5)
            ->get();

        // Missing info - count + peek (with reason)
        $missingInfoQuery = LienProject::query()
            ->where('business_id', $businessId)
            ->where(fn ($q) => $q
                ->whereNull('first_furnish_date')
                ->orWhereNull('last_furnish_date')
                ->orWhereNull('jobsite_county_google'));

        $missingInfoCount = $missingInfoQuery->count();

        $missingInfoProjects = $missingInfoQuery
            ->select(['id', 'public_id', 'name', 'first_furnish_date', 'last_furnish_date', 'jobsite_county_google'])
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'project' => $p,
                'reasons' => collect([
                    $p->first_furnish_date === null ? 'Missing first furnish date' : null,
                    $p->last_furnish_date === null ? 'Missing last furnish date' : null,
                    $p->jobsite_county_google === null ? 'Missing county' : null,
                ])->filter()->values()->all(),
            ]);

        // Draft filings count (for secondary quick action)
        $draftFilingsCount = LienFiling::query()
            ->whereHas('project', fn ($q) => $q->where('business_id', $businessId))
            ->where('status', FilingStatus::Draft)
            ->count();

        // Activity feed - unified
        $activityFeed = $this->buildActivityFeed($businessId);

        return view('livewire.lien.dashboard', [
            'continueDraft' => $continueDraft,
            'overdueCount' => $overdueCount,
            'overdueDeadlines' => $overdueDeadlines,
            'upcomingCount' => $upcomingCount,
            'upcomingDeadlines' => $upcomingDeadlines,
            'pendingPaymentsCount' => $pendingPaymentsCount,
            'pendingPayments' => $pendingPayments,
            'missingInfoCount' => $missingInfoCount,
            'missingInfoProjects' => $missingInfoProjects,
            'draftFilingsCount' => $draftFilingsCount,
            'activityFeed' => $activityFeed,
        ])->layout('layouts.lien', ['title' => 'Dashboard']);
    }

    private function buildActivityFeed(int $businessId): Collection
    {
        // Show recently updated filings with their current status (not every event)
        $recentFilings = LienFiling::query()
            ->select(['id', 'public_id', 'project_id', 'document_type_id', 'status', 'updated_at'])
            ->with(['project:id,name', 'documentType:id,name'])
            ->whereHas('project', fn ($q) => $q->where('business_id', $businessId))
            ->where('status', '!=', FilingStatus::Draft) // Drafts are shown in "Continue" block
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(fn ($f) => ActivityItem::fromFiling($f));

        // Keep deadline notification reminders (these are actionable)
        $notifications = LienNotificationLog::query()
            ->select(['id', 'project_deadline_id', 'interval_days', 'sent_at'])
            ->with(['projectDeadline:id,project_id,document_type_id', 'projectDeadline.project:id,name', 'projectDeadline.documentType:id,name'])
            ->whereHas('projectDeadline.project', fn ($q) => $q->where('business_id', $businessId))
            ->latest('sent_at')
            ->limit(10)
            ->get()
            ->map(fn ($n) => ActivityItem::fromNotification($n));

        return $recentFilings->concat($notifications)
            ->sortByDesc('createdAt')
            ->take(10)
            ->values();
    }
}
