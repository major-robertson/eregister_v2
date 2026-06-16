<?php

namespace App\Support\Workspaces;

use App\Domains\Business\Models\Business;

interface WorkspaceDataResolver
{
    /**
     * Whether the given business has any data in this workspace yet.
     * Drives the "Open" vs "Get Started" CTA on the /portal card.
     */
    public function hasData(Business $business): bool;

    /**
     * Optional one-line summary surfaced on the /portal workspace card
     * (e.g. "3 active projects"). Return null to omit.
     */
    public function summary(Business $business): ?string;
}
