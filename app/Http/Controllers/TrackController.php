<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Album;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\TrackResource;

class TrackController extends Controller
{
    public function index(Request $request)
    {
        return TrackResource::collection(Track::paginate(20));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $album_id)
    {
        $inputs = $request->validate([
            '*.number' => 'required|integer',
            '*.title' => 'required|string|max:255',
        ]);
        $album = Album::findOrFail($album_id);

        try {
            DB::transaction(function () use ($inputs, $album) {
                foreach($inputs as $i) {
                    $album->tracks()->create([
                        'number' => $i['number'],
                        'title' => $i['title'],
                    ]);
                }
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
     * @param  \App\Models\Track  $track
     * @return \Illuminate\Http\Response
     */
    public function show($album_id, $track_id)
    {
        return new TrackResource(Track::findOrFail($track_id));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Track  $track
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $album_id)
    {
        
        $inputs = $request->validate([
            '*.id' => 'required',
            '*.number' => 'required|integer',
            '*.title' => 'required|string|max:255',
        ]);
        $album = Album::findOrFail($album_id);
        
        try {
            DB::transaction(function () use ($inputs, $album) {
                // $album->tracks()->update($inputs);
                foreach($inputs as $i) {
                    $album->tracks()->where('id', $i['id'])->update([
                        'number' => $i['number'],
                        'title' => $i['title']
                    ]);
                }
                // dd('deu certo');
            });
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Track  $track
     * @return \Illuminate\Http\Response
     */
    public function destroy($album_id, $track_id)
    {
        $track = Track::findOrFail($track_id);

        try {
            DB::transaction(function () use ($track) {
                $track->delete();
            });
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
}
