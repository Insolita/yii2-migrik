<?php
/**
 * Created by solly [18.08.16 23:24]
 */

namespace insolita\migrik\tests\unit;
use Codeception\Test\Unit;
use Codeception\Specify;
use Codeception\Verify;
use insolita\migrik\gii\GeneratorTrait;

/**
 * @var Verify
 **/
class GeneratorTraitTest extends Unit
{
    use Specify;
    use GeneratorTrait;

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
}