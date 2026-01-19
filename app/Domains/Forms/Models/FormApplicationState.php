<?php

namespace App\Domains\Forms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormApplicationState extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_application_id',
        'state_code',
        'status',
        'current_step_key',
        'data',
        'data_hash',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(FormApplication::class, 'form_application_id');
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete' || $this->completed_at !== null;
    }

    public function markComplete(): void
    {
        $this->update([
            'status' => 'complete',
            'completed_at' => now(),
        ]);
    }
}
