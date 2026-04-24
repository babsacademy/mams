<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    |
    | Activate or deactivate dashboard modules via .env variables.
    | Set to false to hide the module from the sidebar and disable its routes.
    |
    | FEATURE_PROMOTIONS=true
    | FEATURE_MEDIA=true
    | FEATURE_STOREFRONT=true
    | FEATURE_USERS=true
    | FEATURE_ANALYTICS=true
    | FEATURE_NOTIFICATIONS=true
    |
    */

    'promotions' => (bool) env('FEATURE_PROMOTIONS', true),
    'media' => (bool) env('FEATURE_MEDIA', true),
    'storefront' => (bool) env('FEATURE_STOREFRONT', true),
    'users' => (bool) env('FEATURE_USERS', true),
    'analytics' => (bool) env('FEATURE_ANALYTICS', true),
    'notifications' => (bool) env('FEATURE_NOTIFICATIONS', true),

];
