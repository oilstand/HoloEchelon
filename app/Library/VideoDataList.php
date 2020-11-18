<?php

namespace App\Library;

use App\Library\BaseYTDList;
use App\Library\VideoData;

class VideoDataList extends BaseYTDList
{
    const YTD_KIND = 'video';
    const YTD_REFRESH_TIME = 'P2D';
    const YTD_LIST = array();
    const YTD_NOINDEX = array();
    const YTD_API_DATA_MAP = array();
    const YTDL_ALLOW_YTDTYPE = array(
        __NAMESPACE__ . '\\' . 'VideoData'
    );

    function updateData( $data, $updateCreatedAt = true, $updateUpdatedAt = true ) {

        foreach($data as $dat) {
            $this->data[] = new VideoData($dat);
        }

        if($updateCreatedAt
            && isset($data['createdAt']) && $data['createdAt'] != '') {
            $this->createdAt = $data['createdAt'];
        }
        if($updateUpdatedAt
            && isset($data['updatedAt']) && $data['updatedAt'] != '') {
            $this->updatedAt = $data['updatedAt'];
        }
    }

    function setDataFromAPIResult( $data ) {

        $video = new VideoData();
        $video->setDataFromAPIResult($data);
        $this->data[] = $video;

        $this->updatedAt = self::getDatetimeNowStr();
    }

}

