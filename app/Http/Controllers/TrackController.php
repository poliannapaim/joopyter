<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Album;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
            'number.*' => ['required', 'integer'],
            'title.*' => ['required', 'string', 'max:255']
        ]);

        $album = Album::find($album_id);
        
        try
        {
            DB::transaction(function () use ($album, $inputs)
            {
                for($i = 0; $i < count($inputs['number']); $i++)
                {
                    $album->tracks()->create([
                        'number' => $inputs['number'][$i],
                        'title' => $inputs['title'][$i]
                    ]);
                }
            });
        }
        catch (Exception $e)
        {
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
            'number' => ['sometimes', 'required', 'integer'],
            'title' => ['sometimes', 'required', 'string', 'max:255']
        ]);

        $track = Track::find($track_id);
        
        try
        {
            DB::transaction(function () use ($track, $inputs)
            {
                if(isset($inputs['number']))
                {
                    $track->number = $inputs['number'];
                }

                if(isset($inputs['title']))
                {
                    $track->title = $inputs['title'];
                }

                $track->save();
            });
        }
        catch (Exception $e)
        {
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
        $track = Track::find($track_id);

        try
        {
            DB::transaction(function () use ($track)
            {
                $track->delete();
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
