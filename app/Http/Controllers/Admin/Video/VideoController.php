<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Video\VideoStoreRequest;
use App\Http\Requests\Admin\Video\VideoUpdateRequest;
use App\Models\Video;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::paginate(10);
        return view('admin.video.index', [
            'videos' => $videos
        ]);
    }
    public function create()
    {
        return view('admin.video.create');
    }
    public function store(VideoStoreRequest $request)
    {
        try{
            if ($request->hasFile('video')) {
                $file = $request->file('video');
                $path = Storage::disk('public')->put('videos', $file);
                Video::create([
                    'video' => $path
                ]);
            }
            return Redirect::route('videos.index')->with('success', 'Video Created Successfully');
        }
        catch(Exception $e){
            Log::error('Error uploading video', ['message' => $e->getMessage()]);
            return Redirect::back()->with('error',$e->getMessage());
        }
    }
    public function edit($uuid)
    {
        $video = Video::where('uuid', $uuid)->firstOrFail();
        return view('admin.video.edit', [
            'video' => $video
        ]);
    }
    public function update(VideoUpdateRequest $request, $uuid)
    {
        $video = Video::where('uuid', $uuid)->firstOrFail();
        if ($request->hasFile('video')) {
            Storage::disk('public')->delete($video->video);
            $file = $request->file('video');
            $path = Storage::disk('public')->put('videos', $file);
            $video->update([
                'video' => $path
            ]);
        }
        return Redirect::route('videos.index')->with('success', 'Video Updated Successfully');
    }
    public function delete($uuid)
    {
        $video = Video::where('uuid', $uuid)->firstOrFail();
        Storage::disk('public')->delete($video->video);
        $video->delete();
        return Redirect::route('videos.index')->with('success', 'Video Deleted Successfully');
    }
}
