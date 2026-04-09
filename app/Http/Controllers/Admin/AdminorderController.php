<?php

namespace App\Http\Controllers\Admin;

use App\Events\CompleteOrderMailEvent;
use App\Models\Orders;
use App\Traits\ImageStoragePicker;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\CancelOrder;
use App\Mail\ConfirmOrderWithShipment;;

use App\Mail\PendingRefundNotification;
use App\Models\CancelledOrder;
use App\Models\OrderPayment;
use App\Models\OrderPayments;
use App\Models\OrderRefund;
use App\Models\Payment;
use App\Models\Shipment;
// use App\Models\Order;
use App\Services\PayGlocalService;
use App\Services\ShiprocketService;
use DB;
use Illuminate\Support\Facades\Redirect;
use Session;
use Carbon\Carbon;
use App\Traits\SendInapp;
use App\Traits\SendMail;
use App\Traits\SendSms;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Mail;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

use App\Services\SmsService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;



class AdminorderController extends Controller
{
    use SendMail;
    use SendSms;
    use SendInapp;
    use ImageStoragePicker;

    protected SmsService $smsService;
    protected WhatsAppService $whatsAppService;


    public function __construct(SmsService $smsService, WhatsAppService $whatsAppService)
    {
        $this->smsService = $smsService;
        $this->whatsAppService = $whatsAppService;
    }

    public function admin_com_orders(Request $request)
    {
        $title = trans('keywords.Completed Orders');
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $orders = Orders::with(['orderItems.variation.variation_attributes.attribute.attribute', 'address', 'user'])
            ->where('order_status', 'completed')
            ->orWhere('order_status', 'Completed')
            ->orderBy('order_date', 'DESC')
            ->paginate(150);

        $url_aws = $this->getImageStorage();

        return view('admin.all_orders.com_orders', compact('title', 'logo', 'orders', 'admin', 'url_aws'));
    }


    public function generate_invoice($orderId)
    {
        // Eager-load relationships used in the Blade
        $order = Orders::with([
            'address',
            'orderItems.variation.product.tax',
            'orderItems.variation.variation_attributes.attribute.attribute',
        ])->findOrFail($orderId);

        /**
         * 1) INVOICE LOGO -> BASE64
         *    Change the path as per your project
         */
        $logoBase64 = null;
        $logoMime   = null;

        // Example: logo stored in public/images/logo.png
        $logoPath = public_path('images/logo.png');   // <- update this

        if (file_exists($logoPath)) {
            $logoMime   = mime_content_type($logoPath);
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }

        /**
         * 2) PRODUCT / VARIATION IMAGES -> BASE64
         *    Adjust the image source field as per your DB
         */
        foreach ($order->orderItems as $item) {
            $item->varient_image_base64 = null;
            $item->image_mime_type      = null;

            $imagePath = null;

            // Example 1: image path is stored on variation table (e.g. "products/variants/xxx.jpg")
            if (!empty($item->variation?->image)) {
                $imagePath = Storage::disk('public')->path($item->variation->image);
            }

            // Example 2 (optional): fallback to product main image if you have it
            // if (!$imagePath && !empty($item->variation?->product?->image)) {
            //     $imagePath = Storage::disk('public')->path($item->variation->product->image);
            // }

            if ($imagePath && file_exists($imagePath)) {
                $item->image_mime_type      = mime_content_type($imagePath);
                $item->varient_image_base64 = base64_encode(file_get_contents($imagePath));
            }
        }

        /**
         * 3) Generate PDF with base64 images
         */
        $pdf = Pdf::loadView('frontend.order.invoice', [
            'order'      => $order,
            'logoBase64' => $logoBase64,
            'logoMime'   => $logoMime,
        ]);

        return $pdf->download(' ORD-' . now()->format('Y') . '-' . $order->cart_id . '.pdf');
    }

    public function admin_can_orders(Request $request)
    {
        $title = trans('keywords.Cancelled Orders');
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $orders = Orders::with(['orderItems.variation.variation_attributes.attribute.attribute', 'address', 'user'])
            ->where('order_status', 'cancelled')
            ->orWhere('order_status', 'Cancelled')
            ->orderBy('order_date', 'DESC')
            ->paginate(150);

        $url_aws = $this->getImageStorage();

        return view('admin.all_orders.cancelled', compact('title', 'logo', 'orders', 'admin', 'url_aws'));
    }

    public function admin_pen_orders(Request $request)
    {
        $title = trans('keywords.Pending Orders');
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $orders = Orders::with(['orderItems.variation.variation_attributes.attribute.attribute', 'address', 'user'])
            ->where('order_status', 'pending')
            ->orWhere('order_status', 'Pending')
            ->orderBy('order_date', 'DESC')
            ->paginate(150);

        $url_aws = $this->getImageStorage();

        return view('admin.all_orders.pending', compact('title', 'logo', 'orders', 'admin', "url_aws"));
    }

    public function admin_store_orders(Request $request)
    {
        $title = "Store Order section";
        $id = $request->id;
        $store = DB::table('store')
            ->where('id', $id)
            ->first();
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $ord = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('store', 'orders.store_id', '=', 'store.id')
            ->join('address', 'orders.address_id', '=', 'address.address_id')
            ->where('orders.store_id', $store->id)
            ->orderBy('orders.order_id', 'ASC')
            ->where('order_status', '!=', 'completed')
            ->paginate(150);

        $details = DB::table('orders')
            ->join('store_orders', 'orders.cart_id', '=', 'store_orders.order_cart_id')
            ->where('orders.store_id', $id)
            ->where('store_orders.store_approval', 1)
            ->get();

        $url_aws = $this->getImageStorage();

        return view('admin.store.orders', compact('title', 'logo', 'ord', 'store', 'details', 'admin', 'url_aws'));
    }

