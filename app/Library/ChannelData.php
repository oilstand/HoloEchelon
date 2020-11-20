<?php

namespace App\Library;

use App\Library\BaseYTD;

class ChannelData extends BaseYTD
{
    const YTD_KIND = 'channel';
    const YTD_REFRESH_TIME = 'P1D';
    const YTD_LIST = array(
        'id','title','description','thumbnails','publishedAt',
        'subscriberCount','viewCount','videoCount','videoSearchAt'
    );
    const YTD_NOINDEX = array('description', 'thumbnails');
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
}

