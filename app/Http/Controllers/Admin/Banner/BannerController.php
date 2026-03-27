<?php

namespace App\Http\Controllers\Admin\Banner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Banner\BannerStoreRequest;
use App\Http\Requests\Admin\Banner\BannerUpdateRequest;
use App\Models\Banner;
use App\Traits\ImageStoragePicker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        Banner::create([
            'image' => $this->uploadBannerImage($request->file('image')),
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
        if ($request->hasFile('image')) {
            $this->deleteBannerImage($banner->image);
            $banner->update([
                'image' => $this->uploadBannerImage($request->file('image')),
            ]);
        }

        return redirect()->route('banners')->with('success', 'Banner Updated Successfully');
    }

    public function delete($uuid)
    {
        $banner = Banner::where('uuid', $uuid)->firstOrFail();
        if ($banner) {
            $this->deleteBannerImage($banner->image);
            $banner->delete();
            return redirect()->route('banners')->with('success', 'Banner Deleted Successfully');
        }
    }

    protected function uploadBannerImage($image): string
    {
        $this->getImageStorage();

        $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');
        $fileName = Str::uuid()->toString() . '.' . $extension;

        if ($this->storage_space != "same_server") {
            $filePath = '/banners/' . $fileName;
            Storage::disk($this->storage_space)->put($filePath, fopen($image->getRealPath(), 'r'), 'public');

            return $filePath;
        }

        return Storage::disk('public')->putFileAs('banners', $image, $fileName);
    }

    protected function deleteBannerImage(?string $imagePath): void
    {
        if (!$imagePath) {
            return;
        }

        $this->getImageStorage();

        if ($this->storage_space != "same_server") {
            Storage::disk($this->storage_space)->delete(ltrim($imagePath, '/'));

            return;
        }

        $localImagePath = public_path('storage/' . ltrim($imagePath, '/'));
        if (File::exists($localImagePath)) {
            File::delete($localImagePath);
        }
    }
}
