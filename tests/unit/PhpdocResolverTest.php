<?php
/**
 * Created by solly [16.08.16 12:28]
 */

namespace insolita\migrik\tests\unit;

use Codeception\Util\Debug;
use Codeception\Verify;
use Codeception\Specify;
use common\models\User;

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

    public function testPhpdocReflection(){
        $class = User::className();
        $ref = new \ReflectionClass($class);
        $info = $ref->getDocComment();
        Debug::debug($info);
    }
}