    public function admin_dboy_orders(Request $request)
    {
        $title = "Delivery Boy Order section";
        $id = $request->id;
        $dboy = DB::table('delivery_boy')
            ->where('dboy_id', $id)
            ->first();

        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $date = date('Y-m-d');
        $nearbydboy = DB::table('store_delivery_boy')
            ->leftJoin('orders', 'store_delivery_boy.ad_dboy_id', '=', 'orders.dboy_id')
            ->select("store_delivery_boy.store_id", "store_delivery_boy.boy_name", "store_delivery_boy.dboy_id", "store_delivery_boy.lat", "store_delivery_boy.lng", "store_delivery_boy.boy_city", DB::raw("Count(orders.order_id)as count"), DB::raw("6371 * acos(cos(radians(" . $dboy->lat . "))
                * cos(radians(store_delivery_boy.lat))
                * cos(radians(store_delivery_boy.lng) - radians(" . $dboy->lng . "))
                + sin(radians(" . $dboy->lat . "))
                * sin(radians(store_delivery_boy.lat))) AS distance"))
            ->groupBy("store_delivery_boy.store_id", "store_delivery_boy.boy_name", "store_delivery_boy.dboy_id", "store_delivery_boy.lat", "store_delivery_boy.lng", "store_delivery_boy.boy_city")
            ->where('store_delivery_boy.boy_city', $dboy->boy_city)
            ->where('store_delivery_boy.ad_dboy_id', '!=', $dboy->dboy_id)
            ->orderBy('count')
            ->orderBy('distance')
            ->get();

        $ord = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('store', 'orders.store_id', '=', 'store.id')
            ->join('address', 'orders.address_id', '=', 'address.address_id')
            ->where('orders.dboy_id', $dboy->dboy_id)
            ->orderBy('orders.delivery_date', 'ASC')
            ->where('order_status', '!=', 'completed')
            ->paginate(150);

        $details = DB::table('orders')
            ->join('store_orders', 'orders.cart_id', '=', 'store_orders.order_cart_id')
            ->where('orders.dboy_id', $id)
            ->where('store_orders.store_approval', 1)
            ->get();
        $url_aws = $this->getImageStorage();

        return view('admin.d_boy.orders', compact('title', 'logo', 'ord', 'dboy', 'details', 'admin', 'nearbydboy', 'url_aws'));
    }

    public function store_cancelled(Request $request)
    {
        $title = "Store Cancelled Orders";
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $ord = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('address', 'orders.address_id', '=', 'address.address_id')
            ->orderBy('orders.delivery_date', 'ASC')
            ->where('order_status', '!=', 'completed')
            ->where('order_status', '!=', 'cancelled')
            ->where('payment_method', '!=', NULL)
            ->where('store_id', 0)
            ->paginate(150);

        $nearbystores = DB::table('store')
            ->join('service_area', 'store.id', '=', 'service_area.store_id')
            ->select('store.id', 'store.store_name')
            ->groupBy('store.id', 'store.store_name')
            ->get();


        $details = DB::table('orders')
            ->join('store_orders', 'orders.cart_id', '=', 'store_orders.order_cart_id')
            ->where('store_orders.store_approval', 1)
            ->get();

        $url_aws = $this->getImageStorage();

        return view('admin.store.cancel_orders', compact('title', 'logo', 'ord', 'details', 'admin', 'nearbystores', "url_aws"));
    }

    public function assignstore(Request $request)
    {
        $title = "Store Cancelled Orders";
        $cart_id = $request->id;
        $store = $request->store;
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $ord = DB::table('orders')
            ->where('cart_id', $cart_id)
            ->update(['store_id' => $store, 'cancel_by_store' => 0]);

        return redirect()->back()->withSuccess(trans('keywords.Assigned to Store Successfully'));
    }


    public function assigndboy(Request $request)
    {
        $cart_id = $request->id;
        $d_boy = $request->d_boy;
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $ord = DB::table('orders')
            ->where('cart_id', $cart_id)
            ->update(['dboy_id' => $d_boy]);


        return redirect()->back()->withSuccess(trans('keywords.Assigned to Another Delivery Boy Successfully'));
    }

    public function rejectorder(Request $request)
    {
        $cart_id = $request->id;
        $ord = DB::table('orders')
            ->where('cart_id', $cart_id)
            ->first();
        $date_of_recharge = carbon::now();
        $total_price = $ord->rem_price;
        $user = DB::table('users')
            ->where('id', $ord->user_id)
            ->first();
        $user_id = $ord->user_id;
        $wall = $user->wallet;
        $bywallet = $ord->paid_by_wallet;
        if ($ord->payment_method != 'COD' && $ord->payment_method != 'cod' && $ord->payment_method != 'Cod') {

            $newwallet = $wall + $total_price + $bywallet;
            $update = DB::table('users')
                ->where('id', $ord->user_id)
                ->update(['wallet' => $newwallet]);

            $his = DB::table('wallet_recharge_history')
                ->insert([
                    'user_id' => $user_id,
                    'amount' => $total_price + $bywallet,
                    'date_of_recharge' => $date_of_recharge,
                    'recharge_status' => 'refunded',
                    'payment_gateway' => "cart_id ( " . $cart_id . " )"
                ]);
        } else {

            $newwallet = $wall + $bywallet;
            $update = DB::table('users')
                ->where('id', $ord->user_id)
                ->update(['wallet' => $newwallet]);
            if ($bywallet > 0) {
                $his = DB::table('wallet_recharge_history')
                    ->insert([
                        'user_id' => $user_id,
                        'amount' => $bywallet,
                        'date_of_recharge' => $date_of_recharge,
                        'recharge_status' => 'refunded',
                        'payment_gateway' => "cart_id ( " . $cart_id . " )"
                    ]);
            }
        }

        $cause = $request->cause;

        $checknotificationby = DB::table('notificationby')
            ->where('user_id', $user->id)
            ->first();
        if ($checknotificationby->sms == 1) {
            $sendmsg = $this->sendrejectmsg($cause, $user, $cart_id);
        }
        if ($checknotificationby->email == 1) {
            $sendmail = $this->sendrejectmail($cause, $user, $cart_id);
        }
        if ($checknotificationby->app == 1) {
            //////send notification to user//////////
            $sendinapp = $this->sendrejectnotification($cause, $user, $cart_id, $user_id);
        }

        $ordee = DB::table('orders')
            ->where('cart_id', $cart_id)
            ->update([
                'cancelling_reason' => "Cancelled by Admin due to the following reason: " . $cause,
                'order_status' => "Cancelled"
            ]);

        $cart_status = DB::table('cart_status')
            ->where('cart_id', $cart_id)
            ->first();
        if ($cart_status) {
            $cart_status = DB::table('cart_status')
                ->where('cart_id', $cart_id)
                ->update(['cancelled' => Carbon::now()]);
        }

        $v = DB::table('store_orders')
            ->where('order_cart_id', $cart_id)
            ->get();
        foreach ($v as $vs) {
            $qt = $vs->qty;
            $pr = DB::table('store_products')
                ->join('product_varient', 'store_products.varient_id', '=', 'product_varient.varient_id')
                ->join('product', 'product_varient.product_id', '=', 'product.product_id')
                ->where('store_products.varient_id', $vs->varient_id)
                ->where('store_products.store_id', $ord->store_id)
                ->first();

            $stoc = DB::table('store_products')
                ->where('varient_id', $vs->varient_id)
                ->where('store_id', $ord->store_id)
                ->first();
            if ($stoc) {
                $newstock = $stoc->stock + $qt;
                $st = DB::table('store_products')
                    ->where('varient_id', $vs->varient_id)
                    ->where('store_id', $ord->store_id)
                    ->update(['stock' => $newstock]);
            }
        }

        return redirect()->back()->withSuccess(trans('keywords.Order Rejected Successfully'));
    }

    public function missed_orders(Request $request)
    {
        $title = trans('keywords.Missed Orders');
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $today = date('Y-m-d');
        $day = 1;
        $next_date = date('Y-m-d', strtotime($today . ' - ' . $day . ' days'));

        $orders = Orders::with(['orderItems.variation.variation_attributes.attribute.attribute', 'address', 'user'])
            ->where('order_status', 'missed')
            ->orWhere('order_status', 'Missed')
            ->orderBy('order_date', 'DESC')
            ->paginate(150);

        $delivery = DB::table('store_delivery_boy')
            ->get();
        $url_aws = $this->getImageStorage();

        return view('admin.all_orders.missed', compact('title', 'logo', 'orders', 'admin', 'delivery', "url_aws"));
    }

    public function admin_on_orders(Request $request)
    {
        $title = trans('keywords.Confirmed Orders');
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $orders = Orders::with(['orderItems.variation.variation_attributes.attribute.attribute', 'address', 'user'])
            ->where('order_status', 'confirmed')
            ->orWhere('order_status', 'Confirmed')
            ->orderBy('order_date', 'DESC')
            ->paginate(150);

        $url_aws = $this->getImageStorage();

        return view('admin.all_orders.ongoing', compact('title', 'logo', 'orders', 'admin', "url_aws"));
    }

    public function admin_all_orders(Request $request)
    {
        if ($request->ajax()) {

            $orders = Orders::with(['user', 'shipment', 'address'])
                ->select('orders.*')
                ->orderBy('order_id', 'DESC');

            /* ================= APPLY FILTERS ================= */

            // Order Status
            if ($request->filled('status')) {
                $orders->whereRaw('LOWER(order_status) = ?', [
                    strtolower($request->status)
                ]);
            } else {
                // By default, exclude failed orders (show only when filter applied)
                $orders->where(function ($q) {
                    $q->whereNull('order_status')
                        ->orWhereRaw('LOWER(order_status) != ?', ['failed']);
                });
            }

            // Payment Method
            if ($request->filled('payment_method')) {
                $orders->whereRaw('LOWER(payment_method) = ?', [
                    strtolower($request->payment_method)
                ]);
            }

            // Payment Status
            if ($request->filled('payment_status')) {

                if ($request->payment_status === 'paid') {

                    $orders->whereRaw('LOWER(payment_status) = ?', ['paid']);
                } elseif ($request->payment_status === 'unpaid') {

                    $orders->where(function ($q) {
                        $q->whereNull('payment_status')
                            ->orWhere('payment_status', '')
                            ->orWhereRaw('LOWER(payment_status) != ?', ['paid']);
                    });
                }
            }

            // Date Range
            if ($request->filled('from_date')) {
                $orders->whereDate('order_date', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $orders->whereDate('order_date', '<=', $request->to_date);
            }

            /* ================= DATATABLE ================= */

            return DataTables::of($orders)
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search');
                    if (isset($search) && isset($search['value'])) {
                        $value = $search['value'];
                        $query->where(function ($q) use ($value) {
                            $q->where('orders.cart_id', 'like', "%{$value}%")
                                ->orWhereHas('user', function ($q) use ($value) {
                                    $q->where('name', 'like', "%{$value}%")
                                        ->orWhere('email', 'like', "%{$value}%");
                                })
                                ->orWhereHas('address', function ($q) use ($value) {
                                    $q->where('receiver_name', 'like', "%{$value}%")
                                        ->orWhere('receiver_phone', 'like', "%{$value}%")
                                        ->orWhere('receiver_email', 'like', "%{$value}%");
                                })
                                ->orWhere('orders.order_date', 'like', "%{$value}%")
                                ->orWhere('orders.total_price', 'like', "%{$value}%");
                        });
                    }
                })
                ->addIndexColumn()

                ->addColumn('cart_id_display', function ($row) {
                    $html = $row->cart_id;
                    $isView = $row->is_view ?? 0;
                    if ($isView == 0) {
                        $html .= '<br><span class="badge text-danger bg-danger-subtle animated-badge mt-2" style="animation: pulse 1.5s infinite;">New</span>';
                    }
                    return $html;
                })

                ->addColumn('user_info', function ($row) {

                    // if ($row->user && $row->user->name) {
                    //     return $row->user->name
                    //         . '<br><small>(' . $row->user->user_phone . ')</small>';
                    // }

                    if ($row->address) {
                        return ($row->address->receiver_name ?? $row->user?->name)
                            . '<br><small>('
                            . ($row->address->receiver_phone ?? $row->user?->user_phone)
                            . ' | '
                            . ($row->address->receiver_email ?? $row->user?->email)
                            . ')</small>';
                    }

                    return '-';
                })

                ->addColumn('status_badge', function ($row) {
                    $status = strtolower($row->order_status ?? '');

                    $map = [
                        '' => ['NOT PLACED', 'warning'],
                        'pending' => ['Pending', 'warning'],
                        'confirmed' => ['Confirmed', 'primary'],
                        'out_for_delivery' => ['Out For Delivery', 'info'],
                        'cancelled' => ['Cancelled', 'danger'],
                        'failed' => ['Failed', 'danger'],
                        'completed' => ['Completed', 'success'],
                    ];

                    [$text, $class] = $map[$status] ?? [ucfirst($status), 'secondary'];

                    return "<span class='badge badge-$class'>$text</span>";
                })

                ->addColumn('delivery_date', function ($row) {
                    return $row->delivery_date ?? '-';
                })

                ->addColumn('shipment_status', function ($row) {

                    $status = strtoupper($row->shipment?->status ?? 'PENDING');

                    $statusMap = [
                        'NEW' => 'secondary',
                        'MANIFEST_GENERATED' => 'secondary',

                        'PICKUP_GENERATED' => 'info',
                        'PICKUP_SCHEDULED' => 'info',

                        'PICKED_UP' => 'primary',
                        'SHIPPED' => 'primary',
                        'IN_TRANSIT' => 'primary',

                        'OUT_FOR_DELIVERY' => 'warning',
                        'DELIVERED' => 'success',

                        'RTO_INITIATED' => 'danger',
                        'RTO_DELIVERED' => 'dark',

                        'CANCELLED' => 'danger',
                        'CANCELLED_BEFORE_PICKUP' => 'secondary',

                        'FAILED' => 'danger',
                        'PENDING' => 'warning',
                    ];

                    $class = $statusMap[$status] ?? 'secondary';

                    return "<span class='badge badge-{$class}'>{$status}</span>";
                })


                ->addColumn('payment_method_badge', function ($row) {
                    $isCod = strtolower($row->payment_method) === 'cod';
                    $class = $isCod ? 'danger' : 'success';
                    return "<span class='badge badge-$class'>{$row->payment_method}</span>";
                })

                ->addColumn('action', function ($row) {

                    $html = '<div class="d-flex flex-wrap gap-1">';

                    // DETAILS BUTTON
                    $html .= '<button
                            class="btn btn-secondary btn-sm mr-1 mb-1"
                            data-toggle="modal"
                            data-target="#orderDetailsModal' . $row->cart_id . '">
                            Details
                          </button>';

                    // PENDING → CONFIRM + CANCEL
                    if (strtolower($row->order_status) === 'pending') {
                        if (strtolower($row->payment_status) === 'paid') {
                            $html .= '<button
                                class="btn btn-primary btn-sm mr-1 mb-1 open-confirm-modal" id="confirmOrderBtn"
                                data-order-id="' . $row->order_id . '">
                                Confirm Order
                              </button>';
                        }


                        $html .= '<button
                                class="btn btn-danger btn-sm mr-1 mb-1 btn-cancel-order"
                                data-order-id="' . $row->order_id . '">
                                Cancel Order
                              </button>';
                    }

                    // CONFIRMED → COMPLETE + CANCEL + TRACK (if shipment exists)
                    if (strtolower($row->order_status) === 'confirmed') {

                        if (strtolower($row->shipement?->status) === 'delivered') {


                            $html .= '<button
                                class="btn btn-success btn-sm mr-1 mb-1 btn-complete-order"
                                data-order-id="' . $row->order_id . '">
                                Complete Order
                              </button>';
                        }
                        if (strtolower($row->shipement?->status) != 'shipped') {
                            $html .= '<button
                                class="btn btn-danger btn-sm mr-1 mb-1 btn-cancel-order"
                                data-order-id="' . $row->order_id . '">
                                Cancel Order
                              </button>';
                        }
                        // Track shipment button if shipment exists
                        if ($row->shipment && $row->shipment->status == 'success') {
                            $html .= '<a href="' . route('admin.orders.tracking', $row->order_id) . '"
                                    target="_blank" class="btn btn-info btn-sm mr-1 mb-1">
                                    Track
                                  </a>';
                        }
                    }

                    // OUT FOR DELIVERY → COMPLETE + TRACK
                    if (strtolower($row->order_status) === 'out_for_delivery' || strtolower($row->order_status) === 'delivered') {

                        $html .= '<button
                                class="btn btn-success btn-sm mr-1 mb-1 btn-complete-order"
                                data-order-id="' . $row->order_id . '">
                                Complete Order
                              </button>';

                        if ($row->shipment && $row->shipment->status == 'success') {
                            $html .= '<a href="' . route('admin.orders.tracking', $row->order_id) . '"
                                    target="_blank" class="btn btn-info btn-sm mr-1 mb-1">
                                    Track
                                  </a>';
                        }
                    }

                    // COMPLETED → INVOICE
                    if (strtolower($row->order_status) === 'completed') {

                        $html .= '<a href="' . route('generate_invoice', $row->order_id) . '"
                                class="btn btn-success btn-sm mr-1 mb-1">
                                Generate Invoice
                              </a>';
                    }
                    if ($row->shipment && !empty($row->shipment->awb)) {

                        $html .= '<button
                        class="btn btn-info btn-sm mr-1 mb-1 btn-track-order"
                        data-awb="' . $row->shipment->awb . '">
                        Track
                    </button>
                     <a href="' . $row->shipment->invoice_url . '"
                        data-awb="' . $row->shipment->invoice_url . '"
                        target="_blank" class="btn btn-info btn-sm mr-1 mb-1">
                       Shiprocket Invoice
                    </a>
                    
                    <a href="' . $row->shipment->label_url . '"
                        data-awb="' . $row->shipment->label_url . '"
                        target="_blank" class="btn btn-info btn-sm mr-1 mb-1">
                       Shiprocket Label
                    </a>
                    ';
                    }

                    // CANCELLED/FAILED → show refund status if applicable
                    if (in_array(strtolower($row->order_status), ['cancelled', 'failed'])) {
                        $paymentStatus = $row->payment_status ?? '';
                        if (in_array($paymentStatus, ['Refund Pending', 'Refund Pending Return'])) {
                            $html .= '<button class="btn btn-warning btn-sm mr-1 mb-1" disabled>Refund Pending</button>';
                        } elseif ($paymentStatus == 'Refund Initiated') {
                            $html .= '<button class="btn btn-info btn-sm mr-1 mb-1" disabled>Refund Initiated</button>';
                        } elseif ($paymentStatus == 'Refunded') {
                            $html .= '<button class="btn btn-success btn-sm mr-1 mb-1" disabled>Refunded</button>';
                        } elseif ($paymentStatus == 'Not Applicable') {
                            $html .= '<button class="btn btn-secondary btn-sm mr-1 mb-1" disabled>No Refund</button>';
                        }
                    }

                    // DELETE BUTTON (always shown)
                    $html .= '<button
                            class="btn btn-outline-danger btn-sm mr-1 mb-1 btn-delete-order"
                            data-order-id="' . $row->order_id . '">
                            <i class="fa fa-trash"></i> Delete
                          </button>';

                    $html .= '</div>';
                    return $html;
                })

                ->rawColumns(['cart_id_display', 'user_info', 'status_badge', 'payment_method_badge', 'shipment_status', 'action'])
                ->make(true);
        }

        /* ================= NORMAL PAGE LOAD ================= */

        $title = __('keywords.All orders');

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', Auth::guard('admin')->user()->email)
            ->first();

        $logo = DB::table('tbl_web_setting')->where('set_id', 1)->first();

        $orders = Orders::with([
            'orderItems.variation.variation_attributes.attribute.attribute',
            'address',
            'user'
        ])->orderBy('order_id', 'DESC')->get();

        return view('admin.all_orders.all_orders', compact(
            'title',
            'admin',
            'logo',
            'orders'
        ));
    }

