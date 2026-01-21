<?php

namespace App\Domains\Lien\Models;

use App\Domains\Lien\Concerns\BelongsToBusiness;
use App\Domains\Lien\Enums\ClaimantType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LienProject extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'public_id',
        'business_id',
        'created_by_user_id',
        'name',
        'job_number',
        'claimant_type',
        'jobsite_address1',
        'jobsite_address2',
        'jobsite_city',
        'jobsite_state',
        'jobsite_zip',
        'jobsite_county',
        'legal_description',
        'apn',
        'project_type',
        'contract_date',
        'first_furnish_date',
        'last_furnish_date',
        'completion_date',
        'noc_recorded_date',
    ];

    protected function casts(): array
    {
        return [
            'claimant_type' => ClaimantType::class,
            'contract_date' => 'date',
            'first_furnish_date' => 'date',
            'last_furnish_date' => 'date',
            'completion_date' => 'date',
            'noc_recorded_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $project): void {
            if (! $project->public_id) {
                $project->public_id = Str::ulid()->toBase32();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function parties(): HasMany
    {
        return $this->hasMany(LienParty::class, 'project_id');
    }

    public function deadlines(): HasMany
    {
        return $this->hasMany(LienProjectDeadline::class, 'project_id');
    }

    public function filings(): HasMany
    {
        return $this->hasMany(LienFiling::class, 'project_id');
    }

    /**
     * Get the full jobsite address as a single line.
     */
    public function jobsiteAddressLine(): string
    {
        $parts = array_filter([
            $this->jobsite_address1,
            $this->jobsite_address2,
            $this->jobsite_city,
            $this->jobsite_state,
            $this->jobsite_zip,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the claimant party for this project.
     */
    public function claimantParty(): ?LienParty
    {
        return $this->parties()->where('role', 'claimant')->first();
    }

    /**
     * Get the property owner party for this project.
     */
    public function ownerParty(): ?LienParty
    {
        return $this->parties()->where('role', 'owner')->first();
    }

    /**
     * Get the next upcoming deadline.
     */
    public function nextDeadline(): ?LienProjectDeadline
    {
        return $this->deadlines()
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->first();
    }
}
