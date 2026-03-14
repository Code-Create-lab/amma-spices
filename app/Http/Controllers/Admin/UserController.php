<?php

namespace App\Http\Controllers\Admin;

use App\Traits\ImageStoragePicker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Session;
use Auth;
use Hash;
use App\Setting;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    use ImageStoragePicker;

    public function list(Request $request)
    {
        if ($request->ajax()) {

            $users = DB::table('users')
                ->leftJoin('city', 'users.user_city', '=', 'city.city_id')
                ->leftJoin('society', 'users.user_area', '=', 'society.society_id')
                ->select([
                    'users.id',
                    'users.name',
                    'users.user_phone',
                    'users.email',
                    'users.reg_date',
                    'users.is_verified',
                    'users.block',
                    'users.wallet',
                    'city.city_name as city_name',
                    'society.society_name as society_name'
                ])
                ->whereNotNull('users.email')
                ->whereNotNull('users.user_phone')
                ->orderBy('users.reg_date', 'desc');

            if ($request->from_date && $request->to_date) {
                $users->whereBetween(
                    DB::raw('DATE(users.reg_date)'),
                    [$request->from_date, $request->to_date]
                );
            }

            // SEARCH FILTER (DataTable default search box)
            if ($request->search['value']) {
                $search = $request->search['value'];

                $users->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.user_phone', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%");
                });
            }

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('is_verified', function ($row) {
                    return $row->is_verified
                        ? '<i class="fa fa-check-circle text-success"></i>'
                        : '<i class="fa fa-times-circle text-danger"></i>';
                })
                ->addColumn('block', function ($row) {
                    if ($row->block == 1) {
                        return '<a href="' . route('userunblock', $row->id) . '">
                                    <i class="fa fa-ban text-danger"></i>
                                </a>';
                    }
                    return '<a href="' . route('userblock', $row->id) . '">
                                <i class="fa fa-check-circle text-success"></i>
                            </a>';
                })
                ->addColumn('action', function ($row) {
                // <a href="' . route('ed_user', $row->id) . '"><i class="fa fa-edit text-warning"></i></a>    
                return '
                        
                        <a href="' . route('del_userfromlist', $row->id) . '"
                        onclick="return confirm(\'Are you sure?\')">
                        <i class="fa fa-trash text-danger"></i>
                        </a>';
                })
                ->addColumn('details', function ($row) {
                    return '<a href="' . route('mem_list', $row->id) . '"
                                class="btn btn-primary btn-sm">Details</a>';
                })
                ->rawColumns(['is_verified', 'block', 'action', 'details'])
                ->make(true);
        }

        // normal page load
        $title = "App User List";
        $admin_email = Auth::guard('admin')->user()->email;

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();

        $logo = DB::table('tbl_web_setting')->where('set_id', '1')->first();

        return view('admin.user.list', compact('title', 'admin', 'logo'));
    }

    public function mem_list(Request $request)
    {
        $title = "App User List";
        $user_id = $request->id;
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $userr = DB::table('users')
            ->where('id', $user_id)
            ->first();
        $address = DB::table('address')
            ->where('user_id', $user_id)
            ->get();
        $users = DB::table('membership_bought')
            ->join('users', 'membership_bought.user_id', '=', 'users.id')
            ->join('membership_plan', 'membership_bought.mem_id', '=', 'membership_plan.plan_id')
            ->select('membership_bought.*', 'users.name', 'users.user_phone', 'membership_plan.plan_name')
            ->orderBy('membership_bought.buy_id', 'desc')
            ->where('membership_bought.user_id', $user_id)
            ->get();
        $ord = DB::table('orders')
            ->join('store', 'orders.store_id', '=', 'store.id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('address', 'orders.address_id', '=', 'address.address_id')
            ->orderBy('orders.delivery_date', 'DESC')
            ->where('orders.user_id', $user_id)
            ->paginate(10);

        $details = DB::table('orders')
            ->join('store_orders', 'orders.cart_id', '=', 'store_orders.order_cart_id')
            ->where('store_orders.store_approval', 1)
            ->get();
        $url_aws = $this->getImageStorage();
        return view('admin.user.mem', compact('title', "admin", "logo", "users", "ord", "details", "userr", "address", "url_aws"));
    }

    public function ed_user(Request $request)
    {
        $title = "App User Edit";
        $id = $request->id;
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $city = DB::table('city')
            ->join('society', 'city.city_id', '=', 'society.city_id')
            ->get();
        $society = DB::table('society')
            ->get();
        $user = DB::table('users')
            ->where('id', $id)
            ->orderBy('reg_date', 'desc')
            ->first();
        $url_aws = $this->getImageStorage();
        return view('admin.user.edit', compact('title', "admin", "logo", "user", "city", "society", "url_aws"));
    }


    public function up_user(Request $request)
    {
        if (Setting::valActDeMode()) return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        $id = $request->id;
        $name = $request->name;
        $email = $request->email;
        $phone = $request->phone;
        $city = $request->city;
        $society = $request->society;
        $wallet = $request->wallet;
        $reward = $request->reward;
        $password1 = $request->password;
        $date = date('d-m-Y');

        $this->validate(
            $request,
            [
                'name' => 'required',
                'email' => 'required',
                'phone' => 'required',
                'city' => 'required',
                'society' => 'required',
                // 'wallet' => 'required',
                // 'reward' => 'required'
            ],
            [
                'name.required' => 'Enter user name.',
                'email.required' => 'Enter user email.',
                'phone.required' => 'Enter user phone.',
                'city.required' => 'Enter user city.',
                'society.required' => 'Enter user society.',
                // 'wallet.required' => 'Enter user wallet.',
                // 'reward.required' => 'Enter user reward.'
            ]
        );

        $user = DB::table('users')
            ->where('id', $id)
            ->first();

        if ($password1 != NULL) {
            $password = bcrypt($password1);
        } else {
            $password = $user->password;
        }

        $insertuser = DB::table('users')
            ->where('id', $id)
            ->update([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'user_phone' => $phone,
                'user_city' => $city,
                'user_area' => $society,
                'wallet' => $wallet,
                'rewards' => $reward
            ]);

        if ($insertuser) {
            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Already Updated'));
        }
    }

    public function daywise(Request $request)
    {
        $date = $request->sel_date;
        $to_date = $request->to_date;
        $next_date = date('Y-m-d', strtotime($date));
        $next_date2 = date('Y-m-d', strtotime($to_date));
        $title = " User Registrations on " . $next_date . " - " . $next_date2;
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $users = DB::table('users')
            ->LeftJoin('city', 'users.user_city', '=', 'city.city_id')
            ->LeftJoin('society', 'users.user_area', '=', 'society.society_id')
            ->where('users.email', '!=', NULL)
            ->where('users.user_phone', '!=', NULL)
            ->where('reg_date', '>=', $next_date)
            ->where('reg_date', '<', $next_date2)
            ->paginate(10);

        $url_aws = $this->getImageStorage();

        return view('admin.user.daywiselist', compact('title', "admin", "logo", "users", "url_aws"));
    }

    public function block(Request $request)
    {
        if (Setting::valActDeMode()) return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        $user_id = $request->id;
        $users = DB::table('users')
            ->where('id', $user_id)
            ->update(['block' => 1]);
        if ($users) {
            return redirect()->back()->withSuccess(trans('keywords.User Blocked Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function unblock(Request $request)
    {
        if (Setting::valActDeMode()) return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        $user_id = $request->id;
        $users = DB::table('users')
            ->where('id', $user_id)
            ->update(['block' => 2]);

        if ($users) {
            return redirect()->back()->withSuccess(trans('keywords.User Unblocked Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function del_user(Request $request)
    {
        if (Setting::valActDeMode()) return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));

        $user_id = $request->id;

        try {
            DB::beginTransaction();

            // Delete all related records first (child tables)
            DB::table('recently_viewed_products')
                ->where('user_id', $user_id)
                ->delete();

            DB::table('address')
                ->where('user_id', $user_id)
                ->delete();

            DB::table('orders')
                ->where('user_id', $user_id)
                ->delete();

            // Add other related tables if needed, for example:
            // DB::table('cart')->where('user_id', $user_id)->delete();
            // DB::table('wishlist')->where('user_id', $user_id)->delete();
            // DB::table('reviews')->where('user_id', $user_id)->delete();
            // DB::table('order_items')->where('user_id', $user_id)->delete();

            // Finally delete the user (parent table)
            $users = DB::table('users')
                ->where('id', $user_id)
                ->delete();

            DB::commit();

            if ($users) {
                return redirect()->back()->withSuccess(trans('keywords.Deleted Successfully'));
            } else {
                return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong') . ': ' . $e->getMessage());
        }
    }
    public function exportUsers(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $search = $request->search;

        $fileName = 'users_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new UsersExport($fromDate, $toDate, $search), $fileName);
    }
}
