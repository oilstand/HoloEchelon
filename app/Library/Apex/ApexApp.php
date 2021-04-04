<?php

namespace App\Library\Apex;

use App\Library\DSClient;
use App\Library\Apex\ApexAPI;
use App\Library\Utility;

class ApexPlayerStat
{
    private $playerData = null;
    private $overview = null;

    function __construct( $data ) {
        $this->playerData = $data;
    }

    private function getOverview() {
        if($this->playerData && $this->overview == null) {
            $tmp = Utility::convertArray(
                $this->playerData,
                [ array( 'src'=>array('data','segments'), 'dst'=>array('segments') ) ]
            );
            foreach($tmp['segments'] as $seg) {
                if($seg['type'] == "overview") {
                    $this->overview = $seg;
                    break;
                }
            }
        }
        return $this->overview;
    }

    public function getRankPoint() {
        return Utility::getFromMap(
                $this->getOverview(),
                ['stats','rankScore','value']
            );
    }
}

class ApexApp
{
    const TIMEZONE = 'Asia/Tokyo';

    function __construct($namespace = false) {
        $this->dsc = new DSClient($namespace);
        $this->api = new ApexAPI();
    }

    function query() {
        return $this->dsc->query();
    }

    function runQuery($query) {
        return $this->dsc->runQuery($query);
    }

    public function getUserStat( $platform, $id ) {
        $response = $this->api->getProfile( $platform, $id );
        if($response){
            return new ApexPlayerStat($response->get());
        }
        return $response;
    }

    public function updateUserStat( $userStatEntity ) {

        $userStat = $userStatEntity->get();
        $saveEntities = [];
        $rankPoint = -1;

        if( $res = $this->getUserStat($userStat['platform'], $userStat['id']) ) {

            $rankPoint = $res->getRankPoint();

            if($rankPoint !== null && $userStat['rp'] != $rankPoint){
                $tmpOldRp = $userStat['rp'];
                $userStat['rp'] = $rankPoint;
                $userStat['rpUpdatedAt'] = self::getDatetimeNowStr();

                $rankLogKey = $this->dsc->key('apexRankLog');
                $rankLogData = array(
                    'rp'=>$rankPoint,
                    'name'=>$userStat['name'],
                    'createdAt'=>self::getDatetimeNowStr(),
                    'matchInfo'=>array(
                        'rpDiff'=>$rankPoint - $tmpOldRp
                    )
                );

                $saveEntities[] = $this->dsc->entity(
                    $rankLogKey,
                    $rankLogData
                );
            }

            $userStat['status'] = 'Active';
            $userStat['updatedAt'] = self::getDatetimeNowStr();

            $saveEntities[] = $this->dsc->entity(
                $userStatEntity->key(),
                $userStat
            );
        } else {

            $userStat['status'] = 'Failed';
            $userStat['updatedAt'] = self::getDatetimeNowStr();

            $saveEntities[] = $this->dsc->entity(
                $userStatEntity->key(),
                $userStat
            );
        }

        if(!empty($saveEntities)) {
            $this->dsc->saveEntities($saveEntities);
        }

        return [
            'stat'=>$userStat,
            'res'=>$res,
            'rp'=>$rankPoint
        ];
    }

    public function getApexUserList() {
        $query = $this->dsc->query()
                    ->kind('apexTrack');

        $dsres = $this->dsc->runQuery( $query );

        if($dsres) {
            $dataList = array();
            for($i = 0; $i < 200 && $dsres->valid(); $i++) {
                $dataList[] = $dsres->current();
                $dsres->next();
            }

            return $dataList;
        }
        return FALSE;
    }

    public static function convertArray2KeyValue( $array ) {
        $ret = array();
        foreach((array)$array as $val) {
            $ret[$val->getId()] = $val;
        }
        return $ret;
    }

    public static function getDatetimeNowStr() {
        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone(self::TIMEZONE));
        return $now->format('Y-m-d H:i:sP');
    }

/*
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

    */
}

