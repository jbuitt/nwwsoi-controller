<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Email
    |--------------------------------------------------------------------------
    |
    | This value is the email address of the user that should be considered an
    | administrator.
    */
    'admin_email' => env('ADMIN_EMAIL', 'admin@localhost'),

    /*
    |--------------------------------------------------------------------------
    | NWWS-OI client variables
    |--------------------------------------------------------------------------
    |
    | These values are needed for configuring the NWWS-OI client.
    */
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

    /*
    |--------------------------------------------------------------------------
    | Days to keep products
    |--------------------------------------------------------------------------
    |
    | This value is the number of days to keep NWWS-OI products. Defaults to 1 week.
    |
    */
    'days_to_keep_products' => env('DAYS_TO_KEEP_PRODUCTS', 7),

    /*
    |--------------------------------------------------------------------------
    | Days to keep logs
    |--------------------------------------------------------------------------
    |
    | This value is the number of days to keep log files. Defaults to 1 week.
    |
    */
    'days_to_keep_logs' => env('DAYS_TO_KEEP_LOGS', 7),

    /*
    |--------------------------------------------------------------------------
    | Enabled PAN Plugins
    |--------------------------------------------------------------------------
    |
    | This value is the list of PAN plugins that should be enabled. It is a
    | comma-separated list. Default is no plugins are enabled.
    |
    */
    'enabled_pan_plugins' => env('ENABLED_PAN_PLUGINS', ''),

    /*
    |--------------------------------------------------------------------------
    | NWWS-OI Python client path
    |--------------------------------------------------------------------------
    |
    | This value is the the full path to the NWWS-OI Python client with arguments.
    |
    */
    'python_client_path' => env('NWWSOI_PYTHON_CLIENT_PATH', '/usr/bin/python3 -u scripts/nwws.py'),
];
