<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Twill Imgix configuration
    |--------------------------------------------------------------------------
    |
    | This array allows you to provide the package with your configuration
    | for the Imgix image service.
    |
     */
    'host' => env('AWS_IMGAPI'),
    'default_params' => [
        'fm' => 'jpg',
        'q' => '80',
        'fit' => 'inside',
    ],
    'lqip_default_params' => [
        'fm' => 'gif',
        'blur' => 100,
        'dpr' => 1,
    ],
    'social_default_params' => [
        'fm' => 'jpg',
        'w' => 900,
        'h' => 470,
        'fit' => 'cover',
    ],
    'cms_default_params' => [
        'q' => 60,
        'dpr' => 1,
    ],
];

