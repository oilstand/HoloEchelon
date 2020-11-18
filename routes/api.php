<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::middleware(['cors'])->group(function () {
    Route::resource('batch', 'BatchController', ['only' => ['index','show']]);
    Route::get('channel/{id}', 'BatchController@channel');
    Route::get('channel_raw/{id}', 'BatchController@channel_raw');
    Route::get('video/{id}', 'BatchController@video');
    Route::get('videoc/{id}', 'BatchController@videoc');
    Route::get('videos/{id}', 'BatchController@videos_test');
    Route::get('channelVideos/{id}', 'BatchController@channelVideos');
    Route::get('channelVideoIds/{id}', 'BatchController@channelVideoIds');
    Route::get('channelVideosFromDS/{id}', 'BatchController@channelVideosFromDS');

    Route::get('channelList', 'BatchController@channelList');

});
