<?php

use Carbon\Carbon;
use yii\db\Migration;

class m170428_223742_test_migration_mysql extends Migration
{
    public function init()
    {
        $this->db = 'mysqldb';
        parent::init();
    }
    public function safeUp()
    {
        var_dump([$this->getDb()->dsn, $this->getDb()->getDriverName()]);
        $tableOptions = 'ENGINE=InnoDb';

        $this->clearTables();
        $this->createTable(
            'migrik_test1',
            [
                'id' => $this->primaryKey(),
                'charField' => $this->char(),
                'strField' => $this->string(),
                'textField' => $this->text(),
                'smallintField' => $this->smallInteger()->unique(),
                'tinyintField' => $this->tinyInteger(2)->unique(),
                'intField' => $this->integer(),
                'bigintField' => $this->bigInteger(),
                'floatField' => $this->float(2),
                'doubleField' => $this->double(5),
                'decimalField' => $this->decimal(5, 2),
                'datetimeField' => $this->dateTime(0),
                'timeStampField' => $this->timestamp(0),
                'timeField' => $this->time(0),
                'dateField' => $this->date(),
                'binaryField' => $this->binary(),
                'boolField' => $this->boolean(),
                'moneyField' => $this->money(5, 1),
                'jsonField' => $this->json(),
            ], $tableOptions
        );
        $this->createIndex('complexIdx', 'migrik_test1', ['charField', 'boolField']);

        $this->createTable(
            'migrik_test2',
            [
                'id' => $this->bigPrimaryKey(),
                'charField' => $this->char(128)->null(),
                'strField' => $this->string(100)->null(),
                'textField' => $this->text(1000)->null(),
                'smallintField' => $this->smallInteger(3)->null(),
                'intField' => $this->integer(11)->null(),
                'bigintField' => $this->bigInteger()->null(),
                'floatField' => $this->float(2)->null(),
                'doubleField' => $this->double(5)->null(),
                'decimalField' => $this->decimal(5, 2)->null(),
                'datetimeField' => $this->dateTime(0)->null(),
                'timeStampField' => $this->timestamp(0)->null(),
                'timeField' => $this->time(0)->null(),
                'dateField' => $this->date()->null(),
                'binaryField' => $this->binary()->null(),
                'boolField' => $this->boolean()->null(),
                'moneyField' => $this->money(5, 2)->null(),
            ], $tableOptions
        );
        $this->createIndex('strFieldUniq', 'migrik_test2', ['strField'], true);
        $this->createTable(
            'migrik_test3',
            [
                'id' => $this->bigPrimaryKey(),
                'charField' => $this->char(128)->notNull(),
                'strField' => $this->string(100)->notNull()->defaultValue('dummy'),
                'textField' => $this->text(1000),
                'smallintField' => $this->smallInteger(3)->defaultValue(2),
                'intField' => $this->integer(11)->notNull()->defaultValue(3),
                'bigintField' => $this->bigInteger()->notNull()->defaultValue(4),
                'floatField' => $this->float(10, 2)->defaultValue(323.33),
                'doubleField' => $this->double(5, 2)->defaultValue(323.33),
                'decimalField' => $this->decimal(5, 2)->defaultValue(323.33),
                'timeStampField' => $this->timestamp(0)->defaultValue(
                    Carbon::create(2005, 9, 21, 10, 40, 01)
                                  ->format('Y-m-d H:i:s.u')
                ),
                'timeField' => $this->time(0)->defaultValue(
                    Carbon::createFromTime(10, 40, 01)->toTimeString()
                ),
                'dateField' => $this->date()->defaultValue(
                    Carbon::createFromDate(2005, 9, 21)->toDateString()
                ),
                'boolField' => $this->boolean()->defaultValue(false),
                'moneyField' => $this->money(8, 2)->defaultValue(334),
            ],$tableOptions
        );

        $this->createTable(
            'migrik_testfk',
            [
                'id' => $this->primaryKey()->unsigned()->notNull(),
                'extId' => $this->bigInteger()->notNull(),
                'dval' => $this->text()->notNull(),
                'sval' => $this->text()->notNull(),
                'nval' => $this->text()->defaultValue(null),
            ]
        );
        $this->addForeignKey('someIdx', 'migrik_testfk', 'extId', 'migrik_test3', 'id');
        $this->createTable(
            'migrik_testcomposite',
            [
                'id' => $this->integer()->unsigned()->notNull(),
                'otherId' => $this->bigInteger()->notNull()->comment('SomeComment'),
                'val' => $this->text()->notNull(),
            ],$tableOptions
        );
        $this->addPrimaryKey('otherPk', 'migrik_testcomposite', ['id', 'otherId']);

        $this->createTable(
            'migrik_model',
            [
                'id' => $this->primaryKey(),
                'username' => $this->string(255)->notNull(),
                'email' => $this->string(255)->notNull(),
                'password' => $this->string(255)->notNull(),
                'remember_token' => $this->string(100),
                'access_token' => $this->string(100),
                'created_at' => $this->timestamp(),
                'updated_at' => $this->timestamp(0)->defaultExpression('CURRENT_TIMESTAMP'),
                'role' => $this->string(15)->notNull()->defaultValue('user'),
            ], $tableOptions
        );

        $this->createTable(
            'migrik_special',
            [
                'id' => $this->char(30)->unique()->notNull(),
                'enumField' => "ENUM('uno','dos','tres') DEFAULT  'dos'",
                'setField' => "SET('one','two','three') DEFAULT  'two'",
                'timeStampField' => $this->timestamp(0)->defaultExpression('CURRENT_TIMESTAMP'),
            ], $tableOptions
        );

    }

    private function clearTables()
    {
        $this->dropTableIfExists('migrik_special');
        $this->dropTableIfExists('migrik_testcomposite');
        $this->dropTableIfExists('migrik_testfk');
        $this->dropTableIfExists('migrik_test3');
        $this->dropTableIfExists('migrik_test2');
        $this->dropTableIfExists('migrik_test1');
        $this->dropTableIfExists('migrik_model');
    }

    private function dropTableIfExists(string $table)
    {
        $this->db->createCommand('DROP TABLE IF EXISTS '.$this->db->quoteTableName($table))
                 ->execute();
    }

    public function safeDown()
    {
        $this->dropTable('migrik_special');
        $this->dropPrimaryKey('otherPk', 'migrik_testcomposite');
        $this->dropTable('migrik_testcomposite');
        $this->dropForeignKey('someIdx', 'migrik_testfk');
        $this->dropTable('migrik_testfk');
        $this->dropTable('migrik_test3');
        $this->dropIndex('strFieldUniq', 'migrik_test2');
        $this->dropTable('migrik_test2');
        $this->dropIndex('complexIdx', 'migrik_test1');
        $this->dropTable('migrik_test1');
        $this->dropTable('migrik_model');
    }
}
