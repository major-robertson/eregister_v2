<?php

namespace App\Domains\ResaleCert\Models;

use App\Domains\Business\Concerns\BelongsToBusiness;
use App\Models\User;
use Database\Factories\ResaleCert\ResaleVendorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A supplier the business buys from for resale — the "Seller" every
 * certificate is issued to.
 */
class ResaleVendor extends Model
{
    use BelongsToBusiness, HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'created_by_user_id',
        'legal_name',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'contact_name',
        'contact_email',
        'contact_phone',
    ];

    protected static function newFactory(): ResaleVendorFactory
    {
        return ResaleVendorFactory::new();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(ResaleCertificate::class, 'resale_vendor_id');
    }

    public function cityStateZip(): string
    {
        return "{$this->city}, {$this->state} {$this->postal_code}";
    }
}
