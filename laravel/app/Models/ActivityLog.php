<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'changes',
        'ip_address',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /* ---- Relationships ---- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ---- Static helper to log an action ---- */

    public static function log(
        string $action,
        ?Model $subject = null,
        ?array $changes = null,
        ?string $label = null,
    ): static {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'subject_label' => $label ?? ($subject->title ?? $subject->name ?? null),
            'changes' => $changes,
            'ip_address' => request()->ip(),
        ]);
    }
}

