<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    */
    'fetch' => PDO::FETCH_CLASS,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    | You can override via .env -> DB_CONNECTION=pgsql|mysql|sqlite|sqlsrv
    */
    'default' => env('DB_CONNECTION', 'pgsql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    */
    'connections' => array(

        'sqlite' => array(
            'driver'   => 'sqlite',
            'database' => __DIR__.'/../database/production.sqlite',
            'prefix'   => '',
        ),

        'mysql' => array(
            'driver'    => 'mysql',
            'host'      => env('MYSQL_HOST', '127.0.0.1'),
            'port'      => env('MYSQL_PORT', 3306),
            'database'  => env('MYSQL_DATABASE', 'forge'),
            'username'  => env('MYSQL_USERNAME', 'forge'),
            'password'  => env('MYSQL_PASSWORD', ''),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ),

        // Main app (eproject) → Postgres, from EPROJECT_* vars
        'pgsql' => array(
            'driver'   => 'pgsql',
            'host'     => env('EPROJECT_DB_HOST', '127.0.0.1'),
            'port'     => env('EPROJECT_DB_PORT', 5432),
            'database' => env('EPROJECT_DB_DATABASE', 'eproject'),
            'username' => env('EPROJECT_DB_USERNAME', 'postgres'),
            'password' => env('EPROJECT_DB_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ),

        // Extra connection used to talk to Buildspace DB (if needed)
        'buildspace' => array(
            'driver'   => 'pgsql',
            'host'     => env('BUILDSPACE_DB_HOST', '127.0.0.1'),
            'port'     => env('BUILDSPACE_DB_PORT', 5432),
            'database' => env('BUILDSPACE_DB_DATABASE', 'buildspace_saml'),
            'username' => env('BUILDSPACE_DB_USERNAME', 'postgres'),
            'password' => env('BUILDSPACE_DB_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ),

        'sqlsrv' => array(
            'driver'   => 'sqlsrv',
            'host'     => env('SQLSRV_HOST', 'localhost'),
            'database' => env('SQLSRV_DATABASE', 'database'),
            'username' => env('SQLSRV_USERNAME', 'root'),
            'password' => env('SQLSRV_PASSWORD', ''),
            'prefix'   => '',
        ),
    ),

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    */
    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    */
    'redis' => array(
        'cluster' => false,
        'default' => array(
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'port'     => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ),
    ),
);
