<?php

use yii\log\Logger;

return [
    'id' => 'insolia/yii2-migration-generator',
    'timeZone' => getenv('TZ'),
    'basePath' => dirname(__DIR__) . '/app',
    'runtimePath' => dirname(__DIR__) . '/runtime',
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'bootstrap'=>['log'],
    'controllerMap' => [
        'migrate' => [
            'class' => \yii\console\controllers\MigrateController::class,
            'migrationPath' => dirname(__DIR__).'/migrations',
        ],
    ],
    'components' => [
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => getenv('PG_DSN'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8',
            'tablePrefix'=>'itt_',
        ],
        'mysqldb' => [
            'class' => \yii\db\Connection::class,
            'dsn' => getenv('MYSQL_DSN'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8',
            'tablePrefix'=>'itt_',
        ],
        'mariadb' => [
            'class' => \yii\db\Connection::class,
            'dsn' => getenv('MARIA_DSN'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8',
            'tablePrefix'=>'itt_',
        ],
        'log'=>[
            'class'=>Logger::class,
            'traceLevel' => 3,
            'targets' => [
                [
                    'class' => \yii\log\SyslogTarget::class,
                    'identity'=>'migriktest',
                    'levels' => ['error', 'trace'],
                    'categories'=>['insolita\migrik\*','yii\db\Exception*']
                ],
            ],
        ]
    ],
];
