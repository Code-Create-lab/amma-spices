<?php 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shipping;
use DB;
use Auth;
use App\Traits\ImageStoragePicker;

class ShippingController extends Controller
{
            use ImageStoragePicker;

    public function index()
    {
        $title = "Review Rating";
        $admin_email = Auth::guard('admin')->user()->email;

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $url_aws = $this->getImageStorage();
        $shippings = Shipping::orderBy('minimum_cart_value', 'asc')->get();
        return view('admin.shippings.index', compact('shippings', 'title', "admin", "logo", "url_aws"));
    }

    public function create()
    {
        $title = "Shipping Fee";
        $admin_email = Auth::guard('admin')->user()->email;

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $url_aws = $this->getImageStorage();
        return view('admin.shippings.create', compact('title', "admin", "logo", "url_aws"));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_charge' => 'required|numeric|min:0',
            'minimum_cart_value' => 'required|numeric|min:0',
        ]);

        // Allow only one record
        if (Shipping::count() >= 3) {
            return redirect()->route('shippings.index')
                ->withErrors(['error' => 'Only one shipping configuration is allowed.']);
        }

        Shipping::create([
            'title' => 'Shipping Fee',
            'shipping_charge' => $request->shipping_charge,
            'minimum_cart_value' => $request->minimum_cart_value,
            'status' => '1',
        ]);

        return redirect()->route('shippings.index')->with('success', 'Shipping configuration saved.');
    }

    public function edit($id)
    {
        $title = "Shipping Fee";
        $admin_email = Auth::guard('admin')->user()->email;

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $url_aws = $this->getImageStorage();
        $shipping = Shipping::findOrFail($id);
        return view('admin.shippings.edit', compact('shipping', 'title', "admin", "logo", "url_aws"));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'shipping_charge' => 'required|numeric|min:0',
            'minimum_cart_value' => 'required|numeric|min:0',
        ]);

        $shipping = Shipping::findOrFail($id);
        $shipping->update([
            'shipping_charge' => $request->shipping_charge,
            'minimum_cart_value' => $request->minimum_cart_value,
        ]);

        return redirect()->route('shippings.index')->with('success', 'Shipping configuration updated.');
    }

    public function destroy($id)
    {
        $shipping = Shipping::findOrFail($id);
        $shipping->delete();

        return redirect()->route('shippings.index')->with('success', 'Shipping configuration deleted.');
    }
}
