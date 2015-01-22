Migration Generator From Mysql database (beta)
=======================================
generate migration files (not dumps!) with indexes, and foreign keys, for one table, comma separated list of tables,  by part of table name, for all tables by *

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require-dev --prefer-dist insolita/yii2-migration-generator "*"
```

or add

```
"insolita/yii2-migration-generator": "*"
```

to the require-dev section of your `composer.json` file.


Usage (MYSQL ONLY):
Just install, go to gii and use