<?php
/**
 * Created by solly [11.08.16 5:34]
 */

namespace insolita\migrik\tests\unit;

use Codeception\Specify;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use Codeception\Verify;
use insolita\migrik\resolver\FluentColumnResolver;
use yii\db\ColumnSchema;
use yii\db\Expression;
use yii\db\Schema;
use yii\db\TableSchema;

/**
 * @var Verify
 **/
class FluentColumnResolverTest extends Unit
{
    use Specify;
    
    protected $db;
    
    public function testResolveString()
    {
        $test = [
            [
                'col'    => new ColumnSchema(
                    ['type' => Schema::TYPE_TEXT, 'allowNull' => false, 'dbType' => 'text', 'size' => 1000]
                ),
                'expect' => '$this->text(1000)->notNull()',
            ],
            [
                'col'    => new ColumnSchema(
                    [
                        'type'         => Schema::TYPE_TEXT,
                        'allowNull'    => false,
                        'defaultValue' => 'blabla',
                        'dbType'       => 'text',
                    ]
                ),
                'expect' => '$this->text()->notNull()->defaultValue(\'blabla\')',
            ],
            [
                'col'    => new ColumnSchema(
                    [
                        'type'      => Schema::TYPE_STRING,
                        'allowNull' => true,
                        'comment'   => 'Some comment',
                        'dbType'    => 'char',
                    ]
                ),
                'expect' => '$this->string()->null()->defaultValue(null)->comment(\'Some comment\')',
            ],
            [
                'col'    => new ColumnSchema(
                    [
                        'type'      => Schema::TYPE_BINARY,
                        'allowNull' => false,
                        'comment'   => 'Some comment',
                        'dbType'    => 'binary',
                    ]
                ),
                'expect' => '$this->binary()->notNull()->comment(\'Some comment\')',
            ],
        
        ];
        
        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $resolver = new FluentColumnResolver($schema, $tschema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }
    
    public function testResolvePk()
    {
        $test = [
            [
                'col'    => new ColumnSchema(
                    ['type' => Schema::TYPE_PK, 'allowNull' => true, 'dbType' => 'string']
                ),
                'expect' => '$this->primaryKey()',
            ],
            [
                'col'    => new ColumnSchema(['type' => Schema::TYPE_UBIGPK, 'comment' => 'It`s really big']),
                'expect' => '$this->bigPrimaryKey()->unsigned()->comment(\'It`s really big\')',
            ],
        
        ];
        
        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $resolver = new FluentColumnResolver($schema, $tschema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }
    
    public function testResolveNumeric()
    {
        $test = [
            [
                'col'    => new ColumnSchema(
                    [
                        'type'         => Schema::TYPE_BOOLEAN,
                        'allowNull'    => true,
                        'dbType'       => 'bool',
                        'defaultValue' => true,
                    ]
                ),
                'expect' => '$this->boolean()->null()->defaultValue(true)',
            ],
            [
                'col'    => new ColumnSchema(
                    [
                        'type'         => Schema::TYPE_BOOLEAN,
                        'allowNull'    => false,
                        'dbType'       => 'bool',
                        'defaultValue' => false,
                    ]
                ),
                'expect' => '$this->boolean()->notNull()->defaultValue(false)',
            ],
            [
                'col'    => new ColumnSchema(
                    [
                        'type'   => Schema::TYPE_BOOLEAN,
                        'dbType' => 'bool',
                    ]
                ),
                'expect' => '$this->boolean()->notNull()',
            ],
            
            [
                'col'    => new ColumnSchema(
                    [
                        'type'         => Schema::TYPE_DECIMAL,
                        'scale'        => 2,
                        'precision'    => 8,
                        'defaultValue' => 340.23,
                        'dbType'       => 'decimal',
                    ]
                ),
                'expect' => '$this->decimal(8, 2)->notNull()->defaultValue("340.23")',
            ],
            [
                'col'    => new ColumnSchema(
                    [
                        'type'         => Schema::TYPE_FLOAT,
                        'precision'    => 3,
                        'defaultValue' => 340.213,
                        'unsigned'     => true,
                        'dbType'       => 'float',
                    ]
                ),
                'expect' => '$this->float(3)->unsigned()->notNull()->defaultValue("340.213")',
            ],
            [
                'col'    => new ColumnSchema(
                    [
                        'type'         => Schema::TYPE_INTEGER,
                        'size'         => 6,
                        'defaultValue' => 0,
                        'unsigned'     => true,
                        'dbType'       => 'float',
                    ]
                ),
                'expect' => '$this->integer(6)->unsigned()->notNull()->defaultValue(0)',
            ],
            [
                'col'    => new ColumnSchema(
                    [
                        'type'         => Schema::TYPE_SMALLINT,
                        'size'         => 3,
                        'defaultValue' => 15,
                        'unsigned'     => true,
                    ]
                ),
                'expect' => '$this->smallInteger(3)->unsigned()->notNull()->defaultValue(15)',
            ],
            [
                'col'    => new ColumnSchema(
                    [
                        'type'         => Schema::TYPE_TINYINT,
                        'size'         => 2,
                        'defaultValue' => 15,
                        'unsigned'     => true,
                    ]
                ),
                'expect' => '$this->tinyInteger(3)->unsigned()->notNull()->defaultValue(15)',
            ],
            [
                'col'    => new ColumnSchema(
                    [
                        'type'         => Schema::TYPE_BIGINT,
                        'size'         => 15,
                        'defaultValue' => 15,
                        'unsigned'     => true,
                    ]
                ),
                'expect' => '$this->bigInteger(15)->unsigned()->notNull()->defaultValue(15)',
            ],
        ];
        
        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $resolver = new FluentColumnResolver($schema, $tschema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }
    
    public function testResolveTime()
    {
        $test = [
            [
                'col'    => new ColumnSchema(
                    [
                        'type'         => Schema::TYPE_DATE,
                        'allowNull'    => false,
                        'dbType'       => 'date',
                        'defaultValue' => 'CURRENT_DATE',
                    ]
                ),
                'expect' => '$this->date()->notNull()->defaultExpression("CURRENT_DATE")',
            ],
            [
                'col'    => new ColumnSchema(
                    [
                        'type'         => Schema::TYPE_DATETIME,
                        'allowNull'    => false,
                        'precision'    => 0,
                        'dbType'       => 'datetime',
                        'defaultValue' => new Expression('NOW()'),
                    ]
                ),
                'expect' => '$this->datetime(0)->notNull()->defaultExpression("NOW()")',
            ],
        ];
        
        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $resolver = new FluentColumnResolver($schema, $tschema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }
    
    public function testResolveEnumType()
    {
        $test = [
            [
                'col'    => new ColumnSchema(
                    [
                        'type'         => Schema::TYPE_STRING,
                        'allowNull'    => true,
                        'dbType'       => 'enum',
                        'enumValues'   => ['one', 'two', 'three'],
                        'defaultValue' => 'two',
                    ]
                ),
                'expect' => '"enum(\'one\', \'two\', \'three\') NULL DEFAULT \'two\'"'
,
            ],
        
        ];
        
        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $resolver = new FluentColumnResolver($schema, $tschema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }
    
    public function testResolveMysqlReal()
    {
        $schema = $this->db->getSchema();
        $tschema = $this->db->getTableSchema('migrik_test1');
        $resolver = new FluentColumnResolver($schema, $tschema);
        $fixture = [
            'id'             => '$this->primaryKey(11)',
            'charField'      => '$this->char(1)->null()->defaultValue(null)',
            'strField'       => '$this->string(255)->null()->defaultValue(null)',
            'textField'      => '$this->text()->null()->defaultValue(null)',
            'smallintField'  => '$this->smallInteger(6)->null()->defaultValue(null)',
            'tinyintField'  => '$this->tinyInteger(2)->null()->defaultValue(null)',
            'intField'       => '$this->integer(11)->null()->defaultValue(null)',
            'bigintField'    => '$this->bigInteger(20)->null()->defaultValue(null)',
            'floatField'     => '$this->float()->null()->defaultValue(null)',
            'doubleField'    => '$this->double()->null()->defaultValue(null)',
            'decimalField'   => '$this->decimal(5, 2)->null()->defaultValue(null)',
            'datetimeField'  => '$this->datetime()->null()->defaultValue(null)',
            'timeStampField' => '$this->timestamp()->notNull()->defaultExpression("CURRENT_TIMESTAMP")',
            'timeField'      => '$this->time()->null()->defaultValue(null)',
            'dateField'      => '$this->date()->null()->defaultValue(null)',
            'binaryField'    => '$this->binary()->null()->defaultValue(null)',
            'boolField'      => '$this->smallInteger(1)->null()->defaultValue(null)',
            'moneyField'     => '$this->decimal(5, 1)->null()->defaultValue(null)',
        ];
        foreach ($fixture as $field => $expected) {
            $resolved = $resolver->resolveColumn($field);
            verify($field, $resolved)->equals($expected);
        }
    }
    public function testMysqlSpecific()
    {
        $schema = $this->db->getSchema();
        $tschema = $this->db->getTableSchema('migrik_myspec');
        $resolver = new FluentColumnResolver($schema, $tschema);
        $fixture = [
            'id'=> '$this->char(30)->notNull()',
            'enum'=> '"enum(\'uno\', \'dos\', \'tres\') NULL DEFAULT \'dos\'"',
            'set'=> '"set(\'one\',\'two\',\'three\') NULL DEFAULT \'two\'"',
            'timeStampField'=> '$this->timestamp()->notNull()->defaultExpression("CURRENT_TIMESTAMP")',
        ];
        foreach ($fixture as $field => $expected) {
            $resolved = $resolver->resolveColumn($field);
            verify($field, $resolved)->equals($expected);
        }
    }
    
    protected function _before()
    {
        $this->db = \Yii::$app->dbmm;
        Debug::debug($this->db->dsn);
    }
}