<?php

namespace App\Http\Controllers\Admin\Banner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Banner\BannerStoreRequest;
use App\Http\Requests\Admin\Banner\BannerUpdateRequest;
use App\Models\Banner;
use App\Traits\ImageStoragePicker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    use ImageStoragePicker;
    public function index()
    {
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $banners = Banner::paginate(10);
        $url_aws = $this->getImageStorage();
        return view('admin.banners.index', [
            'banners' => $banners,
            'logo' => $logo,
            'url_aws' => $url_aws,
            'admin' => $admin
        ]);
    }

    public function create()
    {
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $url_aws = $this->getImageStorage();
        return view('admin.banners.create', [
            'logo' => $logo,
            'url_aws' => $url_aws,
            'admin' => $admin
        ]);
    }

    public function store(BannerStoreRequest $request)
    {
        $date = date('d-m-Y');
        if ($request->hasFile('image')) {
            $banner_image = $request->image;
            $fileName = $banner_image->getClientOriginalName();
            $fileName = str_replace(" ", "-", $fileName);
            $this->getImageStorage();
            if ($this->storage_space != "same_server") {
                $banner_image_name = $banner_image->getClientOriginalName();
                $banner_image = $request->file('image');
                $filePath = '/banner/' . $banner_image_name;
                Storage::disk($this->storage_space)->put($filePath, fopen($request->file('image'), 'r+'), 'public');
            } else {
                $filePath = Storage::disk('public')->put('banners', $request->file('image'));
            }
        }

        Banner::create([
            'image' => $filePath
        ]);

        return redirect()->back()->with('success', 'Banner Added Successfully');
    }

    public function edit($uuid)
    {
        $banner = Banner::where('uuid', $uuid)->firstOrFail();
        if ($banner) {
            $admin_email = Auth::guard('admin')->user()->email;
            $admin = DB::table('admin')
                ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
                ->where('admin.email', $admin_email)
                ->first();
            $logo = DB::table('tbl_web_setting')
                ->where('set_id', '1')
                ->first();
            $url_aws = $this->getImageStorage();
            return view('admin.banners.edit', [
                'banner' => $banner,
                'logo' => $logo,
                'url_aws' => $url_aws,
                'admin' => $admin
            ]);
        }
    }

    public function update(BannerUpdateRequest $request, $uuid)
    {
        $banner = Banner::where('uuid', $uuid)->firstOrFail();
        $date = date('d-m-Y');
        if ($banner) {
            if ($request->hasFile('image')) {
                $banner_image = $request->image;
                $fileName = $banner_image->getClientOriginalName();
                $fileName = str_replace(" ", "-", $fileName);
                $this->getImageStorage();
                if ($this->storage_space != "same_server") {
                    $banner_image_name = $banner_image->getClientOriginalName();
                    $banner_image = $request->file('image');
                    $filePath = '/banner/' . $banner_image_name;
                    Storage::disk($this->storage_space)->put($filePath, fopen($request->file('image'), 'r+'), 'public');
                } else {
                    $oldImagePath = public_path('storage/' . $banner->image);
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }
                    $filePath = Storage::disk('public')->put('banners', $request->file('image'));
                }
            }
            $banner->update([
                'image' => $filePath
            ]);
        }
        return redirect()->route('banners')->with('success', 'Banner Updated Successfully');
    }

    public function delete($uuid)
    {
        $banner = Banner::where('uuid', $uuid)->firstOrFail();
        if ($banner) {
            $oldImagePath = public_path('storage/' . $banner->image);
            if (File::exists($oldImagePath)) {
                File::delete($oldImagePath);
            }
            $banner->delete();
            return redirect()->route('banners')->with('success', 'Banner Deleted Successfully');
        }
    }
}
