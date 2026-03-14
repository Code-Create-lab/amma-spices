<?php

namespace App\Http\Controllers\Admin;

use App\Models\Orders;
use App\Traits\ImageStoragePicker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Exports\SalesExport;
use App\Exports\UserSalesExport;
use App\Exports\StockReportExport;
use App\Models\Users;
use Maatwebsite\Excel\Facades\Excel;

class SalesreportController extends Controller
{
    use ImageStoragePicker;

 public function sales_today(Request $request)
{
    $title = "Order section (All Orders)";
    $admin_email = Auth::guard('admin')->user()->email;
    $admin = DB::table('admin')
        ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
        ->where('admin.email', $admin_email)
        ->first();
    $logo = DB::table('tbl_web_setting')
        ->where('set_id', '1')
        ->first();
    
    // Show all orders instead of just today
    $orders = Orders::with(['user', 'address', 'orderItems'])
        ->where('payment_method', '!=', NULL)
        ->where('orders.order_status', '!=', 'cancelled')
        ->orderBy('order_date', 'desc')
        ->paginate(10);
        
    $url_aws = $this->getImageStorage();
    
    return view('admin.salesreport.todaysales', [
        'title' => $title,
        'logo' => $logo,
        'orders' => $orders,
        'admin' => $admin,
        'admin_email' => $admin_email,
        'url_aws' => $url_aws
    ]);
}

public function orders(Request $request)
{
    $date = $request->sel_date;
    $payment_method = $request->payment_method;
    $to_date = $request->to_date;
    
    // Handle empty dates - set defaults or return early
    if (!$date || !$to_date) {
        // Set default dates or redirect back with error
        $date = $date ?: date('Y-m-d');
        $to_date = $to_date ?: date('Y-m-d');
    }
    
    $next_date = date('Y-m-d', strtotime($date));
    $next_date2 = date('Y-m-d', strtotime($to_date));
    $title = $payment_method . " orders of " . $next_date . " - " . $next_date2;
    
    $admin_email = Auth::guard('admin')->user()->email;
    $admin = DB::table('admin')
        ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
        ->where('admin.email', $admin_email)
        ->first();
    $logo = DB::table('tbl_web_setting')->first();
    $url_aws = $this->getImageStorage();
    
    // Build base query
    $query = Orders::with(['user', 'address'])
        ->where('order_date', '>=', $next_date)
        ->where('order_date', '<=', $next_date2)
        ->where('order_status', '!=', 'cancelled')
        ->where('payment_status', '!=', "failed")
        ->orderBy('order_date', 'desc');
    // dd($payment_method, $query->get());
    // Apply payment method filter
    if ($payment_method == "COD") {
        $query->where('payment_method', $payment_method);
    } elseif ($payment_method == "wallet") {
        $query->where('payment_method', $payment_method);
    } elseif ($payment_method == "online"|| $payment_method == "Online") {
        $query->where('payment_method', '!=', 'COD')
              ->where('payment_method', '!=', 'wallet')
              ->where('payment_method', '!=', NULL)
              ->where('payment_method', $payment_method);
    } else {
        // For "all" orders, add orderItems relation
        $query->with(['orderItems'])->orderBy('order_date', 'desc');
    }
    
    $orders = $query->paginate(10);
    
    // **IMPORTANT: Append request parameters to pagination links**
    $orders->appends($request->all());
    
    return view('admin.salesreport.datewise', [
        'title' => $title,
        'logo' => $logo,
        'orders' => $orders,
        'admin' => $admin,
        'admin_email' => $admin_email,
        'url_aws' => $url_aws,
        // Pass these back to the view for form values
        'sel_date' => $date,
        'to_date' => $to_date,
        'payment_method' => $payment_method
    ]);
}
    public function user_sales(Request $request)
    {
        $title = "Order section (Today)";
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $date = date('Y-m-d');

        $user = Users::get();

        $ord = NULL;
        foreach ($user as $u) {
            $ord[] = $u;
            $order = Orders::join('address', 'orders.address_id', '=', 'address.address_id')
                ->select('address.*', 'orders.*', DB::raw('SUM(total_price) as amount'))
                ->where('orders.user_id', $u->id)
                ->where('orders.payment_method', '!=', NULL)
                ->where('orders.order_status', '!=', 'cancelled')
                ->selectRaw('count(orders.order_id) as cnt')
                ->selectRaw('max(orders.total_price) as highest')
                ->selectRaw('min(orders.total_price) as lowest')
                ->first();
            $u->{'cnt'} = $order->cnt;
            $u->{'highest'} = $order->highest;
            $u->{'lowest'} = $order->lowest;
            $u->{'city'} = $order->city;
            $u->{'state'} = $order->state;
            $u->{'house_no'} = $order->house_no;
            $u->{'society'} = $order->society;
            $u->{'landmark'} = $order->landmark;
            $u->{'pincode'} = $order->pincode;
            $u->{'amount'} = $order->amount;
        }
        $details = DB::table('orders')
            ->join('store_orders', 'orders.cart_id', '=', 'store_orders.order_cart_id')
            ->where('store_orders.store_approval', 1)
            ->get();
        $url_aws = $this->getImageStorage();

        return view('admin.salesreport.usersales', [
            'title' => $title,
            'logo' => $logo,
            'user' => $user,
            'admin' => $admin,
            'admin_email' => $admin_email,
            'details' => $details,
            'url_aws' => $url_aws
        ]);
    }
    public function user_orders(Request $request)
    {
        $date = $request->sel_date;
        $to_date = $request->to_date;
        $next_date = date('Y-m-d', strtotime($date));
        $next_date2 = date('Y-m-d', strtotime($to_date));
        $title = "orders of " . $next_date . " - " . $next_date2;
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->first();
        $url_aws = $this->getImageStorage();

        $user = DB::table('users')
            ->get();

        $ord = NULL;
        foreach ($user as $u) {
            $ord[] = $u;
            $order = DB::table('orders')
                ->join('address', 'orders.address_id', '=', 'address.address_id')
                ->select('address.*', 'orders.*', DB::raw('SUM(total_price) as amount'))
                ->where('orders.user_id', $u->id)
                ->where('orders.payment_method', '!=', NULL)
                ->where('orders.order_status', '!=', 'cancelled')
                ->where('orders.delivery_date', '>=', $next_date)
                ->where('orders.delivery_date', '<=', $next_date2)
                ->selectRaw('count(orders.order_id) as cnt')
                ->selectRaw('max(orders.total_price) as highest')
                ->selectRaw('min(orders.total_price) as lowest')
                ->first();
            $u->{'cnt'} = $order->cnt;
            $u->{'highest'} = $order->highest;
            $u->{'lowest'} = $order->lowest;
            $u->{'city'} = $order->city;
            $u->{'state'} = $order->state;
            $u->{'house_no'} = $order->house_no;
            $u->{'society'} = $order->society;
            $u->{'landmark'} = $order->landmark;
            $u->{'pincode'} = $order->pincode;
            $u->{'amount'} = $order->amount;

        }
        $details = DB::table('orders')
            ->join('store_orders', 'orders.cart_id', '=', 'store_orders.order_cart_id')
            ->where('store_orders.store_approval', 1)
            ->get();

        return view('admin.salesreport.userdatewise', [
            'title' => $title,
            'logo' => $logo,
            'user' => $user,
            'admin' => $admin,
            'admin_email' => $admin_email,
            'details' => $details,
            'url_aws' => $url_aws
        ]);
    }
    public function category_sales(Request $request)
    {
        $title = "Order section (Today)";
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $date = date('Y-m-d');

        $user = DB::table('users')
            ->get();

        $category = DB::table('categories')
            ->where('is_deleted', 0)
            ->get();

        $ord = NULL;
        foreach ($category as $u) {
            $ord[] = $u;
            $order = DB::table('orders')
                ->join('store_orders', 'orders.cart_id', '=', 'store_orders.order_cart_id')
                ->join('variations', 'store_orders.varient_id', '=', 'variations.id')
                ->join('products', 'products.product_id', '=', 'variations.product_id')
                ->where('products.cat_id', $u->cat_id)
                ->where('products.is_deleted', 0)
                ->where('orders.payment_method', '!=', NULL)
                ->where('orders.order_status', '!=', 'cancelled')
                ->selectRaw('count(orders.order_id) as cnt')
                ->selectRaw('max(orders.total_price) as highest')
                ->selectRaw('min(orders.total_price) as lowest')
                ->first();

            $u->{'name'} = $order->name;
            $u->{'cnt'} = $order->cnt;
            $u->{'highest'} = $order->highest;
            $u->{'lowest'} = $order->lowest;
            $u->{'amount'} = $order->amount;
        }
        $details = DB::table('orders')
            ->join('store_orders', 'orders.cart_id', '=', 'store_orders.order_cart_id')
            ->where('store_orders.store_approval', 1)
            ->get();
        $url_aws = $this->getImageStorage();

        return view('admin.salesreport.categorysales', [
            'title' => $title,
            'logo' => $logo,
            'user' => $user,
            'admin' => $admin,
            'admin_email' => $admin_email,
            'details' => $details,
            'url_aws' => $url_aws,
            'category' => $category
        ]);
    }

