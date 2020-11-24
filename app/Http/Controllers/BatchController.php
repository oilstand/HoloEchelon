<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Library\YouTubeAPI;
use App\Library\YTDManager;
use App\Library\BaseYTD;
use App\Library\HoloApp;

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

    /*

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
    }*/

////////////////////////////////////////////////////////////////////////////////

    /**
     * /api/video/{id}
     */
    public function video($id)
    {
        $posts = array('hoge'=>'huga');
        $status = 200;

        $yt = new YTDManager();
        $videoc = $yt->getData(YTDManager::TYPE_VIDEO, $id );
        if($videoc
            && $data = $videoc->getData() ) {

            $posts['result'] = 'success';
            $posts['data'] = $data;
        } else {
            $posts['result'] = 'not found';
            $status = 404;
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    /*
     *  /api/channel/{id}
     *  */
    public function channel($id) {

        $posts = array();
        $status = 200;

        $yt = new YTDManager();
        $channelc = $yt->getData(YTDManager::TYPE_CHANNEL, $id );

        if($channelc
            && $data = $channelc->getData() ) {

            $posts['result'] = 'success';
            $posts['data'] = $data;
        } else {
            $posts['result'] = 'not found';
            $status = 404;
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    /*
     *  /api/channelList
     *  */
    function channelList() {

        $posts = array();
        $status = 200;

        $yt = new YTDManager();
        $query = $yt->query()->kind('channel')->limit(30);

        $channelList = $yt->getDataListFromDSQuery('channels', $query);

        $posts['data'] = array();

        if($channelList
            && $channels = $channelList->getDataList()) {
            $posts['result'] = 'success';
            foreach($channels as $channel) {
                $posts['data'][] = $channel->getData();
            }
        } else {
            $posts['result'] = 'not found';
            $status = 404;
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    /**
     *  /api/channelVideos/{id}
     */
    public function channelVideos($id) {

        $posts = array();
        $status = 200;

        $holoApp = new HoloApp();
        $videoListc = $holoApp->getChannelVideos( $id );

        if($videoListc
            && $videoList = $videoListc->getDataList()) {

            $posts['result'] = 'success';
            $posts['data'] = array();
            foreach($videoList as $videoc) {
                $posts['data'][] = $videoc->getData();
            }
        } else {
            $posts['result'] = 'video not found';
            $status = 404;
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    /**
     *  /api/newVideos/{id}
     */
    public function newVideos($id = 0) {

        $posts = array();
        $status = 200;

        $holoApp = new HoloApp();
        $videoListc = $holoApp->newVideosDS( $id );

        if($videoListc
            && $videoList = $videoListc->getDataList()) {

            $posts['result'] = 'success';
            $posts['data'] = array();
            foreach($videoList as $videoc) {
                $posts['data'][] = $videoc->getData();
            }
        } else {
            $posts['result'] = 'video not found';
            $status = 404;
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    public function gameVideos($id) {

        $posts = array();
        $status = 200;

        $holoApp = new HoloApp();
        $videoListc = $holoApp->gameVideosDS( 0 + $id );

        if($videoListc
            && !(is_array($videoListc) && isset($videoListc['code']) && $videoListc['code'] !== HoloApp::RESULT_CODE_SUCCESS)
            && $videoList = $videoListc->getDataList()) {

            $posts['result'] = 'success';
            $posts['data'] = array();
            foreach($videoList as $videoc) {
                $posts['data'][] = $videoc->getData();
            }
        } else {
            $posts['result'] = 'video not found';
            $status = 404;
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    public function checkGameChannelVideos($id) {

        $posts = array();
        $status = 200;

        $holoApp = new HoloApp();
        $videoListc = $holoApp->channelVideosDS( $id );
        if($videoListc
            && $videoList = $videoListc->getDataList()) {

            $result = $holoApp->checkGameTitle($videoList);
            if(isset($result['changed']) && count($result['changed']) > 0) {
                $holoApp->ytdm->saveBatch($result['changed']);
            }
            $posts['result'] = 'success';
            $posts['data'] = array();
            foreach($result['changed'] as $video) {
                $posts['data'][] = $video->getData();
            }
        } else {
            $posts['result'] = 'video not found';
            $status = 404;
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    function testInstantUpgrade($id) {

        $posts = array();
        $status = 200;

        $holoApp = new HoloApp();
        $videoListc = $holoApp->channelVideosDS( $id );
        if($videoListc
            && $videoList = $videoListc->getDataList()) {

            $targetVideos= array();
            foreach($videoList as $video){
                if($video->get('gameId')) {
                    $targetVideos[] = $video;
                }
            }
            $result = $holoApp->upgradeInstant($targetVideos);

            $saveDataList = array_merge($result['create'], $result['update']);
            if(count($saveDataList) > 0) {
                $holoApp->ytdm->saveBatch($saveDataList);
            }
            $posts['result'] = 'success';
            $posts['data'] = array();
            foreach($saveDataList as $video) {
                $posts['data'][] = $video->getData();
            }
        } else {
            $posts['result'] = 'video not found';
            $status = 404;
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);

    }

    function updateTest($id) {

        $posts = array();
        $status = 200;

        $holoApp = new HoloApp();
        $videoListc = $holoApp->newVideosDS( $id );
        if($videoListc
            && $videoList = $videoListc->getDataList()) {

            $result = $holoApp->updateVideos($videoList);

            $saveDataList = array_merge($result['create'], $result['update']);
            if(count($saveDataList) > 0) {
                $holoApp->ytdm->saveBatch($saveDataList);
            }
            $posts['result'] = 'success';
            $posts['data'] = array();
            foreach($saveDataList as $video) {
                $posts['data'][] = $video->getData();
            }
        } else {
            $posts['result'] = 'video not found';
            $status = 404;
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);

    }

////////////////////////////////////////////////////////////////////////////////

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
