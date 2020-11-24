<?php

namespace App\Library;

use App\Library\BaseYTD;

class VideoData extends BaseYTD
{
    const YTD_KIND = 'video';
    const YTD_REFRESH_TIME = 'P2D';
    const YTD_LIST = array(
        'id','title','description','thumbnails','publishedAt',
        'channelId','channelTitle','liveBroadcastContent','embeddable',
        'statistics','duration','type','actualStartTime','actualEndTime','scheduledStartTime',
        'gameId'
    );
    const YTD_NOINDEX = array(
        'description','thumbnails','channelTitle','liveBroadcastContent',
        'embeddable','statistics','liveStreamingDetails','duration'
    );
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
        array(  'src'=>array('snippet','channelId'),
                'dst'=>array('channelId') ),
        array(  'src'=>array('snippet','channelTitle'),
                'dst'=>array('channelTitle') ),
        array(  'src'=>array('snippet','liveBroadcastContent'),
                'dst'=>array('liveBroadcastContent') ),
        array(  'src'=>array('status','embeddable'),
                'dst'=>array('embeddable') ),
        array(  'src'=>array('statistics'),
                'dst'=>array('statistics') ),
        array(  'src'=>array('liveStreamingDetails','actualStartTime'),
                'dst'=>array('actualStartTime') ),
        array(  'src'=>array('liveStreamingDetails','actualEndTime'),
                'dst'=>array('actualEndTime') ),
        array(  'src'=>array('liveStreamingDetails','scheduledStartTime'),
                'dst'=>array('scheduledStartTime') ),
        array(  'src'=>array('contentDetails','duration'),
                'dst'=>array('duration') ),
        array(  'src'=>array('type'),
                'dst'=>array('type') ),
    );

    function needRefresh() {
        return (parent::needRefresh() || $this->getType() === BaseYTD::YTD_TYPE_INSTANT);
    }
}

