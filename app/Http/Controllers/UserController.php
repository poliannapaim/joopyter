<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {        
        $inputs = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        try {
            DB::transaction(function () use ($inputs)
            {
                $user = User::create([
                    'name' => $inputs['name'],
                    'email' => $inputs['email'],
                    'password' => Hash::make($inputs['email'])
                ]);
            });
        } catch (Exception $e)
        {
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
        $user = new UserResource(User::findOrFail($id));

        return response()->json([
            'data' => $user
        ]);
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users'],
            'password' => ['sometimes', 'required', 'confirmed', 'min:8'],
            'dob' => ['nullable', 'date_format:d/m/Y', 'max:10'],
            'profile_pic' => ['nullable', 'image', 'dimensions:min_width=100,max_width=1000', 'mimes:jpeg,jpg,png', 'max:2048'],
            'bio' => ['nullable', 'string', 'max:250'],
        ]);

        try
        {
            DB::transaction(function () use ($id, $request, $inputs)
            {
                $user = User::find($id);

                if(isset($inputs['name']))
                {
                    $user->name = $inputs['name'];
                }

                if(isset($inputs['email']))
                {
                    $user->email = $inputs['email'];
                }

                if(isset($inputs['password']))
                {
                    $user->password = Hash::make($inputs['password']);
                }

                if(isset($inputs['dob']))
                {
                    $user->dob = Carbon::createFromFormat('d/m/Y', $inputs['dob'])->format('Y-m-d');
                }

                if(isset($inputs['profile_pic']))
                {
                    $file = $request->file('profile_pic');
                    $extension = $file->getClientOriginalExtension();
                    $filename = Str::random(64).time().'.'.$extension;
                    $file->move('upload/users/', $filename);

                    $user->profile_pic = $filename;
                }

                if(isset($inputs['bio']))
                {
                    $user->bio = $inputs['bio'];
                }

                $user->save();
            });

            $user = User::where('id', $id)->firstOrFail();

            return response()->json([
                'message' => 'User updated.',
                'data' => $user
            ]);
        }
        catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
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
        $user = User::find($id);
        
        try {
            DB::transaction(function () use ($user)
            {
                $user->tokens()->delete();
                $user->delete();
            });
        }
        catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Login the user.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->firstOrFail();

        if ($user || Hash::check($credentials['password'], $user->password)) {
            Auth::guard('web')->login($user);

            return response()->json([
                'message' => 'User logged in.',
                'token' => $user->createToken('joopyter-token')->plainTextToken
            ]);
        }

        return response()->json([
            'message' => 'Invalid credentials to login.'
        ], 401);
    }

    /**
     * Logout the user.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function logout(Request $request)
    {
        $user = Auth::user();
        $user->tokens()->delete();
        Auth::guard('web')->logout();
    }
}