    /**
     * AJAX: Complete order without redirect
     */
    public function ajaxCompleteOrder(Request $request, $id)
    {
        try {
            $order = Orders::where('order_id', $id)->firstOrFail();

            // Mark as viewed
            $order->is_view = 1;

            if ($order->payment_method == "COD") {
                $order->payment_status = 'paid';
            }
            $order->order_status = 'Completed';
            $order->save();

            // Send completion notifications (optional - can be async)
            try {
                event(new CompleteOrderMailEvent($order));
            } catch (\Exception $e) {
                Log::error('Failed to send completion notification', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order completed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('AJAX Complete Order Error', ['error' => $e->getMessage(), 'order_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX: Cancel order without redirect
     */
    public function ajaxCancelOrder(Request $request, $id)
    {
        try {
            $order = Orders::where('order_id', $id)->firstOrFail();
            $user = auth()->user();

            // Mark as viewed
            $order->is_view = 1;
            $order->save();

            if ($order->payment_method == "COD") {
                $this->handleCODCancellation($order, $request, $user);
            } else {
                $payment = OrderPayments::where('order_id', $order->order_id)->first();
                if (!$payment) {
                    // Still cancel order but mark refund as not applicable
                    $order->update([
                        'order_status' => 'Cancelled',
                        'is_view' => 1
                    ]);
                    return response()->json([
                        'success' => true,
                        'message' => 'Order cancelled (No payment record found)'
                    ]);
                }

                try {
                    $this->handleOnlinePaymentCancellation($order, $request, $user);
                } catch (\Razorpay\Api\Errors\BadRequestError $e) {
                    if (strpos($e->getMessage(), 'does not have enough balance') !== false) {
                        // Mark refund as pending
                        $order->update([
                            'order_status' => 'Cancelled',
                            'payment_status' => 'Refund Pending',
                            'is_view' => 1
                        ]);
                        return response()->json([
                            'success' => true,
                            'message' => 'Order cancelled. Refund pending due to insufficient balance.'
                        ]);
                    }
                    throw $e;
                }

                $this->sendCancellationNotifications($order);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('AJAX Cancel Order Error', ['error' => $e->getMessage(), 'order_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX: Mark order as viewed
     */
    public function ajaxMarkViewed(Request $request, $id)
    {
        try {
            $order = Orders::where('order_id', $id)->firstOrFail();
            $order->is_view = 1;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order marked as viewed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark order as viewed'
            ], 500);
        }
    }

    /**
     * AJAX: Soft delete an order
     */
    public function ajaxDeleteOrder($id)
    {
        try {
            $order = Orders::where('order_id', $id)->firstOrFail();
            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('AJAX Delete Order Error', ['error' => $e->getMessage(), 'order_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX: Confirm order and create shipment without redirect
     */
    public function ajaxConfirmOrder(Request $request, $id)
    {
        if (!$request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request'
            ], 400);
        }

        try {
            $order = Orders::where('order_id', $id)->firstOrFail();

            Log::info('AJAX Order confirmation initiated', [
                'order_id' => $order->order_id,
                'request'  => $request->all(),
            ]);

            if (empty($order->order_id)) {
                Log::error('Order ID missing, shipment not created', ['order' => $order->toArray()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid order.'
                ], 400);
            }

            // Update dimensions
            // $order->update([
            //     'length'  => $request->shipment_length,
            //     'weight'  => $request->weight,
            //     'breadth' => $request->shipment_width,
            //     'height'  => $request->shipment_height,
            // ]);

            Log::info('Order dimensions updated', [
                'order_id' => $order->order_id,
            ]);

            // $courierCompanyId = $request->courier_company_id;
            // $shiprocket = new ShiprocketService();

            // 🔐 Lock to prevent duplicate processing
            // $lock = Cache::lock("shiprocket:order:{$order->order_id}", 30);

            // if (! $lock->get()) {
            //     Log::info('Shipment process already running', ['order_id' => $order->order_id]);
            //     return response()->json([
            //         'success' => true,
            //         'message' => 'Shipment is already being processed. Please refresh shortly.'
            //     ]);
            // }

            // try {
            //     /** STEP 1: CREATE SHIPMENT IF NOT EXISTS */
            //     if (!$order->shipment) {

            //         Log::info('No shipment found. Creating shipment.');

            //         $response = $shiprocket->createOrder($order);
            //         Log::info('Shiprocket createOrder response', $response);

            //         if (empty($response['shipment_id'])) {
            //             Log::error('Shiprocket order creation failed', ['response' => $response]);

            //             return response()->json([
            //                 'success' => false,
            //                 'message' => $response
            //             ], 500);
            //         }

            //         $shipment = Shipment::updateOrCreate(
            //             ['order_id' => $order->order_id],
            //             [
            //                 'shipment_order_id' => $response['order_id'] ?? null,
            //                 'shipment_id'       => $response['shipment_id'],
            //                 'channel_order_id'  => $response['channel_order_id'] ?? null,
            //                 'length'            => $request->shipment_length,
            //                 'width'             => $request->shipment_width,
            //                 'height'            => $request->shipment_height,
            //                 'weight'            => $request->weight,
            //                 'status'            => $response['status'] ?? 'NEW',
            //                 'awb'               => $response['awb_code'] ?? null,
            //                 'response'          => json_encode($response),
            //             ]
            //         );

            //         Log::info('Shipment saved locally', [
            //             'shipment_id' => $shipment->shipment_id
            //         ]);
            //     } else {
            //         $shipment = $order->shipment;
            //         Log::info('Existing shipment found', [
            //             'shipment_id' => $shipment->shipment_id
            //         ]);
            //     }

            //     /** STEP 2: ASSIGN COURIER / AWB */
            //     if (empty($shipment->awb)) {

            //         Log::info('AWB missing. Assigning courier.', [
            //             'shipment_id' => $shipment->shipment_id,
            //             'courier_company_id' => $courierCompanyId
            //         ]);

            //         $courierResponse = $shiprocket->assignCourier(
            //             $shipment->shipment_id,
            //             $courierCompanyId
            //         );

            //         Log::info('Shiprocket assignCourier response', $courierResponse);

            //         $awb = $courierResponse['response']['data']['awb_code'] ?? null;

            //         // ✅ Success path
            //         if ($awb) {
            //             $shipment->awb = $awb;
            //             $shipment->status = !empty($courierResponse['response']['data']['pickup_scheduled_date'])
            //                 ? 'PICKUP SCHEDULED'
            //                 : 'READY TO SHIP';
            //             // $shipment->courier_company_id = $courierResponse['response']['data']['courier_company_id'] ?? null;
            //             $shipment->save();

            //             Log::info('AWB code saved', [
            //                 'shipment_id' => $shipment->shipment_id,
            //                 'awb' => $awb
            //             ]);
            //         }
            //         // ⚠️ Duplicate request — safe to ignore
            //         elseif (
            //             ($courierResponse['status_code'] ?? null) == 500 &&
            //             ($courierResponse['message'] ?? '') === 'Duplicate request for pickup generation'
            //         ) {
            //             Log::info('Duplicate courier assignment attempt — safe to ignore', [
            //                 'shipment_id' => $shipment->shipment_id
            //             ]);
            //         } else {
            //             Log::error('AWB generation failed', ['response' => $courierResponse]);

            //             return response()->json([
            //                 'success' => false,
            //                 'message' => $courierResponse
            //             ], 500);
            //         }
            //         // if (!$awb) {
            //         //     Log::error('AWB generation failed', ['response' => $courierResponse]);

            //         //     return response()->json([
            //         //         'success' => false,
            //         //         'message' => $courierResponse
            //         //     ], 500);
            //         // }

            //         // $shipment->awb = $awb;
            //         // $shipment->save();

            //         // $shipment->status = !empty($courierResponse['response']['data']['pickup_scheduled_date'])
            //         //     ? 'PICKUP SCHEDULED'
            //         //     : 'READY TO SHIP';
            //         // $shipment->save();
            //     }

            //     /** STEP 3: GENERATE PICKUP (IDEMPOTENT) */
            //     if (!in_array($shipment->status, ['PICKUP SCHEDULED', 'READY TO SHIP'])) {

            //         Log::info('Attempting to schedule pickup', [
            //             'shipment_id' => $shipment->shipment_id
            //         ]);

            //         $pickupResponse  = $shiprocket->shipOrder($shipment->shipment_id);
            //         Log::info('Shiprocket shipOrder response', $pickupResponse);

            //         // ✅ Already scheduled — treat as success
            //         if (
            //             ($pickupResponse['status_code'] ?? null) == 400 &&
            //             ($pickupResponse['message'] ?? '') === 'Already in Pickup Queue.'
            //         ) {
            //             Log::info('Pickup already scheduled', ['shipment_id' => $shipment->shipment_id]);
            //             $shipment->status = 'PICKUP SCHEDULED';
            //             $shipment->save();
            //         }
            //         // ✅ Normal success
            //         elseif (($pickupResponse['response']['status'] ?? null) == 3) {
            //             $shipment->status = 'PICKUP SCHEDULED';
            //             $shipment->save();
            //         }

            //         // ❌ Actual failure
            //         else {
            //             Log::error('Ship order failed', ['response' => $pickupResponse]);

            //             return response()->json([
            //                 'success' => false,
            //                 'message' => $pickupResponse
            //             ], 500);
            //         }

            //         // if (!isset($shipOrderResponse['response']['status'])) {
            //         //     Log::error('Ship order failed', ['response' => $shipOrderResponse]);

            //         //     return response()->json([
            //         //         'success' => false,
            //         //         'message' => 'Shipment created but shipping failed. Please retry.'
            //         //     ], 500);
            //         // }

            //     }

            //     /** STEP 4: GENERATE LABEL / MANIFEST / INVOICE (ONLY IF MISSING) */
            //     if (empty($shipment->label_url)) {

            //         $label = $shiprocket->generateLabel($shipment->shipment_id);
            //         Log::info('Label generated', $label);

            //         // if (empty($label['label_url'])) {
            //         //     Log::error('Label generation failed', ['response' => $label]);

            //         //     return response()->json([
            //         //         'success' => false,
            //         //         'message' => 'Shipment created but label generation failed. Please retry.'
            //         //     ], 500);
            //         // }
            //         if (!empty($label['label_url'])) {
            //             $shipment->label_url = $label['label_url'];
            //             $shipment->save();
            //         } else {
            //             Log::error('Label generation failed', ['response' => $label]);
            //         }

            //         // $shipment->label_url = $label['label_url'];
            //         // $shipment->save();
            //     }

            //     if (empty($shipment->manifest_url)) {
            //         $manifest = $shiprocket->generateManifest($shipment->shipment_id);
            //         Log::info('Manifest generated', $manifest);

            //         if (!empty($manifest['manifest_url'])) {
            //             $shipment->manifest_url = $manifest['manifest_url'];
            //             $shipment->save();
            //         }
            //     }

            //     // $manifest = $shiprocket->generateManifest($shipment->shipment_id);
            //     // Log::info('Manifest generated', $manifest);

            //     // $shipment->manifest_url = $manifest['manifest_url'] ?? null;
            //     // $shipment->save();

            //     if (empty($shipment->invoice_url)) {
            //         $invoice = $shiprocket->generateInvoice($shipment->shipment_order_id);
            //         Log::info('Invoice generated', $invoice);

            //         if (!empty($invoice['invoice_url'])) {
            //             $shipment->invoice_url = $invoice['invoice_url'];
            //             $shipment->save();
            //         }
            //     }

            //     // $invoice = $shiprocket->generateInvoice($shipment->shipment_order_id);
            //     // Log::info('Invoice generated', $invoice);

            //     // $shipment->invoice_url = $invoice['invoice_url'] ?? null;
            //     // $shipment->save();
            // } finally {
            //     optional($lock)->release();
            // }

            /** STEP 5: UPDATE ORDER STATUS */
            $order->update([
                'order_status' => 'Confirmed',
                'is_view'      => 1,
            ]);

            /** STEP 6: NOTIFICATIONS */
            try {
                $phone = $order->address?->receiver_phone
                    ?? $order->user?->user_phone;
                $shippingUrlInhouse = "";
                // $shippingUrlInhouse = 'https://bodhiblisssoap.com/track?' . http_build_query([
                //     'o' => $order->cart_id,
                //     's' => $shipment->awb
                // ]);

                // if ($phone) {
                //     $message = "Dear Customer, Your order {$order->cart_id} has been confirmed for Rs.{$order->total_price}. You can track your shipment here: {$shippingUrlInhouse}.";
                //     $this->smsService->sendSms('91' . $phone, $message, "1707176846904792259");
                // }

                Mail::to([
                    auth()->user()?->email,
                    $order->address?->receiver_email
                ])->send(new ConfirmOrderWithShipment($order, $shippingUrlInhouse));
            } catch (\Exception $e) {
                Log::error('Notification failed', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order confirmed'
                //  and shipment created successfully
            ]);
        } catch (\Throwable $e) {

            Log::error('AJAX Order Confirm Error', [
                'order_id' => $id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Order Confirmation failed: ' . $e->getMessage()
            ], 500);
        }
    }






    public function admin_out_orders(Request $request)
    {
        $title = trans('keywords.Out For Delivery Orders');
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $orders = Orders::with(['orderItems.variation.variation_attributes.attribute.attribute', 'address', 'user'])
            ->where('order_status', 'outfordelivery')
            ->orWhere('order_status', 'outfordelivery')
            ->orderBy('order_date', 'DESC')
            ->paginate(150);

        $url_aws = $this->getImageStorage();

        return view('admin.all_orders.out_orders', compact('title', 'logo', 'orders', 'admin', "url_aws"));
    }

    public function admin_failed_orders(Request $request)
    {
        $title = trans('keywords.Payment Failed Orders');
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $orders = Orders::with(['orderItems.variation.variation_attributes.attribute.attribute', 'address', 'user'])
            ->where('order_status', 'failed')
            ->orWhere('order_status', 'Failed')
            ->orWhere('payment_status', 'failed')
            ->orderBy('order_date', 'DESC')
            ->paginate(150);

        $url_aws = $this->getImageStorage();

        return view('admin.all_orders.failed', compact('title', 'logo', 'orders', 'admin', "url_aws"));
    }


    public function create_shipp_order_confirm(Request $request, $id)
    {
        $title = "Home";

        $order = Orders::with([
            'orderItems.variation.variation_attributes.attribute.attribute',
            'address',
            'user',
            'shipment'
        ])
            ->where('order_id', $id)
            ->first();

        if (!$order) {
            return redirect()->route('admin_all_orders')->with('error', 'Order not found');
        }

        // Normalize products for form
        $products = $order->orderItems->map(function ($item) {
            $productName = $item->product->name ?? $item->product_name ?? $item->name ?? '';
            $sku         = $item->variation->product->hsn_number ?? $item->variation->hsn_number ?? '';
            $quantity    = $item->qty ?? 1;
            $price       = $item->price ?? $item->variation->price ?? 0;
            $mrp         = $item->mrp ?? $item->variation->mrp ?? $price;
            $taxRate     = $item->variation->product->tax->value ?? 0;
            $hsn         = $item->variation->product->hsn_number ?? '';
            $discount    = $item->discount ?? 0;

            return [
                'product_name'     => $productName,
                'product_sku'      => $sku,
                'product_quantity' => (int) $quantity,
                'product_price'    => $price,
                'product_mrp'      => $mrp,
                'product_tax_rate' => $taxRate,
                'product_hsn_code' => $hsn,
                'product_discount' => $discount,
            ];
        })->toArray();

        if (count($products) === 0) {
            $products[] = [
                'product_name'     => '',
                'product_sku'      => '',
                'product_quantity' => 1,
                'product_price'    => '',
                'product_mrp'      => '',
                'product_tax_rate' => 0,
                'product_hsn_code' => '',
                'product_discount' => 0,
            ];
        }

        // Safe address extraction
        $addr = optional($order->address);
        $shipping_address = [
            'name'      => $order->user->name ?? $order->shipping_name ?? $order->name ?? '',
            'phone'     => $addr->receiver_phone ?? $order->phone ?? '',
            'house_no'  => $addr->house_no ?? '',
            'society'   => $addr->society ?? '',
            'landmark'  => $addr->landmark ?? '',
            'city'      => $addr->city ?? $order->city ?? '',
            'state'     => $addr->state ?? $order->state ?? '',
            'pincode'   => $addr->pincode ?? $order->pincode ?? '',
            'country'   => 'India',
        ];

        // Dimensions/weight
        $dimensions = [
            'length' => $order->shipment_length ?? $order->length ?? '',
            'width'  => $order->shipment_width ?? $order->breadth ?? '',
            'height' => $order->shipment_height ?? $order->height ?? '',
            'weight' => $order->weight ?? $order->shipment_weight ?? '',
        ];

        // Payment totals
        $cod_amount = 0;
        $advance_amount = 0;
        if ($order->payment_method == 'COD' || $order->payment_method == 'cod' || $order->payment_method == 'Cod') {
            $cod_amount = $order->total_price;
        }
        if ($order->payment_method == 'Online' || $order->payment_method == 'ONLINE' || $order->payment_method == 'online' || $order->payment_method == 'Prepaid') {
            $advance_amount = $order->total_price;
        }
        $totals = [
            'grand_total'     => $order->total_price ?? '',
            'cod_amount'      => $cod_amount,
            'advance_amount'  => $advance_amount,
            'payment_method'  => $order->payment_method ?? '',
        ];

        // Fetch delivery agents with pricing
        $deliveryAgents = [];
        $selectedCourierId = $order->shipment->courier_id ?? null;

        try {
            if ($shipping_address['pincode'] && $dimensions['weight']) {
                $shiprocketService = app(\App\Services\ShiprocketService::class);

                $rateParams = [
                    'pickup_postcode'   => config('services.shiprocket.pickup_pincode', '110001'),
                    'delivery_postcode' => $shipping_address['pincode'],
                    'weight'            => $dimensions['weight'] ?: 500,
                    'cod'               => in_array(strtoupper($order->payment_method), ['COD']) ? 1 : 0,
                    'declared_value'    => $totals['grand_total'],
                ];

                $response = $shiprocketService->getServiceabilityRates($rateParams);
                if ($response && isset($response['data']['available_courier_companies'])) {
                    $deliveryAgents = collect($response['data']['available_courier_companies'])
                        ->map(function ($courier) {
                            return [
                                'id'                => $courier['courier_company_id'] ?? $courier['id'],
                                'name'              => $courier['courier_name'] ?? $courier['name'],
                                'rate'              => $courier['rate'] ?? 0,
                                'freight_charge'    => $courier['freight_charge'] ?? 0,
                                'cod_charge'        => $courier['cod_charges'] ?? 0,
                                'total_charge'      => $courier['rate'] ?? 0,
                                'estimated_days'    => $courier['etd'] ?? 'N/A',
                                'rating'            => $courier['rating'] ?? 0,
                                'min_weight'        => $courier['min_weight'] ?? 0,
                                'is_recommended'    => $courier['is_recommended'] ?? false,
                                'is_surface'        => $courier['is_surface'] ?? false,
                            ];
                        })
                        ->sortBy('total_charge')
                        ->values()
                        ->toArray();
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to fetch delivery agents: ' . $e->getMessage());
        }

        return view('admin.all_orders.create-ship-confirm', compact(
            'title',
            'order',
            'products',
            'shipping_address',
            'dimensions',
            'totals',
            'deliveryAgents',
            'selectedCourierId'
        ));
    }

    public function fetchDeliveryRates(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'length' => 'required|numeric|min:0.1',
                'width' => 'required|numeric|min:0.1',
                'height' => 'required|numeric|min:0.1',
                'weight' => 'required|numeric|min:1',
            ]);

            $order = Orders::with(['address', 'user'])
                ->where('order_id', $id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $addr = optional($order->address);
            $pincode = $addr->pincode ?? $order->pincode ?? null;

            if (!$pincode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Delivery pincode not found for this order'
                ], 400);
            }

            // Get payment method
            $paymentMethod = strtoupper($order->payment_method ?? 'COD');
            $isCOD = in_array($paymentMethod, ['COD']) ? 1 : 0;

            // Fetch delivery rates
            $shiprocketService = app(\App\Services\ShiprocketService::class);

            $rateParams = [
                'pickup_postcode'   => config('services.shiprocket.pickup_pincode', '110001'),
                'delivery_postcode' => $pincode,
                'weight'            => $validated['weight'],
                'cod'               => $isCOD,
                'declared_value'    => $order->total_price ?? 0,
            ];

            $response = $shiprocketService->getServiceabilityRates($rateParams);

            if ($response && isset($response['data']['available_courier_companies'])) {
                $deliveryAgents = collect($response['data']['available_courier_companies'])
                    ->map(function ($courier) {
                        return [
                            'id'                => $courier['courier_company_id'] ?? $courier['id'],
                            'name'              => $courier['courier_name'] ?? $courier['name'],
                            'rate'              => $courier['rate'] ?? 0,
                            'freight_charge'    => $courier['freight_charge'] ?? 0,
                            'cod_charge'        => $courier['cod_charges'] ?? 0,
                            'total_charge'      => $courier['rate'] ?? 0,
                            'estimated_days'    => $courier['etd'] ?? 'N/A',
                            'rating'            => $courier['rating'] ?? 0,
                            'min_weight'        => $courier['min_weight'] ?? 0,
                            'is_recommended'    => $courier['is_recommended'] ?? false,
                            'is_surface'        => $courier['is_surface'] ?? false,
                        ];
                    })
                    ->sortBy('total_charge')
                    ->values()
                    ->toArray();

                return response()->json([
                    'success' => true,
                    'agents' => $deliveryAgents,
                    'message' => count($deliveryAgents) . ' delivery partners found'
                ]);
            }

            $errorMessage = $response['message']
                ?? $response['error']
                ?? 'No delivery partners available for this location';

            return response()->json([
                'success' => false,
                'agents' => [],
                'message' => $errorMessage
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->implode(', ')
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch delivery rates: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch delivery rates: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getShiprocketOrders(ShiprocketService $shiprocket)
    {
        $title = "Shiprocket Orders";

        $response = $shiprocket->getOrders();

        $orders = $response['data'] ?? [];
        $meta = $response['meta']['pagination'] ?? null;
        $permissions = $response['meta']['permissions'] ?? null;

        if (empty($orders)) {
            return view('admin.shiprocket.index', compact(
                'orders',
                'meta',
                'permissions',
                'title'
            ));
        }

        // 1️⃣ Collect channel_order_id (cart_id)
        $cartIds = collect($orders)
            ->pluck('channel_order_id')
            ->filter()
            ->unique()
            ->values();

        // 2️⃣ Fetch Orders WITH relations in ONE query
        $orderMap = Orders::with([
            'shipment',          // adjust relation name if different
            // add more relations if needed
            // 'customer',
            // 'items',
        ])
            ->whereIn('cart_id', $cartIds)
            ->get()
            ->keyBy('cart_id');   // [cart_id => Order model]

        // 3️⃣ Attach full Order model to Shiprocket data
        $orders = collect($orders)->map(function ($order) use ($orderMap) {
            $order['local_order'] = $orderMap[$order['channel_order_id']] ?? null;
            return $order;
        })->values()->toArray();

        return view('admin.shiprocket.index', compact(
            'orders',
            'meta',
            'permissions',
            'title'
        ));
    }



    public function createShipment(Request $request, Orders $order)
    {
        // Basic validation - expand as needed
        $validated = $request->validate([
            'order' => 'required|string',
            'order_date' => 'required|string',
            'name' => 'required|string',
            'phone' => 'required|string',
            'city' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_name' => 'required|string',
            'products.*.product_sku' => 'required|string',
            'products.*.product_quantity' => 'required|string',
            'products.*.product_price' => 'required|string',
            'products.*.product_tax_rate' => 'required|string',
            'products.*.product_discount' => 'required|string',
            'shipment_length' => 'nullable|string',
            'shipment_width' => 'nullable|string',
            'shipment_height' => 'nullable|string',
            'weight' => 'nullable|string',
            'total_amount' => 'nullable|string',
            'payment_mode' => 'nullable|string',
            'cod_amount' => 'nullable|string',
        ]);

        // Build payload
        $shipments = [];
        $shipment = [
            'waybill' => '',
            'order' => $validated['order'],
            'sub_order' => $request->input('sub_order', 'A'),
            'order_date' => $validated['order_date'],
            'total_amount' => $request->input('total_amount', ''),
            'name' => $validated['name'],
            'company_name' => $request->input('company_name', ''),
            'add' => $request->input('add', ''),
            'add2' => $request->input('add2', ''),
            'add3' => $request->input('add3', ''),
            'pin' => $request->input('pin', ''),
            'city' => $request->input('city', ''),
            'state' => $request->input('state', ''),
            'country' => $request->input('country', 'India'),
            'phone' => $validated['phone'],
            'alt_phone' => $request->input('alt_phone', $validated['phone']),
            'email' => $request->input('email', $order->email ?? ''),
            'is_billing_same_as_shipping' => $request->input('is_billing_same_as_shipping', 'no'),
            'billing_name' => $request->input('billing_name', $request->input('name')),
            'billing_company_name' => $request->input('billing_company_name', ''),
            'billing_add' => $request->input('billing_add', $request->input('add', '')),
            'billing_add2' => $request->input('billing_add2', ''),
            'billing_add3' => $request->input('billing_add3', ''),
            'billing_pin' => $request->input('billing_pin', $request->input('pin', '')),
            'billing_city' => $request->input('billing_city', $request->input('city', '')),
            'billing_state' => $request->input('billing_state', $request->input('state', '')),
            'billing_country' => $request->input('billing_country', $request->input('country', 'India')),
            'billing_phone' => $request->input('billing_phone', $request->input('phone')),
            'billing_alt_phone' => $request->input('billing_alt_phone', $request->input('alt_phone', $request->input('phone'))),
            'billing_email' => $request->input('billing_email', $request->input('email', $order->email ?? '')),
            'products' => [],
            'length' => $request->input('shipment_length', ''),
            'height' => $request->input('shipment_height', ''),
            'weight' => $request->input('weight', ''),
            'breadth' => $request->input('shipment_width', ''),
            'shipment_length' => $request->input('shipment_length', ''),
            'shipment_width' => $request->input('shipment_width', ''),
            'shipment_height' => $request->input('shipment_height', ''),
            'shipping_charges' => $request->input('shipping_charges', '0'),
            'giftwrap_charges' => $request->input('giftwrap_charges', '0'),
            'transaction_charges' => $request->input('transaction_charges', '0'),
            'total_discount' => $request->input('total_discount', '0'),
            'first_attemp_discount' => $request->input('first_attemp_discount', '0'),
            'cod_charges' => $request->input('cod_charges', '0'),
            'advance_amount' => $request->input('advance_amount', '0'),
            'cod_amount' => $request->input('cod_amount', '0'),
            'payment_mode' => $request->input('payment_mode', 'COD'),
            'reseller_name' => $request->input('reseller_name', ''),
            'eway_bill_number' => $request->input('eway_bill_number', ''),
            'gst_number' => $request->input('gst_number', ''),
            'return_address_id' => $request->input('return_address_id', config('services.ithinklogistics.default_return_address_id', '')),
        ];
        // dd($validated['products']);
        // Map products
        foreach ($validated['products'] as $p) {

            // dd($p);
            $shipment['products'][] = [
                'product_name' => $p['product_name'],
                'product_sku' => $p['product_sku'] ?? '',
                'product_quantity' => $p['product_quantity'] ?? '1',
                'product_price' => $p['product_price'] ?? '0',
                'product_tax_rate' => $p['product_tax_rate'] ?? '0',
                'product_hsn_code' => $p['product_hsn_code'] ?? '',
                'product_discount' => $p['product_discount'] ?? '0',
            ];
        }

        $shipments[] = $shipment;

        $payload = [
            'data' => [
                'shipments' => $shipments,
                'pickup_location' => "3121, Sobha Petunia, Veerannapalaya, Nagwara, Bangalore, Bangalore, Karnataka, India, 560045",
                // 'pickup_address_id' => $request->input('pickup_address_id', config('services.ithinklogistics.default_pickup_address_id')),
                // 'access_token' => $request->input('access_token', config('services.ithinklogistics.access_token')),
                // 'secret_key' => $request->input('secret_key', config('services.ithinklogistics.secret_key')),
                // 'logistics' => $request->input('logistics', config('services.ithinklogistics.logistics_name', 'Delhivery')),
                // "s_type" => "",
                // "order_type" => "forward",
            ]
        ];

        // dd( $request->input('access_token', config('services.ithinklogistics.access_token')));
        if ($order->shipment?->status == "success") {
            Log::error('iThinkLogistics Error', "'Shipment already created for this order.')");
            return back()->with('error', 'Shipment already created for this order.');
        }

        // return json_encode($payload);
        // dd('asdasd');
        // Send request with Guzzle
        try {


            $shiprocket = new ShiprocketService();

            $order->length = $shipment['shipment_length'];
            $order->weight = $shipment['weight'];
            $order->breadth = $shipment['shipment_width'];
            $order->height = $shipment['shipment_height'];
            $order->save();

            $response = $shiprocket->createOrder($shipment);
            // dd($response, $shipment);

            if ($response['status_code'] == 400) {
                Log::error('Shiprocket Order Creation Error', ['response' => $response]);
                return back()->with('error', 'Shiprocket Order Creation Failed: ' . ($response['message'] ?? 'Unknown error'));
            }
            // 🚨 HARD GUARD — THIS PREVENTS NULL ROWS
            if (empty($order->order_id)) {
                Log::error('Order ID missing, shipment not created', [
                    'order' => $order->toArray()
                ]);
                return;
            }

            if (!empty($response['shipment_id'])) {

                // ✅ Create / Update shipment SAFELY
                $shiprocketShipment = Shipment::updateOrCreate(
                    [
                        'order_id' => $order->order_id,   // MUST be valid
                    ],
                    [
                        'shipment_order_id' => $response['order_id'] ?? null,
                        'shipment_id'       => $response['shipment_id'],
                        'channel_order_id'  => $response['channel_order_id'] ?? null,
                        'length'            => $shipment['shipment_length'] ?? null,
                        'width'             => $shipment['shipment_width'] ?? null,
                        'height'            => $shipment['shipment_height'] ?? null,
                        'weight'            => $shipment['weight'] ?? null,
                        'status'            => $response['status'] ?? 'NEW',
                        'awb'               => $response['awb_code'] ?? null,
                        'response'          => json_encode($response),
                    ]
                );

                // ✅ Assign courier
                // $courierResponse = $shiprocket->assignCourier($shiprocketShipment->shipment_id);
                // if (!empty($courierResponse['response']['data']['awb_code'])) {
                //     $shiprocketShipment->awb = $courierResponse['response']['data']['awb_code'];
                // }

                // // ✅ Ship order
                // $shiporderResponse = $shiprocket->shipOrder($shiprocketShipment->shipment_id);
                // $shiprocketShipment->status = $shiporderResponse['response']['status'] ?? $shiprocketShipment->status;

                // // ✅ Generate manifest
                // $manifest = $shiprocket->generateManifest($shiprocketShipment->shipment_id);
                // $shiprocketShipment->manifest_url = $manifest['manifest_url'] ?? null;

                // // ✅ Generate label
                // $label = $shiprocket->generateLabel($shiprocketShipment->shipment_id);
                // $shiprocketShipment->label_url = $label['label_url'] ?? null;

                // // ✅ Generate invoice
                // $invoice = $shiprocket->generateInvoice($shiprocketShipment->shipment_order_id);
                // $shiprocketShipment->invoice_url = $invoice['invoice_url'] ?? null;

                // $shiprocketShipment->save();
            } else {

                Log::warning('Shiprocket shipment creation failed', [
                    'response' => $response,
                    'order_id' => $order->order_id,
                ]);

                return back()->with(
                    'error',
                    'Failed to create shipment: ' . ($response['message'] ?? 'Unknown error')
                );
            }


            // dd($response);
            // $client = new Client();
            // $apiUrl = config('services.ithinklogistics'); // set this in config/services.php

            // //  dd($apiUrl);
            // if (!$apiUrl) {
            //     // fallback or throw
            //     Log::error('iThinkLogistics Error', "'iThinkLogistics API URL not configured')");
            //     return back()->with('error', 'iThinkLogistics API URL not configured.');
            // }
            // $response = $client->post($apiUrl['base_url'] . '/order/add.json', [
            //     'json' => $payload,
            //     'timeout' => 30,
            // ]);

            // dd($apiUrl['base_url'] . '/order/add.json', $payload, json_decode((string)$response->getBody(), true));

            // $responseBody = json_decode((string)$response->getBody(), true);
            // dd( $responseBody,$payload ,config('services.ithinklogistics'));

            // Shipment::updateOrCreate(
            //     ['order_id' => $order->order_id],
            //     [
            //         'order_id' => $order->order_id,
            //         'shipment_length' => $shipment['shipment_length'],
            //         'shipment_width' => $shipment['shipment_width'],
            //         'shipment_height' => $shipment['shipment_height'],
            //         'weight' => $shipment['weight'],
            //         'status' =>  $responseBody['data'][1]['status'],
            //         'remark' =>  $responseBody['data'][1]['remark'],
            //         'waybill' =>  $responseBody['data'][1]['waybill'],
            //         'refnum' =>  $responseBody['data'][1]['refnum'],
            //         'logistic_name' =>  $responseBody['data'][1]['logistic_name'],
            //         'response' => json_encode($responseBody),
            //     ]
            // );
            // Shipment::where('order_id', $order->order_id,)->update([

            // ]);
            // dd($responseBody);
            // Log and save response (optional)
            // Log::info('iThinkLogistics response', ['order_id' => $order->id, 'response' => $responseBody]);
            // if ($responseBody['status'] === 'error') {
            //     return back()->with('error', 'Failed to create shipment: ' . $responseBody['data'][1]['remark']);
            // }


            // if ($responseBody['data'][1]['status'] == 'error') {
            //     return back()->with('error', 'Failed to create shipment: ' . $responseBody['data'][1]['remark']);
            // }
            // Mark order as confirmed (adjust status field as per your schema)
            $order->order_status = 'confirmed';
            // $order->ithink_response = json_encode($responseBody);
            $order->save();

            $mobile = $order->address->receiver_phone;


            try {
                // $smsParams = [
                //     'user' => 'zakh',
                //     'password' => 'zakh2025', // Consider moving to .env file
                //     'senderid' => 'ZAKHEC',
                //     'channel' => 'Trans',
                //     'DCS' => 0,
                //     'flashsms' => 0,
                //     'number' => '91' . $mobile, // Changed from 'mobile' to 'number', removed '+'
                //     'text' => "Dear Customer, your order {$order->cart_id} has been confirmed for Rs.{$order->total_price}. We are processing your order. Thank you - ZAKHEC", // Changed from 'message' to 'text'
                //     'Peid' => '1701175853936286545', // Changed from 'entityid' to 'Peid'
                //     'DLTTemplateId' => '1707175991995084746' // Changed from 'tempid' to 'DLTTemplateId'
                // ];

                // Use GET instead of POST
                // $response = Http::get('http://www.admagister.net/api/mt/SendSMS', $smsParams);

                // Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6Inpha2hfYm90IiwiZW1haWwiOiJ6YWtoQG1haWxpbmF0b3IuY29tIiwidGltZXN0YW1wIjoiMjAyNS0xMC0zMVQwNjozNTozMS4zNTJaIiwiY2hhbm5lbCI6IndoYXRzYXBwIiwiaWF0IjoxNzYxODkyNTMxfQ.gmwdMS60eECJ9Mdx3VUq9KvMMnBZXQET2U6PVpfDHeI')
                //     ->post('https://api.helloyubo.com/v3/whatsapp/notification', [
                //         "clientId" => "zakh_bot",
                //         "channel" => "whatsapp",
                //         "send_to" => "91$mobile",
                //         "templateName" => "order_confirm",
                //         "parameters" => ["#$order->cart_id", $order->total_price],
                //         "msg_type" => "TEXT",
                //         "header" => "",
                //         "footer" => "-ZAKHEC",
                //         "buttonUrlParam" => null,
                //         "button" => "false",
                //         "media_url" => "",
                //         "lang" => "en",
                //         "msg" => null,
                //         "userName" => null,
                //         "parametersArrayObj" => null,
                //         "headerParam" => null
                //     ]);
                return back()->with('success', 'Shipment created and order marked confirmed.');
                // if ($response->successful()) {
                //     Log::info('OTP SMS sent successfully', ['mobile' => $mobile, 'response' => $response->body()]);
                //     return redirect()->back();
                // } else {
                //     Log::error('OTP SMS sending failed', ['mobile' => $mobile, 'response' => $response->body()]);
                //     return false;
                // }
            } catch (\Exception $e) {
                Log::error('OTP SMS API Error: ' . $e->getMessage(), ['mobile' => $mobile]);
                return false;
            }
            session(['success' => 'Shipment created and order marked confirmed.']);
            return back()->with('success', 'Shipment created and order marked confirmed.');
            // return redirect()->back();
        } catch (\Throwable $e) {

            dd($e->getMessage());
            // Log error
            Log::error('iThinkLogistics Error', ['order_id' => $order->order_id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to create shipment: ' . $e->getMessage());
        }
    }


    // public function track($orderId, ShiprocketService $shiprocket)
    // {
    //     $title = 'Shiprocket Tracking';
    //     // dd($orderId);
    //     $track = $shiprocket->trackByAwb($orderId);
    //     // $track = $shiprocket->trackOrder($orderId);
    //     // dd( $track );
    //     // if (empty($track)) {
    //     //     return back()->with('error', 'Tracking information not available.');
    //     // }

    //     $data = $track;

    //     return view('admin.shiprocket.track', compact('data', 'title'));
    // }


    public function track(string $awb, ShiprocketService $shiprocket)
    {
        $apiResponse = $shiprocket->trackByAwb($awb);

        // If it has 'message' but no 'tracking_data' → it's an error
        if (isset($apiResponse['message']) && !isset($apiResponse['tracking_data'])) {
            $data = [
                'tracking_data' => [
                    'track_status' => 0,
                    'shipment_track' => [['awb_code' => $awb]],
                    'shipment_track_activities' => [],
                ],
                'error' => $apiResponse['message'], // ← The actual error message
                'error_type' => $this->detectErrorType($apiResponse['message']), // ← 'cancelled'
                'awb' => $awb
            ];
        } elseif (isset($apiResponse['tracking_data'])) {
            $data = $apiResponse; // Normal tracking - pass through
        } else {
            $data = [
                'tracking_data' => ['track_status' => 0],
                'error' => 'Unable to fetch tracking.',
                'error_type' => 'general_error',
                'awb' => $awb
            ];
        }

        return view('admin.shiprocket.track', compact('data'));
    }

    private function detectErrorType(string $message): string
    {
        $message = strtolower($message);

        if (str_contains($message, 'cancel')) return 'cancelled';
        if (str_contains($message, 'not found')) return 'not_found';
        if (str_contains($message, 'rto')) return 'returned';

        return 'general_error';
    }

    public function tracking(Request $request, Orders $order)
    {
        // get AWB from order shipment or query param
        $awb = $order->shipment->waybill ?? $request->query('awb');

        if (!$awb) {
            return back()->with('error', 'No AWB / tracking number found for this order.');
        }

        $trackingApiUrl = config('services.ithinklogistics'); // set in config/services.php
        $accessToken = config('services.ithinklogistics.access_token');
        $secret_key = config('services.ithinklogistics.secret_key');

        $response = null;
        $responseData = null;
        $statusCode = null;

        try {
            if ($trackingApiUrl) {
                $client = new \GuzzleHttp\Client();

                // Normalize outgoing awb_number_list: if CSV, convert to array; single keep as string (API accepts either)
                $payloadAwb = $awb;
                if (is_string($awb) && strpos($awb, ',') !== false) {
                    // convert CSV to array of trimmed values
                    $payloadAwb = array_map('trim', explode(',', $awb));
                }

                $resp = $client->post("https://api.ithinklogistics.com/api_v3/order/track.json", [
                    'json' => [
                        'data' => [
                            'awb_number_list' => $payloadAwb,
                            'access_token' => $accessToken,
                            'secret_key' => $secret_key,
                        ]
                    ],
                    'timeout' => 30,
                ]);


                $body = (string)$resp->getBody();
                $response = json_decode($body, true) ?? [];

                $statusCode = $response['status_code'] ?? $resp->getStatusCode();
            } else {
                // sample JSON fallback (kept for dev/test)
                $sampleJson = '{
                "data": {
                    "901234567109": {
                        "message": "success",
                        "awb_no": "901234567109",
                        "logistic": "Fedex",
                        "order_type": "forward",
                        "current_status": "In Transit",
                        "current_status_code": "UD",
                        "return_tracking_no": "",
                        "last_scan_details": {
                            "status": "In Transit",
                            "status_code": "UD",
                            "status_date_time": "2017-06-07 18:11:26",
                            "scan_location": "Surat_Pandesra_Gateway (Gujarat)",
                            "remark": "Shipment Picked Up from Client Location"
                        },
                        "order_details": {
                            "order_type": "COD",
                            "order_number": "11812",
                            "phy_weight": "200.00",
                            "net_payment": "775.00",
                            "ship_length": "10.00",
                            "ship_width": "10.00",
                            "ship_height": "10.00"
                        },
                        "scan_details": [
                            {
                                "status": "Manifested",
                                "status_code": "UD",
                                "scan_location": "HQ (Haryana)",
                                "remark": "Consignment Manifested",
                                "scan_date_time": "2017-06-07 14:05:57"
                            },
                            {
                                "status": "In Transit",
                                "status_code": "UD",
                                "scan_location": "Surat_Pandesra_Gateway (Gujarat)",
                                "remark": "Shipment Picked Up from Client Location",
                                "scan_date_time": "2017-06-07 18:11:26"
                            }
                        ]
                    }
                    },
                    "status_code": 200
                 }';
                $response = json_decode($sampleJson, true);
                $statusCode = $response['status_code'] ?? 200;
            }


            // Normalize and find the correct response data entry for requested AWB(s)
            $requestedAwbs = is_string($awb) && strpos($awb, ',') !== false
                ? array_map('trim', explode(',', $awb))
                : [$awb];

            $data = $response['data'] ?? [];

            // 1) Direct key match (data keyed by AWB)
            foreach ($requestedAwbs as $rAwb) {
                if (isset($data[$rAwb])) {
                    $responseData = $data[$rAwb];
                    break;
                }
            }

            // 2) If not found, try matching by entry 'awb_no' or within 'awb_number_list' field
            if (!$responseData && !empty($data)) {
                foreach ($data as $key => $entry) {
                    if (!is_array($entry)) {
                        continue;
                    }

                    // match by awb_no
                    if (!empty($entry['awb_no'])) {
                        foreach ($requestedAwbs as $rAwb) {
                            if ((string)$entry['awb_no'] === (string)$rAwb) {
                                $responseData = $entry;
                                break 2;
                            }
                        }
                    }

                    // match by awb_number_list (array or CSV or string)
                    if (!empty($entry['awb_number_list'])) {
                        $anl = $entry['awb_number_list'];
                        $list = [];
                        if (is_array($anl)) {
                            $list = $anl;
                        } elseif (is_string($anl)) {
                            if (strpos($anl, ',') !== false) {
                                $list = array_map('trim', explode(',', $anl));
                            } else {
                                $list = [(string)$anl];
                            }
                        }
                        foreach ($requestedAwbs as $rAwb) {
                            if (in_array((string)$rAwb, $list, true)) {
                                $responseData = $entry;
                                break 2;
                            }
                        }
                    }

                    // sometimes the provider uses the numeric key or other key; check if entry.awb_no equals key or key matches requested AWB
                    foreach ($requestedAwbs as $rAwb) {
                        if ((string)$key === (string)$rAwb) {
                            $responseData = $entry;
                            break 2;
                        }
                    }
                }

                // 3) fallback: if still not found but only one item in data, use that
                if (!$responseData && count($data) === 1) {
                    $responseData = reset($data);
                }
            }
        } catch (\Throwable $e) {


            dd($e->getMessage());
            \Log::error('Tracking API error', [
                'awb' => $awb,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to fetch tracking details: ' . $e->getMessage());
        }

        if (!$responseData) {
            $trackingUrl = json_decode($order->shipment->response, true)['data'][1]['tracking_url'] ?? null;
            if ($trackingUrl) {
                return redirect()->away($trackingUrl);
            }
            return back()->with('error', 'Tracking data not available for AWB: ' . $awb);
        }

        // Normalize awb list for view: prefer responseData.awb_number_list, else responseData.awb_no, else $awb(s)
        $awbList = [];
        if (!empty($responseData['awb_number_list'])) {
            $anl = $responseData['awb_number_list'];
            if (is_array($anl)) {
                $awbList = $anl;
            } elseif (is_string($anl) && strpos($anl, ',') !== false) {
                $awbList = array_map('trim', explode(',', $anl));
            } else {
                $awbList = [(string)$anl];
            }
        } elseif (!empty($responseData['awb_no'])) {
            $awbList = [(string)$responseData['awb_no']];
        } else {
            // fall back to requested list
            $awbList = $requestedAwbs;
        }

        // decide display AWB (prefer awb_no, otherwise first of list)
        $displayAwb = $responseData['awb_no'] ?? ($awbList[0] ?? $awb);

        // Prepare scan list sorted descending by date/time (most recent first)
        $scanDetails = $responseData['scan_details'] ?? [];
        usort($scanDetails, function ($a, $b) {
            $at = strtotime($a['scan_date_time'] ?? ($a['status_date_time'] ?? ''));
            $bt = strtotime($b['scan_date_time'] ?? ($b['status_date_time'] ?? ''));
            return $bt <=> $at; // descending
        });

        $lastScan = $responseData['last_scan_details'] ?? ($scanDetails[0] ?? null);

        return view('admin.tracking.tracking', [
            'order' => $order,
            'title' => 'Tracking Details',
            'awb' => $awb,
            'response' => $response,
            'tracking' => $responseData,
            'scanDetails' => $scanDetails,
            'lastScan' => $lastScan,
            'statusCode' => $statusCode,
            'displayAwb' => $displayAwb,
            'awbList' => $awbList,
        ]);
    }

    // public function changeOrderStatusConfirmed(Request $request, $id)
    // {
    //     try
    //     {
    //         $order = Orders::where('order_id', $id)->firstOrFail();

    //         // 🚨 HARD GUARD — THIS PREVENTS NULL ROWS
    //         if (empty($order->order_id)) {
    //             Log::error('Order ID missing, shipment not created', [
    //                 'order' => $order->toArray()
    //             ]);
    //             return;
    //         }

    //         $length = $request->shipment_length;
    //         $weight = $request->weight;
    //         $breadth = $request->shipment_width;
    //         $height = $request->shipment_height;
    //         $courier_company_id = $request->courier_company_id;


    //         $order->length = $length;
    //         $order->weight = $weight;
    //         $order->breadth = $breadth;
    //         $order->height = $height;
    //         $order->save();

    //         $shiprocket = new ShiprocketService();

    //         if(!$order->shipment && $order->shipment->shipment_id == ''){
    //             // Create order in Shiprocket
    //             $response = $shiprocket->createOrder($order);

    //             Log::info('Shiprocket createOrder response:', $response);

    //             if ($response['status_code'] == 400) 
    //             {
    //                 Log::error('Shiprocket Order Creation Error', ['response' => $response]);
    //                 return back()->with('error', 'Shiprocket Order Creation Failed: ' . ($response['message'] ?? 'Unknown error'));
    //             }
    //             if (!empty($response['shipment_id'])) {

    //                     // ✅ Create / Update shipment SAFELY
    //                     $shiprocketShipment = Shipment::updateOrCreate(
    //                         [
    //                             'order_id' => $order->order_id,   // MUST be valid
    //                         ],
    //                         [
    //                             'shipment_order_id' => $response['order_id'] ?? null,
    //                             'shipment_id'       => $response['shipment_id'],
    //                             'channel_order_id'  => $response['channel_order_id'] ?? null,
    //                             'length'            => $length ?? null,
    //                             'width'             => $width ?? null,
    //                             'height'            => $height ?? null,
    //                             'weight'            => $weight ?? null,
    //                             'status'            => $response['status'] ?? 'NEW',
    //                             'awb'               => $response['awb_code'] ?? null,
    //                             'response'          => json_encode($response),
    //                         ]
    //                     );

    //                     // Assign courier & get AWB
    //                                 $courierResponse = $shiprocket->assignCourier( $response['shipment_id'],$courier_company_id);
    //                                 Log::info('Shiprocket assignCourier:', $courierResponse);

    //                                 $shiprocketShipment->awb = $courierResponse['response']['data']['awb_code'] ?? null;
    //                                 $shiprocketShipment->save();
    //                                 Log::info('Shiprocket awb code saved successfully:', ['shipment_awb' =>$shiprocketShipment->awb ]);

    //                                 $shiporderResponse = $shiprocket->shipOrder($response['shipment_id']);
    //                              $shiprocketShipment->status = $shiporderResponse['response']['status'] ?? $shiprocketShipment->status;

    //                             $manifest = $shiprocket->generateManifest($response['shipment_id']);
    //                             $shiprocketShipment->manifest_url = $manifest['manifest_url'] ?? null;

    //                             // Generate label and manifest
    //                             $label = $shiprocket->generateLabel($response['shipment_id']);
    //                             $shiprocketShipment->label_url = $label['label_url'] ?? null;

    //                             $invoice = $shiprocket->generateInvoice($shiprocketShipment->shipment_order_id);
    //                             $shiprocketShipment->invoice_url = $invoice['invoice_url'] ?? null;


    //             }
    //             else 
    //             {

    //                     Log::warning('Shiprocket shipment creation failed', [
    //                         'response' => $response,
    //                         'order_id' => $order->order_id,
    //                     ]);

    //                     return back()->with(
    //                         'error',
    //                         'Failed to create shipment: ' . ($response['message'] ?? 'Unknown error')
    //                     );
    //             }
    //         }

    //         if($order->shipment && $order->shipment->awb == '')
    //         {
    //             $shipment_id = $order->shipment->shipment_id;
    //             $shipment = Shipment::where('order_id',$order->order_id)->first();
    //             // Assign courier & get AWB
    //                                 $courierResponse = $shiprocket->assignCourier( $shipment_id,$courier_company_id);
    //                                 Log::info('Shiprocket assignCourier:', $courierResponse);

    //                                 $shipment->awb = $courierResponse['response']['data']['awb_code'] ?? null;
    //                                 $shipment->save();
    //                                 Log::info('Shiprocket awb code saved successfully:', ['shipment_awb' =>$shipment->awb ]);

    //                             $shiporderResponse = $shiprocket->shipOrder($shipment_id);
    //                              $shipment->status = $shiporderResponse['response']['status'] ?? $shipment->status;

    //                             $manifest = $shiprocket->generateManifest($shipment_id);
    //                             $shipment->manifest_url = $manifest['manifest_url'] ?? null;

    //                             // Generate label and manifest
    //                             $label = $shiprocket->generateLabel($shipment_id);
    //                             $shipment->label_url = $label['label_url'] ?? null;

    //                             $invoice = $shiprocket->generateInvoice($shipment->shipment_order_id);
    //                             $shipment->invoice_url = $invoice['invoice_url'] ?? null;


    //                             $shipment->save();
    //         }
    //         $order->update([
    //             'order_status' => 'Confirmed'
    //         ]);

    //         $mobile = $order->user->user_phone;

    //         // try {
    //         //     $smsParams = [
    //         //         'user' => 'zakh',
    //         //         'password' => 'zakh2025', // Consider moving to .env file
    //         //         'senderid' => 'ZAKHEC',
    //         //         'channel' => 'Trans',
    //         //         'DCS' => 0,
    //         //         'flashsms' => 0,
    //         //         'number' => '91' . $mobile, // Changed from 'mobile' to 'number', removed '+'
    //         //         'text' => "Dear Customer, your order {$order->cart_id} has been confirmed for Rs.{$order->total_price}. We are processing your order. Thank you - ZAKHEC", // Changed from 'message' to 'text'
    //         //         'Peid' => '1701175853936286545', // Changed from 'entityid' to 'Peid'
    //         //         'DLTTemplateId' => '1707175991995084746' // Changed from 'tempid' to 'DLTTemplateId'
    //         //     ];

    //         //     // Use GET instead of POST
    //         //     $response = Http::get('http://www.admagister.net/api/mt/SendSMS', $smsParams);

    //         //     if ($response->successful()) {
    //         //         Log::info('OTP SMS sent successfully', ['mobile' => $mobile, 'response' => $response->body()]);
    //         //         return redirect()->back();
    //         //     } else {
    //         //         Log::error('OTP SMS sending failed', ['mobile' => $mobile, 'response' => $response->body()]);
    //         //         return false;
    //         //     }
    //         // } catch (\Exception $e) {
    //         //     Log::error('OTP SMS API Error: ' . $e->getMessage(), ['mobile' => $mobile]);
    //         //     return false;
    //         // }
    //         return Redirect::route('admin_on_orders')->with('success', 'Order Status Changed Successfully');
    //     } catch (\Throwable $e) {

    //         // Log error
    //         Log::error('Order Confirm Error', ['order_id' => $order->order_id, 'error' => $e->getMessage()]);
    //         return back()->with('error', 'Failed to create shipment: ' . $e->getMessage());
    //     }    
    // }

    public function changeOrderStatusConfirmed(Request $request, $id)
    {
        try {
            $order = Orders::where('order_id', $id)->firstOrFail();


            Log::info('Order confirmation initiated', [
                'order_id' => $order->order_id,
                'request'  => $request->all(),
            ]);

            if (empty($order->order_id)) {
                Log::error('Order ID missing, shipment not created', ['order' => $order->toArray()]);
                return redirect()->back()->with('error', 'Invalid order.');
            }

            // Update dimensions early (safe to persist)
            $order->update([
                'length'  => $request->shipment_length,
                'weight'  => $request->weight,
                'breadth' => $request->shipment_width,
                'height'  => $request->shipment_height,
            ]);

            //  dd($order, $request->all());
            Log::info('Order dimensions updated', [
                'order_id' => $order->order_id,
                'length'   => $request->shipment_length,
                'weight'   => $request->weight,
                'breadth'  => $request->shipment_width,
                'height'   => $request->shipment_height,
            ]);

            $courierCompanyId = $request->courier_company_id;
            $shiprocket = new ShiprocketService();

            // =========================
            // STEP 1: CREATE SHIPMENT IF NOT EXISTS (DO NOT ROLLBACK THIS)
            // =========================
            if (!$order->shipment) {
                Log::info('No shipment found. Creating shipment in Shiprocket.', [
                    'order_id' => $order->order_id,
                ]);

                $response = $shiprocket->createOrder($order);
                Log::info('Shiprocket createOrder response', $response);

                if (empty($response['shipment_id'])) {
                    Log::error('Shiprocket order creation failed', ['response' => $response]);
                    return redirect()->back()->with('error', 'Failed to create shipment in Shiprocket.');
                }

                $shipment = Shipment::updateOrCreate(
                    ['order_id' => $order->order_id],
                    [
                        'shipment_order_id' => $response['order_id'] ?? null,
                        'shipment_id'       => $response['shipment_id'],
                        'channel_order_id'  => $response['channel_order_id'] ?? null,
                        'length'            => $request->shipment_length,
                        'width'             => $request->shipment_width,
                        'height'            => $request->shipment_height,
                        'weight'            => $request->weight,
                        'status'            => $response['status'] ?? 'NEW',
                        'awb'               => $response['awb_code'] ?? null,
                        'response'          => json_encode($response),
                    ]
                );

                Log::info('Shipment saved locally after Shiprocket order creation', [
                    'shipment_id' => $shipment->shipment_id,
                    'order_id'    => $order->order_id,
                ]);
            } else {
                $shipment = $order->shipment;
                Log::info('Existing shipment found', [
                    'shipment_id' => $shipment->shipment_id,
                    'order_id'    => $order->order_id,
                ]);
            }

            // =========================
            // STEP 2: TRY AWB / LABEL / MANIFEST / INVOICE
            // =========================
            if (empty($shipment->awb)) {
                Log::info('AWB missing. Assigning courier.', [
                    'shipment_id' => $shipment->shipment_id,
                    'courier_company_id' => $courierCompanyId,
                ]);

                $courierResponse = $shiprocket->assignCourier($shipment->shipment_id, $courierCompanyId);
                Log::info('Shiprocket assignCourier response', $courierResponse);

                $awb = $courierResponse['response']['data']['awb_code'] ?? null;
                if (!$awb) {
                    Log::error('AWB generation failed', ['response' => $courierResponse]);
                    return back()->with('error', 'Shipment created but AWB generation failed. Please retry.');
                }

                $shipment->awb = $awb;
                $shipment->save();

                Log::info('AWB code saved', [
                    'shipment_id' => $shipment->shipment_id,
                    'awb'         => $shipment->awb,
                ]);

                if (!empty($response['response']['data']['pickup_scheduled_date'])) {
                    $internalStatus = 'PICKUP SCHEDULED';
                } else {
                    $internalStatus = 'READY TO SHIP';
                }

                $shipment->status = $internalStatus;
                $shipment->save();

                Log::info('shipment status saved', [
                    'status' => $shipment->status,
                    'shipment_id' => $shipment->shipment_id,
                    'awb'         => $shipment->awb,
                ]);

                $shipOrderResponse = $shiprocket->shipOrder($shipment->shipment_id);
                Log::info('Shiprocket shipOrder response', $shipOrderResponse);

                if (!isset($shipOrderResponse['response']['status'])) {
                    Log::error('Ship order failed', ['response' => $shipOrderResponse]);
                    return back()->with('error', 'Shipment created but shipping failed. Please retry.');
                }

                $shipment->status = $shipOrderResponse['response']['status'];
                $shipment->save();



                $label = $shiprocket->generateLabel($shipment->shipment_id);
                Log::info('Label generated', $label);

                if (empty($label['label_url'])) {
                    Log::error('Label generation failed', ['response' => $label]);
                    return redirect()->back()->with('error', 'Shipment created but label generation failed. Please retry.');
                }

                $shipment->label_url = $label['label_url'];
                $shipment->save();

                $manifest = $shiprocket->generateManifest($shipment->shipment_id);
                Log::info('Manifest generated', $manifest);

                if (empty($manifest['manifest_url'])) {
                    Log::error('Manifest generation failed', ['response' => $manifest]);
                    // return redirect()->back()->with('error', 'Shipment created but manifest generation failed. Please retry.');
                }

                $shipment->manifest_url = $manifest['manifest_url'] ?? null;
                $shipment->save();

                $invoice = $shiprocket->generateInvoice($shipment->shipment_order_id);
                Log::info('Invoice generated', $invoice);

                if (empty($invoice['invoice_url'])) {
                    Log::error('Invoice generation failed', ['response' => $invoice]);
                    // return redirect()->back()->with('error', 'Shipment created but invoice generation failed. Please retry.');
                }

                $shipment->invoice_url = $invoice['invoice_url'] ?? null;
                $shipment->save();
            } else {
                Log::info('AWB already exists. Skipping courier assignment.', [
                    'shipment_id' => $shipment->shipment_id,
                    'awb'         => $shipment->awb,
                ]);
            }

            // =========================
            // STEP 3: UPDATE ORDER STATUS ONLY AFTER EVERYTHING SUCCEEDS
            // =========================
            $order->update([
                'order_status' => 'Confirmed',
                'is_view' => 1,
            ]);

            $shippingUrlInhouse = 'https://bodhiblisssoap.com/track?' . http_build_query([
                'o' => $order->cart_id,
                's' => $shipment->awb
            ]);


            $message = "Dear Customer, Your order {$order->cart_id} has been confirmed for Rs.{$order->total_price}. We are now processing your order. You can track your shipment here: $shippingUrlInhouse. Thank you for shopping with us! - BBSOAP";
            $this->smsService->sendSms('91' . $order->address->receiver_phone ?? $order->user->user_phone, $message, "1707176846904792259");
            Mail::to([auth()->user()->email, $order->address->receiver_email])->send(new ConfirmOrderWithShipment($order,  $shippingUrlInhouse));

            Log::info('Order status updated to Confirmed', [
                'order_id' => $order->order_id,
            ]);

            return Redirect::route('admin_on_orders')->with('success', 'Order Status Changed Successfully');
        } catch (\Throwable $e) {
            Log::error('Order Confirm Error', [
                'order_id' => $id ?? null,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Shipment process failed: ' . $e->getMessage());
        }
    }


    public function changeOrderStatusCompleted(Request $request, $id)
    {
        try {
            $order = Orders::where('order_id', $id)->firstOrFail();

            if ($order->payment_method == "COD") {

                $order->update([
                    'payment_status' => 'paid'
                ]);
            }
            $order->update([
                'order_status' => 'Completed',
                'is_view' => 1
            ]);



            // Convert logo to base64
            $logoPath = public_path('assets/images/logo.png');
            $logoBase64 = '';
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }

            // Convert product images to base64
            foreach ($order->orderItems as $item) {
                if ($item->varient_image) {
                    $imagePath = storage_path('app/public/' . $item->varient_image);
                    if (file_exists($imagePath)) {
                        $imageContent = file_get_contents($imagePath);
                        $item->varient_image_base64 = base64_encode($imageContent);

                        // Optional: Determine image type for better data URI
                        $imageInfo = getimagesize($imagePath);
                        if ($imageInfo) {
                            $mimeType = $imageInfo['mime'];
                            $item->image_mime_type = $mimeType;
                        } else {
                            $item->image_mime_type = 'image/jpeg'; // Default fallback
                        }
                    }
                }
            }


            try {
                // Generate PDF with base64 images
                $pdf = Pdf::loadView('frontend.order.invoice', [
                    'order' => $order,
                    'logoBase64' => $logoBase64
                ]);

                // Configure PDF options for better rendering
                $pdf->setPaper('a4', 'portrait')
                    ->setOption('margin-top', 10)
                    ->setOption('margin-right', 10)
                    ->setOption('margin-bottom', 10)
                    ->setOption('margin-left', 10)
                    ->setOption('enable-local-file-access', true)
                    ->setOption('images', true)
                    ->setOption('enable-javascript', false)
                    ->setOption('javascript-delay', 1000)
                    ->setOption('no-stop-slow-scripts', true)
                    ->setOption('lowquality', false)
                    ->setOption('print-media-type', true);

                $pdf->render();
                $pdfContent = $pdf->output();

                // Store PDF
                $directory = 'orders';
                $filename = 'order-generate-' . $order->cart_id . '.pdf';
                $path = $directory . '/' . $filename;

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                Storage::disk('public')->put($path, $pdfContent);

                // Add PDF path to order for email attachment
                $order->pdf_path = $path;

                // Dispatch email event
                // $this->sendOrderSuccessSMS($order->address->receiver_phone,$order->address->receiver_name, $order->cart_id );
                CompleteOrderMailEvent::dispatch($order);

                return Redirect::route('admin_com_orders')->with('success', 'Order Status Changed Successfully');
                // return [
                //     'success' => true,
                //     'pdf_path' => Storage::disk('public')->url($path),
                //     'message' => 'Order PDF generated successfully'
                // ];

            } catch (\Exception $e) {
                // \Log::error('PDF Generation Error: ' . $e->getMessage());

                // Still send email even if PDF generation fails
                CompleteOrderMailEvent::dispatch($order);


                return Redirect::route('admin_com_orders')->with('success', 'Order Status Changed Successfully');
                // return [
                //     'success' => false,
                //     'error' => $e->getMessage(),
                //     'message' => 'PDF generation failed but order email sent'
                // ];
            }

            return Redirect::route('admin_com_orders')->with('success', 'Order Status Changed Successfully');
        } catch (Exception $e) {
            return Redirect::back()->with('error', $e->getMessage());
        }
    }
    public function changeOrderStatusCancelled(Request $request, $id)
    {
        $order = Orders::where('order_id', $id)->firstOrFail();
        $user = auth()->user();

        // dd($order);

        //     $shiprocket = new ShiprocketService();
        //    $response = $shiprocket->cancelOrder($order->shipment->shipment_order_id);

        //     dd($response,$order->shipment->shipment_order_id);

        try {


            if ($order->payment_method == "COD") {
                // $order->update([
                //     'order_status' => 'Cancelled',
                //     'payment_status' => 'Pending'  // Update this field in orders table

                // ]);
                $this->handleCODCancellation($order, $request, $user);
            } else {
                $payment = OrderPayments::where('order_id', $order->order_id)->first();
                if (!$payment) {

                    Log::error('OrderPayments Order Creation Error', ['response' => $order]);
                    return Redirect::back()->with('success', 'Payment record not found for this order');
                }

                // Attempt refund with specific error handling
                try {
                    $this->handleOnlinePaymentCancellation($order, $request, $user);
                } catch (\Razorpay\Api\Errors\BadRequestError $e) {
                    // Handle insufficient balance specifically
                    if (strpos($e->getMessage(), 'does not have enough balance') !== false) {
                        return $this->handleInsufficientBalanceRefund($order, $payment, $request, $user);
                    }
                    throw $e; // Re-throw if it's a different error
                }

                // Send notifications
                $this->sendCancellationNotifications($order);

                // $refundData = [
                //     'order_id' => $payment->order_id,
                //     'type' => "F", // Full refund for cancellation
                //     'currency' => 'INR',
                //     'amount' => $order->total_price
                // ];

                // $payglocal = new PayGlocalService();
                // $response = $payglocal->processRefund($payment->gid, $refundData);
            }


            Log::info('Order cancelled and refunded successfully', [
                'order_id' => $order->order_id,
                // 'payment_id' => $payment->id,
                'refund_amount' => $order->total_price,
                // 'gid' => $payment->gid,
                // 'payglocal_response' => $response
            ]);

            return Redirect::route('admin_can_orders')->with('success', 'Order cancelled and refund processed successfully');
        } catch (\Exception $e) {
            return $this->handleCancellationError($order, $payment ?? null, $e);
        }
    }

    /**
     * Handle refund when Razorpay balance is insufficient
     */
    private function handleInsufficientBalanceRefund($order, $payment, $request, $user)
    {
        // Update order status but mark refund as pending
        $order->update([
            'order_status' => 'Cancelled',
            'payment_status' => 'Refund Pending',
            'is_view' => 1
        ]);

        // Create a refund request record for manual processing
        DB::table('pending_refunds')->insert([
            'order_id' => $order->order_id,
            'payment_id' => $payment->id,
            'amount' => $order->total_price,
            'reason' => 'Insufficient Razorpay balance',
            'status' => 'pending',
            'requested_at' => now(),
            'requested_by' => $user->id
        ]);

        // Log for admin monitoring
        Log::warning('Refund pending due to insufficient balance', [
            'order_id' => $order->order_id,
            'payment_id' => $payment->id,
            'amount' => $order->total_price
        ]);

        // Update payment notes
        $payment->update([
            'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                '[' . now()->format('Y-m-d H:i:s') . '] Refund pending - Insufficient Razorpay balance',
            'refund_status' => 'pending'
        ]);

        // Send modified notification to customer
        $message = "Your order #{$order->cart_id} for Rs.{$order->total_price} has been canceled as it could not be confirmed. In case you paid online, your amount will be refunded within 5-7 business days. You can place a new order anytime. - ZAKHEC";
        $this->smsService->sendSms('91' . $order->address->receiver_phone ?? $order->user->user_phone, $message, "1707176845453676607");

        // Send email notification
        Mail::to([auth()->user()->email, $order->address->receiver_email])
            ->send(new CancelOrder($order));

        // Notify admin/finance team
        $this->notifyAdminAboutPendingRefund($order, $payment);

        return Redirect::route('admin_can_orders')
            ->with('warning', 'Order cancelled. Refund is pending due to insufficient balance. Please process manually.');
    }

    /**
     * Notify admin about pending refunds
     */
    private function notifyAdminAboutPendingRefund($order, $payment)
    {
        // Send email to finance team
        Mail::to(config('mail.admin_finance_email'))
            ->send(new PendingRefundNotification($order, $payment));

        // Or create an admin notification in your system
        // Notification::create([...]);
    }

    /**
     * Extract notifications to separate method
     */
    private function sendCancellationNotifications($order)
    {
        // SMS
        // Send SMS notification
        $message = "Your Order #{$order->cart_id} for Rs.{$order->total_price} has been canceled as it could not be confirmed. In case you paid online, your amount will be refunded within 5-7 business days. You can place a new order anytime. - BBSOAP"; // Changed from 'message' to 'text'
        $response = $this->smsService->sendSms('91' . $order->address->receiver_phone ?? $order->user->user_phone, $message, "1707176846913021679");


        Log::error('Order sendCancellationNotifications', [
            'order' => $order,
            'error' =>  $response
        ]);
        // Send cancellation email
        Mail::to([
            auth()->user()->email,
            $order->address->receiver_email
        ])->send(new CancelOrder($order));
    }

    /**
     * Handle cancellation errors
     */
    private function handleCancellationError($order, $payment, $exception)
    {
        if ($payment) {
            $payment->update([
                'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                    '[' . now()->format('Y-m-d H:i:s') . '] Refund failed: ' . $exception->getMessage(),
                'failure_reason' => 'Refund processing failed: ' . $exception->getMessage()
            ]);
        }

        Log::error('Order cancellation/refund failed', [
            'order_id' => $order->order_id,
            'payment_id' => $payment->id ?? 'not found',
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ]);

        return Redirect::route('admin_pen_orders')
            ->with('error', 'Order cancellation failed: ' . $exception->getMessage());
    }

    private function handleCODCancellation($order, $request, $user)
    {


        // Cancel shipment if exists
        $shiprocket = new ShiprocketService();
        if ($order->shipment) {
            $shiprocketStatus = strtoupper($order->shipment->status ?? '');

            // Determine cancellation method based on Shiprocket status
            if ($shiprocketStatus == 'NEW' && empty($order->shipment->awb)) {
                // Status: NEW - Order just created, no AWB assigned
                // Method: cancelOrder API
                try {
                    $shiprocket->cancelOrder($order->shipment->shipment_order_id);
                    Log::info('Order cancelled in Shiprocket (NEW status)', [
                        'order_id' => $order->oder_id,
                        'shiprocket_order_id' => $order->shipment->shipment_order_id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to cancel order in Shiprocket', [
                        'order_id' => $order->oder_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if (in_array($shiprocketStatus, ['READY TO SHIP', 'PICKUP GENERATED', 'PICKUP SCHEDULED', 'MANIFESTED', 'LABEL GENERATED'])) {
                // Status: AWB assigned but courier hasn't picked up yet
                // Method: cancelShipment API using AWB code
                if ($order->shipment->awb) {
                    try {
                        $shiprocket->cancelShipment($order->shipment->awb);
                        Log::info('Shipment cancelled in Shiprocket via AWB', [
                            'order_id' => $order->id,
                            'shiprocket_status' => $shiprocketStatus,
                            'awb_code' => $order->shipment->awb
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to cancel shipment in Shiprocket', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    Log::warning('Cannot cancel shipment - AWB code missing', [
                        'order_id' => $order->id,
                        'shiprocket_status' => $shiprocketStatus
                    ]);
                }
            } elseif (in_array($shiprocketStatus, ['PICKED UP', 'IN TRANSIT', 'SHIPPED'])) {
                // Status: Package already with courier - Cannot cancel directly
                // Would need RTO (Return to Origin) process
                Log::warning('Cannot cancel - shipment already picked up. Consider RTO process.', [
                    'order_id' => $order->id,
                    'shiprocket_status' => $shiprocketStatus,
                    'awb_code' => $order->shiprocket_awb_code
                ]);
                // Continue with refund processing but order won't be cancelled in Shiprocket
            } else {
                // Unknown status or status that doesn't need Shiprocket cancellation
                Log::info('Shiprocket cancellation skipped', [
                    'order_id' => $order->id,
                    'shiprocket_status' => $shiprocketStatus,
                    'reason' => 'Status does not require or allow cancellation'
                ]);
            }
        }

        // Update order status
        $order->update([
            'order_status' => 'Cancelled',
            'payment_status' => 'Not Applicable',
            'is_view' => 1
        ]);

        // Create cancelled order record
        CancelledOrder::updateOrCreate(
            ['order_id' => $order->order_id],
            [
                'user_id' => $user->id ?? 1,
                'comment' => $request->cancel_reason ?? 'Cancelled by admin',
                'amount' => $order->total_price,
                'is_refunded' => 'no', // COD doesn't need refund
                'read_at' => now(),
            ]
        );
    }

    // private function handleOnlinePaymentCancellation($order, $request, $user)
    // {
    //     $payment = OrderPayments::where('order_id', $order->order_id)->first();

    //     if (!$payment) {
    //         throw new \Exception('Payment record not found for this order');
    //     }

    //     $shiprocket = new ShiprocketService();
    //     $shouldProcessRefundNow = false;
    //     $cancellationStatus = 'pending';

    //     if ($order->shipment) {
    //         $shiprocketStatus = strtoupper($order->shipment->status ?? '');

    //         // Case 1: Order created but not shipped - Cancel and refund immediately
    //         if ($shiprocketStatus == 'NEW' && empty($order->shipment->awb)) {
    //             try {
    //                 $shiprocket->cancelOrder($order->shipment->shipment_order_id);
    //                 $shouldProcessRefundNow = true;
    //                 $cancellationStatus = 'cancelled_not_shipped';

    //                 Log::info('Order cancelled in Shiprocket (NEW status) - Refund will be processed', [
    //                     'order_id' => $order->order_id,
    //                     'shiprocket_order_id' => $order->shipment->shipment_order_id
    //                 ]);
    //             } catch (\Exception $e) {
    //                 Log::error('Failed to cancel order in Shiprocket', [
    //                     'order_id' => $order->order_id,
    //                     'error' => $e->getMessage()
    //                 ]);
    //             }
    //         }

    //         // Case 2: AWB assigned but not picked up - Cancel and refund immediately
    //         if (in_array($shiprocketStatus, ['READY TO SHIP', 'PICKUP GENERATED', 'PICKUP SCHEDULED', 'MANIFESTED', 'LABEL GENERATED'])) {
    //             if ($order->shipment->awb) {
    //                 try {
    //                     $shiprocket->cancelShipment($order->shipment->awb);
    //                     $shouldProcessRefundNow = true;
    //                     $cancellationStatus = 'cancelled_before_pickup';

    //                     Log::info('Shipment cancelled in Shiprocket via AWB - Refund will be processed', [
    //                         'order_id' => $order->order_id,
    //                         'shiprocket_status' => $shiprocketStatus,
    //                         'awb_code' => $order->shipment->awb
    //                     ]);
    //                 } catch (\Exception $e) {
    //                     Log::error('Failed to cancel shipment in Shiprocket', [
    //                         'order_id' => $order->order_id,
    //                         'error' => $e->getMessage()
    //                     ]);
    //                 }
    //             } else {
    //                 Log::warning('Cannot cancel shipment - AWB code missing', [
    //                     'order_id' => $order->order_id,
    //                     'shiprocket_status' => $shiprocketStatus
    //                 ]);
    //             }
    //         }

    //         // Case 3: Already shipped - Wait for product return
    //         if (in_array($shiprocketStatus, ['PICKED UP', 'IN TRANSIT', 'SHIPPED', 'OUT FOR DELIVERY', 'DELIVERED'])) {
    //             $shouldProcessRefundNow = false;
    //             $cancellationStatus = 'awaiting_return';

    //             Log::info('Shipment already in transit - Refund will be processed after product return', [
    //                 'order_id' => $order->order_id,
    //                 'shiprocket_status' => $shiprocketStatus,
    //                 'awb_code' => $order->shipment->awb
    //             ]);

    //             // Initiate RTO (Return to Origin) process
    //             if ($order->shipment->awb) {
    //                 try {
    //                     $shiprocket->initiateRTO($order->shipment->awb);

    //                     // Update shipment status
    //                     $order->shipment->update([
    //                         'rto_initiated_at' => now(),
    //                         'status' => 'RTO_INITIATED'
    //                     ]);

    //                     Log::info('RTO initiated for shipped order', [
    //                         'order_id' => $order->order_id,
    //                         'awb_code' => $order->shipment->awb
    //                     ]);
    //                 } catch (\Exception $e) {
    //                     Log::error('Failed to initiate RTO', [
    //                         'order_id' => $order->order_id,
    //                         'error' => $e->getMessage()
    //                     ]);
    //                 }
    //             }
    //         }
    //     } else {
    //         // No shipment exists - Process refund immediately
    //         $shouldProcessRefundNow = true;
    //         $cancellationStatus = 'no_shipment';

    //         Log::info('No shipment found - Refund will be processed immediately', [
    //             'order_id' => $order->order_id
    //         ]);
    //     }

    //     // Update order status based on refund processing decision
    //     $paymentStatus = $shouldProcessRefundNow ? 'Refund Initiated' : 'Refund Pending Return';

    //     $order->update([
    //         'order_status' => 'Cancelled',
    //         'payment_status' => $paymentStatus
    //     ]);

    //     // Create cancelled order record
    //     CancelledOrder::updateOrCreate(
    //         ['order_id' => $order->order_id],
    //         [
    //             'user_id' => $user->id ?? 1,
    //             'comment' => $request->cancel_reason ?? 'Cancelled by admin',
    //             'amount' => $order->total_price,
    //             'is_refunded' => $shouldProcessRefundNow ? 'processing' : 'pending_return',
    //             'cancellation_status' => $cancellationStatus,
    //             'read_at' => now(),
    //         ]
    //     );

    //     // Process refund immediately if shipment wasn't shipped yet
    //     if ($shouldProcessRefundNow) {
    //         $this->processRefund($order, $payment, $user);

    //         Log::info('Immediate refund processed', [
    //             'order_id' => $order->order_id,
    //             'reason' => $cancellationStatus
    //         ]);
    //     } else {
    //         Log::info('Refund deferred until product return', [
    //             'order_id' => $order->order_id,
    //             'current_status' => $shiprocketStatus ?? 'N/A'
    //         ]);

    //         // Optionally send notification to customer about return requirement
    //         // $this->notifyCustomerAboutReturnRequirement($order, $user);
    //     }
    // }

    private function handleOnlinePaymentCancellation($order, $request, $user)
    {
        Log::info('Starting online payment cancellation process', [
            'order_id' => $order->order_id,
            'user_id' => $user->id ?? null,
        ]);

        $payment = OrderPayments::where('order_id', $order->order_id)->first();

        if (!$payment) {
            Log::error('Payment record not found for this order', [
                'order_id' => $order->order_id,
            ]);
            throw new \Exception('Payment record not found for this order');
        }

        $shiprocket = new ShiprocketService();
        $shouldProcessRefundNow = false;
        $cancellationStatus = 'pending';
        $shiprocketStatus = null;

        if ($order->shipment) {
            $shiprocketStatus = strtoupper(trim($order->shipment->status ?? ''));

            Log::info('Shipment found for order', [
                'order_id' => $order->order_id,
                'shipment_id' => $order->shipment->id ?? null,
                'shiprocket_status' => $shiprocketStatus,
                'awb' => $order->shipment->awb ?? null,
            ]);

            /**
             * Case 1: Order created but not shipped - Cancel and refund immediately
             */
            if ($shiprocketStatus === 'NEW' && empty($order->shipment->awb)) {
                Log::info('Order is NEW and not shipped, attempting cancellation', [
                    'order_id' => $order->order_id,
                ]);

                try {
                    $shiprocket->cancelOrder($order->shipment->shipment_order_id);
                    $shouldProcessRefundNow = true;
                    $cancellationStatus = 'cancelled_not_shipped';

                    Log::info('Order cancelled in Shiprocket (NEW status)', [
                        'order_id' => $order->order_id,
                        'shiprocket_order_id' => $order->shipment->shipment_order_id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to cancel order in Shiprocket (NEW status)', [
                        'order_id' => $order->order_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            /**
             * Case 2: AWB assigned but not picked up - Cancel and refund immediately
             */
            elseif (in_array($shiprocketStatus, [
                'READY TO SHIP',
                'OUT FOR PICKUP',
                'PICKUP GENERATED',
                'PICKUP SCHEDULED',
                'MANIFESTED',
                'LABEL GENERATED'
            ])) {
                Log::info('Shipment is before pickup, cancelling shipment and order', [
                    'order_id' => $order->order_id,
                    'shiprocket_status' => $shiprocketStatus,
                    'awb' => $order->shipment->awb ?? null,
                ]);

                if ($order->shipment->awb) {
                    try {
                        // Step 1: Cancel shipment (AWB)
                        $shiprocket->cancelShipment($order->shipment->awb);

                        Log::info('Shipment cancelled in Shiprocket via AWB', [
                            'order_id' => $order->order_id,
                            'awb_code' => $order->shipment->awb,
                        ]);

                        // Step 2: Cancel order (since it moves to NEW)
                        try {
                            $shiprocket->cancelOrder($order->shipment->shipment_order_id);

                            $shouldProcessRefundNow = true;
                            $cancellationStatus = 'cancelled_before_pickup';

                            Log::info('Order cancelled in Shiprocket after shipment cancellation', [
                                'order_id' => $order->order_id,
                                'shiprocket_order_id' => $order->shipment->shipment_order_id,
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Shipment cancelled but failed to cancel order in Shiprocket', [
                                'order_id' => $order->order_id,
                                'shiprocket_order_id' => $order->shipment->shipment_order_id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to cancel shipment in Shiprocket', [
                            'order_id' => $order->order_id,
                            'awb_code' => $order->shipment->awb,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } else {
                    Log::warning('Cannot cancel shipment — AWB code missing', [
                        'order_id' => $order->order_id,
                        'shiprocket_status' => $shiprocketStatus,
                    ]);
                }
            }


            /**
             * Case 3: Already shipped or delivered - Wait for return (RTO)
             */
            elseif (in_array($shiprocketStatus, [
                'PICKED UP',
                'IN TRANSIT',
                'SHIPPED',
                'OUT FOR DELIVERY',
                'DELIVERED'
            ])) {
                $shouldProcessRefundNow = false;
                $cancellationStatus = 'awaiting_return';

                Log::info('Shipment already in transit or delivered, refund deferred', [
                    'order_id' => $order->order_id,
                    'shiprocket_status' => $shiprocketStatus,
                    'awb_code' => $order->shipment->awb ?? null,
                ]);

                // Initiate RTO (Return to Origin) process
                if ($order->shipment->awb) {
                    try {
                        $shiprocket->initiateRTO($order->shipment->awb);

                        $order->shipment->update([
                            'rto_initiated_at' => now(),
                            'status' => 'RTO_INITIATED',
                        ]);

                        Log::info('RTO initiated successfully', [
                            'order_id' => $order->order_id,
                            'awb_code' => $order->shipment->awb,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to initiate RTO', [
                            'order_id' => $order->order_id,
                            'awb_code' => $order->shipment->awb,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } else {
                    Log::warning('Cannot initiate RTO — AWB code missing', [
                        'order_id' => $order->order_id,
                    ]);
                }
            } else {
                Log::warning('Unhandled Shiprocket status encountered', [
                    'order_id' => $order->order_id,
                    'shiprocket_status' => $shiprocketStatus,
                ]);
            }
        } else {
            /**
             * Case 4: No shipment exists - Process refund immediately
             */
            $shouldProcessRefundNow = true;
            $cancellationStatus = 'no_shipment';

            Log::info('No shipment record found, refund will be processed immediately', [
                'order_id' => $order->order_id,
            ]);
        }

        /**
         * Update order status
         */
        $paymentStatus = $shouldProcessRefundNow ? 'Refund Initiated' : 'Refund Pending Return';

        $order->update([
            'order_status' => 'Cancelled',
            'payment_status' => $paymentStatus,
            'is_view' => 1,
        ]);

        if ($order->shipment) {
            $order->shipment->update([
                'status' => $cancellationStatus
            ]);
        }


        Log::info('Order status updated after cancellation decision', [
            'order_id' => $order->order_id,
            'order_status' => 'Cancelled',
            'payment_status' => $paymentStatus,
            'cancellation_status' => $cancellationStatus,
        ]);

        /**
         * Create / Update cancelled order record
         */
        CancelledOrder::updateOrCreate(
            ['order_id' => $order->order_id],
            [
                'user_id' => $user->id ?? 1,
                'comment' => $request->cancel_reason ?? 'Cancelled by admin',
                'amount' => $order->total_price,
                'is_refunded' => $shouldProcessRefundNow ? 'processing' : 'pending_return',
                'cancellation_status' => $cancellationStatus,
                'read_at' => now(),
            ]
        );

        Log::info('Cancelled order record saved', [
            'order_id' => $order->order_id,
            'is_refunded' => $shouldProcessRefundNow ? 'processing' : 'pending_return',
            'cancellation_status' => $cancellationStatus,
        ]);

        /**
         * Process refund if applicable
         */
        if ($shouldProcessRefundNow) {
            Log::info('Processing immediate refund', [
                'order_id' => $order->order_id,
            ]);

            $this->processRefund($order, $payment, $user);

            Log::info('Refund processed successfully', [
                'order_id' => $order->order_id,
                'reason' => $cancellationStatus,
            ]);
        } else {
            Log::info('Refund deferred until product return', [
                'order_id' => $order->order_id,
                'current_status' => $shiprocketStatus ?? 'N/A',
            ]);

            // Optional: notify customer about return requirement
            // $this->notifyCustomerAboutReturnRequirement($order, $user);
        }

        Log::info('Online payment cancellation flow completed', [
            'order_id' => $order->order_id,
        ]);
    }


    private function processRefund($order, $payment, $user = null)
    {
        try {
            $refundAmount = $order->total_price;
            $razorpayPaymentId = $payment->payment_id;

            // Initialize Razorpay API
            $api = new \Razorpay\Api\Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            // Process refund
            $refundResponse = $api->payment->fetch($razorpayPaymentId)->refund([
                'amount' => $refundAmount * 100, // amount in paise
                'speed' => 'normal' // or 'optimum'
            ]);

            // Create refund record
            OrderRefund::create([
                'order_id' => $order->order_id,
                'payment_id' => $razorpayPaymentId,
                'refund_id' => $refundResponse->id,
                'status' => $refundResponse->status,
                'amount' => $refundAmount,
                'method' => 'razorpay',
                'refunded_at' => now(),
                'created_by' => $user->id ?? null,
            ]);

            // Update order payment status
            $order->update([
                'payment_status' => 'Refunded'
            ]);

            // Update cancelled order
            $order->cancel_detail?->update([
                'is_refunded' => 'yes',
                'refunded_at' => now(),
                'read_at'     => now(),
            ]);

            Log::info('Refund processed successfully', [
                'order_id' => $order->order_id,
                'refund_id' => $refundResponse->id,
                'amount' => $refundAmount
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'order_id' => $order->order_id,
                'error' => $e->getMessage()
            ]);

            // Update status to failed
            $order->update([
                'payment_status' => 'Refund Failed'
            ]);

            throw $e;
        }
    }

    public function exportOrders(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $fileName = 'orders_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new OrdersExport($search, $status, $fromDate, $toDate), $fileName);
    }

    public function exportPendingOrders(Request $request)
    {
        $search = $request->search;
        $fileName = 'pending_orders_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new OrdersExport($search, 'pending'), $fileName);
    }

    public function exportCompletedOrders(Request $request)
    {
        $search = $request->search;
        $fileName = 'completed_orders_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new OrdersExport($search, 'completed'), $fileName);
    }

    public function exportCancelledOrders(Request $request)
    {
        $search = $request->search;
        $fileName = 'cancelled_orders_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new OrdersExport($search, 'cancelled'), $fileName);
    }

    public function exportOngoingOrders(Request $request)
    {
        $search = $request->search;
        $fileName = 'ongoing_orders_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new OrdersExport($search, 'confirmed'), $fileName);
    }

    public function exportFailedOrders(Request $request)
    {
        $search = $request->search;
        $fileName = 'failed_orders_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new OrdersExport($search, 'failed'), $fileName);
    }
}
