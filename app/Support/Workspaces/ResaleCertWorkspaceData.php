<?php

namespace App\Support\Workspaces;

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Models\ResaleCertificate;

class ResaleCertWorkspaceData implements WorkspaceDataResolver
{
    public function hasData(Business $business): bool
    {
        return ResaleCertificate::forBusiness($business)->exists();
    }

    public function summary(Business $business): ?string
    {
        $count = ResaleCertificate::forBusiness($business)->count();

        if ($count === 0) {
            return null;
        }

        return $count === 1 ? '1 certificate' : "{$count} certificates";
    }
}
