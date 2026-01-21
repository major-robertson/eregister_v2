<?php

namespace App\Domains\Lien\Concerns;

use App\Domains\Business\Models\Business;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBusiness
{
    protected static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope('business', function (Builder $query): void {
            if ($business = auth()->user()?->currentBusiness()) {
                $query->where($query->getModel()->getTable().'.business_id', $business->id);
            }
        });

        static::creating(function ($model): void {
            if (! $model->business_id && $business = auth()->user()?->currentBusiness()) {
                $model->business_id = $business->id;
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Scope to a specific business, bypassing the global scope.
     */
    public function scopeForBusiness(Builder $query, Business|int $business): Builder
    {
        $businessId = $business instanceof Business ? $business->id : $business;

        return $query->withoutGlobalScope('business')->where('business_id', $businessId);
    }
}
