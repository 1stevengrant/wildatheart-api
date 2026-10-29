<?php

return [
    /*
     * Production database host, e.g. the Laravel Cloud public endpoint
     */
    'host' => env('REMOTE_DATABASE_HOST', ''),

    /*
     * Database port
     */
    'port' => env('REMOTE_DATABASE_PORT', '3306'),

    /*
     * Database username
     */
    'username' => env('REMOTE_DATABASE_USERNAME', ''),

    /*
     * Set the database name
     */
    'database' => env('REMOTE_DATABASE_NAME', ''),

    /*
     * Set the database password
     */
    'password' => env('REMOTE_DATABASE_PASSWORD', ''),

    /*
     * provide a comma seperated list of tables to ignore, when set the tables specified will not be exported
     */
    'ignore' => env('REMOTE_DATABASE_IGNORE_TABLES', ''),

    /*
     * provide a comma separated list of tables to exclude data from, but include structure
     * these tables will have their structure exported but not their data
     */
    'excludeData' => env('REMOTE_DATABASE_EXCLUDE_DATA', ''),

    /*
     * Set true to dump TIMESTAMP values in the server's time zone instead of UTC
     */
    'skipTzUtc' => env('REMOTE_SKIP_TZ_UTC', false),

    /*
     * TLS mode for the production connection, VERIFY_IDENTITY for Laravel Cloud
     */
    'sslMode' => env('REMOTE_DATABASE_SSL_MODE'),

    /*
     * CA bundle used to verify the server certificate with VERIFY_IDENTITY, /etc/ssl/cert.pem on macOS
     */
    'sslCa' => env('REMOTE_DATABASE_SSL_CA'),

    /*
     * Number of parallel threads mydumper and myloader use
     */
    'threads' => (int) env('REMOTE_DATABASE_THREADS', 8),

    /*
     * Sets if the exported dump will be imported into the current database connection
     */
    'importDump' => env('REMOTE_IMPORT_FILE', true),

    /*
     * Sets if the dump directory will be deleted after it has been imported.
     */
    'removeDumpAfterImport' => env('REMOTE_REMOVE_FILE_AFTER_IMPORT', true),

    /*
     * Sets the default dump directory if --directory is not provided, relative paths are created in the system temp directory
     */
    'defaultDirectory' => env('REMOTE_DEFAULT_DIRECTORY', 'dbsync'),

    /*
     * Sets the target database connection
     */
    'targetConnection' => env('LOCAL_TARGET_CONNECTION', 'mysql'),

    /*
    * List all the environment variables that need to be set for the command to work
    */
    'environments' => [
        'local',
        'staging',
        'testing',
    ],

    /*
     * mydumper and myloader binaries, install with `brew install mydumper`
     */
    'mydumperPath' => env('MYDUMPER_PATH', 'mydumper'),

    'myloaderPath' => env('MYLOADER_PATH', 'myloader'),
];
