<?php

namespace App\Http\Middleware;

use App\Helpers\DeviceDetector;
use App\Models\UserActivity;
use App\Models\VisitorSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TrackVisitorActivity
{
    /**
     * Idle gap (seconds) after which a new visitor session row is started.
     */
    protected const SESSION_TIMEOUT = 1800;

    protected const VISITOR_COOKIE = 'amma_visitor_id';

    /**
     * Paths that must never be tracked (admin panels, ajax/webhook endpoints).
     */
    protected const EXCLUDED_PATHS = [
        'admin', 'admin/*',
        'cityadmin', 'cityadmin/*',
        'store', 'store/*',
        'delivery', 'delivery/*',
        'track/*',
        'livewire/*',
        'api/*',
        'webhook/*',
        'payglocal/*',
        'razorpay/*',
        '_debugbar/*',
    ];

    public function handle(Request $request, Closure $next)
    {
        $trackable = $this->isTrackable($request);

        if ($trackable) {
            // The page-view uuid must exist before the view renders so the
            // tracker JS partial can pick it up for beacon/event calls.
            $request->attributes->set('activity_pv_uuid', (string) Str::uuid());
        }

        $response = $next($request);

        if ($trackable && $this->isTrackableResponse($response)) {
            try {
                $this->record($request);
            } catch (\Throwable $e) {
                // Tracking must never break the site.
                Log::debug('activity-tracker failed: ' . $e->getMessage());
            }
        }

        return $response;
    }

    protected function isTrackable(Request $request): bool
    {
        if (!config('services.activity_tracker.enabled', true)) {
            return false;
        }

        if (!$request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return false;
        }

        if ($request->is(...self::EXCLUDED_PATHS)) {
            return false;
        }

        if (DeviceDetector::isBot($request->userAgent())) {
            return false;
        }

        return true;
    }

    protected function isTrackableResponse($response): bool
    {
        if (!method_exists($response, 'getStatusCode') || $response->getStatusCode() !== 200) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type');

        return $contentType === null || stripos($contentType, 'html') !== false;
    }

    protected function record(Request $request): void
    {
        $now = now();

        $visitorId = $request->cookie(self::VISITOR_COOKIE);
        if (!is_string($visitorId) || !Str::isUuid($visitorId)) {
            $visitorId = (string) Str::uuid();
        }
        Cookie::queue(self::VISITOR_COOKIE, $visitorId, 60 * 24 * 365);

        $userId = auth()->id();
        $session = $this->resolveSession($request, $visitorId, $userId, $now);

        [$eventType, $productId, $searchQuery, $metadata] = $this->resolvePageEvent($request);

        UserActivity::create([
            'uuid' => $request->attributes->get('activity_pv_uuid') ?: (string) Str::uuid(),
            'visitor_session_id' => $session->id,
            'visitor_id' => $visitorId,
            'user_id' => $userId,
            'event_type' => $eventType,
            'page_url' => Str::limit('/' . ltrim($request->path(), '/'), 500, ''),
            'route_name' => optional($request->route())->getName(),
            'product_id' => $productId,
            'search_query' => $searchQuery ? Str::limit($searchQuery, 255, '') : null,
            'metadata' => $metadata ?: null,
        ]);

        $updates = [
            'last_activity_at' => $now,
            'exit_page' => Str::limit('/' . ltrim($request->path(), '/'), 500, ''),
            'page_views' => $session->page_views + 1,
            'duration_seconds' => max(0, $now->getTimestamp() - $session->started_at->getTimestamp()),
        ];

        // Guest session becomes attributable the moment the visitor logs in —
        // retro-link the session and all its guest activity rows to the user.
        if ($userId && !$session->user_id) {
            $updates['user_id'] = $userId;
            UserActivity::where('visitor_session_id', $session->id)
                ->whereNull('user_id')
                ->update(['user_id' => $userId]);
        }

        if (!$session->utm_source && $request->query('utm_source')) {
            $updates['utm_source'] = Str::limit($request->query('utm_source'), 100, '');
            $updates['utm_medium'] = Str::limit((string) $request->query('utm_medium'), 100, '') ?: null;
            $updates['utm_campaign'] = Str::limit((string) $request->query('utm_campaign'), 150, '') ?: null;
        }

        $session->update($updates);
    }

    protected function resolveSession(Request $request, string $visitorId, $userId, $now): VisitorSession
    {
        $sessionRowId = session('va_session_id');
        $lastSeen = (int) session('va_last_seen', 0);

        $session = null;
        if ($sessionRowId && ($now->getTimestamp() - $lastSeen) <= self::SESSION_TIMEOUT) {
            $session = VisitorSession::find($sessionRowId);
        }

        if (!$session) {
            $userAgent = Str::limit((string) $request->userAgent(), 500, '');
            $device = DeviceDetector::detect($userAgent);

            $referrer = $request->headers->get('referer');
            if ($referrer && stripos($referrer, $request->getHost()) !== false) {
                $referrer = null; // internal navigation is not a traffic source
            }

            $session = VisitorSession::create([
                'visitor_id' => $visitorId,
                'user_id' => $userId,
                'ip_address' => $request->ip(),
                'user_agent' => $userAgent ?: null,
                'device_type' => $device['device_type'],
                'browser' => $device['browser'],
                'os' => $device['os'],
                'referrer' => $referrer ? Str::limit($referrer, 500, '') : null,
                'utm_source' => $request->query('utm_source') ? Str::limit($request->query('utm_source'), 100, '') : null,
                'utm_medium' => $request->query('utm_medium') ? Str::limit($request->query('utm_medium'), 100, '') : null,
                'utm_campaign' => $request->query('utm_campaign') ? Str::limit($request->query('utm_campaign'), 150, '') : null,
                'landing_page' => Str::limit('/' . ltrim($request->path(), '/'), 500, ''),
                'page_views' => 0,
                'duration_seconds' => 0,
                'started_at' => $now,
                'last_activity_at' => $now,
            ]);

            session(['va_session_id' => $session->id]);
        }

        session(['va_last_seen' => $now->getTimestamp()]);

        return $session;
    }

    /**
     * @return array{0: string, 1: int|null, 2: string|null, 3: array}
     */
    protected function resolvePageEvent(Request $request): array
    {
        $routeName = optional($request->route())->getName();
        $eventType = 'page_view';
        $productId = null;
        $searchQuery = null;
        $metadata = [];

        switch ($routeName) {
            case 'single.product.view':
                $eventType = 'product_view';
                $slug = $request->route('slug');
                if ($slug) {
                    $productId = DB::table('products')->where('slug', $slug)->value('product_id');
                }
                break;

            case 'search':
            case 'search.results':
                $eventType = 'search';
                foreach (['q', 'query', 'search', 'keyword', 's'] as $param) {
                    if ($request->filled($param)) {
                        $searchQuery = (string) $request->query($param);
                        break;
                    }
                }
                break;

            case 'checkout.index':
                $eventType = 'checkout_start';
                break;

            case 'customer.orders.order_success':
                $eventType = 'order_placed';
                try {
                    $metadata['order_id'] = Crypt::decrypt($request->route('orderId'));
                } catch (\Throwable $e) {
                    $metadata['order_id'] = (string) $request->route('orderId');
                }
                break;

            case 'successOrder':
                $eventType = 'order_placed';
                $metadata['cart_id'] = (string) $request->route('cart_id');
                break;
        }

        // Capture a search-style query on any page (e.g. filtered listings).
        if (!$searchQuery && $request->filled('q')) {
            $searchQuery = (string) $request->query('q');
            if ($eventType === 'page_view') {
                $eventType = 'search';
            }
        }

        return [$eventType, $productId ? (int) $productId : null, $searchQuery, $metadata];
    }
}
