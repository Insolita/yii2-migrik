<?php
/**
 * Created by solly [11.08.16 5:34]
 */

namespace insolita\migrik\tests\unit;

use Codeception\Specify;
use Codeception\Verify;
use insolita\migrik\resolver\TableResolver;
use yii\db\Schema;
use yii\db\TableSchema;

/**
 * @var Verify
 **/
class TableResolverTest extends DbTestCase
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

    public function testGetSchema()
    {
        $resolver = new TableResolver(\Yii::$app->getDb());
        $schema = $resolver->schema;
        verify($schema)->isInstanceOf(Schema::class);
        verify($schema)->isInstanceOf(\yii\db\pgsql\Schema::class);
    }

    public function testGetTableSchema()
    {
        $resolver = new TableResolver(\Yii::$app->getDb());
        $tableSchema = $resolver->getTableSchema('itt_migration');
        verify_that($tableSchema);
        verify($tableSchema)->isInstanceOf(TableSchema::class);
    }

    public function testGetTableNames()
    {
        $resolver = new TableResolver(\Yii::$app->getDb());
        $tableNames = $resolver->getTableNames();
        verify($tableNames)->notEmpty();
        verify($tableNames)->contains('itt_migration');
        verify($tableNames)->contains('itt_auth_item');
    }

    public function testFindTablesByPattern()
    {
        $resolver = new TableResolver(\Yii::$app->getDb());
        $this->specify(
            'by table name',
            function () use ($resolver) {
                $founds = $resolver->findTablesByPattern('itt_migration');
                verify($founds)->notEmpty();
                verify($founds)->contains('itt_migration');
                verify(count($founds))->equals(1);
            }
        );

        $this->specify(
            'by pattern one result',
            function () use ($resolver) {
                $founds = $resolver->findTablesByPattern('itt_migrat*');
                verify($founds)->notEmpty();
                verify($founds)->contains('itt_migration');
                verify(count($founds))->equals(1);
            }
        );

        $this->specify(
            'by pattern bulk result',
            function () use ($resolver) {
                $founds = $resolver->findTablesByPattern('itt_auth*');
                verify($founds)->notEmpty();
                verify($founds)->contains('itt_auth_rule');
                verify(count($founds))->equals(4);
            }
        );
    }

    public function testGetRelations()
    {
        $resolver = new TableResolver(\Yii::$app->getDb());
        $this->specify(
            'by no relationed table',
            function () use ($resolver) {
                $founds = $resolver->getRelations('itt_migration');
                verify($founds)->isEmpty();
            }
        );

        $this->specify(
            'by  relationed table',
            function () use ($resolver) {
                $founds = $resolver->getRelations('itt_auth_item');
                verify($founds)->notEmpty();
                verify(count($founds))->equals(1);
                verify($founds[0])->equals(['ftable' => 'itt_auth_rule', 'pk' => 'rule_name', 'fk' => 'name']);
            }
        );
    }

    public function testGetIndexes()
    {
        $resolver = new TableResolver(\Yii::$app->getDb());
        $this->specify(
            'one',
            function () use ($resolver) {
                $founds = $resolver->getIndexes('itt_migration');
                verify(count($founds))->equals(0);
            }
        );

        $this->specify(
            'two',
            function () use ($resolver) {
                $founds = $resolver->getIndexes('itt_auth_item');
                verify(count($founds))->equals(1);
            }
        );
    }


}