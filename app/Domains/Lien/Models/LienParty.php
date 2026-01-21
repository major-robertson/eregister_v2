<?php

namespace App\Domains\Lien\Models;

use App\Domains\Lien\Concerns\BelongsToBusiness;
use App\Domains\Lien\Enums\PartyRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LienParty extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'project_id',
        'role',
        'name',
        'company_name',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'email',
        'phone',
    ];

    protected function casts(): array
    {
        return [
            'role' => PartyRole::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(LienProject::class, 'project_id');
    }

    /**
     * Get the full address as a single line.
     */
    public function addressLine(): string
    {
        $parts = array_filter([
            $this->address1,
            $this->address2,
            $this->city,
            $this->state,
            $this->zip,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the display name (company name if available, otherwise name).
     */
    public function displayName(): string
    {
        return $this->company_name ?: $this->name;
    }

    /**
     * Convert party data to snapshot array for immutable storage.
     */
    public function toSnapshot(): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role->value,
            'name' => $this->name,
            'company_name' => $this->company_name,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }
}
