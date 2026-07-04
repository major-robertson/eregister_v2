<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDF Coordinate Mapper — template libraries
    |--------------------------------------------------------------------------
    |
    | Directories the admin coordinate-mapper tool lists as pick-able PDF
    | sources (any PDF can also be uploaded ad hoc). Add an entry here when a
    | new domain grows a folder of fill-able PDF forms (e.g. county lien
    | recording forms). Paths are relative to resource_path().
    |
    */
    'libraries' => [
        'resale_certificates' => [
            'label' => 'Resale certificate templates',
            'path' => 'pdfs/state_resale_certificates',
        ],
    ],

];
