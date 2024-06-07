<?php

return [

    'default' => 'connection',

    'connections' => [

        'connection_first' => [
            'driver' => env('DB_CONNECTION'),
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => env('DB_CHARSET'),
            'collation' => env('DB_COLLATION'),
        ],

        'connection_second' => [
            'driver' => env('DB_CONNECTION_SECOND'),
            'host' => env('DB_HOST_SECOND'),
            'port' => env('DB_PORT_SECOND'),
            'database' => env('DB_DATABASE_SECOND'),
            'username' => env('DB_USERNAME_SECOND'),
            'password' => env('DB_PASSWORD_SECOND'),
            'charset' => env('DB_CHARSET_SECOND'),
            'collation' => env('DB_COLLATION_SECOND'),
        ],

        'connection_third' => [
            'driver' => env('DB_CONNECTION_THIRD'),
            'host' => env('DB_HOST_THIRD'),
            'port' => env('DB_PORT_THIRD'),
            'database' => env('DB_DATABASE_THIRD'),
            'username' => env('DB_USERNAME_THIRD'),
            'password' => env('DB_PASSWORD_THIRD'),
            'charset' => env('DB_CHARSET_THIRD'),
            'collation' => env('DB_COLLATION_THIRD'),
        ],

        'connection_fourth' => [
            'driver' => env('DB_CONNECTION_FOURTH'),
            'host' => env('DB_HOST_FOURTH'),
            'port' => env('DB_PORT_FOURTH'),
            'database' => env('DB_DATABASE_FOURTH'),
            'username' => env('DB_USERNAME_FOURTH'),
            'password' => env('DB_PASSWORD_FOURTH'),
            'charset' => env('DB_CHARSET_FOURTH'),
            'collation' => env('DB_COLLATION_FOURTH'),
        ],

        'connection_fifth' => [
            'driver' => env('DB_CONNECTION_FIFTH'),
            'host' => env('DB_HOST_FIFTH'),
            'port' => env('DB_PORT_FIFTH'),
            'database' => env('DB_DATABASE_FIFTH'),
            'username' => env('DB_USERNAME_FIFTH'),
            'password' => env('DB_PASSWORD_FIFTH'),
            'charset' => env('DB_CHARSET_FIFTH'),
            'collation' => env('DB_COLLATION_FIFTH'),
        ],

    ],

];
