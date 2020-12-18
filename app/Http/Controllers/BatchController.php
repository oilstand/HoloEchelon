<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Library\YouTubeAPI;
use App\Library\YTDManager;
use App\Library\BaseYTD;
use App\Library\QuoteVideoData;
use App\Library\HoloApp;
use App\Library\Twitter\TwitterAPI;
use Abraham\TwitterOAuth\TwitterOAuth;
use Google\Cloud\Datastore\Query\Query;


class BatchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * TwitterSearchBatch
     */
    public function twitter() {

        function dumpMemory()
        {
            static $initialMemoryUse = null;

            if ( $initialMemoryUse === null )
            {
                $initialMemoryUse = memory_get_usage();
                var_dump("start:".number_format($initialMemoryUse));
            }

            var_dump(number_format(memory_get_usage() - $initialMemoryUse));
        }

        $status = 200;
        $posts = array();

        $holoApp = new HoloApp();

        $searchLimit = getenv('TWITTER_SEARCH_LIMIT');
        if(!$searchLimit)$searchLimit = 1;

        $query = $holoApp->ytdm->query()
            ->kind('channel')
            ->order('twitterSearchedAt', Query::ORDER_ASCENDING)
            ->limit($searchLimit);

        $idx = array();
        $channelIds = array();
        $saveDataList = array();

        $channelListClass = $holoApp->ytdm->getDataListFromDSQuery('channels', $query);
        if($channelListClass && $channels = $channelListClass->getDataList()) {
            foreach((array)$channels as $channel) {
                $keywords = $channel->get('keywords');
                $channelIds[] = $channel->get('id');

                foreach((array)$keywords as $keyword) {
                    $holoApp->searchTweetVideoIds($keyword.' AND youtu.be', $idx);
                }

                $channel->updateData(array('twitterSearchedAt'=>BaseYTD::getDatetimeNowStr()));
                $saveDataList[] = $channel;
            }
        }

        if(!empty($idx)) {
            $ids = array();
            foreach($idx as $id => $value) {
                $ids[] = $id;
            }
            $posts['targetNum'] = count($ids);

            $requestNum = (int)((count($ids) + 49) / 50);
            $posts['requestNum'] = $requestNum;

            //$posts['targetIds'] = array();

            $rVideos = array();
            $cVideos = array();
            for($i = 0; $i < $requestNum; $i++){
                $targetIds = array_slice($ids, $i * 50, 50);
                //$posts['targetIds'][] = $targetIds;

                $videoListC = $holoApp->ytdm->getDataNoCache(YTDManager::TYPE_VIDEOS, $targetIds);

                if($videoListC && $videos = $videoListC->getDataList() ) {
                    foreach($videos as $video){
                        if(in_array($video->get('channelId'), $channelIds, TRUE)){
                            $cVideos[] = $video->getData();
                            $saveDataList[] = $video;
                        } else {
                            $vdata = $video->getData();
                            preg_match_all(
                                '/http[s]?:\/\/(youtu\.be\/|www\.youtube\.com\/watch\?v=)([a-zA-Z0-9_-]+)/u',
                                $video->get('description'),
                                $matches
                            );
                            if(isset($matches[2]) && !empty($matches[2])) {
                                $vdata['quote'] = $matches[2];
                                $saveDataList[] = new QuoteVideoData($vdata);
                            }
                            $rVideos[] = $vdata;
                        }
                    }
                }

                unset($videoListC);
                unset($videos);
            }
            $posts['videos'] = $rVideos;
            $posts['channelVideos'] = $cVideos;
        }

        if(!empty($saveDataList)) {
            $holoApp->ytdm->saveBatch($saveDataList);
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    public function twitterId($id) {

        function dumpMemory()
        {
            static $initialMemoryUse = null;

            if ( $initialMemoryUse === null )
            {
                $initialMemoryUse = memory_get_usage();
                var_dump("start:".number_format($initialMemoryUse));
            }

            var_dump(number_format(memory_get_usage() - $initialMemoryUse));
        }

        $status = 200;
        $posts = array();

        $holoApp = new HoloApp();

        $query = $holoApp->ytdm->query()
            ->kind('channel')
            ->filter('id', '=', $id);

        $idx = array();
        $channelIds = array();
        $saveDataList = array();

        $channelListClass = $holoApp->ytdm->getDataListFromDSQuery('channels', $query);
        if($channelListClass && $channels = $channelListClass->getDataList()) {
            foreach((array)$channels as $channel) {
                $keywords = $channel->get('keywords');
                $channelIds[] = $channel->get('id');

                foreach((array)$keywords as $keyword) {
                    $holoApp->searchTweetVideoIds($keyword.' AND youtu.be', $idx);
                }

                $channel->updateData(array('twitterSearchedAt'=>BaseYTD::getDatetimeNowStr()));
                $saveDataList[] = $channel;
            }
        }

        $posts['idx'] = $idx;
        if(!empty($idx)) {
            $ids = array();
            foreach($idx as $id => $value) {
                $ids[] = $id;
            }
            //@$posts['ids'] = $ids;
            $videoListC = $holoApp->ytdm->getDataNoCache(YTDManager::TYPE_VIDEOS, $ids);
            $rVideos = array();
            $cVideos = array();

            if($videoListC && $videos = $videoListC->getDataList() ) {
                foreach($videos as $video){
                    if(in_array($video->get('channelId'), $channelIds, TRUE)){
                        $cVideos[] = $video->getData();
                        $saveDataList[] = $video;
                    } else {
                        $vdata = $video->getData();
                        preg_match_all(
                            '/http[s]?:\/\/(youtu\.be\/|www\.youtube\.com\/watch\?v=)([a-zA-Z0-9_-]+)/u',
                            $video->get('description'),
                            $matches
                        );
                        if(isset($matches[2]) && !empty($matches[2])) {
                            $vdata['quote'] = $matches[2];
                            $saveDataList[] = new QuoteVideoData($vdata);
                        }
                        $rVideos[] = $vdata;
                    }
                }
                $posts['videos'] = $rVideos;
                $posts['channelVideos'] = $cVideos;
            }

            unset($videoListC);
            unset($videos);
        }

//        $posts['saveDataList'] = $saveDataList;
        if(!empty($saveDataList)) {
            $holoApp->ytdm->saveBatch($saveDataList);
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }
    /**
     *   Search channel Videos Batch
     */
    public function batchChannelSearchVideos() {

        $status = 200;
        $posts = array();

        $holoApp = new HoloApp();

        $searchLimit = getenv('CHANNEL_SEARCH_LIMIT');
        if(!$searchLimit)$searchLimit = 1;

        $query = $holoApp->ytdm->query()
            ->kind('channel')
            ->order('videoSearchAt', Query::ORDER_ASCENDING)
            ->limit($searchLimit);

        $channelIds = array();
        $saveDataList = array();
        $successCount = 0;

        $channelListClass = $holoApp->ytdm->getDataListFromDSQuery('channels', $query);
        if($channelListClass && $channels = $channelListClass->getDataList()) {
            foreach((array)$channels as $channel) {
                $channelId = $channel->get('id');
                $channelIds[] = $channelId;

                $channelVideoList = $holoApp->searchChannelVideos($channelId);
                if($channelVideoList) {
                    $vIds = $channelVideoList->getId();
                    $dsVideoListc = $holoApp->ytdm->getDataListFromDS(YTDManager::TYPE_VIDEOS, $vIds );
                    $apiVideoListc = $holoApp->ytdm->getDataNoCache(YTDManager::TYPE_VIDEOS, $vIds);

                    $dsVideos = array();
                    $apiVideos = array();
                    if($dsVideoListc) {
                        $dsVideos = $dsVideoListc->getDataList();
                    }
                    if($apiVideoListc) {
                        $apiVideos = $apiVideoListc->getDataList();
                    }
                    $result = $holoApp->videoListMerge($dsVideos, $apiVideos);
                    $saveDataList = array_merge($saveDataList, $result['create']);
                    $saveDataList = array_merge($saveDataList, $result['update']);

                    $channel->updateData(array('videoSearchAt'=>BaseYTD::getDatetimeNowStr()));
                    $saveDataList[] = $channel;
                    $successCount++;
                }
            }
        }

//        $posts['merged'] = $result;
//        $posts['savedata'] = $saveDataList;
        $posts['target'] = $channelIds;
        $posts['success'] = $successCount;

        if(!empty($saveDataList)) {
            $holoApp->ytdm->saveBatch($saveDataList);
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    /**
     *
     */
    public function batchUpdateLiveOrComingVideos() {
        $status = 200;
        $posts = array();

        $holoApp = new HoloApp();

        $targetVideos = array();
        $vIds = array();

        $query = $holoApp->ytdm->query()
            ->kind('video')
            ->filter('liveBroadcastContent', '=', 'upcoming')
            ->limit(50);
        $videoListClass = $holoApp->ytdm->getDataListFromDSQuery('videos', $query);
        if($videoListClass && $videos = $videoListClass->getDataList()){
            $targetVideos = $videos;
            $vIds = $videoListClass->getId();
        }

        $query2 = $holoApp->ytdm->query()
            ->kind('video')
            ->filter('liveBroadcastContent', '=', 'live')
            ->limit(50);
        $videoList2Class = $holoApp->ytdm->getDataListFromDSQuery('videos', $query2);
        if($videoList2Class && $videos = $videoList2Class->getDataList()){
            $targetVideos = array_merge($targetVideos, $videos);
            $vIds = array_merge($vIds, $videoList2Class->getId());
        }

        $saveDataList = array();
        if(!empty($targetVideos)) {

            $apiVideoListc = $holoApp->ytdm->getDataNoCache(YTDManager::TYPE_VIDEOS, $vIds);

            $dsVideos = $targetVideos;
            $apiVideos = array();
            $apiVideoIds = array();
            if($apiVideoListc) {
                $apiVideos = $apiVideoListc->getDataList();
                $apiVideoIds = $apiVideoListc->getId();
            }
            $result = $holoApp->videoListMerge($dsVideos, $apiVideos);

            $saveDataList = array_merge($result['update'], $result['create']);

            $notFoundIds = array();
            foreach($vIds as $vId) {
                if(!in_array($vId, $apiVideoIds, TRUE)) {
                    $notFoundIds[] = $vId;
                }
            }
            if(!empty($notFoundIds)) {
                foreach($notFoundIds as $notFoundId) {
                    foreach((array)$targetVideos as $videoc) {
                        if($videoc->getId() === $notFoundId) {
                            $videoc->updateData(array('liveBroadcastContent'=>'suspended'));
                            $saveDataList[] = $videoc;
                            break;
                        }
                    }
                }
            }
            $posts['notfound'] = $notFoundIds;
        }
        if(!empty($saveDataList)) {
            $holoApp->ytdm->saveBatch($saveDataList);
        }

        $posts['targets'] = $vIds;
//        $posts['retv'] = $apiVideos;
        $posts['saved'] = $saveDataList;
        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    public function batchTwitterSearchOfficialTweets(){
        $status = 200;
        $posts = array();
        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

    // list:1339139547653840896 url:youtu.be

    /**
     *      channelデータ twitterSearchedAt/videoSearchAt 穴埋め用
     */
    private function batchChannelSetTwitterSearchedAt( $holoApp ) {
        $query = $holoApp->ytdm->query()
            ->kind('channel');

        $channelListClass = $holoApp->ytdm->getDataListFromDSQuery('channels', $query);
        $updList = array();
        if($channelListClass && $dataList = $channelListClass->getDataList() ) {
            foreach((array)$dataList as $channel){
                if($channel->get('twitterSearchedAt') == FALSE || $channel->get('videoSearchAt') == FALSE) {
                    if($channel->get('twitterSearchedAt') == FALSE) {
                        $channel->updateData(array('twitterSearchedAt'=>'2000-01-01 00:00:00+09:00'));
                    }
                    if($channel->get('videoSearchAt') == FALSE) {
                        $channel->updateData(array('videoSearchAt'=>'2000-01-01 00:00:00+09:00'));
                    }
                    $updList[] = $channel;
                }
            }
            if(!empty($updList)) {
                $holoApp->ytdm->saveBatch($updList);
            }
        }

    }

    /**
     *      scheduledStartTime新しい順にデータを更新する
     */
    public function batchUpdateNewVideos() {
        $status = 200;
        $posts = array();

        $holoApp = new HoloApp();
        $saveDataList = array();
        $videoListc = $holoApp->newVideosDS( 0 );
        if($videoListc) {

            $vIds = $videoListc->getId();

            //$dsVideoListc = $holoApp->ytdm->getDataListFromDS(YTDManager::TYPE_VIDEOS, $vIds );
            $apiVideoListc = $holoApp->ytdm->getDataNoCache(YTDManager::TYPE_VIDEOS, $vIds);

            $dsVideos = array();
            $apiVideos = array();
            if($videoListc) {
                $dsVideos = $videoListc->getDataList();
            }
            if($apiVideoListc) {
                $apiVideos = $apiVideoListc->getDataList();
            }
            $result = $holoApp->videoListMerge($dsVideos, $apiVideos);
            //var_dump($result);
            $saveDataList = array_merge($result['update'], $result['create']);
        }
        if(!empty($saveDataList)) {
            $holoApp->ytdm->saveBatch($saveDataList);
        }
        $posts = $saveDataList;
        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }

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
        $query = $yt->query()->kind('channel')->limit(40);

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

    public function quoteVideos($id) {
        $posts = array();
        $status = 200;

        $holoApp = new HoloApp();

        $targetVideos = array();
        $vIds = array();

        $query = $holoApp->ytdm->query()
            ->kind('quoteVideo')
            ->filter('quote', '=', $id)
            ->limit(50);
        $videoListClass = $holoApp->ytdm->getDataListFromDSQuery('quoteVideos', $query);
        if($videoListClass && $videoList = $videoListClass->getDataList()){
            $posts['data'] = array();
            $posts['result'] = 'success';
            foreach($videoList as $videoc) {
                $posts['data'][] = $videoc->getData();
            }
        } else {
            $posts['result'] = 'notfound';
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);

    }

    public function videos(Request $request) {
        $posts = array('api'=>'videos');
        $status = 200;

        $holoApp = new HoloApp();

        $rawDateStr = $request->input('date', '');
        $range = (int)$request->input('range', 1);
        $range = $range > 0 && $range <= 3 ? $range : 1;
        preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/', $rawDateStr, $result);

        if( isset($result[1])
            && isset($result[2])
            && isset($result[3]) ) {
            $dateStr = "${result[1]}-${result[2]}-${result[3]} 23:59:59+09:00";
            $sinceDate = new \DateTime($dateStr);
            $sinceDate->setTimezone(new \DateTimeZone('GMT'));
            $untilDate = new \DateTime($dateStr);
            $untilDate->setTimezone(new \DateTimeZone('GMT'));
            $interval = new \DateInterval("P${range}D");
            $sinceDate->sub($interval);

            $posts['sinceDate'] = $sinceDate->format(DATE_ATOM);
            $posts['untilDate'] = $untilDate->format(DATE_ATOM);

            $query = $holoApp->ytdm->query()
                ->kind('video')
                ->filter('scheduledStartTime', '>', $sinceDate->format(DATE_ATOM))
                ->filter('scheduledStartTime', '<=', $untilDate->format(DATE_ATOM));
            $videoListClass = $holoApp->ytdm->getDataListFromDSQuery('videos', $query);

            if($videoListClass && $videoList = $videoListClass->getDataList()){
                $posts['data'] = array();
                $posts['result'] = 'success';
                foreach($videoList as $videoc) {
                    $posts['data'][] = $videoc->getData();
                }
            } else {
                $posts['result'] = 'notfound';
            }
        }

        return response()->json($posts, $status)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header("Access-Control-Allow-Origin" , $this->CORS_ORIGIN);
    }


///////////////////////////////////////////////////////////////////////



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
}
