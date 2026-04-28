<?php

namespace App\Support\Workspaces;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;

class SalesTaxWorkspaceData implements WorkspaceDataResolver
{
    public function hasData(Business $business): bool
    {
        return FormApplication::query()
            ->where('business_id', $business->id)
            ->where('form_type', $this->formType())
            ->exists();
    }

    public function summary(Business $business): ?string
    {
        $count = FormApplication::query()
            ->where('business_id', $business->id)
            ->where('form_type', $this->formType())
            ->count();

        if ($count === 0) {
            return null;
        }

        return $count === 1 ? '1 registration' : "{$count} registrations";
    }

    private function formType(): string
    {
        return (string) config('workspaces.sales_tax.form_type');
    }
}
