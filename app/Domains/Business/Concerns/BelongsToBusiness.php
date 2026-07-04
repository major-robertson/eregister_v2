<?php

namespace App\Domains\Business\Concerns;

use App\Domains\Business\Models\Business;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tenant scoping for business-owned domain models: a global scope pins
 * queries to the current business and business_id auto-fills on create.
 * Shared by the Lien and ResaleCert domains (and any future business-scoped
 * domain) — promoted from the identical per-domain copies.
 */
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
