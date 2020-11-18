<?php

namespace App\Library;

use App\Library\BaseYTD;

class BaseYTDList extends BaseYTD
{
    const YTD_KIND = 'baseList';
    const YTD_REFRESH_TIME = 'P2D';
    const YTD_LIST = array();
    const YTD_NOINDEX = array();
    const YTD_API_DATA_MAP = array();
    const YTDL_ALLOW_YTDTYPE = array();

    function updateData( $data, $updateCreatedAt = true, $updateUpdatedAt = true ) {

        /* 継承先で実装すること */

    }

    function setDataFromAPIResult( $data ) {

        /* 継承先で実装すること */

        $this->updatedAt = self::getDatetimeNowStr();
    }

    function getId() {
        $data = $this->getDataList();
        $ids = array();
        foreach((array)$data as $datac) {
            if($datac
                && $id = $datac->getId() ) {

                $ids[] = $id;
            }
        }
        return count($ids) > 0 ? $ids : FALSE;
    }

    function checkData() {
        if(count($this->data)) {
            return TRUE;
        }
        return FALSE;
    }

    function getDataList() {
        $dataList = array();
        foreach((array)$this->data as $datac) {
            if($datac
                && gettype($datac) == "object"
                && in_array(get_class($datac), static::YTDL_ALLOW_YTDTYPE) ) {

                $dataList[] = $datac;
            }
        }
        return $dataList;
    }
}

