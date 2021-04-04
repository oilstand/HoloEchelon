<?php

namespace App\Library\Apex;

use GuzzleHttp\Client;
use App\Library\Utility;

class ApexAPIResponse
{
    const S_NONE    = "none";
    const S_SUCCESS = "success";
    const S_FAILED  = "failed";

    public $response = null;
    public $status = self::S_NONE;
    private $dataBody = false;

    function __construct($response) {
        if($response && $response->getStatusCode() == 200) {
            $this->status = self::S_SUCCESS;
            $this->response = $response;
        } else {
            $this->status = self::S_FAILED;
        }
    }
    public function get() {
        if($this->status == self::S_SUCCESS && $this->dataBody == false) {
            $this->dataBody = json_decode($this->response->getBody(), true);
        }
        return $this->dataBody;
    }
}
class ApexAPI
{
    const API_BASE_URL = "https://public-api.tracker.gg/v2/apex/standard/";

    const PLAT_ORIGIN = "origin";
    const PLAT_PSN = "psn";
    const PLAT_XBL = "xbl";

    function __construct() {
        $this->APIKey = getenv('APEX_API_KEY');
    }

    public function getProfile( $platform, $userId ){
        return $this->get_api(
            "profile/${platform}/${userId}",
            []
        );
    }

    private function get_api( $method, $params ) {
        $paramStr = "?";
        foreach((array)$params as $key => $val) {
            if($paramStr !== "?")$paramStr .= "&";
            $paramStr .= "${key}=${val}";
        }

        $response = $this->request("GET", self::API_BASE_URL.$method.$paramStr);

        return new ApexAPIResponse($response);
    }

    private function request( $method, $url ) {

        $client = new Client();

        try {
            $response = $client->request($method, $url, [
                'headers' => [
                    'TRN-Api-Key' => $this->APIKey
                ]
            ]);
        } catch(\Exception $e) {
            $response = FALSE;
            // 外からエラーを検知する方法を用意する必要がある
        }

        return $response;
    }
}

