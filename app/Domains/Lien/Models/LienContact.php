<?php

namespace App\Domains\Lien\Models;

use App\Domains\Business\Concerns\BelongsToBusiness;
use App\Models\User;
use Database\Factories\Lien\LienContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A business-level directory entry: the GCs, subs, and suppliers a business
 * exchanges lien waivers with. Project parties (LienParty) are per-project;
 * contacts are reusable across projects so repeat counterparties are a
 * dropdown pick instead of retyped details.
 */
class LienContact extends Model
{
    use BelongsToBusiness, HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'created_by_user_id',
        'company_name',
        'contact_name',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
    ];

    protected static function newFactory(): LienContactFactory
    {
        return LienContactFactory::new();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function waivers(): HasMany
    {
        return $this->hasMany(LienWaiver::class, 'lien_contact_id');
    }

    public function displayName(): string
    {
        return $this->company_name.($this->contact_name ? " ({$this->contact_name})" : '');
    }
}
