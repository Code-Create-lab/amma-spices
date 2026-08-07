<?php

namespace App\Http\Controllers\Frontend\Tracking;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use App\Models\VisitorSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrackingController extends Controller
{
    /**
     * Heartbeat / final beacon: updates time-on-page and scroll depth for a
     * page-view row created by TrackVisitorActivity middleware.
     * Called via fetch() heartbeats and navigator.sendBeacon() on page exit.
     */
    public function beacon(Request $request)
    {
        $data = $this->payload($request);

        $uuid = $data['pv'] ?? '';
        if (!is_string($uuid) || !Str::isUuid($uuid)) {
            return response()->noContent();
        }

        $seconds = min(max((int) ($data['s'] ?? 0), 0), 21600);
        $scroll = min(max((int) ($data['d'] ?? 0), 0), 100);

        $activity = UserActivity::where('uuid', $uuid)->first();
        if (!$activity || !in_array($activity->event_type, UserActivity::PAGE_EVENTS, true)) {
            return response()->noContent();
        }

        $activity->update([
            'duration_seconds' => max((int) $activity->duration_seconds, $seconds),
            'scroll_depth' => max((int) $activity->scroll_depth, $scroll),
        ]);

        VisitorSession::where('id', $activity->visitor_session_id)->update([
            'last_activity_at' => now(),
            'duration_seconds' => DB::raw('GREATEST(TIMESTAMPDIFF(SECOND, started_at, NOW()), duration_seconds)'),
        ]);

        return response()->noContent();
    }

    /**
     * Client-side interaction events (add to cart, wishlist) tied to the
     * current page-view row's session.
     */
    public function event(Request $request)
    {
        $data = $this->payload($request);

        $uuid = $data['pv'] ?? '';
        $type = $data['type'] ?? '';

        if (!is_string($uuid) || !Str::isUuid($uuid)
            || !in_array($type, UserActivity::CLIENT_EVENTS, true)) {
            return response()->noContent();
        }

        $parent = UserActivity::where('uuid', $uuid)->first();
        if (!$parent) {
            return response()->noContent();
        }

        $productId = (int) ($data['product_id'] ?? 0);

        $metadata = [];
        $meta = $data['meta'] ?? [];
        if (is_array($meta)) {
            if (isset($meta['quantity'])) {
                $metadata['quantity'] = (int) $meta['quantity'];
            }
            if (isset($meta['price']) && is_numeric($meta['price'])) {
                $metadata['price'] = (float) $meta['price'];
            }
        }

        UserActivity::create([
            'uuid' => (string) Str::uuid(),
            'visitor_session_id' => $parent->visitor_session_id,
            'visitor_id' => $parent->visitor_id,
            'user_id' => $parent->user_id,
            'event_type' => $type,
            'page_url' => $parent->page_url,
            'route_name' => $parent->route_name,
            'product_id' => $productId > 0 ? $productId : null,
            'metadata' => $metadata ?: null,
        ]);

        VisitorSession::where('id', $parent->visitor_session_id)->update([
            'last_activity_at' => now(),
        ]);

        return response()->noContent();
    }

    protected function payload(Request $request): array
    {
        if ($request->isJson()) {
            return (array) $request->json()->all();
        }

        // navigator.sendBeacon posts as text/plain — parse the raw body.
        $decoded = json_decode($request->getContent(), true);

        return is_array($decoded) ? $decoded : $request->all();
    }
}
