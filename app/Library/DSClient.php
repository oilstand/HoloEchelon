<?php

namespace App\Library;

use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\Query\Query;

class DSClient
{
    function __construct() {
        $this->ds = new DatastoreClient();
    }

    function entity( $key, $value, $noIndex = array()) {
        return $this->ds->entity( $key, $value, array('excludeFromIndexes'=>$noIndex) );
    }

    function save( $key, $value, $noIndex = array() ) {
        $entity = $this->entity($key, $value, $noIndex);
        $result = $this->saveEntity( $entity );
        return $result;
    }

    function saveEntity( $entity ) {
        return $this->ds->upsert( $entity );
    }

    function saveEntities( $entities ) {
        return $this->ds->upsertBatch( $entities );
    }

    function loadEntity( $key ) {
        return $this->ds->lookup($key);
    }

    function loadEntities( $keys ) {
        return $this->ds->lookupBatch($keys);
    }

    function key( $kind, $id = NULL ) {
        return $id === NULL
                ?$this->ds->key( $kind )
                :$this->ds->key( $kind, $id );
    }
}

