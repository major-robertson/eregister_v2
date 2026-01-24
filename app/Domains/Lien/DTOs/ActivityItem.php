<?php

namespace App\Domains\Lien\DTOs;

use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienNotificationLog;
use Carbon\CarbonInterface;

readonly class ActivityItem
{
    public function __construct(
        public string $type,
        public string $label,
        public string $icon,
        public ?string $filingPublicId,
        public ?int $projectId,
        public string $projectName,
        public CarbonInterface $createdAt,
    ) {}

    public static function fromFiling(LienFiling $filing): self
    {
        $docTypeName = $filing->documentType?->name ?? 'Filing';
        $statusLabel = $filing->status->label();

        return new self(
            type: 'filing',
            label: "{$docTypeName}: {$statusLabel}",
            icon: $filing->status->icon(),
            filingPublicId: $filing->public_id,
            projectId: $filing->project_id,
            projectName: $filing->project?->name ?? 'Unknown Project',
            createdAt: $filing->updated_at,
        );
    }

    public static function fromNotification(LienNotificationLog $log): self
    {
        $deadline = $log->projectDeadline;
        $docTypeName = $deadline?->documentType?->name ?? 'Document';
        $daysText = $log->interval_days === 1 ? '1 day' : "{$log->interval_days} days";

        return new self(
            type: 'notification',
            label: "Reminder: {$docTypeName} due in {$daysText}",
            icon: 'bell',
            filingPublicId: null,
            projectId: $deadline?->project_id,
            projectName: $deadline?->project?->name ?? 'Unknown Project',
            createdAt: $log->sent_at,
        );
    }
}
