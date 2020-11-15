<?php

namespace App\Library;

class Utility
{
    static function copyArrayListed($src, &$dst, $list) {
        foreach((array)$list as $idx) {
            if(isset($src[$idx])) {
                $dst[$idx] = $src[$idx];
            }
        }
    }
    static function convertArray($src, $mapRules) {

        $ret = array();
        foreach($mapRules as $mapRule){
            $val = self::getFromMap($src,$mapRule['src']);
            self::setFromMap($ret, $mapRule['dst'], $val);
        }
        return $ret;
    }
    static function getFromMap($src, $map) {
        $key = array_shift($map);
        if(!isset($src[$key])) return NULL;

        if(empty($map)) {
            return $src[$key];
        } else {
            return self::getFromMap($src[$key], $map);
        }
    }
    static function setFromMap(&$dst, $map, $val) {
        $key = array_shift($map);
        if(!isset($dst[$key])) $dst[$key] = array();

        if(empty($map)) {
            $dst[$key] = $val;
            return TRUE;
        } else {
            return self::setFromMap($dst[$key], $map, $val);
        }

    }
}

