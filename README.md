Migration Generator From Mysql database
=======================================
generate migration files (not dumps!) with indexes, and foreign keys, for one table, comma separated list of tables,  by part of table name, for all tables by *

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist insolita/yii2-migration-generator "*"
```

or add

```
"insolita/yii2-migration-generator": "*"
```

to the require section of your `composer.json` file.


Usage (MYSQL ONLY)
-----

Once the extension is installed, go to Gii and use it, and also create own templates - [see more](http://www.yiiframework.com/doc-2.0/guide-tool-gii).html#creating-your-own-templates :

```php
<?= \insolita\migrik\AutoloadExample::widget(); ?>```