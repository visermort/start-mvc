<?php

/**
 * database access
 */
return [
    'connection' => [
        'host' => '127.0.0.1',
        'database' => 'beejee',
        'user' => 'root',
        'password' => '',
        'table_prefix' => 'xx_',
        'charset' => 'utf8',
        'driver' => 'mysql',
        'collation' => 'utf8_unicode_ci',
//        'attributes' => [
//            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION, // вывод ошибок
//            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC // PDO::FETCH_NUM
//        ]
    ]
];
