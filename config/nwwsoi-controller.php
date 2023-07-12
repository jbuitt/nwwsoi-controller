<?php

return [

    'nwwsoi' => [
        'host' => env('NWWSOI_SERVER_HOST', 'nwws-oi.weather.gov'),
        'port' => env('NWWSOI_SERVER_PORT', 5222),
        'username' => env('NWWSOI_USERNAME', ''),
        'password' => env('NWWSOI_PASSWORD', ''),
        'resource' => env('NWWSOI_RESOURCE', 'SleekXMPP_Client'),
        'archivedir' => env('NWWSOI_ARCHIVE_DIR', 'app/public/products/nwws'),
        'pan_run' => env('NWWSOI_PAN_RUN', 'artisan nwwsoi-controller:pan-run'),
        'retry' => env('NWWSOI_SERVER_CONNECT_RETRY', 1),
        'autostart' => env('NWWSOI_CONFIG_AUTOSTART', 0),
        'file_save_regex' => env('NWWSOI_FILE_SAVE_REGEX', '.*'),
    ],

    'days_to_keep_products' => env('DAYS_TO_KEEP_PRODUCTS', 7),

    'days_to_keep_logs' => env('DAYS_TO_KEEP_LOGS', 7),

    'enabled_pan_plugins' => env('ENABLED_PAN_PLUGINS', ''),

    'python_client_path' => env('NWWSOI_PYTHON_CLIENT_PATH', '/usr/bin/python3 -u scripts/nwws.py'),
];
