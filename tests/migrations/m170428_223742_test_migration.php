<?php

use yii\db\Migration;

class m170428_223742_test_migration extends Migration
{
    public function safeUp()
    {
        $this->createTable(
            'migrik_test1',
            [
                'id'             => $this->primaryKey(),
                'charField'      => $this->char(),
                'strField'       => $this->string(),
                'textField'      => $this->text(),
                'smallintField'  => $this->smallInteger()->unique(),
                'intField'       => $this->integer(),
                'bigintField'    => $this->bigInteger(),
                'floatField'     => $this->float(2),
                'doubleField'    => $this->double(5),
                'decimalField'   => $this->decimal(5, 2),
                'datetimeField'  => $this->dateTime(0),
                'timeStampField' => $this->timestamp(0),
                'timeField'      => $this->time(0),
                'dateField'      => $this->date(),
                'binaryField'    => $this->binary(),
                'boolField'      => $this->boolean(),
                'moneyField'     => $this->money(5),
            ]
        );
        $this->createIndex(
            'complexIdx',
            'migrik_test1',
            [
                'charField',
                'boolField',
            ]
        );
        $this->createTable(
            'migrik_test2',
            [
                'id'             => $this->bigPrimaryKey(),
                'charField'      => $this->char(128)->null(),
                'strField'       => $this->string(100)->null(),
                'textField'      => $this->text(1000)->null(),
                'smallintField'  => $this->smallInteger(3)->null(),
                'intField'       => $this->integer(11)->null(),
                'bigintField'    => $this->bigInteger()->null(),
                'floatField'     => $this->float(2)->null(),
                'doubleField'    => $this->double(5)->null(),
                'decimalField'   => $this->decimal(5, 2)->null(),
                'datetimeField'  => $this->dateTime(0)->null(),
                'timeStampField' => $this->timestamp(0)->null(),
                'timeField'      => $this->time(0)->null(),
                'dateField'      => $this->date()->null(),
                'binaryField'    => $this->binary()->null(),
                'boolField'      => $this->boolean()->null(),
                'moneyField'     => $this->money(2)->null(),
            ]
        );
        $this->createIndex('strFieldUniq', 'migrik_test2', ['strField'], true);
        $this->createTable(
            'migrik_test3',
            [
                'id'             => $this->bigPrimaryKey(),
                'charField'      => $this->char(128)->notNull(),
                'strField'       => $this->string(100)->notNull()->defaultValue('dummy'),
                'textField'      => $this->text(1000)->defaultValue(null),
                'smallintField'  => $this->smallInteger(3)->defaultValue(2),
                'intField'       => $this->integer(11)->notNull()->defaultValue(3),
                'bigintField'    => $this->bigInteger()->notNull()->defaultValue(4),
                'floatField'     => $this->float(2)->defaultValue(323.33),
                'doubleField'    => $this->double(5)->defaultValue(323.33),
                'decimalField'   => $this->decimal(5, 2)->defaultValue(323.33),
                'datetimeField'  => $this->dateTime(0)->defaultExpression('NOW()'),
                'timeStampField' => $this->timestamp(0)->defaultValue(
                    \Carbon\Carbon::create(2005, 9, 21, 10, 40, 01)
                                  ->format('Y-m-d H:i:s.u')
                ),
                'timeField'      => $this->time(0)->defaultValue(
                    \Carbon\Carbon::createFromTime(10, 40, 01)->toTimeString()
                ),
                'dateField'      => $this->date()->defaultValue(
                    Carbon\Carbon::createFromDate(2005, 9, 21)->toDateString()
                ),
                'binaryField'    => $this->binary()->defaultValue('DummyVal'),
                'boolField'      => $this->boolean()->defaultValue(false),
                'moneyField'     => $this->money(2)->defaultValue(334),
            ]
        );
        
        $this->createTable(
            'migrik_testfk',
            [
                'id'    => $this->primaryKey()->unsigned()->notNull(),
                'extId' => $this->bigInteger()->notNull(),
                'dval'  => $this->text()->notNull()->defaultValue(''),
                'sval'  => $this->text()->notNull(),
                'nval'  => $this->text()->defaultValue(null),
                'zval'  => $this->text()->null()->defaultValue('dummy'),
            ]
        );
        $this->addForeignKey('someIdx', 'migrik_testfk', 'extId', 'migrik_test3', 'id');
        $this->createTable(
            'migrik_testcomposite',
            [
                'id'      => $this->integer()->unsigned()->notNull(),
                'otherId' => $this->bigInteger()->notNull()->comment('SomeComment'),
                'val'     => $this->text()->notNull()->defaultValue(''),
            ]
        );
        $this->addPrimaryKey('otherPk', 'migrik_testcomposite', ['id', 'otherId']);
        if ($this->getDb()->driverName === 'pgsql') {
            $this->createTable(
                'migrik_pgspec',
                [
                    'id'        => $this->char(30)->unique()->notNull(),
                    'arrField'  => 'int[]',
                    'jsonField' => 'JSON',
                ]
            );
        }
    }
    
    public function safeDown()
    {
        if ($this->getDb()->driverName === 'pgsql') {
            $this->dropTable('migrik_pgspec');
        }
        $this->dropPrimaryKey('otherPk', 'migrik_testcomposite');
        $this->dropTable('migrik_testcomposite');
        $this->dropForeignKey('someIdx', 'migrik_testfk');
        $this->dropTable('migrik_testfk');
        $this->dropTable('migrik_test3');
        $this->dropIndex('strFieldUniq', 'migrik_test2');
        $this->dropTable('migrik_test2');
        $this->dropIndex('complexIdx', 'migrik_test1');
        $this->dropTable('migrik_test1');
    }
}
