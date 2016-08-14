Migration Generator From database
=======================================
 - generate migration files (not dumps!) with indexes, and foreign keys, for one table, comma separated list of tables,  by part of table name, for all tables by 
 - generate migrations based on table data - in two ways - as batchInsert Query or as insert via model 

CHANGELOG
-----------
15.08.2016 - 2.1 version release 
 - add ability for generate migrations in fluent interface (raw format also available)
 - improve templates; add database initializations
 - structure design; separate logic in external classes
 - add ability to 
 
13.08.2016 - 2.0 version release with new ability - generate migrations based on table data
__Possible BC__
- class insolita\migrik\gii\Generator was changed on insolita\migrik\gii\StructureGenerator
if you made template customizations - see your gii config and replace old Generator class name

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require -dev --prefer-dist insolita/yii2-migration-generator:~2.0
```
or 
```
composer require -dev --prefer-dist insolita/yii2-migration-generator:~2.0
```

or add

```
"insolita/yii2-migration-generator": "~2.0"
```

to the require-dev section of your `composer.json` file.


Just install, go to gii and use (By default composer bootstrap hook)

Customizing
-----------
Copy default templates from folders 
   vendor/insolita/yii2-migration-generator/gii/default_structure //schema migrations
   vendor/insolita/yii2-migration-generator/gii/default_data //data migrations
to some project directory, for example 
   @backend/gii/templates/migrator_data;
   @backend/gii/templates/migrator_schema;

Change gii configuration like this
```php
$config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', 'localhost', '::1'],
        'generators' => [
            'migrik'=>[
                        'class'=>\insolita\migrik\gii\StructureGenerator::class,
                        'templates'=>
                        [
                             'custom'=>'@backend/gii/templates/migrator_schema'
                        ]
            ],
            'migrikdata'=>[
                        'class'=>\insolita\migrik\gii\DataGenerator::class,
                        'templates'=>
                        [        
                            'custom'=>'@backend/gii/templates/migrator_data'
                        ]
            ],
        ]
]
```


Known Issues:
-------------
  - sometimes not correct work gii preview - it`s features of gii preview and naming of migration files which has names with timestamp data. Try click preview button more times