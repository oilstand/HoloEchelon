<?php

namespace App\Library;

use App\Library\Utility;
use App\Library\YTDManager;
use App\Library\BaseYTD;
use Google\Cloud\Datastore\Query\Query;

class HoloApp
{
    const RESULT_CODE_NOTFOUND = 404;
    const RESULT_CODE_SUCCESS = 200;

    const CHANNEL_VIDEO_SEARCH_INTERVAL = "P1D";

    function __construct() {
        $this->ytdm = new YTDManager();
    }

    function upgradeInstant($videoList) {
        $ids = array();
        foreach($videoList as $video) {
            if($video->getType() === BaseYTD::YTD_TYPE_INSTANT) {
                $ids[] = $video->getId();
            }
        }
        $newVideoListc = $this->ytdm->getDataNoCache(YTDManager::TYPE_VIDEOS, $ids);
        $newVideoList = array();
        if($newVideoListc) {
            $newVideoList = $newVideoListc->getDataList();
            foreach($newVideoList as $video ) {
                $video->updateData(array('type'=>''));
            }
        }
        return $this->videoListMerge($videoList, $newVideoList);
    }

    function updateVideos($videoList) {
        $ids = array();
        foreach($videoList as $video) {
            if($video) {
                $ids[] = $video->getId();
            }
        }
        $newVideoListc = $this->ytdm->getDataNoCache(YTDManager::TYPE_VIDEOS, $ids);
        $newVideoList = array();
        if($newVideoListc) {
            $newVideoList = $newVideoListc->getDataList();
            foreach($newVideoList as $video ) {
                $video->updateData(array('type'=>''));
            }
        }
        return $this->videoListMerge($videoList, $newVideoList);
    }

    function checkGameTitle( &$videos ) {
        mb_regex_encoding("UTF-8");
        mb_internal_encoding("UTF-8");

        $gameMatches = array(
            1=>array(
                'MINECRAFT',
                'マインクラフト',
                'マイクラ'
            )
        );

        $changed = array();
        $skipped = array();

        foreach( $videos as $video ) {
            if($video->get('gameId') === FALSE) {
                $title = mb_strtoupper(mb_convert_kana($video->get('title'),'asK'));
                $gameId = FALSE;
                foreach( $gameMatches as $idx => $patterns ) {
                    foreach( $patterns as $pattern ) {
                        if(strpos($title, $pattern) !== FALSE) {
                            $gameId = $idx;
                            break 2;
                        }
                    }
                }
                if($gameId !== FALSE) {
                    $video->updateData(array('gameId'=>$gameId));
                    $changed[] = $video;
                } else {
                    $skipped[] = $video;
                }

            }
        }

        return array('changed'=>$changed,'skipped'=>$skipped);
    }

    function videoListMerge($originList, $newList) {
        $originKV = YTDManager::convertArray2KeyValue($originList);

        $saveDataList = array();
        $saveDataList['create'] = array();
        $saveDataList['update'] = array();
        $saveDataList['skip'] = array();

        foreach( $newList as $newVideo ) {
            $id = $newVideo->getId();
            if(isset($originKV[$id])) {
                if($originKV[$id]->getType() !== BaseYTD::YTD_TYPE_INSTANT
                    && $newVideo->getType() === BaseYTD::YTD_TYPE_INSTANT) {
                    continue;
                }
                $data = $newVideo->getData();
                $diff = $originKV[$id]->compare($data);

                if(!empty($diff)) {
                    $originKV[$id]->updateData($diff);
                    $originKV[$id]->updateUpdatedAt();
                    $saveDataList['update'][] = $originKV[$id];
                } else{
                    $saveDataList['skip'][] = $originKV[$id];
                }
            } else {
                $saveDataList['create'][] = $newVideo;
            }
        }
        return $saveDataList;
    }

