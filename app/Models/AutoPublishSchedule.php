<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutoPublishSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'context',
        'category',
        'tags',
        'author',
        'author_role',
        'ai_provider',
        'ai_model',
        'start_date',
        'end_date',
        'blogs_per_day',
        'status',
        'last_run_at',
        'total_generated',
        'last_error',
        'notes',
    ];

    protected $casts = [
        'tags'        => 'array',
        'start_date'  => 'date',
        'end_date'    => 'date',
        'last_run_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'schedule_id');
    }

    // ── State Checks ──────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active'
            && now()->between(
                $this->start_date->startOfDay(),
                $this->end_date->endOfDay()
            );
    }

    public function isDueToday(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        // Never run before → due now
        if (! $this->last_run_at) {
            return true;
        }

        // Already ran today → skip
        return $this->last_run_at->lt(today());
    }

    // ── Computed Attributes ───────────────────────────────────────────────

    public function getTotalDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getRemainingDaysAttribute(): int
    {
        return (int) max(0, now()->startOfDay()->diffInDays($this->end_date->endOfDay(), false));
    }

    public function getProgressPercentageAttribute(): int
    {
        $total = $this->total_days;
        if ($total === 0) return 100;

        return (int) min(100, round(($this->total_generated / $total) * 100));
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'active'    => 'green',
            'paused'    => 'yellow',
            'completed' => 'blue',
            'failed'    => 'red',
            default     => 'gray',
        };
    }
}
