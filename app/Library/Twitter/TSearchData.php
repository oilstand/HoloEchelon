<?php

namespace App\Library\Twitter;

use App\Library\Twitter\BaseTRD;

class TSearchData extends BaseTRD {
    const TRD_DATA_MAP = array(
        array(  'src'=>array('search_metadata'), 'dst'=>array('meta') ),
        array(  'src'=>array('statuses'),
                'dst'=>array('data') ),
    );
}