    public function category_orders(Request $request)
    {
        $date = $request->sel_date;
        // $payment_method = $request->payment_method;
        $to_date = $request->to_date;
        $next_date = date('Y-m-d', strtotime($date));
        $next_date2 = date('Y-m-d', strtotime($to_date));
        $title = "orders of " . $next_date . " - " . $next_date2;
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->first();
        $url_aws = $this->getImageStorage();

        $user = DB::table('users')
            ->get();

        $category = DB::table('categories')
            ->where('is_deleted', 0)
            ->get();

        $ord = NULL;
        foreach ($category as $u) {
            $ord[] = $u;

            $order = DB::table('orders')
                ->join('store_orders', 'orders.cart_id', '=', 'store_orders.order_cart_id')
                ->join('variations', 'store_orders.varient_id', '=', 'variations.id')
                ->join('products', 'products.product_id', '=', 'variations.product_id')
                ->select('orders.*', DB::raw('SUM(total_price) as amount'))
                ->where('products.cat_id', $u->cat_id)
                ->where('products.is_deleted', 0)
                ->where('orders.payment_method', '!=', NULL)
                ->where('orders.order_status', '!=', 'cancelled')
                ->where('orders.delivery_date', '>=', $next_date)
                ->where('orders.delivery_date', '<=', $next_date2)
                ->selectRaw('count(orders.order_id) as cnt')
                ->selectRaw('max(orders.total_price) as highest')
                ->selectRaw('min(orders.total_price) as lowest')
                ->first();

            $u->{'cnt'} = $order->cnt;
            $u->{'highest'} = $order->highest;
            $u->{'lowest'} = $order->lowest;
            $u->{'amount'} = $order->amount;
        }

        $details = DB::table('orders')
            ->join('store_orders', 'orders.cart_id', '=', 'store_orders.order_cart_id')
            ->where('store_orders.store_approval', 1)
            ->get();

        return view('admin.salesreport.categorydatewise', [
            'title' => $title,
            'logo' => $logo,
            'user' => $user,
            'admin' => $admin,
            'admin_email' => $admin_email,
            'details' => $details,
            'url_aws' => $url_aws,
            'category' => $category
        ]);
    }

