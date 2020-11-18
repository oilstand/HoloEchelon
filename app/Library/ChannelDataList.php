<?php

namespace App\Library;

use App\Library\BaseYTDList;
use App\Library\ChannelData;

class ChannelDataList extends BaseYTDList
{
    const YTD_KIND = 'channel';
    const YTD_REFRESH_TIME = 'P2D';
    const YTD_LIST = array();
    const YTD_NOINDEX = array();
    const YTD_API_DATA_MAP = array();
    const YTDL_ALLOW_YTDTYPE = array(
        __NAMESPACE__ . '\\' . 'ChannelData'
    );

    function updateData( $data, $updateCreatedAt = true, $updateUpdatedAt = true ) {

        foreach($data as $dat) {
            $this->data[] = new ChannelData($dat);
        }

        /*if($updateCreatedAt
            && isset($data['createdAt']) && $data['createdAt'] != '') {
            $this->createdAt = $data['createdAt'];
        }
        if($updateUpdatedAt
            && isset($data['updatedAt']) && $data['updatedAt'] != '') {
            $this->updatedAt = $data['updatedAt'];
        }*/
    }

    function setDataFromAPIResult( $data ) {

        $channel = new ChannelData();
        $channel->setDataFromAPIResult($data);
        $this->data[] = $channel;

        $this->updatedAt = self::getDatetimeNowStr();
    }

}