    function searchChannelVideos($id) {
        // channnel video search
        $channelVideos = $this->ytdm->getDataNoCache(YTDManager::TYPE_SEARCH_CHANNEL_VIDEOS, $id);
        if( $channelVideos
            && $apiVideos = $channelVideos->getDataList() ) {

            $videoIdList = $channelVideos->getId();
            $dsVideoDataList = $this->ytdm->getDataListFromDS(YTDManager::TYPE_VIDEOS, $videoIdList );

            $dsVideos = array();
            if($dsVideoDataList) {
                $dsVideos = $dsVideoDataList->getDataList();
            }

            $merged = $this->videoListMerge($dsVideos, $apiVideos);

            $saveDataList = array_merge($merged['create'], $merged['update']);

            if(!empty($saveDataList)) {
                $this->checkGameTitle($saveDataList);
                $this->ytdm->saveBatch($saveDataList);
            }
            return TRUE;
        }
        return FALSE;
    }
    function getChannelVideos($id) {

        $channel = $this->ytdm->getData(YTDManager::TYPE_CHANNEL, $id );
        if($channel
            && $channelData = $channel->getData() ) {

            $needRefresh = TRUE;

            if(isset($channelData['videoSearchAt'])) {

                $now = new \DateTime();
                $updateAt = new \DateTime($channelData['videoSearchAt']);
                $interval = new \DateInterval(static::CHANNEL_VIDEO_SEARCH_INTERVAL);

                if($updateAt->add($interval) < $now) {
                } else {
                    $needRefresh = FALSE;
                }
            }

            if($needRefresh) {
                if($this->searchChannelVideos($id)) {
                    $channel->updateData(array('videoSearchAt'=>BaseYTD::getDatetimeNowStr()));
                    $this->ytdm->save($channel);/**/
                }
                $videoListClass = $this->channelVideosDS($id);
            } else {
                $videoListClass = $this->channelVideosDS($id);

                if($videoListClass) {
                    $videoList = $videoListClass->getDataList();
                    $result = $this->checkGameTitle($videoList);
                    if(isset($result['changed']) && count($result['changed']) > 0) {
                        $this->ytdm->saveBatch($result['changed']);
                    }/**/
                }
            }

            return $videoListClass;
        } else {
            return self::fail(self::RESULT_CODE_NOTFOUND, 'channel not found');
        }
    }

    function channelVideosDS($id) {

        $query = $this->ytdm->query()
            ->kind('video')
            ->filter('channelId', '=', $id)
            ->order('publishedAt', Query::ORDER_DESCENDING)
            ->limit(50);

        $videoListClass = $this->ytdm->getDataListFromDSQuery('videos', $query);

        return $videoListClass
                ? $videoListClass
                : self::fail(self::RESULT_CODE_NOTFOUND, 'video not found');
    }

    function newVideosDS($page = 0) {

        $query = $this->ytdm->query()
            ->kind('video')
            ->order('actualStartTime', Query::ORDER_DESCENDING)
            ->limit(100)->offset(100 * $page);

        $videoListClass = $this->ytdm->getDataListFromDSQuery('videos', $query);

        return $videoListClass
                ? $videoListClass
                : self::fail(self::RESULT_CODE_NOTFOUND, 'video not found');
    }

    function gameVideosDS($id) {

        $query = $this->ytdm->query()
            ->kind('video')
            ->filter('gameId', '=', $id)
            ->order('actualStartTime', Query::ORDER_DESCENDING)
            ->limit(100);

        $videoListClass = $this->ytdm->getDataListFromDSQuery('videos', $query);

        return $videoListClass
                ? $videoListClass
                : self::fail(self::RESULT_CODE_NOTFOUND, 'video not found');
    }

    private static function fail($code, $message, $data = NULL) {
        return self::result($code, $data, $message);
    }

    private static function success($data) {
        return self::result(self::RESULT_CODE_SUCCESS, $data);
    }

    private static function result( $code, $data, $message = '' ) {
        return array('code'=>$code,'data'=>$data,'message'=>$message);
    }
}

