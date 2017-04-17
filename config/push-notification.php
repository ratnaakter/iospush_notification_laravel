<?php

return array(

    'appNameIOS'     => array(
        'environment' =>'development',
        'certificate' =>'/path/to/ck.pem',
        'passPhrase'  =>'password',
        'service'     =>'apns'
    ),
    'appNameAndroid' => array(
        'environment' =>'production',
        'apiKey'      =>'yourAPIKey',
        'service'     =>'gcm'
    )

);