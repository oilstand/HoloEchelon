<?php

namespace App\Library\Twitter;

use App\Library\Twitter\TSearchData;
use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterAPI {

    function __construct() {
        $this->client = new TwitterOAuth(
            getenv('TWITTER_API_KEY'),
            getenv('TWITTER_API_SECRET'),
            getenv('TWITTER_TOKEN'),
            getenv('TWITTER_TOKEN_SECRET')
        );
    }

    function search( $keyword, $addParams = array() ) {
        return $this->search_tweet( $keyword.' -RT', $addParams );
    }

    function search_tweet( $keyword, $addParams = array() ) {

        $params = array(
            'q' => $keyword,
            'count' => 100,
            'tweet_mode'=>'extended',
//            'since_id'=>"".$this->data['sinceId'],
//            'max_id'=>"".$maxId
        );

        return $this->get_api('search/tweets', $addParams + $params);
    }

    function get_api($method, $params) {

        $apiResult = NULL;
        $code = 0;  $msg = "";
        try {
            $apiResult = $this->client->get($method, $params);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $msg = '捕捉した例外: '.$e->getMessage();
        }

        $apiResultClass = new TSearchData(
            $this,
            $apiResult,
            array('code'=>$code,'msg'=>$msg)
        );

        //unset($apiResult);
        //var_dump($apiResultClass);

        return $apiResultClass;
    }

}
