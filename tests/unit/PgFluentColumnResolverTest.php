<?php
/**
 * Created by solly [11.08.16 5:34]
 */

namespace insolita\migrik\tests\unit;

use Codeception\Specify;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use Codeception\Verify;
use insolita\migrik\resolver\PgFluentColumnResolver;

/**
 * @var Verify
 **/
class PgFluentColumnResolverTest extends Unit
{
    use Specify;
    
    protected $db;
    
    public function testResolvePgsqlReal()
    {
        $schema = $this->db->getSchema();
        $tschema = $this->db->getTableSchema('migrik_test1');
        $resolver = new PgFluentColumnResolver($schema, $tschema);
        $fixture = [
            'id'             => '$this->primaryKey()',
            'charField'      => '$this->char(1)',
            'strField'       => '$this->string(255)',
            'textField'      => '$this->text()',
            'smallintField'  => '$this->smallInteger(16)',
            'tinyintField'  => '$this->tinyInteger(2)',
            'intField'       => '$this->integer(32)',
            'bigintField'    => '$this->bigInteger(64)',
            'floatField'     => '$this->double(53)',
            'doubleField'    => '$this->double(53)',
            'decimalField'   => '$this->decimal(5, 2)',
            'datetimeField'  => '$this->timestamp()',
            'timeStampField' => '$this->timestamp()',
            'timeField'      => '$this->time()',
            'dateField'      => '$this->date()',
            'binaryField'    => '$this->binary()',
            'boolField'      => '$this->boolean()',
            'moneyField'     => '$this->decimal(5, 1)',
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
            'id'=> '$this->char(30)->notNull()',
            'arrField'=> '"int[]"',
            'arrField2'=> '"text[]"',
            'arrField3'=> '"int[] DEFAULT ARRAY[2, 3, 4, 5, 6]"',
            'arrField4'=> '"text[] NOT NULL DEFAULT ARRAY[\'this\'::text, \'some\'::text, \'test\'::text, \'data\'::text]"',
            'arrField5'=> '"int[] NOT NULL"',
            'arrField6'=> '"int[] NOT NULL"',
            'jsonField'=> '"json"',
            'jsonField2'=> '"json DEFAULT \'{\"one\":\"foo\",\"two\":\"bar\"}\'"',
            'datetimeField'=> '$this->timestamp()->null()->defaultExpression("now()")',
            'binaryField'=> '$this->binary()->null()->defaultValue(\'\x44756d6d7956616c\')',
            'zval'=> '$this->text()->null()->defaultValue(\'dummy\')',
            'jsonb'=>'"json"'
        ];
        foreach ($fixture as $field => $expected) {
            $resolved = $resolver->resolveColumn($field);
            verify($field, $resolved)->equals($expected);
        }
    }
    protected function _before()
    {
        $this->db = \Yii::$app->db;
        Debug::debug($this->db->dsn);
    }
}