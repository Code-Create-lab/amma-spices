<?php

namespace App\Http\Controllers\Admin;

use App\Traits\ImageStoragePicker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Orders;
use DB;
use Auth;
use Session;
use Carbon\carbon;

class HomeController extends Controller
{
    use ImageStoragePicker;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

public function adminHome(Request $request)
{
    $title = "Dashboard";
    $admin_email = Auth::guard('admin')->user()->email;
    $admin = DB::table('admin')
        ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
        ->where('admin.email', $admin_email)
        ->first();
    $logo = DB::table('tbl_web_setting')
        ->where('set_id', '1')
        ->first();

    // Filter logic - Only Date Filters
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');
    $quickFilter = $request->input('quickDateFilter');

    // Handle quick date filter
    if ($quickFilter) {
        switch ($quickFilter) {
            case 'today':
                $startDate = Carbon::today()->format('Y-m-d');
                $endDate = Carbon::today()->format('Y-m-d');
                break;
            case 'yesterday':
                $startDate = Carbon::yesterday()->format('Y-m-d');
                $endDate = Carbon::yesterday()->format('Y-m-d');
                break;
            case 'last7days':
                $startDate = Carbon::now()->subDays(7)->format('Y-m-d');
                $endDate = Carbon::today()->format('Y-m-d');
                break;
            case 'last30days':
                $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
                $endDate = Carbon::today()->format('Y-m-d');
                break;
            case 'thisMonth':
                $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'lastMonth':
                $startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'thisYear':
                $startDate = Carbon::now()->startOfYear()->format('Y-m-d');
                $endDate = Carbon::now()->endOfYear()->format('Y-m-d');
                break;
        }
    }

    // Determine if filters are applied
    $hasDateFilter = !empty($startDate) && !empty($endDate);

    // For comparison purposes, set default dates
    $ddate = Date('Y-m-d');
    
    // Set comparison periods - we won't use these for filtering, only for comparison display
    if ($hasDateFilter) {
        $to = $startDate;
        $next_date = $endDate;
    } else {
        $to = null;
        $next_date = null;
    }

    // Revenue calculations - SHOW ALL DATA if no filter
    $this_week_query = Orders::where('order_status', 'Completed')->whereNull('deleted_at');

    if ($hasDateFilter) {
        $this_week_query->whereBetween('order_date', [$startDate, $endDate]);
    }

    $this_week = $this_week_query->sum('total_price');

    // For comparison (previous period) - only calculate if date filter is applied
    $last_week = 0;
    if ($hasDateFilter) {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysDiff = $start->diffInDays($end) + 1;
        
        $prevPeriodEnd = $start->copy()->subDay()->format('Y-m-d');
        $prevPeriodStart = $start->copy()->subDays($daysDiff)->format('Y-m-d');
        
        $last_week = DB::table('orders')
            ->where('order_status', 'Completed')
            ->whereNull('deleted_at')
            ->whereBetween('order_date', [$prevPeriodStart, $prevPeriodEnd])
            ->sum('total_price');
    }

    $la = $last_week / 100;
    if ($la == 0) {
        $difference = $this_week;
    } else {
        $difference = ($this_week - $last_week) / $la;
    }

    // Completed Orders count - SHOW ALL DATA if no filter
    $this_week_ord_query = Orders::where('order_status', 'Completed')->whereNull('deleted_at');

    if ($hasDateFilter) {
        $this_week_ord_query->whereBetween('order_date', [$startDate, $endDate]);
    }

    $this_week_ord = $this_week_ord_query->count();

    // Previous period completed orders - only if filter applied
    $last_week_ord = 0;
    if ($hasDateFilter) {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysDiff = $start->diffInDays($end) + 1;
        
        $prevPeriodEnd = $start->copy()->subDay()->format('Y-m-d');
        $prevPeriodStart = $start->copy()->subDays($daysDiff)->format('Y-m-d');
        
        $last_week_ord = DB::table('orders')
            ->where('order_status', 'Completed')
            ->whereNull('deleted_at')
            ->whereBetween('order_date', [$prevPeriodStart, $prevPeriodEnd])
            ->count();
    }

    $la1 = $last_week_ord / 100;
    if ($la1 == 0) {
        $diff_ord = $this_week_ord;
    } else {
        $diff_ord = ($this_week_ord - $last_week_ord) / $la1;
    }

    // Cancelled orders - SHOW ALL DATA if no filter
    $this_week_can_query = Orders::where('order_status', 'Cancelled')->whereNull('deleted_at');
    
    if ($hasDateFilter) {
        $this_week_can_query->whereBetween('order_date', [$startDate, $endDate]);
    }
    
    $this_week_can = $this_week_can_query->count();

    // Previous period cancelled orders
    $last_week_can = 0;
    if ($hasDateFilter) {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysDiff = $start->diffInDays($end) + 1;
        
        $prevPeriodEnd = $start->copy()->subDay()->format('Y-m-d');
        $prevPeriodStart = $start->copy()->subDays($daysDiff)->format('Y-m-d');
        
        $last_week_can = DB::table('orders')
            ->where('order_status', 'Cancelled')
            ->whereNull('deleted_at')
            ->whereBetween('order_date', [$prevPeriodStart, $prevPeriodEnd])
            ->count();
    }

    $la2 = $last_week_can / 100;
    if ($la2 == 0) {
        $diff_can = $this_week_can;
    } else {
        $diff_can = ($this_week_can - $last_week_can) / $la2;
    }

    // Pending orders - SHOW ALL DATA if no filter
    $this_week_pen_query = Orders::where('order_status', 'pending')->whereNull('deleted_at');
    
    if ($hasDateFilter) {
        $this_week_pen_query->whereBetween('order_date', [$startDate, $endDate]);
    }
    
    $this_week_pen = $this_week_pen_query->count();

    // Previous period pending orders
    $last_week_pen = 0;
    if ($hasDateFilter) {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysDiff = $start->diffInDays($end) + 1;
        
        $prevPeriodEnd = $start->copy()->subDay()->format('Y-m-d');
        $prevPeriodStart = $start->copy()->subDays($daysDiff)->format('Y-m-d');
        
        $last_week_pen = DB::table('orders')
            ->where('order_status', 'pending')
            ->whereNull('deleted_at')
            ->whereBetween('order_date', [$prevPeriodStart, $prevPeriodEnd])
            ->count();
    }

    $la3 = $last_week_pen / 100;
    if ($la3 == 0) {
        $diff_pen = $this_week_pen;
    } else {
        $diff_pen = ($this_week_pen - $last_week_pen) / $la3;
    }

    // Users - SHOW ALL DATA if no filter
    $this_week_usr_query = DB::table('users');
    
    if ($hasDateFilter) {
        $this_week_usr_query->whereBetween('reg_date', [$startDate, $endDate]);
    }
    
    $this_week_usr = $this_week_usr_query->count();

    // Previous period users
    $last_week_usr = 0;
    if ($hasDateFilter) {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysDiff = $start->diffInDays($end) + 1;
        
        $prevPeriodEnd = $start->copy()->subDay()->format('Y-m-d');
        $prevPeriodStart = $start->copy()->subDays($daysDiff)->format('Y-m-d');
        
        $last_week_usr = DB::table('users')
            ->whereBetween('reg_date', [$prevPeriodStart, $prevPeriodEnd])
            ->count();
    }

    $la4 = $last_week_usr / 100;
    if ($la4 == 0) {
        $diff_usr = $this_week_usr;
    } else {
        $diff_usr = ($this_week_usr - $last_week_usr) / $la4;
    }

    // Total earnings - SHOW ALL DATA if no filter
    $total_earnings_query = Orders::where('order_status', 'Completed')->whereNull('deleted_at');

    if ($hasDateFilter) {
        $total_earnings_query->whereBetween('order_date', [$startDate, $endDate]);
    }

    $total_earnings = $total_earnings_query->sum('total_price');

    // Today earnings
    $today_earnings = Orders::where('order_status', 'Completed')
        ->whereNull('deleted_at')
        ->where('order_date', $ddate)
        ->sum('total_price');

    // Store earnings - SHOW ALL DATA if no filter
    $store_earning_query = DB::table('store')
        ->join('orders', 'store.id', '=', 'orders.store_id')
        ->select(DB::raw('SUM(orders.price_without_delivery)-SUM(orders.price_without_delivery)*(store.admin_share)/100 as sumprice'))
        ->groupBy('orders.order_status', 'store.admin_share')
        ->where('orders.order_status', 'Completed')
        ->whereNull('orders.deleted_at')
        ->where('orders.payment_method', '!=', NULL);

    if ($hasDateFilter) {
        $store_earning_query->whereBetween('orders.order_date', [$startDate, $endDate]);
    }

    $store_earning = $store_earning_query->first();
    
    if ($store_earning) {
        if ($store_earning->sumprice != NULL) {
            $store_earnings = $store_earning->sumprice;
        } else {
            $store_earnings = 0;
        }
    } else {
        $store_earnings = 0;
    }

    $admin_earnings = $total_earnings - $store_earnings;

    // Top selling products - SHOW ALL DATA if no filter
    $topselling_query = DB::table('store_orders')
        ->join('orders', 'store_orders.order_cart_id', '=', 'orders.cart_id')
        ->select(
            'store_orders.store_id', 
            'store_orders.product_name', 
            'store_orders.varient_id', 
            'store_orders.varient_image', 
            'store_orders.quantity', 
            'store_orders.unit', 
            'store_orders.description', 
            DB::raw('count(store_orders.varient_id) as count'), 
            DB::raw('SUM(store_orders.qty) as totalqty'), 
            DB::raw('SUM(store_orders.price) as revenue')
        )
        ->groupBy(
            'store_orders.store_id', 
            'store_orders.product_name', 
            'store_orders.varient_id', 
            'store_orders.varient_image', 
            'store_orders.quantity', 
            'store_orders.unit', 
            'store_orders.description'
        )
        ->orderBy('count', 'desc')
        ->whereNull('orders.deleted_at')
        ->where('orders.order_status', 'Completed');

    if ($hasDateFilter) {
        $topselling_query->whereBetween('orders.order_date', [$startDate, $endDate]);
    }

    $topselling = $topselling_query->limit(5)->get();

    // Ongoing orders - Always show latest 5
    $ongoin = Orders::join('address', 'orders.address_id', '=', 'address.address_id')
        ->join('users', 'orders.user_id', '=', 'users.id')
        ->where('orders.order_status', '!=', NULL)
        ->where('orders.payment_method', '!=', NULL)
        ->whereNull('orders.deleted_at')
        ->orderBy('orders.order_id', 'DESC')
        ->limit(5)
        ->get();

    $url_aws = $this->getImageStorage();

    $details = Orders::join('store_orders', 'orders.cart_id', '=', 'store_orders.order_cart_id')
        ->where('store_orders.store_approval', 1)
        ->whereNull('orders.deleted_at')
        ->get();

    // Total Orders - SHOW ALL DATA if no filter
    $totalConfirmedOrderQuery = Orders::query()->where('order_status', 'confirmed');

    if ($hasDateFilter) {
        $totalConfirmedOrderQuery->whereBetween('order_date', [$startDate, $endDate]);
    }
    

    $totalConfimedOrder = $totalConfirmedOrderQuery->whereNull('deleted_at')->count();

    // Total Failure Orders - SHOW ALL DATA if no filter
    $totalFailureOrderQuery = Orders::query()->where('order_status', 'failed')->whereNull('deleted_at');
                // dd($totalFailureOrderQuery->toSql());
    // $totalFailureOrderQuery = Orders::whereNotIn('payment_status', ['Success', 'pending', 'paid', 'Refunded']);


    if ($hasDateFilter) {
        $totalFailureOrderQuery->whereBetween('order_date', [$startDate, $endDate]);
    }

                
    $totalFailureOrder = $totalFailureOrderQuery->count();
// dd($totalFailureOrder, $totalFailureOrderQuery->toSql(), $totalFailureOrderQuery->getBindings());
    return view('admin.home', compact(
        'totalFailureOrder', 
        'totalConfimedOrder', 
        'title', 
        'admin', 
        'logo', 
        'total_earnings', 
        'store_earnings', 
        'admin_earnings', 
        'today_earnings', 
        'last_week', 
        'difference', 
        'this_week', 
        'diff_ord', 
        'last_week_ord', 
        'this_week_ord', 
        'last_week_can', 
        'this_week_can', 
        'diff_can', 
        'diff_pen', 
        'last_week_pen', 
        'this_week_pen', 
        'diff_usr', 
        'last_week_usr', 
        'this_week_usr', 
        'topselling', 
        'ongoin', 
        'url_aws', 
        'to', 
        'next_date', 
        'details'
    ));
}

    private function applyOrderFilters($query, $statusFilter = null, $startDate = null, $endDate = null)
    {
        // Status filter
        if ($statusFilter) {
            switch ($statusFilter) {
                case 'completed':
                    $query->where('order_status', 'Completed');
                    break;

                case 'pending':
                    $query->where('order_status', 'pending');
                    break;

                case 'cancelled':
                    $query->where('order_status', 'Cancelled');
                    break;

                case 'new':
                    $query->whereIn('order_status', ['pending', 'Confirmed']);
                    break;
            }
        }

        // Date filter
        if ($startDate && $endDate) {
            $query->whereBetween('order_date', [$startDate, $endDate]);
        }

        return $query;
    }

}
