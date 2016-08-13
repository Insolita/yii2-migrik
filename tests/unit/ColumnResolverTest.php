<?php
/**
 * Created by solly [11.08.16 5:34]
 */

namespace insolita\migrik\tests\unit;


use Codeception\Specify;
use Codeception\Util\Debug;
use Codeception\Verify;
use insolita\migrik\resolver\ColumnResolver;
use yii\db\ColumnSchema;
use yii\db\ColumnSchemaBuilder;
use yii\db\Schema;
use yii\db\TableSchema;

/**
 * @var Verify
 **/
class ColumnResolverTest extends DbTestCase
{
    use Specify;

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function fixtures()
    {
        return [

        ];
    }

    public function testClassBehavior()
    {
        $this->specify('check pk column types', function (){
            $schema = $this->getMockBuilder(TableSchema::class)->getMock();
            $schema->expects($this->exactly(4))->method('getColumn')->willReturnOnConsecutiveCalls(
               new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_BIGPK]),
               new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_PK]),
               new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_UBIGPK]),
               new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_UPK])
                                                                                              );
            $cbuilder = $this->getMockBuilder(ColumnSchemaBuilder::class)->disableOriginalConstructor()->getMock();
            $resolver = $this->getMockBuilder(ColumnResolver::class)
                             ->setConstructorArgs([$schema, $cbuilder])
                             ->setMethods(['resolvePk'])
                             ->enableProxyingToOriginalMethods()
                             ->getMock();
            $resolver->expects($this->exactly(4))->method('resolvePk');
            for ($i=1;$i<=4;$i++){
                $resolver->resolveColumn('col');
            }
        });

        $this->specify('check numeric column types', function (){
            $schema = $this->getMockBuilder(TableSchema::class)->getMock();
            $schema->expects($this->exactly(6))->method('getColumn')->willReturnOnConsecutiveCalls(
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_SMALLINT]),
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_INTEGER]),
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_BIGINT]),
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_DOUBLE]),
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_DECIMAL]),
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_FLOAT])
            );
            $cbuilder = $this->getMockBuilder(ColumnSchemaBuilder::class)->disableOriginalConstructor()->getMock();
            $resolver = $this->getMockBuilder(ColumnResolver::class)
                             ->setConstructorArgs([$schema, $cbuilder])
                             ->setMethods(['resolveNumeric'])
                             ->enableProxyingToOriginalMethods()
                             ->getMock();
            $resolver->expects($this->exactly(6))->method('resolveNumeric');
            for ($i=1;$i<=6;$i++){
                $resolver->resolveColumn('col');
            }
        });

        $this->specify('check datetime column types', function (){
            $schema = $this->getMockBuilder(TableSchema::class)->getMock();
            $schema->expects($this->exactly(5))->method('getColumn')->willReturnOnConsecutiveCalls(
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_DATE]),
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_TIME]),
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_DATE]),
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_DATETIME]),
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_TIMESTAMP])
            );
            $cbuilder = $this->getMockBuilder(ColumnSchemaBuilder::class)->disableOriginalConstructor()->getMock();
            $resolver = $this->getMockBuilder(ColumnResolver::class)
                             ->setConstructorArgs([$schema, $cbuilder])
                             ->setMethods(['resolveTime'])
                             ->enableProxyingToOriginalMethods()
                             ->getMock();
            $resolver->expects($this->exactly(5))->method('resolveTime');
            for ($i=1;$i<=5;$i++){
                $resolver->resolveColumn('col');
            }
        });

        $this->specify('check other column types', function (){
            $schema = $this->getMockBuilder(TableSchema::class)->getMock();
            $schema->expects($this->exactly(1))->method('getColumn')->willReturnOnConsecutiveCalls(
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_BINARY])
            );
            $cbuilder = $this->getMockBuilder(ColumnSchemaBuilder::class)->disableOriginalConstructor()->getMock();
            $resolver = $this->getMockBuilder(ColumnResolver::class)
                             ->setConstructorArgs([$schema, $cbuilder])
                             ->setMethods(['resolveOther'])
                             ->enableProxyingToOriginalMethods()
                             ->getMock();
            $resolver->expects($this->exactly(1))->method('resolveOther');
            $resolver->resolveColumn('col');
        });

        $this->specify('check string column types', function (){
            $schema = $this->getMockBuilder(TableSchema::class)->getMock();
            $schema->expects($this->exactly(3))->method('getColumn')->willReturnOnConsecutiveCalls(
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_TEXT]),
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_STRING]),
                new ColumnSchema(['name'=>'col', 'dbType'=>'varchar', 'type'=>Schema::TYPE_CHAR])
            );
            $cbuilder = $this->getMockBuilder(ColumnSchemaBuilder::class)->disableOriginalConstructor()->getMock();
            $resolver = $this->getMockBuilder(ColumnResolver::class)
                             ->setConstructorArgs([$schema, $cbuilder])
                             ->setMethods(['resolveString'])
                             ->enableProxyingToOriginalMethods()
                             ->getMock();
            $resolver->expects($this->exactly(3))->method('resolveString');
            for ($i=1;$i<=3;$i++){
                $resolver->resolveColumn('col');
            }
        });

    }

    public function testInitialization()
    {

    }

    public function testResolveString()
    {

    }

    public function testResolvePk()
    {

    }

    public function testResolveNumeric()
    {

    }

    public function testResolveTime()
    {

    }

    public function testResolveEnumType()
    {

    }
}