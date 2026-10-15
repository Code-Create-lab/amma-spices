<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserActivity extends Model
{
    use HasFactory;

    /**
     * Event types written by the tracking middleware (one row per page view).
     */
    public const PAGE_EVENTS = ['page_view', 'product_view', 'search', 'checkout_start', 'order_placed'];

    /**
     * Event types written by the frontend tracker JS.
     */
    public const CLIENT_EVENTS = ['add_to_cart', 'remove_from_cart', 'wishlist_add', 'wishlist_remove'];

    /**
     * Event types written server-side via recordServerEvent().
     */
    public const SERVER_EVENTS = ['address_added', 'address_selected'];

    protected $fillable = [
        'uuid',
        'visitor_session_id',
        'visitor_id',
        'user_id',
        'event_type',
        'page_url',
        'route_name',
        'product_id',
        'search_query',
        'metadata',
        'duration_seconds',
        'scroll_depth',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(VisitorSession::class, 'visitor_session_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'product_id', 'product_id');
    }

    /**
     * Record an event from server-side code (Livewire actions, controllers)
     * where no page-view uuid is available. No-ops if the visitor has no
     * tracked session; never throws.
     */
    public static function recordServerEvent(string $type, ?int $productId = null, array $metadata = []): void
    {
        try {
            $sessionRowId = session('va_session_id');
            if (!$sessionRowId) {
                return;
            }

            $session = VisitorSession::find($sessionRowId);
            if (!$session) {
                return;
            }

            $referer = request()->headers->get('referer');
            $pageUrl = $referer ? parse_url($referer, PHP_URL_PATH) : null;

            self::create([
                'uuid' => (string) Str::uuid(),
                'visitor_session_id' => $session->id,
                'visitor_id' => $session->visitor_id,
                'user_id' => auth()->id() ?? $session->user_id,
                'event_type' => $type,
                'page_url' => $pageUrl ? Str::limit($pageUrl, 500, '') : null,
                'product_id' => $productId,
                'metadata' => $metadata ?: null,
            ]);

            $session->update(['last_activity_at' => now()]);
        } catch (\Throwable $e) {
            Log::debug('activity-tracker recordServerEvent failed: ' . $e->getMessage());
        }
    }
}
