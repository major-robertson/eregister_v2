<?php

namespace App\Domains\Lien\Models;

use App\Domains\Lien\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LienFilingRecipient extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'filing_id',
        'party_id',
        'delivery_method',
        'address_snapshot_json',
        'tracking_number',
        'sent_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'address_snapshot_json' => 'array',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function filing(): BelongsTo
    {
        return $this->belongsTo(LienFiling::class, 'filing_id');
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(LienParty::class, 'party_id');
    }

    /**
     * Create a recipient from a party with address snapshot.
     */
    public static function fromParty(LienParty $party, string $deliveryMethod = 'certified_mail'): array
    {
        return [
            'business_id' => $party->business_id,
            'party_id' => $party->id,
            'delivery_method' => $deliveryMethod,
            'address_snapshot_json' => [
                'name' => $party->name,
                'company_name' => $party->company_name,
                'address1' => $party->address1,
                'address2' => $party->address2,
                'city' => $party->city,
                'state' => $party->state,
                'zip' => $party->zip,
            ],
        ];
    }

    /**
     * Get the formatted address from the snapshot.
     */
    public function snapshotAddressLine(): string
    {
        $snapshot = $this->address_snapshot_json ?? [];
        $parts = array_filter([
            $snapshot['address1'] ?? null,
            $snapshot['address2'] ?? null,
            $snapshot['city'] ?? null,
            $snapshot['state'] ?? null,
            $snapshot['zip'] ?? null,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the recipient name from the snapshot.
     */
    public function snapshotName(): string
    {
        $snapshot = $this->address_snapshot_json ?? [];

        return $snapshot['company_name'] ?? $snapshot['name'] ?? 'Unknown';
    }
}
