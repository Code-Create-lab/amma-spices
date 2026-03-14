<?php

namespace App\Http\Controllers\Admin;

use App\Models\AboutUs;
use App\Models\CancelAndRefund;
use App\Models\PrivacyPolicy;
use App\Models\ReturnAndExchange;
use App\Models\ShippingAndDelivery;
use App\Models\TermAndCondition;
use App\Traits\ImageStoragePicker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Session;
use Auth;
use App\Setting;

class PagesController extends Controller
{
    use ImageStoragePicker;

    public function about_us(Request $request)
    {
        $title = "About Us";
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $check = AboutUs::first();
        $url_aws = $this->getImageStorage();
        return view('admin.about_us', compact('title', "admin", "logo", "check", "url_aws"));
    }

    public function updateabout_us(Request $request)
    {
        if (Setting::valActDeMode())
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        $title = "About Us";
        $description = $request->description;
        $check = AboutUs::first();

        if ($check) {
            $update = AboutUs::first()->update(['description' => $description]);
        } else {
            $update = AboutUs::insert([
                'title' => $title,
                'description' => $description
            ]);
        }
        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function terms(Request $request)
    {
        $title = "Terms & Condition";
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $check = TermAndCondition::first();
        $url_aws = $this->getImageStorage();
        return view('admin.terms', compact('title', "admin", "logo", "check", "url_aws"));
    }

    public function updateterms(Request $request)
    {
        if (Setting::valActDeMode())
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        $title = "Terms & Condition";
        $description = $request->description;
        $check = TermAndCondition::first();

        if ($check) {
            $update = TermAndCondition::first()->update(['description' => $description]);
        } else {
            $update = TermAndCondition::insert([
                'title' => $title,
                'description' => $description
            ]);
        }
        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }
    public function privacypolicy()
    {
        $title = "Privacy & Policy";
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $check = PrivacyPolicy::first();
        $url_aws = $this->getImageStorage();
        return view('admin.pages.privacy_policy', compact('title', "admin", "logo", "check", "url_aws"));
    }

    public function updateprivacypolicy(Request $request)
    {
        if (Setting::valActDeMode())
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        $title = "Privacy & Policy";
        $description = $request->description;
        $check = PrivacyPolicy::first();

        if ($check) {
            $update = PrivacyPolicy::first()->update(['description' => $description]);
        } else {
            $update = PrivacyPolicy::create([
                'title' => $title,
                'description' => $description
            ]);
        }
        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }
    public function cancelandrefundpolicy()
    {
        $title = "Cancel & Refund";
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $check = CancelAndRefund::first();
        $url_aws = $this->getImageStorage();
        return view('admin.pages.cancel_and_refund', compact('title', "admin", "logo", "check", "url_aws"));
    }

    public function updatecancelandrefundpolicy(Request $request)
    {
        if (Setting::valActDeMode())
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        $title = "Cancel & Refund";
        $description = $request->description;
        $check = DB::table('cancelandrefundpage')
            ->first();

        if ($check) {
            $update = CancelAndRefund::first()->update(['description' => $description]);
        } else {
            $update = CancelAndRefund::insert([
                'title' => $title,
                'description' => $description
            ]);
        }
        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }
    public function returnandexchangepolicy()
    {
        $title = "Return & Exchange";
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $check = ReturnAndExchange::first();
        $url_aws = $this->getImageStorage();
        return view('admin.pages.return_and_exchange', compact('title', "admin", "logo", "check", "url_aws"));
    }

    public function updatereturnandexchangepolicy(Request $request)
    {
        if (Setting::valActDeMode())
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        $title = "Return & Exchange";
        $description = $request->description;
        $check = DB::table('returnandexchangepage')
            ->first();

        if ($check) {
            $update = ReturnAndExchange::first()->update(['description' => $description]);
        } else {
            $update = ReturnAndExchange::insert([
                'title' => $title,
                'description' => $description
            ]);
        }
        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }
    public function shippinganddeliverypolicy()
    {
        $title = "Shipping & Delivery";
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $check = ShippingAndDelivery::first();
        $url_aws = $this->getImageStorage();
        return view('admin.pages.shipping_and_delivery', compact('title', "admin", "logo", "check", "url_aws"));
    }

    public function updateshippinganddeliverypolicy(Request $request)
    {
        if (Setting::valActDeMode())
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        $title = "Shipping & Delivery";
        $description = $request->description;
        $check = ShippingAndDelivery::first();

        if ($check) {
            $update = ShippingAndDelivery::first()->update(['description' => $description]);
        } else {
            $update = ShippingAndDelivery::insert([
                'title' => $title,
                'description' => $description
            ]);
        }
        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }
}
