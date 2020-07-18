<?php
/**
 * Created by solly [11.08.16 5:34]
 */

namespace tests\unit\mariadb;

use Codeception\Specify;
use Codeception\Verify;
use insolita\migrik\resolver\FluentColumnResolver;
use Yii;

/**
 * @var Verify
 **/
class FluentColumnResolverTest extends \tests\unit\common\FluentColumnResolverTest
{
    use Specify;

    protected $db;

    public function testResolveMysqlReal()
    {
        $schema = $this->db->getSchema();
        $tschema = $this->db->getTableSchema('migrik_test1');
        $resolver = new FluentColumnResolver($schema, $tschema);
        $fixture = [
            'id' => '$this->primaryKey(11)',
            'charField' => '$this->char(1)->null()->defaultValue(null)',
            'strField' => '$this->string(255)->null()->defaultValue(null)',
            'textField' => '$this->text()->null()->defaultValue(null)',
            'smallintField' => '$this->smallInteger(6)->null()->defaultValue(null)',
            'tinyintField' => '$this->tinyInteger(2)->null()->defaultValue(null)',
            'intField' => '$this->integer(11)->null()->defaultValue(null)',
            'bigintField' => '$this->bigInteger(20)->null()->defaultValue(null)',
            'floatField' => '$this->float()->null()->defaultValue(null)',
            'doubleField' => '$this->double()->null()->defaultValue(null)',
            'decimalField' => '$this->decimal(5, 2)->null()->defaultValue(null)',
            'datetimeField' => '$this->datetime()->null()->defaultValue(null)',
            'timeStampField' => '$this->timestamp()->notNull()->defaultExpression("CURRENT_TIMESTAMP")',
            'timeField' => '$this->time()->null()->defaultValue(null)',
            'dateField' => '$this->date()->null()->defaultValue(null)',
            'binaryField' => '$this->binary()->null()->defaultValue(null)',
            'boolField' => '$this->tinyInteger(1)->null()->defaultValue(null)',
            'moneyField' => '$this->decimal(5, 1)->null()->defaultValue(null)',
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
        $resolver = new FluentColumnResolver($schema, $tschema);
        $fixture = [
            'id' => '$this->char(30)->notNull()',
            'enumField' => '"enum(\'uno\', \'dos\', \'tres\') NULL DEFAULT \'dos\'"',
            'setField' => '"set(\'one\',\'two\',\'three\') NULL DEFAULT \'two\'"',
            'timeStampField' => '$this->timestamp()->notNull()->defaultExpression("CURRENT_TIMESTAMP")',
        ];
        foreach ($fixture as $field => $expected) {
            $resolved = $resolver->resolveColumn($field);
            verify($field, $resolved)->equals($expected);
        }
    }

    protected function setUp()
    {
        parent::setUp();
        $this->db = Yii::$app->mariadb;
    }
}
