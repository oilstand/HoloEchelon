<?php

namespace App\Library;

use App\Library\BaseYTD;

class ChannelData extends BaseYTD
{
    const YTD_KIND = 'channel';
    const YTD_REFRESH_TIME = 'P1D';
    const YTD_LIST = array(
        'id','title','description','thumbnails','publishedAt',
        'subscriberCount','viewCount','videoCount','videoSearchAt',
        'keywords','twitterSearchedAt','color','country'
    );
    const YTD_NOINDEX = array('description', 'thumbnails','color');
    const YTD_API_DATA_MAP = array(
        array(  'src'=>array('id'), 'dst'=>array('id') ),
        array(  'src'=>array('snippet','title'),
                'dst'=>array('title') ),
        array(  'src'=>array('snippet','description'),
                'dst'=>array('description') ),
        array(  'src'=>array('snippet','thumbnails'),
                'dst'=>array('thumbnails') ),
        array(  'src'=>array('snippet','publishedAt'),
                'dst'=>array('publishedAt') ),
        array(  'src'=>array('statistics','subscriberCount'),
                'dst'=>array('subscriberCount') ),
        array(  'src'=>array('statistics','viewCount'),
                'dst'=>array('viewCount') ),
        array(  'src'=>array('statistics','videoCount'),
                'dst'=>array('videoCount') ),
    );
    function updateData( $data, $updateCreatedAt = true, $updateUpdatedAt = true ) {

        parent::updateData( $data, $updateCreatedAt, $updateUpdatedAt );

        if(!isset($this->data['twitterSearchedAt'])) {
            $this->data['twitterSearchedAt'] = "2000-01-01 00:00:00+09:00";
        }
        if(!isset($this->data['videoSearchAt'])) {
            $this->data['videoSearchAt'] = "2000-01-01 00:00:00+09:00";
        }
    }

    function setDataFromAPIResult( $data ) {
        parent::setDataFromAPIResult( $data );

        if(!isset($this->data['twitterSearchedAt'])) {
            $this->data['twitterSearchedAt'] = "2000-01-01 00:00:00+09:00";
        }
        if(!isset($this->data['videoSearchAt'])) {
            $this->data['videoSearchAt'] = "2000-01-01 00:00:00+09:00";
        }
    }
}

