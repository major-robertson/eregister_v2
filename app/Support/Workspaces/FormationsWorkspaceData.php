<?php

namespace App\Support\Workspaces;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;

class FormationsWorkspaceData implements WorkspaceDataResolver
{
    public function hasData(Business $business): bool
    {
        return FormApplication::query()
            ->where('business_id', $business->id)
            ->whereIn('form_type', $this->formTypes())
            ->exists();
    }

    public function summary(Business $business): ?string
    {
        $count = FormApplication::query()
            ->where('business_id', $business->id)
            ->whereIn('form_type', $this->formTypes())
            ->count();

        if ($count === 0) {
            return null;
        }

        return $count === 1 ? '1 formation' : "{$count} formations";
    }

    /**
     * @return array<int, string>
     */
    private function formTypes(): array
    {
        return (array) config('workspaces.formations.form_types', []);
    }
}
