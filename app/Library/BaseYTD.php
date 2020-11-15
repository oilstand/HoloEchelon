<?php

namespace App\Library;

use App\Library\Utility;

class BaseYTD
{
    // ds kind
    const YTD_KIND = 'base';
    // copy list
    const YTD_LIST = array();
    // ds noindex list
    const YTD_NOINDEX = array();
    // api raw data convert mapping data
    const YTD_API_DATA_MAP = array();
    // time zone
    const YTD_TIMEZONE = 'Asia/Tokyo';
    // refresh time
    const YTD_REFRESH_TIME = 'P1DT1H';

    function __construct( $data = array() ) {
        $this->createdAt = self::getDatetimeNowStr();
        $this->setData($data);
    }

    function setData( $data ) {
        $this->data = array();
        Utility::copyArrayListed($data, $this->data, static::YTD_LIST);

        if(isset($data['createdAt']) && $data['createdAt'] != '') {
            $this->createdAt = $data['createdAt'];
        }
        if(isset($data['updatedAt']) && $data['updatedAt'] != '') {
            $this->updatedAt = $data['updatedAt'];
        }
    }

    function setDataFromAPIResult( $data ) {
        $this->apiRawData = $data;
        $this->data = Utility::convertArray($data, static::YTD_API_DATA_MAP);

        $this->updatedAt = self::getDatetimeNowStr();
    }

    function checkData() {
        if(static::YTD_KIND !== self::YTD_KIND
            && $this->getId() !== FALSE && $this->getId() != '') {

            return TRUE;
        }
        return FALSE;
    }

    function needRefresh() {
        $now = new \DateTime();
        $updateAt = new \DateTime($this->updatedAt);
        $interval = new \DateInterval(static::YTD_REFRESH_TIME);
        $updateAt->add($interval);
        if($updateAt < $now) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function getData() {
        $this->data['createdAt'] = $this->createdAt;
        $this->data['updatedAt'] = $this->updatedAt;
        return $this->data;
    }

    function getId() {
        return isset($this->data['id']) ? $this->data['id'] : FALSE;
    }

    function getKind() {
        return static::YTD_KIND;
    }

    function getNoindex() {
        return static::YTD_NOINDEX;
    }

    static function getDatetimeNowStr() {
        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone(self::YTD_TIMEZONE));
        return $now->format('Y-m-d H:i:sP');
    }

}

