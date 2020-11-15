<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Library\YouTubeAPI;
use App\Library\YTDManager;

class BatchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = array('hoge'=>'huga');
        $status = 200;

        $api = new YouTubeAPI();
        //@$posts['test'] = $api->getChannelVideos('UCWCc8tO-uUl_7SJXIKJACMw');
        //@$posts['test'] = $api->getVideo('mRqrkTQ_fL8');

        $yt = new YTDManager();
        //@$channel = $yt->getData(YTDManager::TYPE_CHANNEL,'UCWCc8tO-uUl_7SJXIKJACMw');
        //@$posts['test'] = $channel->getData();
        $video = $yt->getData(YTDManager::TYPE_VIDEO,'mRqrkTQ_fL8');
        $posts['test'] = $video->getData();

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    public function channelVideos($id)
    {
        $posts = array('hoge'=>'huga');
        $status = 200;

        $yt = new YTDManager();
        $channelVideos = $yt->getData(YTDManager::TYPE_SEARCH_CHANNEL_VIDEOS, $id, false, false, false );
        if( $channelVideos
            && $videos = $channelVideos->getData() ) {

            $yt->saveBatch($videos);
        }
        $posts['test'] = $channelVideos->getData();

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    public function videos_test($id)
    {
        $posts = array('hoge'=>'huga');
        $status = 200;

        $yt = new YTDManager();
        $videos = $yt->getData(YTDManager::TYPE_VIDEOS, array('1LmxK1whApI','5vZqrFOPG8k'), false, false, false );
        if( $videos
            && $videodata = $videos->getData() ) {

            $yt->saveBatch($videodata);
            $posts['test'] = $videodata;
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    public function channel($id)
    {
        $posts = array('hoge'=>'huga');
        $status = 200;

        $yt = new YTDManager();
        $channel = $yt->getData(YTDManager::TYPE_CHANNEL, $id );
        $posts['test'] = $channel->getData();

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    public function channel_raw($id)
    {
        $posts = array('hoge'=>'huga');
        $status = 200;

        $api = new YouTubeAPI();
        $posts['test'] = $api->getChannel($id)['body'];

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    public function video($id)
    {
        $posts = array('hoge'=>'huga');
        $status = 200;

        $yt = new YTDManager();
        $video = $yt->getData(YTDManager::TYPE_VIDEO, $id );
        $posts['test'] = $video->getData();

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    public function videoc($id)
    {
        $posts = array('hoge'=>'huga');
        $status = 200;

        $yt = new YTDManager();
        $video = $yt->getData(YTDManager::TYPE_VIDEO, $id, false );
        $posts['test'] = $video->getData();

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $posts = array();

        if($id && $id != '') {
            $yt = new YTDManager();
            $channel = $yt->getData(YTDManager::TYPE_CHANNEL, $id);
            if($channel) {
                $posts = $channel->getData();
            }

            $status = 200;
        } else {
            $status = 404;
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
