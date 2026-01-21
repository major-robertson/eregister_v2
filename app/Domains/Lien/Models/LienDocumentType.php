<?php

namespace App\Domains\Lien\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LienDocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function templates(): HasMany
    {
        return $this->hasMany(LienDocumentTemplate::class, 'document_type_id');
    }

    public function deadlineRules(): HasMany
    {
        return $this->hasMany(LienDeadlineRule::class, 'document_type_id');
    }

    public function filings(): HasMany
    {
        return $this->hasMany(LienFiling::class, 'document_type_id');
    }
}
