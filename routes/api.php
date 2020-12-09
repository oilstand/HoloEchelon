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
/*
    Route::get('videos/{id}', 'BatchController@videos_test');*/

    Route::get('video/{id}', 'BatchController@video');
    Route::get('channelList', 'BatchController@channelList');
    Route::get('channel/{id}', 'BatchController@channel');
    Route::get('channelVideos/{id}', 'BatchController@channelVideos');
    Route::get('channelGameVideos/{id}', 'BatchController@checkGameChannelVideos');

    Route::get('newVideos/{id}', 'BatchController@newVideos');

//tmp batches
    Route::get('updnew', 'BatchController@batchUpdateNewVideos');
//tmp batches

    Route::get('twitter', 'BatchController@twitter');
    Route::get('twitter/{id}', 'BatchController@twitterId');
    Route::get('search', 'BatchController@batchChannelSearchVideos');
    Route::get('updLiveComing', 'BatchController@batchUpdateLiveOrComingVideos');

    Route::get('gameVideos/{id}', 'BatchController@gameVideos');

    Route::get('upgrade/{id}', 'BatchController@testInstantUpgrade');
    Route::get('update/{id}', 'BatchController@updateTest');

});
