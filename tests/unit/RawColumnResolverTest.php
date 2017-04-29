<?php
/**
 * Created by solly [11.08.16 5:34]
 */

namespace insolita\migrik\tests\unit;

use Codeception\Specify;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use Codeception\Verify;
use insolita\migrik\resolver\RawColumnResolver;
use yii\db\ColumnSchema;
use yii\db\ColumnSchemaBuilder;
use yii\db\Expression;
use yii\db\Schema;
use yii\db\TableSchema;

/**
 * @var Verify
 **/
class RawColumnResolverTest extends Unit
{
    use Specify;
    
    protected $db;
    
    public function testClassBehavior()
    {
        $this->specify(
            'check pk column types',
            function () {
                $tschema = $this->getMockBuilder(TableSchema::class)
                                ->getMock();
                $tschema->expects($this->exactly(4))->method('getColumn')->willReturnOnConsecutiveCalls(
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_BIGPK]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_PK]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_UBIGPK]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_UPK])
                );
                $schema = \Yii::$app->getDb()->getSchema();
                $resolver = $this->getMockBuilder(RawColumnResolver::class)
                                 ->setConstructorArgs([$schema, $tschema])
                                 ->setMethods(['resolvePk'])
                                 ->enableProxyingToOriginalMethods()
                                 ->getMock();
                $resolver->expects($this->exactly(4))->method('resolvePk');
                for ($i = 1; $i <= 4; $i++) {
                    $resolver->resolveColumn('col');
                }
            }
        );
        
        $this->specify(
            'check numeric column types',
            function () {
                $schema = \Yii::$app->getDb()->getSchema();
                $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
                $tschema->expects($this->exactly(6))->method('getColumn')->willReturnOnConsecutiveCalls(
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_SMALLINT]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_INTEGER]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_BIGINT]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_DOUBLE]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_DECIMAL]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_FLOAT]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_BOOLEAN]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_MONEY])
                );
                $resolver = $this->getMockBuilder(RawColumnResolver::class)->setConstructorArgs(
                    [$schema, $tschema]
                )->setMethods(['resolveNumeric'])->enableProxyingToOriginalMethods()->getMock();
                $resolver->expects($this->exactly(6))->method('resolveNumeric');
                for ($i = 1; $i <= 6; $i++) {
                    $resolver->resolveColumn('col');
                }
            }
        );
        
        $this->specify(
            'check datetime column types',
            function () {
                $schema = \Yii::$app->getDb()->getSchema();
                $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
                $tschema->expects($this->exactly(5))->method('getColumn')->willReturnOnConsecutiveCalls(
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_DATE]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_TIME]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_DATE]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_DATETIME]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_TIMESTAMP])
                );
                $resolver = $this->getMockBuilder(RawColumnResolver::class)->setConstructorArgs(
                    [$schema, $tschema]
                )->setMethods(['resolveTime'])->enableProxyingToOriginalMethods()->getMock();
                $resolver->expects($this->exactly(5))->method('resolveTime');
                for ($i = 1; $i <= 5; $i++) {
                    $resolver->resolveColumn('col');
                }
            }
        );
        
        $this->specify(
            'check other column types',
            function () {
                $schema = \Yii::$app->getDb()->getSchema();
                $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
                $tschema->expects($this->exactly(1))->method('getColumn')->willReturnOnConsecutiveCalls(
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_BINARY])
                );
                $resolver = $this->getMockBuilder(RawColumnResolver::class)->setConstructorArgs(
                    [$schema, $tschema]
                )->setMethods(['resolveOther'])->enableProxyingToOriginalMethods()->getMock();
                $resolver->expects($this->exactly(1))->method('resolveOther');
                $resolver->resolveColumn('col');
            }
        );
        
        $this->specify(
            'check string column types',
            function () {
                $schema = \Yii::$app->getDb()->getSchema();
                $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
                $tschema->expects($this->exactly(3))->method('getColumn')->willReturnOnConsecutiveCalls(
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_TEXT]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_STRING]),
                    new ColumnSchema(['name' => 'col', 'dbType' => 'varchar', 'type' => Schema::TYPE_CHAR])
                );
                $resolver = $this->getMockBuilder(RawColumnResolver::class)->setConstructorArgs(
                    [$schema, $tschema]
                )->setMethods(['resolveString'])->enableProxyingToOriginalMethods()->getMock();
                $resolver->expects($this->exactly(3))->method('resolveString');
                for ($i = 1; $i <= 3; $i++) {
                    $resolver->resolveColumn('col');
                }
            }
        );
        
        $this->specify(
            'check customization',
            function () {
                $schema = $this->getMockBuilder(Schema::class)->getMock();
                $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
                $tschema->expects($this->exactly(1))->method('getColumn')->willReturnOnConsecutiveCalls(
                    new ColumnSchema(['name' => 'col', 'dbType' => 'enum', 'type' => Schema::TYPE_TEXT])
                );
                $cbuilder = $this->getMockBuilder(ColumnSchemaBuilder::class)->disableOriginalConstructor()->getMock();
                $resolver = $this->getMockBuilder(RawColumnResolver::class)->setConstructorArgs(
                    [$schema, $tschema]
                )->setMethods(['resolveEnumType'])->enableProxyingToOriginalMethods()->getMock();
                $resolver->expects($this->exactly(1))->method('resolveEnumType');
                $resolver->resolveColumn('col');
            }
        );
        
    }
    
    /**
     * @depends  testClassBehavior
     */
    public function testResolveString()
    {
        $test = [
            [
                'col'    => new ColumnSchema(
                    ['type' => Schema::TYPE_TEXT, 'allowNull' => false, 'dbType' => 'text', 'size' => 1000]
                ),
                'expect' => 'Schema::TYPE_TEXT' . '."(1000) NOT NULL"',
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
                'expect' => 'Schema::TYPE_TEXT' . '." NOT NULL DEFAULT \'blabla\'"',
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
                'expect' => 'Schema::TYPE_STRING' . '." DEFAULT NULL COMMENT \'Some comment\'"',
            ],
        
        ];
        
        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $resolver = new RawColumnResolver($schema, $tschema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }
    
    /**
     * @depends  testClassBehavior
     */
    public function testResolvePk()
    {
        $test = [
            [
                'col'    => new ColumnSchema(
                    ['type' => Schema::TYPE_PK, 'allowNull' => true, 'dbType' => 'string', 'size' => 1000]
                ),
                'expect' => 'Schema::TYPE_PK',
            ],
            [
                'col'    => new ColumnSchema(['type' => Schema::TYPE_UBIGPK, 'comment' => 'It`s really big']),
                'expect' => 'Schema::TYPE_UBIGPK' . '." COMMENT \'It`s really big\'"',
            ],
        
        ];
        
        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $resolver = new RawColumnResolver($schema, $tschema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }
    
    /**
     * @depends  testClassBehavior
     */
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
                'expect' => 'Schema::TYPE_BOOLEAN' . '." DEFAULT TRUE"',
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
                'expect' => 'Schema::TYPE_BOOLEAN' . '." NOT NULL DEFAULT FALSE"',
            ],
            [
                'col'    => new ColumnSchema(
                    [
                        'type'   => Schema::TYPE_BOOLEAN,
                        'dbType' => 'bool',
                    ]
                ),
                'expect' => 'Schema::TYPE_BOOLEAN' . '." NOT NULL"',
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
                'expect' => 'Schema::TYPE_DECIMAL' . '."(8, 2) NOT NULL DEFAULT 340.23"',
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
                'expect' => 'Schema::TYPE_FLOAT' . '."(3) UNSIGNED NOT NULL DEFAULT 340.213"',
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
                'expect' => 'Schema::TYPE_INTEGER' . '."(6) UNSIGNED NOT NULL DEFAULT 0"',
            ],
            [
                'col'    => new ColumnSchema(
                    [
                        'type'         => Schema::TYPE_BIGINT,
                        'size'         => 15,
                        'defaultValue' => 0,
                        'dbType'       => 'float',
                    ]
                ),
                'expect' => 'Schema::TYPE_BIGINT' . '."(15) NOT NULL DEFAULT 0"',
            ],
        
        ];
        
        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $resolver = new RawColumnResolver($schema, $tschema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }
    
    /**
     * @depends  testClassBehavior
     */
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
                'expect' => 'Schema::TYPE_DATE' . '." NOT NULL DEFAULT CURRENT_DATE"',
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
                'expect' => 'Schema::TYPE_DATETIME' . '."(0) NOT NULL DEFAULT NOW()"',
            ],
        ];
        
        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $resolver = new RawColumnResolver($schema, $tschema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }
    
    /**
     * @depends  testClassBehavior
     */
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
                'expect' => '"enum(\'one\', \'two\', \'three\') DEFAULT \'two\'"',
            ],
        
        ];
        
        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $resolver = new RawColumnResolver($schema, $tschema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }
    
    public function testResolveMysqlReal()
    {
        $schema = $this->db->getSchema();
        $tschema = $this->db->getTableSchema('migrik_test1');
        $resolver = new RawColumnResolver($schema, $tschema);
        $fixture = [
            'id'             => 'Schema::TYPE_PK',
            'charField'      => 'Schema::TYPE_CHAR."(1) DEFAULT NULL"',
            'strField'       => 'Schema::TYPE_STRING."(255) DEFAULT NULL"',
            'textField'      => 'Schema::TYPE_TEXT." DEFAULT NULL"',
            'smallintField'  => 'Schema::TYPE_SMALLINT."(6) DEFAULT NULL"',
            'intField'       => 'Schema::TYPE_INTEGER."(11) DEFAULT NULL"',
            'bigintField'    => 'Schema::TYPE_BIGINT."(20) DEFAULT NULL"',
            'floatField'     => 'Schema::TYPE_FLOAT." DEFAULT NULL"',
            'doubleField'    => 'Schema::TYPE_DOUBLE." DEFAULT NULL"',
            'decimalField'   => 'Schema::TYPE_DECIMAL."(5, 2) DEFAULT NULL"',
            'datetimeField'  => 'Schema::TYPE_DATETIME." DEFAULT NULL"',
            'timeStampField' => 'Schema::TYPE_TIMESTAMP." NOT NULL DEFAULT CURRENT_TIMESTAMP"',
            'timeField'      => 'Schema::TYPE_TIME." DEFAULT NULL"',
            'dateField'      => 'Schema::TYPE_DATE." DEFAULT NULL"',
            'binaryField'    => 'Schema::TYPE_BINARY." DEFAULT NULL"',
            'boolField'      => 'Schema::TYPE_SMALLINT."(1) DEFAULT NULL"',
            'moneyField'     => 'Schema::TYPE_DECIMAL."(5, 1) DEFAULT NULL"',
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
        $resolver = new RawColumnResolver($schema, $tschema);
        $fixture = [
            'id'=> 'Schema::TYPE_CHAR."(30) NOT NULL"',
            'enum'=> '"enum(\'uno\', \'dos\', \'tres\') DEFAULT \'dos\'"',
            'set'=> '"set(\'one\',\'two\',\'three\') DEFAULT \'two\'"',
            'timeStampField'=> 'Schema::TYPE_TIMESTAMP." NOT NULL DEFAULT CURRENT_TIMESTAMP"',
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