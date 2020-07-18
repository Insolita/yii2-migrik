<?php
/**
 * Created by solly [11.08.16 5:34]
 */

namespace tests\unit\mysql;

use Codeception\Specify;
use Codeception\Verify;
use insolita\migrik\resolver\RawColumnResolver;
use tests\TestCase;
use Yii;

/**
 * @var Verify
 **/
class RawColumnResolverTest extends TestCase
{
    use Specify;

    protected $db;

    protected function setUp()
    {
        parent::setUp();
        $this->db = Yii::$app->mysqldb;
    }

    public function testResolveMysqlReal()
    {
        $schema = $this->db->getSchema();
        $tschema = $this->db->getTableSchema('migrik_test1');
        $resolver = new RawColumnResolver($schema, $tschema);
        $fixture = [
            'id' => 'Schema::TYPE_PK',
            'charField' => 'Schema::TYPE_CHAR."(1) NULL DEFAULT NULL"',
            'strField' => 'Schema::TYPE_STRING."(255) NULL DEFAULT NULL"',
            'textField' => 'Schema::TYPE_TEXT." NULL DEFAULT NULL"',
            'smallintField' => 'Schema::TYPE_SMALLINT."(6) NULL DEFAULT NULL"',
            'intField' => 'Schema::TYPE_INTEGER."(11) NULL DEFAULT NULL"',
            'bigintField' => 'Schema::TYPE_BIGINT."(20) NULL DEFAULT NULL"',
            'floatField' => 'Schema::TYPE_FLOAT." NULL DEFAULT NULL"',
            'doubleField' => 'Schema::TYPE_DOUBLE." NULL DEFAULT NULL"',
            'decimalField' => 'Schema::TYPE_DECIMAL."(5, 2) NULL DEFAULT NULL"',
            'datetimeField' => 'Schema::TYPE_DATETIME." NULL DEFAULT NULL"',
            'timeStampField' => 'Schema::TYPE_TIMESTAMP." NOT NULL DEFAULT CURRENT_TIMESTAMP"',
            'timeField' => 'Schema::TYPE_TIME." NULL DEFAULT NULL"',
            'dateField' => 'Schema::TYPE_DATE." NULL DEFAULT NULL"',
            'binaryField' => 'Schema::TYPE_BINARY." NULL DEFAULT NULL"',
            'boolField' => 'Schema::TYPE_TINYINT."(1) NULL DEFAULT NULL"',
            'moneyField' => 'Schema::TYPE_DECIMAL."(5, 1) NULL DEFAULT NULL"',
        ];
        foreach ($fixture as $field => $expected) {
            $resolved = $resolver->resolveColumn($field);
            verify($field, $resolved)->equals($expected);
        }
    }

    public function testMysqlSpecific()
    {
        $schema = $this->db->getSchema();
        $tschema = $this->db->getTableSchema('migrik_special');
        $resolver = new RawColumnResolver($schema, $tschema);
        $fixture = [
            'id' => 'Schema::TYPE_CHAR."(30) NOT NULL"',
            'enumField' => '"enum(\'uno\', \'dos\', \'tres\') NULL DEFAULT \'dos\'"',
            'setField' => '"set(\'one\',\'two\',\'three\') NULL DEFAULT \'two\'"',
            'timeStampField' => 'Schema::TYPE_TIMESTAMP." NOT NULL DEFAULT CURRENT_TIMESTAMP"',
        ];
        foreach ($fixture as $field => $expected) {
            $resolved = $resolver->resolveColumn($field);
            verify($field, $resolved)->equals($expected);
        }
    }
}
