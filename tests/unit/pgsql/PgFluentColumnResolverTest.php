<?php
/**
 * Created by solly [11.08.16 5:34]
 */

namespace tests\unit\pgsql;

use Codeception\Specify;
use Codeception\Verify;
use insolita\migrik\resolver\PgFluentColumnResolver;
use tests\TestCase;
use Yii;
use const PHP_EOL;

/**
 * @var Verify
 **/
class PgFluentColumnResolverTest extends TestCase
{
    use Specify;

    protected $db;

    protected function setUp()
    {
        parent::setUp();
        $this->db = Yii::$app->db;
    }

    public function testResolvePgsqlReal()
    {
        $schema = $this->db->getSchema();
        $tschema = $this->db->getTableSchema('migrik_test1');
        $resolver = new PgFluentColumnResolver($schema, $tschema);
        $fixture = [
            'id' => '$this->primaryKey()',
            'charField' => '$this->char(1)',
            'strField' => '$this->string(255)',
            'textField' => '$this->text()',
            'smallintField' => '$this->smallInteger(16)',
            'tinyintField' => '$this->smallInteger(16)',
            'intField' => '$this->integer(32)',
            'bigintField' => '$this->bigInteger(64)',
            'floatField' => '$this->double(53)',
            'doubleField' => '$this->double(53)',
            'decimalField' => '$this->decimal(5, 2)',
            'datetimeField' => '$this->timestamp()',
            'timeStampField' => '$this->timestamp()',
            'timeField' => '$this->time()',
            'dateField' => '$this->date()',
            'binaryField' => '$this->binary()',
            'boolField' => '$this->boolean()',
            'moneyField' => '$this->decimal(5, 1)',
        ];
        foreach ($fixture as $field => $expected) {
            $resolved = $resolver->resolveColumn($field);
            verify($field, $resolved)->equals($expected);
        }
    }

    public function testPgsqlSpecific()
    {
        $schema = $this->db->getSchema();
        $tschema = $this->db->getTableSchema('migrik_pgspec');
        $resolver = new PgFluentColumnResolver($schema, $tschema);
        $fixture = [
            'id' => '$this->char(30)->notNull()',
            'arrField' => '"int[]"',
            'arrField2' => '"text[]"',
            'arrField3' => '"int[] DEFAULT \'{2, 3, 4, 5, 6}\'"',
            'arrField4' => '"text[] NOT NULL DEFAULT \'{this, some, test, data}\'"',
            'arrField5' => '"int[] NOT NULL"',
            'arrField6' => '"int[] NOT NULL"',
            'jsonField' => '"json"',
            'jsonField2' => '"json DEFAULT \'{\"one\":\"foo\",\"two\":\"bar\"}\'"',
            'datetimeField' => '$this->timestamp()->null()->defaultExpression("now()")',
            'binaryField' => '$this->binary()->null()->defaultValue(\'\x44756d6d7956616c\')',
            'zval' => '$this->text()->null()->defaultValue(\'dummy\')'
        ];
        foreach ($fixture as $field => $expected) {
            $resolved = $resolver->resolveColumn($field);
            verify($field, $resolved)->equals($expected);
        }
    }
}
