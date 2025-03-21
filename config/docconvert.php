<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LibreOffice Configuration
    |--------------------------------------------------------------------------
    |
    | Path to the LibreOffice binary (soffice). If not specified, the system
    | will attempt to detect it automatically. Specify the full path if your
    | LibreOffice installation is not in the system PATH.
    |
    | Examples:
    | 'libreoffice_path' => 'soffice',                                     // Use PATH
    | 'libreoffice_path' => '/usr/bin/soffice',                            // Linux
    | 'libreoffice_path' => '/opt/libreoffice/program/soffice',            // Custom Linux install
    | 'libreoffice_path' => '/Applications/LibreOffice.app/Contents/MacOS/soffice' // macOS
    |
    */
    'libreoffice_path' => env('LIBREOFFICE_PATH', '/opt/homebrew/bin/soffice'),

    /*
    |--------------------------------------------------------------------------
    | Conversion Timeouts
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds allowed for document conversions
    |
    */
    'conversion_timeout' => 300, // 5 minutes
];
