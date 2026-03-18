<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'blogs';

    protected $fillable = [
        'title',
        'slug',
        'thumbnail',
        'category',
        'author',
        'author_role',
        'excerpt',
        'content',
        'read_time',
        'tags',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'tags'         => 'array',          // auto encode/decode JSON
        'published_at' => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];
 
    // ── SCOPES ────────────────────────────────────────────

    /** Only published posts — used on frontend */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** Latest published first */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('published_at', 'desc');
    }
 
    // ── ACCESSORS ─────────────────────────────────────────

    /**
     * Auto-calculate read time from content word count.
     * Returns stored value if manually set, otherwise calculates.
     * Average reading speed: 200 words/min.
     */
    public function getReadTimeAttribute($value): int
    {
        if ($value) return (int) $value;
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        return max(1, (int) ceil($wordCount / 200));
    }

    /**
     * Auto-generate excerpt from content if not set.
     * Used in listing card (3-line clamp ~120 chars).
     */
    public function getExcerptAttribute($value): string
    {
        if ($value) return $value;
        return Str::limit(strip_tags($this->content ?? ''), 150);
    }

    /**
     * Formatted published date for display.
     * e.g. "12 March 2025"
     */
    public function getFormattedDateAttribute(): string
    {
        $date = $this->published_at ?? $this->created_at;
        return $date ? Carbon::parse($date)->format('d F Y') : '';
    }

    /**
     * Short date for listing cards.
     * e.g. "12 Mar 2025"
     */
    public function getShortDateAttribute(): string
    {
        $date = $this->published_at ?? $this->created_at;
        return $date ? Carbon::parse($date)->format('d M Y') : '';
    }

    /**
     * First letter of author name for avatar.
     * e.g. "A" from "Amma's Kitchen"
     */
    public function getAuthorInitialAttribute(): string
    {
        return strtoupper(substr($this->author ?? 'A', 0, 1));
    }
 
    // ── MUTATORS ──────────────────────────────────────────

    /**
     * Auto-generate slug from title when saving.
     * Only sets slug if not already provided.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
            }
        });
    }
}
