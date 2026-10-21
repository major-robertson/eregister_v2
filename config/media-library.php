<?php

/*
|--------------------------------------------------------------------------
| Media library (partial override — vendor defaults fill the rest)
|--------------------------------------------------------------------------
|
| The largest file any media collection takes; a larger one throws
| FileIsTooBig (vendor default 10 MB). The lien filing wizard takes
| contracts and invoices up to 20 MB each (EREG-139). Each upload field
| still checks its own, lower limit.
|
*/

return [

    'max_file_size' => 1024 * 1024 * 20,

];
