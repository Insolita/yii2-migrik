<?php
/**
 * Created by solly [16.08.16 12:28]
 */

namespace insolita\migrik\tests\unit;

use Codeception\Test\Unit;
use Codeception\Util\Debug;
use Codeception\Verify;
use Codeception\Specify;
use insolita\migrik\resolver\PhpDocResolver;
use insolita\migrik\tests\data\HistoryItem;
use insolita\migrik\tests\data\TestModel;

/**
 * @var Verify
 **/
class PhpdocResolverTest extends Unit
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

    public function testGetPhpdoc()
    {
        $resolver = new PhpDocResolver(TestModel::className());
        $doc = $resolver->getPhpdoc();
        verify($doc)->contains('@column');
        verify($doc)->contains('@table');
    }

    public function testGetTableName()
    {
        $resolver = new PhpDocResolver(TestModel::className());
        $table = $resolver->getTableName();
        verify($table)->equals('{{%somenew}}');

        $resolver = new PhpDocResolver(HistoryItem::class);
        $table = $resolver->getTableName();
        verify($table)->equals('{{%guyii_log}}');
    }

    public function testGetConnectionName()
    {
        $resolver = new PhpDocResolver(TestModel::className());
        $table = $resolver->getConnectionName();
        verify($table)->equals('db2');

        $resolver = new PhpDocResolver(HistoryItem::class);
        $table = $resolver->getConnectionName();
        verify($table)->equals('db');
    }

    public function testGetAttributes()
    {
        $resolver = new PhpDocResolver(TestModel::className());
        $info = $resolver->getAttributes();
        verify($info)->equals(
            [
                'id' => 'pk()|comment("Id")',
                'username' => 'string(100)|notNull()|defaultValue("Vasya")',
                'email' => 'string(200)|null()|defaultValue(null)'
            ]
        );

        $resolver = new PhpDocResolver(HistoryItem::class);
        $info = $resolver->getAttributes();
        verify($info)->hasKey('id');
        verify($info)->hasKey('route');
        verify($info)->hasKey('args');
        verify($info)->hasKey('success');
        Debug::debug($info);
    }

    public function testGetAttributes2()
    {
        $phpdoc
            = <<<PHPDOC
        /**************
         *
         * @column(foo) string(10)|unique|notNull
         * @column(bar) decimal(5,2)
         * @property boolean \$baz @column boolean|default('bla')
         * @column(empty)
         * @var string \$zuu @column text|default('bla')
         * 
        */
PHPDOC;


        $resolver = $this->getMockBuilder(PhpDocResolver::class)->setConstructorArgs(
            [HistoryItem::class]
        )->setMethods(['getPhpdoc'])->getMock();
        $resolver->expects($this->any())->method('getPhpdoc')->willReturn($phpdoc);
        verify($resolver->getPhpdoc())->equals($phpdoc);
        $info = $resolver->getAttributes();
        Debug::debug($info);
        verify($info)->hasKey('foo');
        verify($info)->hasKey('bar');
        verify($info)->hasKey('baz');
        verify($info)->hasKey('zuu');
        verify($info)->hasntKey('empty');
    }

}