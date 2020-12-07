<?php

namespace App\Library\Twitter;

use App\Library\Utility;

// Twitter Response Data
class BaseTRD {

    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 99;
    // map list
    const TRD_DATA_MAP = array();

    function __construct( $api, $data = array(), $more = array() ) {
        $this->api = $api;
        $this->more = $more;
        $this->setDataFromAPIResult($data);
    }
    function __destruct ( ) {
        unset($this->data);
    }

    function setDataFromAPIResult( $data ) {
        $this->apiRawData = $data;
//var_dump((array)$data);
        $this->data = Utility::convertArray((array)$data, static::TRD_DATA_MAP);
    }

    function getData() {
        return isset($this->data['data']) ? $this->data['data'] : FALSE;
    }

    function get($index) {
        if(isset($this->data[$index])) {
            return $this->data[$index];
        } else {
            return FALSE;
        }
    }

    function getMeta() {
        return isset($this->data['meta']) ? $this->data['meta'] : FALSE;
    }

}
