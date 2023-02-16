<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\TrackController;

// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
// header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * /api/v1
 */
Route::prefix('v2')->group(function () {
    /**
     * authentication routes
     */
    Route::post('register', [UserController::class, 'store']);
    Route::post('login', [UserController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        /**
         * authentication routes
         */
        Route::post('logout', [UserController::class, 'logout']);
        
        /**
         * user routes
         */
        Route::get('user', [UserController::class, 'user']);
        Route::get('account', [UserController::class, 'show']);
        Route::put('account/update', [UserController::class, 'update']);
        Route::put('account/update-profile-pic', [UserController::class, 'updateProfilePic']);
        Route::delete('account/delete', [UserController::class, 'destroy']);

        /**
         * album routes
         */
        Route::resource('albums', AlbumController::class);
        Route::put('albums/{id}/update-cover-pic', [AlbumController::class, 'updateCoverPic']);

        /**
         * tracks routes
         */
        Route::get('albums/{album_id}/tracks/{track_id}', [TrackController::class, 'show']);
        Route::post('albums/{album_id}/tracks', [TrackController::class, 'store']);
        Route::put('albums/{album_id}/tracks/{track_id}', [TrackController::class, 'update']);
        Route::delete('albums/{album_id}/tracks/{track_id}', [TrackController::class, 'destroy']);

        /**
         * soft delete routes
         */
        Route::post('albums/trashed', [AlbumController::class, 'trashed']);
        Route::post('albums/{id}/restore', [AlbumController::class, 'restore']);
        Route::delete('albums/{id}/delete-permanently', [AlbumController::class, 'deletePermanently']);
    });
});
