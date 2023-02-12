<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        try {
            DB::transaction(function () use ($inputs) {
                User::create([
                    'name' => $inputs['name'],
                    'email' => $inputs['email'],
                    'password' => Hash::make($inputs['password'])
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
        return new UserResource(User::findOrFail($id));
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
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'dob' => 'nullable|date|max:10',
            'bio' => 'nullable|string|max:250',
            'base64_profile_pic' => 'nullable|base64image|base64dimensions:min_width=100,max_width=1000|base64mimes:jpg,jpeg,png|base64max:2048',
        ]);
        $user = User::findOrFail($id);

        try {
            DB::transaction(function () use ($inputs, $user) {
                if (isset($inputs['base64_profile_pic'])) {
                    $imageData = explode(',', $inputs['base64_profile_pic'])[1];
                    $imageExtension = explode('/', mime_content_type($inputs['base64_profile_pic']))[1];
                    $filename = 'profile_pictures/'.Str::random(10).'.'.$imageExtension;
                    Storage::disk('public')->put($filename, base64_decode($imageData));
                    $inputs['profile_pic'] = $filename;
                }
                $user->update($inputs);
            });

            return response()->json([
                'message' => 'User updated.',
                'data' => $user
            ]);
        } catch (Exception $e) {
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
            DB::transaction(function () use ($user) {
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
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => 1])) {
            return response()->json([
                'message' => 'User logged in.',
                'token' => $request->user()->createToken('joopyter-token')->plainTextToken
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
        $request->user()->tokens()->delete();
    }

    public function user(Request $request)
    {
        return $request->user();
    }
}
