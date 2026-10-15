<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class UserActivityController extends Controller
{
    public function dashboard(Request $request)
    {
        [$start, $end] = $this->range($request);

        $cards = [
            'live_now' => DB::table('visitor_sessions')->where('last_activity_at', '>=', now()->subMinutes(5))->count(),
            'unique_visitors' => DB::table('visitor_sessions')->whereBetween('started_at', [$start, $end])->distinct()->count('visitor_id'),
            'sessions' => DB::table('visitor_sessions')->whereBetween('started_at', [$start, $end])->count(),
            'page_views' => DB::table('user_activities')->whereBetween('created_at', [$start, $end])->whereIn('event_type', UserActivity::PAGE_EVENTS)->count(),
            'avg_session' => (int) DB::table('visitor_sessions')->whereBetween('started_at', [$start, $end])->avg('duration_seconds'),
            'logged_in_visitors' => DB::table('visitor_sessions')->whereBetween('started_at', [$start, $end])->whereNotNull('user_id')->distinct()->count('user_id'),
            'add_to_carts' => DB::table('user_activities')->whereBetween('created_at', [$start, $end])->where('event_type', 'add_to_cart')->count(),
            'orders_placed' => DB::table('user_activities')->whereBetween('created_at', [$start, $end])->where('event_type', 'order_placed')->count(),
        ];

        $sessionsByDay = DB::table('visitor_sessions')
            ->selectRaw('DATE(started_at) as d, COUNT(DISTINCT visitor_id) as visitors')
            ->whereBetween('started_at', [$start, $end])
            ->groupBy('d')
            ->pluck('visitors', 'd');

        $viewsByDay = DB::table('user_activities')
            ->selectRaw('DATE(created_at) as d, COUNT(*) as views')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('event_type', UserActivity::PAGE_EVENTS)
            ->groupBy('d')
            ->pluck('views', 'd');

        $trend = ['labels' => [], 'visitors' => [], 'page_views' => []];
        foreach (CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay()) as $day) {
            $key = $day->toDateString();
            $trend['labels'][] = $day->format('d M');
            $trend['visitors'][] = (int) ($sessionsByDay[$key] ?? 0);
            $trend['page_views'][] = (int) ($viewsByDay[$key] ?? 0);
        }

        $devices = DB::table('visitor_sessions')
            ->selectRaw('device_type, COUNT(*) as total')
            ->whereBetween('started_at', [$start, $end])
            ->groupBy('device_type')
            ->orderByDesc('total')
            ->get();

        $topPages = DB::table('user_activities')
            ->selectRaw('page_url, COUNT(*) as views, COUNT(DISTINCT visitor_id) as unique_visitors, ROUND(COALESCE(AVG(duration_seconds), 0)) as avg_time')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('event_type', UserActivity::PAGE_EVENTS)
            ->groupBy('page_url')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $topProducts = DB::table('user_activities as ua')
            ->join('products as p', 'p.product_id', '=', 'ua.product_id')
            ->whereBetween('ua.created_at', [$start, $end])
            ->whereIn('ua.event_type', ['product_view', 'add_to_cart'])
            ->groupBy('ua.product_id', 'p.product_name')
            ->selectRaw("ua.product_id, p.product_name,
                SUM(ua.event_type = 'product_view') as views,
                COUNT(DISTINCT CASE WHEN ua.event_type = 'product_view' THEN ua.visitor_id END) as unique_viewers,
                ROUND(COALESCE(AVG(CASE WHEN ua.event_type = 'product_view' THEN ua.duration_seconds END), 0)) as avg_time,
                SUM(ua.event_type = 'add_to_cart') as carts")
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $sources = DB::table('visitor_sessions')
            ->selectRaw("COALESCE(NULLIF(utm_source, ''), CASE WHEN referrer IS NULL THEN 'Direct' ELSE SUBSTRING_INDEX(SUBSTRING_INDEX(REPLACE(REPLACE(referrer, 'https://', ''), 'http://', ''), '/', 1), '?', 1) END) as source, COUNT(*) as total")
            ->whereBetween('started_at', [$start, $end])
            ->groupBy('source')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $recentActivities = DB::table('user_activities as ua')
            ->leftJoin('users as u', 'u.id', '=', 'ua.user_id')
            ->leftJoin('products as p', 'p.product_id', '=', 'ua.product_id')
            ->orderByDesc('ua.id')
            ->limit(15)
            ->select('ua.event_type', 'ua.page_url', 'ua.search_query', 'ua.created_at', 'ua.visitor_session_id', 'u.name as user_name', 'p.product_name')
            ->get();

        return view('admin.user_activity.dashboard', array_merge(
            $this->pageData('User Activity Dashboard'),
            [
                'cards' => $cards,
                'trend' => $trend,
                'devices' => $devices,
                'topPages' => $topPages,
                'topProducts' => $topProducts,
                'sources' => $sources,
                'recentActivities' => $recentActivities,
                'fromDate' => $start->toDateString(),
                'toDate' => $end->toDateString(),
            ]
        ));
    }

    public function live()
    {
        $rows = DB::table('visitor_sessions as vs')
            ->leftJoin('users as u', 'u.id', '=', 'vs.user_id')
            ->where('vs.last_activity_at', '>=', now()->subMinutes(5))
            ->orderByDesc('vs.last_activity_at')
            ->limit(50)
            ->select('vs.id', 'vs.device_type', 'vs.browser', 'vs.exit_page', 'vs.page_views', 'vs.duration_seconds', 'vs.last_activity_at', 'u.name as user_name')
            ->get()
            ->map(function ($row) {
                return [
                    'user' => $row->user_name ?: 'Guest',
                    'is_guest' => !$row->user_name,
                    'device' => ucfirst($row->device_type) . ' · ' . $row->browser,
                    'current_page' => $row->exit_page,
                    'pages' => $row->page_views,
                    'duration' => activity_duration_format($row->duration_seconds),
                    'last_seen' => Carbon::parse($row->last_activity_at)->diffForHumans(),
                    'url' => route('admin.activity.session.detail', $row->id),
                ];
            });

        return response()->json(['count' => $rows->count(), 'rows' => $rows]);
    }

    public function sessions(Request $request)
    {
        if ($request->ajax()) {
            [$start, $end] = $this->range($request);

            $query = DB::table('visitor_sessions as vs')
                ->leftJoin('users as u', 'u.id', '=', 'vs.user_id')
                ->whereBetween('vs.started_at', [$start, $end])
                ->select(
                    'vs.id', 'vs.visitor_id', 'vs.user_id', 'vs.device_type', 'vs.browser', 'vs.os',
                    'vs.referrer', 'vs.utm_source', 'vs.landing_page',
                    'vs.page_views', 'vs.duration_seconds', 'vs.started_at',
                    'u.name as user_name', 'u.user_phone'
                );

            if ($request->filled('device_type')) {
                $query->where('vs.device_type', $request->device_type);
            }
            if ($request->visitor_type === 'registered') {
                $query->whereNotNull('vs.user_id');
            } elseif ($request->visitor_type === 'guest') {
                $query->whereNull('vs.user_id');
            }
            if (!empty($request->search['value'])) {
                $s = $request->search['value'];
                $query->where(function ($q) use ($s) {
                    $q->where('u.name', 'like', "%{$s}%")
                        ->orWhere('u.user_phone', 'like', "%{$s}%")
                        ->orWhere('vs.landing_page', 'like', "%{$s}%")
                        ->orWhere('vs.visitor_id', 'like', "%{$s}%");
                });
            }

            $query->orderByDesc('vs.started_at');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('started_at', function ($row) {
                    return date('d M Y, h:i A', strtotime($row->started_at));
                })
                ->addColumn('user_label', function ($row) {
                    if ($row->user_id) {
                        $name = e($row->user_name ?: ('User #' . $row->user_id));
                        $phone = $row->user_phone ? '<br><small class="text-muted">' . e($row->user_phone) . '</small>' : '';
                        return '<a href="' . route('admin.activity.user', $row->user_id) . '">' . $name . '</a>' . $phone;
                    }
                    return '<span class="badge badge-secondary">Guest</span>';
                })
                ->addColumn('device_label', function ($row) {
                    return e(ucfirst($row->device_type) . ' · ' . $row->browser . ' · ' . $row->os);
                })
                ->addColumn('source', function ($row) {
                    if ($row->utm_source) {
                        return e($row->utm_source);
                    }
                    if ($row->referrer) {
                        return e(parse_url($row->referrer, PHP_URL_HOST) ?: $row->referrer);
                    }
                    return 'Direct';
                })
                ->editColumn('landing_page', function ($row) {
                    return e(Str::limit($row->landing_page, 40));
                })
                ->editColumn('duration_seconds', function ($row) {
                    return activity_duration_format($row->duration_seconds);
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('admin.activity.session.detail', $row->id) . '" class="btn btn-primary btn-sm">View Journey</a>';
                })
                ->rawColumns(['user_label', 'action'])
                ->make(true);
        }

        return view('admin.user_activity.sessions', $this->pageData('Visitor Sessions'));
    }

    public function sessionDetail($id)
    {
        $session = DB::table('visitor_sessions as vs')
            ->leftJoin('users as u', 'u.id', '=', 'vs.user_id')
            ->where('vs.id', $id)
            ->select('vs.*', 'u.name as user_name', 'u.user_phone', 'u.email as user_email')
            ->first();

        abort_if(!$session, 404);

        $activities = DB::table('user_activities as ua')
            ->leftJoin('products as p', 'p.product_id', '=', 'ua.product_id')
            ->where('ua.visitor_session_id', $id)
            ->orderBy('ua.created_at')
            ->orderBy('ua.id')
            ->select('ua.*', 'p.product_name', 'p.slug as product_slug')
            ->get()
            ->map(function ($row) {
                $row->metadata = $row->metadata ? json_decode($row->metadata, true) : null;
                return $row;
            });

        return view('admin.user_activity.session_detail', array_merge(
            $this->pageData('Session Journey'),
            ['session' => $session, 'activities' => $activities]
        ));
    }

    public function userActivity($id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        abort_if(!$user, 404);

        $sessionStats = DB::table('visitor_sessions')
            ->where('user_id', $id)
            ->selectRaw('COUNT(*) as total_sessions, COALESCE(SUM(duration_seconds), 0) as total_time, COALESCE(SUM(page_views), 0) as total_page_views, MIN(started_at) as first_seen, MAX(last_activity_at) as last_seen')
            ->first();

        $eventCounts = DB::table('user_activities')
            ->where('user_id', $id)
            ->selectRaw('event_type, COUNT(*) as total')
            ->groupBy('event_type')
            ->pluck('total', 'event_type');

        $topDevice = DB::table('visitor_sessions')
            ->where('user_id', $id)
            ->selectRaw('device_type, COUNT(*) as total')
            ->groupBy('device_type')
            ->orderByDesc('total')
            ->value('device_type');

        $topProducts = DB::table('user_activities as ua')
            ->join('products as p', 'p.product_id', '=', 'ua.product_id')
            ->where('ua.user_id', $id)
            ->where('ua.event_type', 'product_view')
            ->groupBy('ua.product_id', 'p.product_name')
            ->selectRaw('ua.product_id, p.product_name, COUNT(*) as views, ROUND(COALESCE(AVG(ua.duration_seconds), 0)) as avg_time, MAX(ua.created_at) as last_viewed')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $searches = DB::table('user_activities')
            ->where('user_id', $id)
            ->where('event_type', 'search')
            ->whereNotNull('search_query')
            ->orderByDesc('id')
            ->limit(10)
            ->get(['search_query', 'created_at']);

        $sessions = DB::table('visitor_sessions')
            ->where('user_id', $id)
            ->orderByDesc('started_at')
            ->limit(25)
            ->get();

        return view('admin.user_activity.user_detail', array_merge(
            $this->pageData('User Activity — ' . ($user->name ?: 'User #' . $id)),
            compact('user', 'sessionStats', 'eventCounts', 'topDevice', 'topProducts', 'searches', 'sessions')
        ));
    }

    public function products(Request $request)
    {
        if ($request->ajax()) {
            [$start, $end] = $this->range($request);

            $query = DB::table('user_activities as ua')
                ->join('products as p', 'p.product_id', '=', 'ua.product_id')
                ->whereBetween('ua.created_at', [$start, $end])
                ->whereIn('ua.event_type', ['product_view', 'add_to_cart', 'wishlist_add'])
                ->groupBy('ua.product_id', 'p.product_name')
                ->selectRaw("ua.product_id, p.product_name,
                    SUM(ua.event_type = 'product_view') as views,
                    COUNT(DISTINCT CASE WHEN ua.event_type = 'product_view' THEN ua.visitor_id END) as unique_viewers,
                    ROUND(COALESCE(AVG(CASE WHEN ua.event_type = 'product_view' THEN ua.duration_seconds END), 0)) as avg_time,
                    SUM(ua.event_type = 'add_to_cart') as carts,
                    SUM(ua.event_type = 'wishlist_add') as wishlists");

            if (!empty($request->search['value'])) {
                $query->where('p.product_name', 'like', '%' . $request->search['value'] . '%');
            }

            $query->orderByDesc('views');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('avg_time', function ($row) {
                    return activity_duration_format($row->avg_time);
                })
                ->addColumn('conversion', function ($row) {
                    return $row->views > 0 ? round(($row->carts / $row->views) * 100, 1) . '%' : '—';
                })
                ->make(true);
        }

        return view('admin.user_activity.products', $this->pageData('Product Analytics'));
    }

    protected function range(Request $request): array
    {
        try {
            $from = $request->filled('from_date') ? Carbon::parse($request->from_date) : now()->subDays(6);
        } catch (\Throwable $e) {
            $from = now()->subDays(6);
        }

        try {
            $to = $request->filled('to_date') ? Carbon::parse($request->to_date) : now();
        } catch (\Throwable $e) {
            $to = now();
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->startOfDay(), $to->endOfDay()];
    }

    protected function pageData(string $title): array
    {
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', Auth::guard('admin')->user()->email)
            ->first();

        $logo = DB::table('tbl_web_setting')->where('set_id', '1')->first();

        return ['title' => $title, 'admin' => $admin, 'logo' => $logo];
    }
}
