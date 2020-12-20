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

// for production ->
    Route::get('video/{id}', 'BatchController@video');
    Route::get('channelList', 'BatchController@channelList');
    Route::get('channel/{id}', 'BatchController@channel');
    Route::get('channelVideos/{id}', 'BatchController@channelVideos');
    Route::get('quoteVideos/{id}', 'BatchController@quoteVideos');
    // temp
    Route::get('newVideos/{id}', 'BatchController@newVideos');
    //consider
    Route::get('gameVideos/{id}', 'BatchController@gameVideos');
// <- for production

// for develop ->
    Route::get('channelGameVideos/{id}', 'BatchController@checkGameChannelVideos');
    Route::get('videos', 'BatchController@videos');

    //Route::get('twitterTest', 'BatchController@twitterTest');
// <- for develop

// cron batch ->
    Route::get('twitter', 'BatchController@twitter');
    Route::get('search', 'BatchController@batchChannelSearchVideos');
    Route::get('updLiveComing', 'BatchController@batchUpdateLiveOrComingVideos');
    // for maintenance
    //Route::get('twitter/{id}', 'BatchController@twitterId');
    //Route::get('updnew', 'BatchController@batchUpdateNewVideos');
// <- cron batch

// for test ->
    //Route::get('upgrade/{id}', 'BatchController@testInstantUpgrade');
    //Route::get('update/{id}', 'BatchController@updateTest');
    //Route::get('videos/{id}', 'BatchController@videos_test');
// <- for test

});
