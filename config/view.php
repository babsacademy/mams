<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    | Pointed to Windows temp dir to avoid OneDrive permission issues.
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        sys_get_temp_dir().DIRECTORY_SEPARATOR.'schic_views'
    ),

];
