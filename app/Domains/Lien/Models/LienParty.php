<?php

namespace App\Domains\Lien\Models;

use App\Domains\Business\Concerns\BelongsToBusiness;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Support\PartyAddress;
use App\Domains\Lien\Support\ZipStateRanges;
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
        'address_same_as_jobsite_confirmed',
        'address_confirmed_by_customer',
        'email',
        'phone',
    ];

    protected function casts(): array
    {
        return [
            'role' => PartyRole::class,
            'address_same_as_jobsite_confirmed' => 'boolean',
            'address_confirmed_by_customer' => 'boolean',
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
            'address_same_as_jobsite_confirmed' => (bool) $this->address_same_as_jobsite_confirmed,
            'address_confirmed_by_customer' => $this->address_confirmed_by_customer,
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

    /**
     * The parts of a full mailing address (street, city, state, ZIP code)
     * this party is missing, in plain words.
     *
     * @return list<string>
     */
    public function missingMailingAddressParts(): array
    {
        return array_keys(array_filter([
            'street address' => blank($this->address1),
            'city' => blank($this->city),
            'state' => blank($this->state),
            'ZIP code' => blank($this->zip),
        ]));
    }

    /**
     * Whether this party's address is the project's jobsite address.
     */
    public function matchesJobsite(LienProject $project): bool
    {
        return PartyAddress::sameAsJobsite($project, $this->address1, $this->city, $this->zip);
    }

    /**
     * The warning when this party's ZIP code doesn't seem to be in its state,
     * or null (see ZipStateRanges).
     */
    public function zipStateWarning(): ?string
    {
        return ZipStateRanges::mismatchMessage($this->zip, $this->state);
    }

    /**
     * Whether the street line has no street number (a warning, not a block).
     */
    public function lacksStreetNumber(): bool
    {
        return PartyAddress::lacksStreetNumber($this->address1);
    }
}
