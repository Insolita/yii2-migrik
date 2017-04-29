<?php
/**
 * Created by solly [11.08.16 5:34]
 */

namespace insolita\migrik\tests\unit;

use Codeception\Specify;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use Codeception\Verify;
use insolita\migrik\resolver\TableResolver;
use yii\db\Schema;
use yii\db\TableSchema;

/**
 * @var Verify
 **/
class TableResolverPgTest extends Unit
{
    use Specify;
    
    protected $db;
    protected function _before()
    {
        $this->db =  \Yii::$app->db;
        Debug::debug($this->db->dsn);
    }
    
    public function testGetSchema()
    {
        $resolver = new TableResolver($this->db);
        $schema = $resolver->schema;
        verify($schema)->isInstanceOf(Schema::class);
        verify($schema)->isInstanceOf(\yii\db\pgsql\Schema::class);
    
    }
    
    public function testGetTableSchema()
    {
        $resolver = new TableResolver($this->db);
    
        $tableSchema = $resolver->getTableSchema('migrik_test1');
        verify_that($tableSchema);
        verify($tableSchema)->isInstanceOf(TableSchema::class);
    }
    
    public function testGetTableNames()
    {
        $resolver = new TableResolver($this->db);
    
        $tableNames = $resolver->getTableNames();
        verify($tableNames)->notEmpty();
        verify($tableNames)->contains('migrik_test1');
        verify($tableNames)->contains('migrik_test2');
    }
    
    public function testFindTablesByPattern()
    {
        $resolver = new TableResolver($this->db);
    
        $this->specify(
            'by table name',
            function () use ($resolver) {
                $founds = $resolver->findTablesByPattern('migrik_testcomposite');
                verify($founds)->notEmpty();
                verify($founds)->contains('migrik_testcomposite');
                verify(count($founds))->equals(1);
            }
        );
        
        $this->specify(
            'by pattern one result',
            function () use ($resolver) {
                $founds = $resolver->findTablesByPattern('migrik_testcompos*');
                verify($founds)->notEmpty();
                verify($founds)->contains('migrik_testcomposite');
                verify(count($founds))->equals(1);
            }
        );
        
        $this->specify(
            'by pattern bulk result',
            function () use ($resolver) {
                $founds = $resolver->findTablesByPattern('migrik_test*');
                verify($founds)->notEmpty();
                verify($founds)->contains('migrik_testcomposite');
                verify($founds)->contains('migrik_testfk');
                verify(count($founds))->greaterOrEquals(5);
            }
        );
    }
    
    public function testGetRelations()
    {
        $resolver = new TableResolver($this->db);
        $this->specify(
            'by no relationed table',
            function () use ($resolver) {
                $founds = $resolver->getRelations('migrik_testunexisted');
                verify($founds)->isEmpty();
            }
        );
        
        $this->specify(
            'by  relationed table',
            function () use ($resolver) {
                $founds = $resolver->getRelations('migrik_testfk');
                verify($founds)->notEmpty();
                verify(count($founds))->equals(1);
                verify($founds['someIdx'])->equals(['ftable' => 'migrik_test3', 'pk' => 'extId', 'fk' => 'id']);
                Debug::debug($founds);
            }
        );
    }
    
    public function testGetIndexes()
    {
        $resolver = new TableResolver($this->db);
    
        $this->specify(
            'not indexed',
            function () use ($resolver) {
                $founds = $resolver->getIndexes('migrik_test3');
                verify(count($founds))->equals(0);
            }
        );
        
        $this->specify(
            'indexed',
            function () use ($resolver) {
                $founds = $resolver->getIndexes('migrik_test2');
                verify(count($founds))->equals(1);
                \verify($founds)->hasKey('strFieldUniq');
                $founds = $resolver->getIndexes('migrik_test1');
                verify(count($founds))->equals(2);
                \verify($founds)->hasKey('complexIdx');
                \verify($founds)->hasKey('migrik_test1_smallintField_key');
            }
        );
            $this->specify('pgspec',function () use($resolver){
                $founds = $resolver->getIndexes('migrik_pgspec');
                verify(count($founds))->equals(1);
            });
        $this->specify(
            'composite',
            function () use ($resolver) {
                $founds = $resolver->getIndexes('migrik_testcomposite');
                verify(count($founds))->equals(0);
            }
        );
    }
    
    public function testGetPimaryKeys()
    {
        $resolver = new TableResolver(\Yii::$app->getDb());
        $this->specify(
            'simplePk',
            function () use ($resolver){
               $pk = $resolver->getPrimaryKeys('migrik_test1');
               verify(is_array($pk))->true();
               verify(count($pk))->equals(1);
               verify($pk[0])->equals('id');
    
                $pk = $resolver->getPrimaryKeys('migrik_test2');
                verify(is_array($pk))->true();
                verify(count($pk))->equals(1);
                verify($pk[0])->equals('id');
            }
        );
            $this->specify('noPk',function () use($resolver){
                $pk = $resolver->getPrimaryKeys('migrik_pgspec');
                verify(is_array($pk))->true();
                verify(count($pk))->equals(0);
            });
        $this->specify(
            'compositePk',
            function () use ($resolver){
                $pk = $resolver->getPrimaryKeys('migrik_testcomposite');
                verify(is_array($pk))->true();
                verify(count($pk))->equals(2);
                Debug::debug($pk);
            }
        );
    }
    
}