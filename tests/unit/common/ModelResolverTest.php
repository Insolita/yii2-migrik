<?php
/**
 * Created by solly [18.08.16 23:24]
 */

namespace tests\unit\common;

use Codeception\Specify;
use Codeception\Verify;
use insolita\migrik\resolver\ModelResolver;
use tests\data\TestModel;
use tests\TestCase;
use yii\helpers\ArrayHelper;

/**
 * @var Verify
 **/
class ModelResolverTest extends TestCase
{
    use Specify;

    public function testGetTableName()
    {
        $resolver = new ModelResolver(TestModel::class);
        $tableName = $resolver->getTableName();
        verify($tableName)->equals(TestModel::tableName());
    }

    public function testGetAttributes()
    {
        $resolver = new ModelResolver(TestModel::class);
        $attrs = $resolver->getAttributes();
        verify($attrs)->notEmpty();
        verify(ArrayHelper::isIndexed($attrs))->true();
        verify($attrs)->contains('id');
        verify($attrs)->contains('username');
        verify($attrs)->contains('password');
        verify($attrs)->notContains('verifyPassword');
        verify($attrs)->notContains('verifyCaptcha');
    }

    public function testGetAttributeLabels()
    {
        $resolver = new ModelResolver(TestModel::class);
        $labels = $resolver->getAttributeLabels();
        verify($labels)->notEmpty();
        verify(ArrayHelper::isAssociative($labels))->true();
        verify(array_keys($labels))->contains('id');
        verify(array_keys($labels))->contains('username');
        verify(array_keys($labels))->contains('password');
        verify(array_values($labels))->contains('ID');
        verify(array_values($labels))->contains('Пароль');
        verify(array_values($labels))->contains('Роль');
    }
}
