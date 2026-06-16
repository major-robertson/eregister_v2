<?php

namespace App\Support\Workspaces;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\SalesTaxRegistration;

class SalesTaxWorkspaceData implements WorkspaceDataResolver
{
    public function hasData(Business $business): bool
    {
        return SalesTaxRegistration::query()
            ->where('business_id', $business->id)
            ->exists();
    }

    public function summary(Business $business): ?string
    {
        $count = SalesTaxRegistration::query()
            ->where('business_id', $business->id)
            ->count();

        if ($count === 0) {
            return null;
        }

        return $count === 1 ? '1 registration' : "{$count} registrations";
    }
}
