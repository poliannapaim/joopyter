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
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $album_id)
    {
        $inputs = $request->validate([
            'number.*' => 'required|integer',
            'title.*' => 'required|string|max:255',
        ]);

        $album = Album::findOrFail($album_id);
        
        try {
            DB::transaction(function () use ($inputs, $album) {
                for($i = 0; $i < count($inputs['number']); $i++) {
                    $album->tracks()->create([
                        'number' => $inputs['number'][$i],
                        'title' => $inputs['title'][$i]
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
    public function update(Request $request, $album_id, $track_id)
    {
        $inputs = $request->validate([
            'number' => '|required|integer',
            'title' => '|required|string|max:255'
        ]);
        $track = Track::findOrFail($track_id);
        
        try {
            DB::transaction(function () use ($track, $inputs) {
                $track->update($inputs);
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
