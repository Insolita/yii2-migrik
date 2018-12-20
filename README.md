Migration Generator
=======================================
![Latest Stable Version](https://img.shields.io/packagist/v/insolita/yii2-migration-generator.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/insolita/yii2-migration-generator.svg)](https://packagist.org/packages/insolita/yii2-migration-generator)
![License](https://img.shields.io/packagist/l/insolita/yii2-migration-generator.svg)


 - generate migration files (not dumps!) with indexes, and foreign keys, for one table, comma separated list of tables,  by part of table name, for all tables by 
 - generate migrations based on table data - in two ways - as batchInsert Query or as insert via model 
 - generate migrations based on PHPDOC and model properties

### [CHANGELOG](CHANGELOG.md)

### Installation

**NOTE** : Use 2.x versions for yii <=2.0.13

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run
```
composer require --dev --prefer-dist insolita/yii2-migration-generator:~3.1
```

or add

```
"insolita/yii2-migration-generator": "~3.1"
```

to the require-dev section of your `composer.json` file.


Just install, go to gii and use (By default composer bootstrap hook)


### ANNOTATION SYNTAX

In general the syntax of column definitions is based  on style of yii-migration, only separated by "|" and provide a little more opportunities for reducing code
 - as you see in examples - empty brackets not necessary
 - also shortcut expr() will be replaced to defaultExpression() and default() to defaultValue 
 
You can add annotations in your model(not necessary AR or yii\\base\\Model or Object or stdClass)

`@db (db2)` - specify connection id required for migration 'db' - by default"

`@table ({{%my_table}})`- specify table for migration"

__Supported column annotations:__
 - As separate annotation above class  or above current variable
 
 ```php 
/**
 * @column (name) string|notNull|default('SomeValue')
 */
 ```
 
 - As addition to @property or @var definition 
 ```
    /**
     * @var int $id @column pk()
     */
    public $id;
    /**
     * @var string $route @column string(100)|notNull()
     */
    public $route;
 
 ```

```
/**
 * @property integer    $id         @column pk|comment("Id")
 * @property string     $username   @column string(100)|unique|notNull|default("Vasya")
 * @property string     $email      @column string(200)|unique()|defaultValue("123@mail.ru")
 * @property string     $password   @column string(200)|notNull|expr(null)
 * @property string     $created_at @column string(200)|notNull|expr('CURRENT_TIMESTAMP')
 */
class TestModel extends ActiveRecord{
```

 
### Customizing 
##### Use Own templates

Copy default templates from folders 

   `vendor/insolita/yii2-migration-generator/gii/default_structure //schema migrations`
   
   `vendor/insolita/yii2-migration-generator/gii/default_data //data migrations`
   
to some project directory, for example 

   `@backend/gii/templates/migrator_data;`
   
   `@backend/gii/templates/migrator_schema;`
   

Change gii configuration like this
```php
$config['modules']['gii'] = [
    'class' => 'yii\gii\Module',
    'allowedIPs' => ['127.0.0.1', 'localhost', '::1'],
    'generators' => [
        'migrik' => [
            'class' => \insolita\migrik\gii\StructureGenerator::class,
            'templates' => [
                'custom' => '@backend/gii/templates/migrator_schema',
            ],
        ],
        'migrikdata' => [
            'class' => \insolita\migrik\gii\DataGenerator::class,
            'templates' => [
                'custom' => '@backend/gii/templates/migrator_data',
            ],
        ],
    ],
];
```

##### Use own resolver for definition of columns 
  - create new class, inherited from \insolita\migrik\resolver\*ColumnResolver
    - override required methods, or create methods for exclusive columns based on database types - see insolita\migrik\resolver\BaseColumnResolver resolveColumn() phpdoc and realization
    
##### Use own resolver for definition of  indexes or relations 
  - create new class, inherited from \insolita\migrik\resolver\TableResolver
  - in bootsrap your apps add injection 
  
  ```\Yii::$container->set(IMigrationTableResolver::class, YourTableResolver::class);```
    
