<?php
/**
 * Created by solly [16.08.16 12:28]
 */

namespace insolita\migrik\tests\unit;

use Codeception\Util\Debug;
use Codeception\Verify;
use Codeception\Specify;
use insolita\migrik\resolver\PhpDocResolver;
use insolita\migrik\tests\data\TestModel;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

/**
 * @var Verify
 **/
class PhpdocResolverTest extends DbTestCase
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
        self::markTestIncomplete();
        $resolver = new PhpDocResolver(TestModel::className());
        $table = $resolver->getTableName();
        verify($table)->equals('{{%somenew}}');
    }

    public function testGetAttributeInfo()
    {
        self::markTestIncomplete();
        $resolver = new PhpDocResolver(TestModel::className());
        $info = $resolver->getAttributeInfo('id');
        verify($info)->equals(['attribute' => 'id', 'type' => 'pk', 'comment' => 'Id']);
        $info = $resolver->getAttributeInfo('username');
        verify($info)->equals(
            ['attribute' => 'username', 'type' => 'string(100)', 'notNull' => true, 'defaultValue' => 'Vasya']
        );
        $info = $resolver->getAttributeInfo('email');
        verify($info)->equals(
            ['attribute' => 'email', 'type' => 'string(200)', 'null' => true, 'defaultValue' => null]
        );
    }

    public function testGetAttributes()
    {
        self::markTestIncomplete();
        $resolver = new PhpDocResolver(TestModel::className());
        $info = $resolver->getAttributes();
        verify($info)->equals(
            [
                'id' => 'pk()|comment("Id")',
                'username' => 'string(100)|notNull()|defaultValue("Vasya")',
                'email' => 'string(200)|null()|defaultValue(null)'
            ]
        );
    }

    public function testPhpdocReflection()
    {
        $class = TestModel::className();
        $ref = new \ReflectionClass($class);
        $info = $ref->getDocComment();
        Debug::debug($info);

        $ext = new PhpDocExtractor();
        $types = $ext->getTypes($class, 'password');
        Debug::debug($types);

        $short = $ext->getShortDescription($class, 'password');
        Debug::debug($types);

        $long = $ext->getLongDescription($class, 'password');
        Debug::debug($types);

    }
}