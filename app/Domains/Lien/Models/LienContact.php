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
        'first_name',
        'last_name',
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

    /**
     * The person's name, "First Last" — empty when this contact is a
     * company-only entry.
     */
    public function personName(): string
    {
        return trim(implode(' ', array_filter([$this->first_name, $this->last_name])));
    }

    /**
     * A contact carries a company, a person, or both. Show whichever exists,
     * with the person in parens when both are present.
     */
    public function displayName(): string
    {
        $person = $this->personName();

        if ($this->company_name && $person !== '') {
            return "{$this->company_name} ({$person})";
        }

        return $this->company_name ?: $person;
    }
}
