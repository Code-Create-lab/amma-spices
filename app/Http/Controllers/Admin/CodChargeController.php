<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CodCharge;
use DB;
use Auth;
use App\Traits\ImageStoragePicker;

class CodChargeController extends Controller
{
    use ImageStoragePicker;

    public function index()
    {
        $title = "COD Charge";
        $admin_email = Auth::guard('admin')->user()->email;

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $url_aws = $this->getImageStorage();
        $codCharges = CodCharge::orderBy('id', 'asc')->get();
        return view('admin.cod_charges.index', compact('codCharges', 'title', 'admin', 'logo', 'url_aws'));
    }

    public function edit($id)
    {
        $title = "COD Charge";
        $admin_email = Auth::guard('admin')->user()->email;

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $url_aws = $this->getImageStorage();
        $codCharge = CodCharge::findOrFail($id);
        return view('admin.cod_charges.edit', compact('codCharge', 'title', 'admin', 'logo', 'url_aws'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cod_charge' => 'required|numeric|min:0',
            'status' => 'required|in:0,1',
        ]);

        $codCharge = CodCharge::findOrFail($id);
        $codCharge->update([
            'cod_charge' => $request->cod_charge,
            'status' => $request->status,
        ]);

        return redirect()->route('cod-charges.index')->with('success', 'COD charge updated.');
    }
}
