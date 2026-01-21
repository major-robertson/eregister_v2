<?php

namespace App\Domains\Lien\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LienDocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type_id',
        'state',
        'county',
        'version',
        'effective_date',
        'schema_json',
        'blade_view',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'schema_json' => 'array',
            'is_active' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(LienDocumentType::class, 'document_type_id');
    }

    /**
     * Find the best matching template for a given state/county.
     * Falls back to state-only, then generic template.
     */
    public static function findForJurisdiction(int $documentTypeId, ?string $state, ?string $county = null): ?self
    {
        // Try exact match first
        if ($state && $county) {
            $template = static::where('document_type_id', $documentTypeId)
                ->where('state', $state)
                ->where('county', $county)
                ->where('is_active', true)
                ->orderByDesc('version')
                ->first();

            if ($template) {
                return $template;
            }
        }

        // Try state-only match
        if ($state) {
            $template = static::where('document_type_id', $documentTypeId)
                ->where('state', $state)
                ->whereNull('county')
                ->where('is_active', true)
                ->orderByDesc('version')
                ->first();

            if ($template) {
                return $template;
            }
        }

        // Fallback to generic template
        return static::where('document_type_id', $documentTypeId)
            ->whereNull('state')
            ->whereNull('county')
            ->where('is_active', true)
            ->orderByDesc('version')
            ->first();
    }
}
