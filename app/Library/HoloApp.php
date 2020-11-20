<?php

namespace App\Library;

use App\Library\Utility;
use App\Library\YTDManager;
use App\Library\BaseYTD;
use Google\Cloud\Datastore\Query\Query;

class HoloApp
{
    const RESULT_TYPE_NOTFOUND = 404;
    const RESULT_TYPE_SUCCESS = 200;

    const CHANNEL_VIDEO_SEARCH_INTERVAL = "P1D";

    function __construct() {
        $this->ytdm = new YTDManager();
    }

    function searchChannelVideos($id) {
        // channnel video search
        $channelVideos = $this->ytdm->getDataNoCache(YTDManager::TYPE_SEARCH_CHANNEL_VIDEOS, $id);
        if( $channelVideos
            && $apiVideos = $channelVideos->getDataList() ) {

            $videoIdList = $channelVideos->getId();
            $dsVideoDataList = $this->ytdm->getDataListFromDS(YTDManager::TYPE_VIDEOS, $videoIdList );

            $dsVideos = $dsVideoDataList->getDataList();
            $dsVideosKV = YTDManager::convertArray2KeyValue($dsVideos);

            $saveDataList = array();

            foreach( $apiVideos as $apiVideo ) {
                $id = $apiVideo->getId();
                if(isset($dsVideosKV[$id])) {
                    if($dsVideosKV[$id]->getType() !== BaseYTD::YTD_TYPE_INSTANT) {
                        continue;
                    }
                    $data = $apiVideo->getData();
                    $diff = $dsVideosKV[$id]->compare($data);

                    if(!empty($diff)) {
                        $dsVideosKV[$id]->updateData($diff);
                        $dsVideosKV[$id]->updateUpdatedAt();
                        $saveDataList[] = $dsVideosKV[$id];
                    }
                } else {
                    $saveDataList[] = $apiVideo;
                }
            }
            if(!empty($saveDataList)) {
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
            }
            $query = $this->ytdm->query()
                ->kind('video')
                ->filter('channelId', '=', $id)
                ->order('publishedAt', Query::ORDER_DESCENDING)
                ->limit(50);

            $videoListClass = $this->ytdm->getDataListFromDSQuery('videos', $query);

            return $videoListClass
                    ? $videoListClass
                    : self::fail(self::RESULT_TYPE_NOTFOUND, 'video not found');

        } else {
            return self::fail(self::RESULT_TYPE_NOTFOUND, 'channel not found');
        }
    }

    private static function fail($type, $message, $data = NULL) {
        return self::result($type, $data, $message);
    }

    private static function success($data) {
        return self::result(self::RESULT_TYPE_SUCCESS, $data);
    }

    private static function result( $type, $data, $message = '' ) {
        return array('type'=>$type,'data'=>$data,'message'=>$message);
    }
}