    public function exportUserSales()
    {
        $fileName = 'user_sales_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new UserSalesExport(), $fileName);
    }

    public function exportDatewiseOrders(Request $request)
    {
        $startDate = $request->sel_date;
        $endDate = $request->to_date;
        $paymentMethod = $request->payment_method;

        $fileName = 'sales_report_' . $startDate . '_to_' . $endDate . '_' . date('His') . '.xlsx';

        return Excel::download(new SalesExport($startDate, $endDate, $paymentMethod), $fileName);
    }

    public function stock_report(Request $request)
    {
        $title = "Stock Report";
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $url_aws = $this->getImageStorage();

        $categories = DB::table('categories')
            ->where('is_deleted', 0)
            ->get();

        $query = DB::table('variations')
            ->join('products', 'variations.product_id', '=', 'products.product_id')
            ->join('categories', 'products.cat_id', '=', 'categories.cat_id')
            ->leftJoin(DB::raw('(SELECT varient_id, SUM(qty) as total_ordered
                FROM store_orders
                INNER JOIN orders ON store_orders.order_cart_id = orders.cart_id
                WHERE orders.order_status != "cancelled"
                GROUP BY varient_id) as ordered'), 'variations.id', '=', 'ordered.varient_id')
            ->where('products.is_deleted', 0)
            ->where('variations.is_deleted', 0)
            ->select(
                'variations.id',
                'products.product_name',
                'variations.uuid',
                'categories.title as cat_name',
                'variations.stock',
                DB::raw('COALESCE(ordered.total_ordered, 0) as total_ordered')
            );

        if ($request->category) {
            $query->where('products.cat_id', $request->category);
        }

        $stockData = $query->get();

        return view('admin.salesreport.stockreport', [
            'title' => $title,
            'logo' => $logo,
            'admin' => $admin,
            'admin_email' => $admin_email,
            'url_aws' => $url_aws,
            'categories' => $categories,
            'stockData' => $stockData,
        ]);
    }

    public function exportStockReport(Request $request)
    {
        $categoryId = $request->category;
        $fileName = 'stock_report_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new StockReportExport($categoryId), $fileName);
    }
}
