<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Album;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\AlbumResource;
use App\Http\Resources\AlbumCollection;
use Illuminate\Support\Facades\Storage;

class AlbumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return AlbumResource::collection(Album::orderBy('release_date', 'DESC')->paginate(20));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $inputs = $request->validate([
            'title' => 'required|string|max:255',
            'base64_cover_pic' => 'base64image|base64dimensions:min_width=100,max_width=1000|base64mimes:jpg,jpeg,png|base64max:2048',
            'release_date' => 'required|date|max:10',
        ]);
        
        try {
            DB::transaction(function () use ($inputs, $request) {
                $imageData = explode(',', $inputs['base64_cover_pic'])[1];
                $imageExtension = explode('/', mime_content_type($inputs['base64_cover_pic']))[1];
                $filename = 'album_covers/'.Str::random(10).'.'.$imageExtension;
                Storage::disk('public')->put($filename, base64_decode($imageData));
                $inputs['cover_pic'] = $filename;

                $request->user()->albums()->create([
                    'title' => $inputs['title'],
                    'cover_pic' => $filename,
                    'release_date' => $inputs['release_date']
                ]);
            });
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return new AlbumResource(Album::findOrFail($id)->load('user')->load('tracks'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $inputs = $request->validate([
            'title' => 'required|string|max:255',
            'base64_cover_pic' => 'base64image|base64dimensions:min_width=100,max_width=1000|base64mimes:jpg,jpeg,png|base64max:2048',
            'release_date' => 'required|date|max:10',
        ]);

        $album = Album::findOrFail($id);

        try {
            DB::transaction(function () use ($inputs, $album) {
                if (isset($inputs['base64_cover_pic'])) {
                    $imageData = explode(',', $inputs['base64_cover_pic'])[1];
                    $imageExtension = explode('/', mime_content_type($inputs['base64_cover_pic']))[1];
                    $filename = 'album_covers/'.Str::random(10).'.'.$imageExtension;
                    Storage::disk('public')->put($filename, base64_decode($imageData));
                    $inputs['cover_pic'] = $filename;
                }

                $album->update($inputs);
            });
        } catch (Exception $e) {   
            return response()->json([
                "message" => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $album = Album::findOrFail($id);

        try {
            DB::transaction(function () use ($album) {
                $album->delete();
            });
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function trashed()
    {
        $albums = Album::onlyTrashed()->get();

        return response()->json([
            'data' => $albums
        ]);
    }

    public function restore($id)
    {
        try {
            DB::transaction(function () use ($id) {
                Album::withTrashed()
                    ->where('id', $id)
                    ->restore();
            });
        }
        catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function deletePermanently($id)
    {
        $album = Album::withTrashed()
            ->where('id', $id)
            ->first();

        try {
            DB::transaction(function () use ($album) {
                $album->forceDelete();
            });
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
}
