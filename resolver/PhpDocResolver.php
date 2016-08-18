<?php
/**
 * Created by solly [18.08.16 23:10]
 */

namespace insolita\migrik\resolver;

use insolita\migrik\contracts\IPhpdocResolver;

/**
 * @var \ReflectionClass $classReflection
 **/
class PhpDocResolver implements IPhpdocResolver
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var \ReflectionClass
     **/
    protected $classReflection;

    /**
     * @var string
     **/
    private $_phpdoc;

    /**
     * ModelResolver constructor.
     *
     * @param $class
     */
    public function __construct($class)
    {
        $this->setClass($class);
        $this->createClassReflection();
    }

    /**
     * @param string $class
     **/
    public function setClass($class)
    {
        $this->class = $class;
    }


    /**
     * @return array
     **/
    public function getAttributes()
    {
        return $this->classReflection->getDefaultProperties();
    }

    /**
     * @return string|false
     **/
    public function getTableName()
    {
        // TODO: Implement getTableName() method.
    }

    /**
     * @param string
     *
     * @return array
     **/
    public function getAttributeInfo($attribute)
    {
        // TODO: Implement getAttributeInfo() method.
    }

    /**
     * @return string|false
     **/
    public function getConnectionName()
    {
        // TODO: Implement getConnectionName() method.
    }

    protected function createClassReflection()
    {
        if ($this->class) {
            $this->classReflection = new \ReflectionClass($this->class);
        }
    }

    public function getPhpdoc()
    {
        if (!$this->_phpdoc) {
            $this->_phpdoc = $this->classReflection->getDocComment();
        }
        return $this->_phpdoc;
    }

}