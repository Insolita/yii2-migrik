<?php
/**
 * Created by solly [14.08.16 1:51]
 */

namespace insolita\migrik\tests;


trait PrivateTestTrait
{
    public function callPrivateMethod($object, $method, $args=[])
    {
        $classReflection = new \ReflectionClass(get_class($object));
        $methodReflection = $classReflection->getMethod($method);
        $methodReflection->setAccessible(true);
        $result = $methodReflection->invokeArgs($object, $args);
        $methodReflection->setAccessible(false);
        return $result;
    }

    public function callPrivateProp($object, $property)
    {
        $classReflection = new \ReflectionClass(get_class($object));
        $propReflection = $classReflection->getProperty($property);
        $propReflection->setAccessible(true);
        $result = $propReflection->getValue($object);
        $propReflection->setAccessible(false);
        return $result;
    }
}