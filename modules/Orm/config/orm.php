<?php
return [
    'default' => 'default',
    'connections' => [
        'default' => [
            'driver' => getenv('DB_DRIVER') ?: 'sqlite', // 'mysql' | 'sqlite'
            'dsn'    => getenv('DB_DSN') ?: 'sqlite::memory:',
            'user'   => getenv('DB_USER') ?: null,
            'pass'   => getenv('DB_PASS') ?: null,
            'options'=> [],
        ],
        // Example MySQL connection:
        // 'mysql' => [
        //     'driver' => 'mysql',
        //     'dsn'    => 'mysql:host=127.0.0.1;dbname=yore;charset=utf8mb4',
        //     'user'   => 'root',
        //     'pass'   => '',
        //     'options'=> [],
        // ],
    ],
];
