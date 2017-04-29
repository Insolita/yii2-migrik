<?php
/**
 * Created by solly [11.08.16 5:34]
 */

namespace insolita\migrik\tests\unit;

use Codeception\Specify;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use Codeception\Verify;
use insolita\migrik\resolver\PgRawColumnResolver;

/**
 * @var Verify
 **/
class PgRawColumnResolverTest extends Unit
{
    use Specify;
    
    protected $db;
    
    public function testResolvePgsqlReal()
    {
        $schema = $this->db->getSchema();
        $tschema = $this->db->getTableSchema('migrik_test1');
        $resolver = new PgRawColumnResolver($schema, $tschema);
        $fixture = [
            'id'             => 'Schema::TYPE_PK',
            'charField'      => 'Schema::TYPE_CHAR."(1) "',
            'strField'       => 'Schema::TYPE_STRING."(255) "',
            'textField'      => 'Schema::TYPE_TEXT',
            'smallintField'  => 'Schema::TYPE_SMALLINT."(16) "',
            'intField'       => 'Schema::TYPE_INTEGER."(32) "',
            'bigintField'    => 'Schema::TYPE_BIGINT."(64) "',
            'floatField'     => 'Schema::TYPE_DOUBLE."(53) "',
            'doubleField'    => 'Schema::TYPE_DOUBLE."(53) "',
            'decimalField'   => 'Schema::TYPE_DECIMAL."(5, 2) "',
            'datetimeField'  => 'Schema::TYPE_TIMESTAMP',
            'timeStampField' => 'Schema::TYPE_TIMESTAMP',
            'timeField'      => 'Schema::TYPE_TIME',
            'dateField'      => 'Schema::TYPE_DATE',
            'binaryField'    => 'Schema::TYPE_BINARY',
            'boolField'      => 'Schema::TYPE_BOOLEAN',
            'moneyField'     => 'Schema::TYPE_DECIMAL."(5, 1) "',
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
        $resolver = new PgRawColumnResolver($schema, $tschema);
        $fixture = [
            'id'=> 'Schema::TYPE_CHAR."(30) NOT NULL"',
            'arrField'=> '"int[]"',
            'arrField2'=> '"text[]"',
            'arrField3'=> '"int[] DEFAULT ARRAY[2, 3, 4, 5, 6]"',
            'arrField4'=> '"text[] NOT NULL DEFAULT ARRAY[\'this\'::text, \'some\'::text, \'test\'::text, \'data\'::text]"',
            'arrField5'=> '"int[] NOT NULL"',
            'arrField6'=> '"int[] NOT NULL"',
            'jsonField'=> '"json"',
            'jsonField2'=> '"json DEFAULT \'{\"one\":\"foo\",\"two\":\"bar\"}\'"',
            'datetimeField'=> 'Schema::TYPE_TIMESTAMP." DEFAULT now()"',
            'binaryField'=> 'Schema::TYPE_BINARY." DEFAULT \'\x44756d6d7956616c\'"',
            'zval'=> 'Schema::TYPE_TEXT." DEFAULT \'dummy\'"'
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