<?php

namespace App\Domains\Lien\Models;

use App\Domains\Lien\Concerns\BelongsToBusiness;
use App\Domains\Lien\Enums\ClaimantType;
use App\Models\User;
use Database\Factories\Lien\LienProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LienProject extends Model
{
    use BelongsToBusiness, HasFactory;

    protected static function newFactory(): LienProjectFactory
    {
        return LienProjectFactory::new();
    }

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
        'jobsite_county_google',
        'jobsite_place_id',
        'jobsite_formatted_address',
        'jobsite_lat',
        'jobsite_lng',
        'legal_description',
        'apn',
        'project_type',
        'owner_is_tenant',
        'has_written_contract',
        'base_contract_amount_cents',
        'change_orders_cents',
        'credits_deductions_cents',
        'payments_received_cents',
        'uncompleted_work_cents',
        'contract_date',
        'first_furnish_date',
        'last_furnish_date',
        'completion_date',
        'noc_recorded_date',
        'wizard_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'claimant_type' => ClaimantType::class,
            'owner_is_tenant' => 'boolean',
            'has_written_contract' => 'boolean',
            'base_contract_amount_cents' => 'integer',
            'change_orders_cents' => 'integer',
            'credits_deductions_cents' => 'integer',
            'payments_received_cents' => 'integer',
            'uncompleted_work_cents' => 'integer',
            'contract_date' => 'date',
            'first_furnish_date' => 'date',
            'last_furnish_date' => 'date',
            'completion_date' => 'date',
            'noc_recorded_date' => 'date',
            'wizard_completed_at' => 'datetime',
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

    /**
     * Calculate the balance due based on financial breakdown.
     * Returns null if no base contract amount is set.
     */
    public function balanceDueCents(): ?int
    {
        if ($this->base_contract_amount_cents === null) {
            return null;
        }

        return $this->base_contract_amount_cents
            + ($this->change_orders_cents ?? 0)
            - ($this->credits_deductions_cents ?? 0)
            - ($this->payments_received_cents ?? 0)
            - ($this->uncompleted_work_cents ?? 0);
    }

    /**
     * Get the formatted balance due for display.
     */
    public function formattedBalanceDue(): ?string
    {
        $balance = $this->balanceDueCents();

        if ($balance === null) {
            return null;
        }

        return '$'.number_format($balance / 100, 2);
    }

    /**
     * Check if the project has financial data entered.
     */
    public function hasFinancialData(): bool
    {
        return $this->base_contract_amount_cents !== null;
    }

    /**
     * Get the customer/hiring party for this project.
     */
    public function customerParty(): ?LienParty
    {
        return $this->parties()->where('role', 'customer')->first();
    }

    /**
     * Get the general contractor party for this project.
     */
    public function gcParty(): ?LienParty
    {
        return $this->parties()->where('role', 'gc')->first();
    }

    /**
     * Check if the project wizard is complete.
     */
    public function isWizardComplete(): bool
    {
        return $this->wizard_completed_at !== null;
    }

    /**
     * Check if this project is a draft (wizard not completed).
     */
    public function isDraft(): bool
    {
        return $this->wizard_completed_at === null;
    }

    /**
     * Mark the wizard as completed.
     */
    public function markWizardComplete(): void
    {
        $this->update(['wizard_completed_at' => now()]);
    }
}
