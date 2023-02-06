<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\TrackController;

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
Route::prefix('v1')->group(function () {
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
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::delete('users/{id}', [UserController::class, 'destroy']);

        /**
         * album routes
         */
        Route::resource('albums', AlbumController::class);

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
    });
});
