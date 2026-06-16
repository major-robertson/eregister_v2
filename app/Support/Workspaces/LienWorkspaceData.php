<?php

namespace App\Support\Workspaces;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienProject;

class LienWorkspaceData implements WorkspaceDataResolver
{
    public function hasData(Business $business): bool
    {
        return LienProject::query()
            ->where('business_id', $business->id)
            ->exists();
    }

    public function summary(Business $business): ?string
    {
        $count = LienProject::query()
            ->where('business_id', $business->id)
            ->count();

        if ($count === 0) {
            return null;
        }

        return $count === 1 ? '1 project' : "{$count} projects";
    }
}
