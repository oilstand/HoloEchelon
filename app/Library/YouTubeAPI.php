<?php

namespace App\Library;

use GuzzleHttp\Client;
use App\Library\Utility;

class YouTubeAPI
{
    const API_BASE_URL = "https://content.googleapis.com/youtube/v3/";
    const API_S_NONE    = "none";
    const API_S_SUCCESS = "success";
    const API_S_FAILED  = "failed";

    function __construct() {
        $this->APIKey = getenv('YT_API_KEY');
    }

    public function getChannelVideos( $channelId ) {
        $res = $this->searchVideos(
                array(
                    'channelId'=>$channelId,
                    'order'=>'date'
                )
            );
        return $res ? $res : false;
    }

    public function searchVideos( $params ) {
        $res = $this->get_api(
            'search',
            $params + array(
                    'part'=>'id,snippet',
                    'type'=>'video',
                    'order'=>'date'
                )
            );
        return $res ? $res : false;
    }

    public function getChannel( $channelId ){
        $res = $this->getChannels(array($channelId));
        return $res ? $res : false;
    }

    public function getChannels( $channelIds ){
        $res = $this->get_api(
            'channels',
            array(
                'part'=>'id,snippet,statistics',
                //,topicDetails,contentDetails,brandingSettings
                'id'=>implode(',',$channelIds)
                )
            );
        return $res ? $res : false;
    }

    public function getVideo( $videoId ){
        $res = $this->getVideos(array($videoId));
        return $res ? $res : false;
    }

    public function getVideos( $videoIds ){
        $res = $this->get_api(
            'videos',
            array(
                'part'=>'id,snippet,contentDetails,liveStreamingDetails,statistics,status',
                //,player,fileDetails,processingDetails,suggestions,recordingDetails,topicDetails
                'id'=>implode(',',$videoIds)
                )
            );
        return $res ? $res : false;
    }

    private function get_api( $method, $params ) {
        $url = self::API_BASE_URL.$method;

        $paramStr = "?";
        foreach((array)$params as $key => $val) {
            if($paramStr !== "?")$paramStr .= "&";
            $paramStr .= "${key}=${val}";
        }

        $response = $this->request("GET",$url.$paramStr);
        $success = false;

        $status = self::API_S_NONE;
        if( $response
            && $response->getStatusCode() == 200){
            $rawbody = $response->getBody();
            $bodyobj = json_decode($rawbody, true);
            $status = self::API_S_SUCCESS;
        } else{
            $status = self::API_S_FAILED;
            $bodyobj = array();
        }

        return array(
            'status' => $status,
            'body' => $bodyobj,
            'response'=>$response
        );
    }

    private function request( $method, $url ) {

        $url .= "&key=".$this->APIKey;

        $client = new Client();

        try {
            $response = $client->request($method, $url);
        } catch(\Exception $e) {
            $response = FALSE;
            // 外からエラーを検知する方法を用意する必要がある
        }

        return $response;
    }

    public static function getBodyFromResponse( $response ) {
        if($response
            && $response['status'] === self::API_S_SUCCESS
            && $response['body'] ) {

            return $response['body'];
        }
        return FALSE;
    }
}

