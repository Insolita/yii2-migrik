<?php
/**
 * Created by solly [18.08.16 23:24]
 */

namespace insolita\migrik\tests\unit;

use Codeception\Specify;
use Codeception\Test\Unit;
use Codeception\Verify;
use insolita\migrik\gii\GeneratorTrait;

/**
 * @var Verify
 **/
class GeneratorTraitTest extends Unit
{
    use Specify;
    use GeneratorTrait;
    
    public $db = 'db';
    
    public function testCreateNextPrefix()
    {
        $this->specify(
            'check normal Behavior',
            function () {
                verify($this->createNextPrefix('m161221_123022'))->equals('m161221_123023');
                verify($this->createNextPrefix('m161221_123059'))->equals('m161221_123100');
                verify($this->createNextPrefix('m161221_235959'))->equals('m161222_000000');
            }
        );
    }
    
    public function testRefreshNextPrefix()
    {
        $this->nextPrefix = 'm161221_123022';
        $this->refreshNextPrefix();
        verify($this->nextPrefix)->equals('m161221_123023');
    }
    
    public function testRefreshPrefix()
    {
        $this->prefix = 'm161221_123022';
        $this->nextPrefix = 'm161221_123023';
        $this->refreshPrefix();
        verify($this->prefix)->notEquals('m161221_123022');
        verify($this->nextPrefix)->equals($this->createNextPrefix($this->prefix));
    }
    
    public function testTableCaption()
    {
        $fixt = [
            ['table' => 'tableName', 'prefix' => 'tbl_', 'expect' => 'tableName'],
            ['table' => 'tbl_tableName', 'prefix' => 'tbl_', 'expect' => 'tableName'],
            ['table' => 'theMytable', 'prefix' => 'the', 'expect' => 'Mytable'],
            ['table' => 'tbl_somename', 'prefix' => 'tbl_', 'expect' => 'somename'],
            ['table' => 'tbl_somentbl_name', 'prefix' => 'tbl_', 'expect' => 'somentbl_name'],
            ['table' => 'tbl_somename', 'prefix' => 'foo_', 'expect' => 'tbl_somename'],
            ['table' => 'tbl_somename', 'prefix' => '', 'expect' => 'tbl_somename'],
        ];
        foreach ($fixt as $fixture) {
            \Yii::$app->db->tablePrefix = $fixture['prefix'];
            verify('test_' . $fixture['table'], $this->getTableCaption($fixture['table']))->equals($fixture['expect']);
        }
    }
}