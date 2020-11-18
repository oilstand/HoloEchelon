<?php

namespace App\Library;

use App\Library\BaseYTDList;
use App\Library\VideoData;

class SearchVideoResult extends BaseYTDList
{
    const YTD_KIND = 'channelVideos';
    const YTD_REFRESH_TIME = 'P2D';
    const YTD_LIST = array();
    const YTD_NOINDEX = array();
    const YTD_API_DATA_MAP = array();
    const YTDL_ALLOW_YTDTYPE = array(
        __NAMESPACE__ . '\\' . 'VideoData'
    );

    function setDataFromAPIResult( $data ) {

        $video = new VideoData();
        $data['id'] = $data['id']['videoId'];
        $data['type'] = self::YTD_TYPE_INSTANT;
        $video->setDataFromAPIResult($data);
        $this->data[] = $video;

        $this->updatedAt = self::getDatetimeNowStr();
    }

}

