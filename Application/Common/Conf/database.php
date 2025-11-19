<?php

return [
    /*数据库连接相关配置信息 S*/
    PDO::ATTR_CASE => PDO::CASE_NATURAL,
    // 数据库类型
    'DB_TYPE' => 'mysql',
    'DB_HOST' => env('db_host') ?: '127.0.0.1',
    'DB_NAME' => env('db_name') ?: 'test_data',
    'DB_USER' => env('db_user') ?: 'root',
    'DB_PWD' => env('db_pwd') ?: 'root',
    // 端口      线上 3106
    'DB_PORT' => env('db_port') ?: '3106',
    // 数据库表前缀
    'DB_PREFIX' => 'sb_',
    /*数据库相关配置信息 E*/
];
