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

    const YTD_TYPE_INSTANT = "instant";

    function __construct( $data = array() ) {
        $this->createdAt = self::getDatetimeNowStr();
        $this->changed = FALSE;
        $this->setData($data);
    }

    function setData( $data ) {
        $this->data = array();
        $tmp = isset($this->changed) ? $this->changed : FALSE;
        $this->updateData( $data );
        $this->changed = $tmp;
    }

    function updateData( $data, $updateCreatedAt = true, $updateUpdatedAt = true ) {
        Utility::copyArrayListed($data, $this->data, static::YTD_LIST);

        if($updateCreatedAt
            && isset($data['createdAt']) && $data['createdAt'] != '') {
            $this->createdAt = $data['createdAt'];
        }
        if($updateUpdatedAt
            && isset($data['updatedAt']) && $data['updatedAt'] != '') {
            $this->updatedAt = $data['updatedAt'];
        }
        $this->changed = TRUE;
    }

    function setDataFromAPIResult( $data ) {
        $this->apiRawData = $data;
        $this->data = Utility::convertArray($data, static::YTD_API_DATA_MAP);

        $this->updateUpdatedAt();
        $this->changed = TRUE;
    }

    function updateUpdatedAt() {
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

    function getType() {
        return isset($this->data['type']) ? $this->data['type'] : FALSE;
    }

    function getKind() {
        return static::YTD_KIND;
    }

    function getNoindex() {
        return static::YTD_NOINDEX;
    }

    function compare( $data ) {
        $diffs = array();
        foreach(static::YTD_LIST as $index) {
            if(!isset($this->data[$index])) {
                if(isset($data[$index])) {
                    $diffs[$index] = $data[$index];
                }
            } else if( is_array($this->data[$index]) ) {
                if($diff = self::compareArray($this->data[$index], $data[$index])) {
                    $diffs[$index] = $diff;
                }
            } else if( !is_object($this->data[$index]) ) {
                if(isset($data[$index])
                    && $this->data[$index] !== $data[$index]) {
                    $diffs[$index] = $data[$index];
                }
            } else {
                // objectは比較できない
            }
        }
        return $diffs;
    }

    function get($index) {
        if(isset($this->data[$index])) {
            return $this->data[$index];
        } else {
            return FALSE;
        }
    }

    public static function compareArray( $src1, $src2 ) {
        if(is_array($src1)) {

            $diffs = array();
            foreach($src1 as $key => $val) {
                $diff = self::compareArray($src1[$key], $src2[$key]);
                if($diff) {
                    $diffs[$key] = $diff;
                }
            }
            return empty($diffs) ? FALSE : $diffs;
        } else {
            if($src1 === $src2) {
                return FALSE;
            } else {
                return $src2;
            }
        }
    }

    static function getDatetimeNowStr() {
        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone(self::YTD_TIMEZONE));
        return $now->format('Y-m-d H:i:sP');
    }

}

