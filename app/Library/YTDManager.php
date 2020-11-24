<?php

namespace App\Library;

use App\Library\DSClient;
use App\Library\YouTubeAPI;
use App\Library\ChannelData;
use App\Library\ChannelDataList;
use App\Library\VideoData;
use App\Library\VideoDataList;

class YTDManager
{
    const TYPE_CHANNEL = 'channel';
    const TYPE_CHANNELS = 'channels';
    const TYPE_VIDEO = 'video';
    const TYPE_VIDEOS = 'videos';
    const TYPE_SEARCH_CHANNEL_VIDEOS = 'channelVideos';

    const YTDM_DATA_API_MAP = array(
        self::TYPE_CHANNEL=>array(
            'class'=>__NAMESPACE__ . '\\' . 'ChannelData',
            'api'=>'getChannel'
        ),
        self::TYPE_CHANNELS=>array(
            'class'=>__NAMESPACE__ . '\\' . 'ChannelDataList',
            'api'=>'getChannels'
        ),
        self::TYPE_VIDEO=>array(
            'class'=>__NAMESPACE__ . '\\' . 'VideoData',
            'api'=>'getVideo'
        ),
        self::TYPE_VIDEOS=>array(
            'class'=>__NAMESPACE__ . '\\' . 'VideoDataList',
            'api'=>'getVideos'
        ),
        self::TYPE_SEARCH_CHANNEL_VIDEOS=>array(
            'class'=>__NAMESPACE__ . '\\' . 'SearchVideoResult',
            'api'=>'getChannelVideos'
        ),
    );

    function __construct() {
        $this->dsc = new DSClient();
        $this->api = new YouTubeAPI();
    }

    function getDataFromDS( $dataType, $id ) {

        if(!isset(self::YTDM_DATA_API_MAP[$dataType])) {
            return FALSE;
        }
        $dataClass = self::YTDM_DATA_API_MAP[$dataType]['class'];

        $dsres = $this->dsc->loadEntity(
            $this->dsc->key( $dataClass::YTD_KIND, $id )
        );

        if($dsres) {
            $datac = new $dataClass($dsres->get());
            if($datac->checkData()) {
                return $datac;
            }
        }
        return FALSE;
    }

    function getDataListFromDS( $dataType, $ids ) {

        if(!isset(self::YTDM_DATA_API_MAP[$dataType]) && !is_array($ids)) {
            return FALSE;
        }
        $dataClass = self::YTDM_DATA_API_MAP[$dataType]['class'];

        $keys = array();
        foreach($ids as $id) {
            $keys[] = $this->dsc->key( $dataClass::YTD_KIND, $id );
        }
        $dsres = $this->dsc->loadEntities( $keys );

        if($dsres && isset($dsres['found'])) {
            $dataList = array();
            foreach((array)$dsres['found'] as $entity) {
                $dataList[] = $entity->get();
            }

            $datac = new $dataClass($dataList);
            if($datac->checkData()) {
                return $datac;
            }
        }
        return FALSE;
    }

    function getDataListFromDSQuery( $dataType, $query ) {

        if(!isset(self::YTDM_DATA_API_MAP[$dataType])
            && get_class($query) !== "Google\\Cloud\\Datastore\\Query\\Query") {
            return FALSE;
        }
        $dataClass = self::YTDM_DATA_API_MAP[$dataType]['class'];

        $dsres = $this->dsc->runQuery( $query );

        if($dsres) {
            $dataList = array();
            for($i = 0; $i < 100 && $dsres->valid(); $i++) {
                $dataList[] = $dsres->current()->get();
                $dsres->next();
            }

            $datac = new $dataClass($dataList);//($dsres);
            if($datac->checkData()) {
               return $datac;
            }
        }
        return FALSE;
    }

    function query() {
        return $this->dsc->query();
    }

    function runQuery($query) {
        return $this->dsc->runQuery($query);
    }

    function getDataFromAPI( $dataType, $id, $datac = FALSE ) {
        if(!isset(self::YTDM_DATA_API_MAP[$dataType])) {
            return FALSE;
        }
        $dataDef = self::YTDM_DATA_API_MAP[$dataType];

        if(!$datac) {  // 新規作成
            $datac = new $dataDef['class']();
        }

        $apifunc = $dataDef['api'];
        $response = $this->api->$apifunc($id);
        $responseBody = YouTubeAPI::getBodyFromResponse($response);

        if($responseBody
            && isset($responseBody['items'])
            && count($responseBody['items']) > 0 ) {

            foreach( $responseBody['items'] as $item ) {
                $datac->setDataFromAPIResult($item);
            }

            if( $datac->checkData() ) {
                return $datac;
            }
        }
        return FALSE;
    }

    function getDataNoCache( $dataType, $id ) {
        return $this->getData( $dataType, $id, false, false, false );
    }
    function getData( $dataType, $id, $useCache = true, $saveDS = true, $useDS = true ) {

        $datac = FALSE;

        if($useDS) {
            $datac = $this->getDataFromDS( $dataType, $id );

            if( $datac
                // リフレッシュチェック
                && !$datac->needRefresh()
                && $useCache ) {
                // リフレッシュ不要
                return $datac;
            }
        }

        $datac = $this->getDataFromAPI( $dataType, $id, $datac );

        if($datac) {
            if($saveDS
                && $data = $datac->getData() ) {

                $entity = $this->dsc->entity(
                    $this->dsc->key($datac->getKind(), $datac->getId()),
                    $data,
                    $datac->getNoindex()
                );
                $this->dsc->saveEntity($entity);
            }
            return $datac;
        }
        return FALSE;
    }

    function save( $dataClass ) {
        $this->saveBatch( array( $dataClass ) );
    }

    function saveBatch( $dataClasses ) {

        $entities = array();
        foreach( $dataClasses as $dataClass ) {

            if( gettype($dataClass) == "object" ) {

                $safe = false;
                foreach( self::YTDM_DATA_API_MAP as $classDef ) {
                    if($classDef['class'] == get_class($dataClass)) {
                        $safe = true;
                        break;
                    }
                }

                if($safe
                    && $data = $dataClass->getData()) {

                    $entity = $this->dsc->entity(
                        $this->dsc->key($dataClass->getKind(), $dataClass->getId()),
                        $data,
                        $dataClass->getNoindex()
                    );
                    $entities[] = $entity;
                }
            }
        }

        if(!empty($entities)) {
            $this->dsc->saveEntities($entities);
        }
    }

    public static function convertArray2KeyValue( $array ) {
        $ret = array();
        foreach((array)$array as $val) {
            $ret[$val->getId()] = $val;
        }
        return $ret;
    }
}

