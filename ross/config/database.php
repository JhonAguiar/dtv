<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'mongodb' => [
            'driver' => 'mongodb',
            'host' => '10.165.1.9',
            'port' => 27017,
            'database' => 'db'
        ],

        // Conexión del Mongo del ROSS
        'mongo_task' => [
            'driver'            => 'mongo',
            'host'              => env('MONGO_HOST', '10.165.1.9'),
            'username'          => env('MONGO_USERNAME', ''),
            'password'          => env('MONGO_PASSWORD', ''),
        ],

        // Conexión del MySql del ROSS.
        'mysql' => [
            'driver'            => 'mysql',
            'host'              => env('MYSQL_HOST', '127.0.0.1'),
            'port'              => env('MYSQL_PORT', '3308'),
            'database'          => env('MYSQL_DATABASE', 'forge'),
            'username'          => env('MYSQL_USERNAME', 'forge'),
            'password'          => env('MYSQL_PASSWORD', ''),
            'unix_socket'       => env('MYSQL_SOCKET', ''),
            'charset'           => 'utf8mb4',
            'collation'         => 'utf8mb4_unicode_ci',
            'prefix'            => '',
            'prefix_indexes'    => true,
            'strict'            => true,
            'engine'            => null,
            'options'           => extension_loaded('pdo_mysql') ? array_filter([ PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'), ]) : [],
        ],

        // Conexión del ProvisioningDB MySql del ROSS.
        'db_provisioning' => [
            'driver'            => 'mysql',
            'host'              => env('PROVISIONING_HOST', ''),
            'port'              => env('PROVISIONING_PORT', '3306'),
            'database'          => env('PROVISIONING_DATABASE', 'forge'),
            'username'          => env('PROVISIONING_USERNAME', 'forge'),
            'password'          => env('PROVISIONING_PASSWORD', ''),
            'unix_socket'       => env('PROVISIONING_SOCKET', ''),
            'charset'           => 'utf8mb4',
            'collation'         => 'utf8mb4_unicode_ci',
            'prefix'            => '',
            'prefix_indexes'    => true,
            'strict'            => true,
            'engine'            => null,
            'options'           => extension_loaded('pdo_mysql') ? array_filter([ PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'), ]) : [],
        ],

        // Conexión del ProvisioningDB Test MySql del ROSS.
        'db_provisioning_test' => [
            'driver'            => 'mysql',
            'host'              => env('PROVISIONING_HOST_TEST', ''),
            'port'              => env('PROVISIONING_PORT_TEST', '3306'),
            'database'          => env('PROVISIONING_DATABASE_TEST', 'forge'),
            'username'          => env('PROVISIONING_USERNAME_TEST', 'forge'),
            'password'          => env('PROVISIONING_PASSWORD_TEST', ''),
            'unix_socket'       => env('PROVISIONING_SOCKET_TEST', ''),
            'charset'           => 'utf8mb4',
            'collation'         => 'utf8mb4_unicode_ci',
            'prefix'            => '',
            'prefix_indexes'    => true,
            'strict'            => true,
            'engine'            => null,
            'options'           => extension_loaded('pdo_mysql') ? array_filter([ PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'), ]) : [],
        ],

        // Conexión portal cautivo Mysql.
        'db_captive_portal' => [
            'driver'            => 'mysql',
            'host'              => env('CAPTIVE_PORTAL_HOST', ''),
            'port'              => env('CAPTIVE_PORTAL_PORT', '3306'),
            'database'          => env('CAPTIVE_PORTAL_DATABASE', 'forge'),
            'username'          => env('CAPTIVE_PORTAL_USERNAME', 'forge'),
            'password'          => env('CAPTIVE_PORTAL_PASSWORD', ''),
            'unix_socket'       => env('CAPTIVE_PORTAL_SOCKET', ''),
            'charset'           => 'utf8mb4',
            'collation'         => 'utf8mb4_unicode_ci',
            'prefix'            => '',
            'prefix_indexes'    => true,
            'strict'            => true,
            'engine'            => null,
            'options'           => extension_loaded('pdo_mysql') ? array_filter([ PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'), ]) : [],
        ],




        // conexión a SqlBog Colombia
        'sqlsrv_sqlbog' => [
            'driver'            => 'sqlsrv',
            'url'               => env('SQLBOG_SERVER'),
            'host'              => env('SQLBOG_SERVER', 'localhost'),
            'port'              => env('SQLBOG_PORT', '1433'),
            'database'          => env('SQLBOG_DATABASE', 'forge'),
            'username'          => env('SQLBOG_USERNAME', 'forge'),
            'password'          => env('SQLBOG_PASSWORD', ''),
            'charset'           => 'utf8',
            'prefix'            => '',
            'prefix_indexes'    => true,
        ],


        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],


        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],


    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
        ],

    ],

];
