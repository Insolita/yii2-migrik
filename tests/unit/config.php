<?php

return [
    'id' => 'insolia/yii2-migration-generator',
    'basePath' => __DIR__ . '/../../',
    'bootstrap'=>['log'],
    'components' => [
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => 'pgsql:host=localhost;port=5432;dbname=ptrack',
            'username' => 'lusik',
            'password' => 'password',
            'charset' => 'utf8',
            'tablePrefix'=>'it_',
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