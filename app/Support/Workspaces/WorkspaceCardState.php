<?php

namespace App\Support\Workspaces;

final readonly class WorkspaceCardState
{
    public function __construct(
        public bool $hasData,
        public ?string $summary,
        public string $ctaLabel,
    ) {}
}
