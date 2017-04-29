<?php

return [
    'id' => 'insolia/yii2-migration-generator',
    'basePath' => __DIR__ . '/../../',
    'bootstrap'=>['log'],
    'components' => [
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => getenv('PG_DSN'),
            'username' => getenv('PG_USER'),
            'password' => getenv('PG_PASSWORD'),
            'charset' => 'utf8',
            'tablePrefix'=>'itt_',
        ],
        'dbmm' => [
            'class' => \yii\db\Connection::class,
            'dsn' => getenv('MYSQL_DSN'),
            'username' => getenv('MYSQL_USER'),
            'password' => getenv('MYSQL_PASSWORD'),
            'charset' => 'utf8',
            'tablePrefix'=>'itt_',
        ],
        'log'=>[
            'traceLevel' => 3,
            'targets' => [
                [
                    'class' => \yii\log\SyslogTarget::class,
                    //'class' => \core\loggy\components\SyslogTarget::class,
                    'identity'=>'migriktest',
                    'levels' => ['error', 'trace'],
                    'categories'=>['insolita\migrik\*','yii\db\Exception*']
                ],
            ],
        ]
    ],
];