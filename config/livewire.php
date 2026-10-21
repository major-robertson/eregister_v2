<?php

/*
|--------------------------------------------------------------------------
| Livewire (partial override — vendor defaults fill the rest)
|--------------------------------------------------------------------------
|
| Full-page Livewire components that don't declare a layout (the ⚡settings
| pages) render inside the unified portal shell. Livewire's vendor default
| is 'layouts::app', the deleted starter-kit shell.
|
*/

return [

    'component_layout' => 'components.layouts.portal',

    // Temporary uploads up to 20 MB (the vendor default is 12 MB): a customer
    // answering a request on the filing page may send a scan or a phone photo
    // that size (FilingShow::submitAnswer). Each component still validates
    // its own file types and size. Unset keys keep the vendor defaults.
    'temporary_file_upload' => [
        'rules' => ['required', 'file', 'max:20480'],
    ],

];
