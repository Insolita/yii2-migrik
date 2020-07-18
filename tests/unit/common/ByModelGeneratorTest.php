<?php
/**
 * Created by solly [20.08.16 5:35]
 */

namespace tests\unit\common;

use Codeception\Specify;
use Codeception\Verify;
use insolita\migrik\gii\ByModelGenerator;
use tests\TestCase;

/**
 * @var Verify;
 **/
class ByModelGeneratorTest extends TestCase
{
    use Specify;

    public function testPrepareColumnDefinition()
    {
        $gen = new ByModelGenerator();
        $defs = [
            [
                'input' => 'string(10)|unique|notNull',
                'expect' => '$this->string(10)->unique()->notNull()',
            ],
            [
                'input' => 'string|unique()|notNull()',
                'expect' => '$this->string()->unique()->notNull()',
            ],
            [
                'input' => 'text|default(\'bla\')',
                'expect' => '$this->text()->defaultValue(\'bla\')',
            ],
            [
                'input' => 'datetime(0)|expr(\'CURRENT_TIMESTAMP\')',
                'expect' => '$this->datetime(0)->defaultExpression(\'CURRENT_TIMESTAMP\')',
            ],
        ];
        foreach ($defs as $definition) {
            $result =
                $this->callPrivateMethod($gen, 'prepareColumnDefinition', [$definition['input']]);
            verify($result)->equals($definition['expect']);
        }
    }
}
