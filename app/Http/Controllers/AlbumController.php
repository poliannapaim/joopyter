<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Album;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AlbumResource;
use App\Http\Resources\AlbumCollection;

class AlbumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new AlbumCollection(Album::orderBy('release_date', 'asc')->paginate(20));
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
            'title' => ['required', 'string', 'max:255'],
            'cover_pic' => ['required', 'image', 'dimensions:min_width=100,max_width=1000', 'mimes:jpeg,jpg,png', 'max:2048'],
            'release_date' => ['required', 'date_format:d/m/Y', 'max:10'],
        ]);
        
        try
        {
            DB::transaction(function () use ($request, $inputs)
            {
                $file = $request->file('cover_pic');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(64).time().'.'.$extension;
                $file->move('upload/albums/', $filename);

                $album = Auth::user()->albums()->create([
                    'title' => $inputs['title'],
                    'cover_pic' => $filename,
                    'release_date' => Carbon::createFromFormat('d/m/Y', $inputs['release_date'])->format('Y-m-d')
                ]);
            });
        }
        catch (Exception $e) {
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
        return new AlbumResource(Album::findOrFail($id));
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'cover_pic' => ['sometimes', 'required', 'image', 'dimensions:min_width=100,max_width=1000', 'mimes:jpeg,jpg,png', 'max:2048'],
            'release_date' => ['sometimes', 'required', 'date_format:d/m/Y', 'max:10'],
        ]);

        $album = Album::find($id);

        try
        {
            DB::transaction(function () use ($album, $request, $inputs)
            {
                if(isset($inputs['title']))
                {
                    $album->title = $inputs['title'];
                }

                if(isset($inputs['cover_pic']))
                {
                    $file = $request->file('cover_pic');
                    $extension = $file->getClientOriginalExtension();
                    $filename = Str::random(64).time().'.'.$extension;
                    $file->move('upload/albums/', $filename);

                    $album->cover_pic = $filename;
                }

                if(isset($inputs['release_date']))
                {
                    $album->release_date = $inputs['release_date'];
                }

                $album->save();
            });
        }
        catch (Exception $e)
        {   
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
        $album = Album::find($id);

        try
        {
            DB::transaction(function () use ($album)
            {
                $album->delete();
            });
        }
        catch (Exception $e)
        {
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
        try
        {
            DB::transaction(function () use ($id){
                Album::withTrashed()
                    ->where('id', $id)
                    ->restore();
            });
        }
        catch (Exception $e)
        {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
}
