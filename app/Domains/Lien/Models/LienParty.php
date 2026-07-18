<?php

namespace App\Domains\Lien\Models;

use App\Domains\Business\Concerns\BelongsToBusiness;
use App\Domains\Lien\Enums\PartyRole;
use Database\Factories\Lien\LienPartyFactory;
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
        'county',
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

    protected static function newFactory(): LienPartyFactory
    {
        return LienPartyFactory::new();
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
     * Mailing address as stacked block lines: street, optional unit, then
     * "City, State Zip". Excludes name/company. Each part is optional.
     *
     * @return list<string>
     */
    public function addressLines(): array
    {
        return array_values(array_filter([
            $this->address1,
            $this->address2,
            $this->cityStateZip(),
        ]));
    }

    /**
     * "City, State Zip" on a single line (each component optional).
     */
    public function cityStateZip(): string
    {
        $cityState = implode(', ', array_filter([$this->city, $this->state]));

        return trim(implode(' ', array_filter([$cityState, $this->zip])));
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
            'county' => $this->county,
            'zip' => $this->zip,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }

    /**
     * Check if the party has a complete mailing address.
     * Requires at least name and basic address components.
     */
    public function hasMailingAddress(): bool
    {
        $hasName = ! empty($this->name) || ! empty($this->company_name);
        $hasAddress = ! empty($this->address1) && ! empty($this->city) && ! empty($this->state);

        return $hasName && $hasAddress;
    }
}
