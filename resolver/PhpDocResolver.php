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
        $pattern1 = '/(?:@property|@var)\s{1,}(?:\w+)\s{1,}\$(\w+)\s{1,}@column\s{1,}(.*?)$/sium';
        $pattern2 = '/@column\s?\(["\'\s]?(.*?)["\'\s]?\)\s{1,}(.*?)$/sium';
        preg_match_all($pattern1, $this->_phpdoc, $matches1);
        if (!empty($matches1) && isset($matches1[0][1])) {
            return $matches1;
        }
        preg_match_all($pattern2, $this->_phpdoc, $matches2);
        if (!empty($matches2) && isset($matches2[0][1])) {
            return $matches2;
        }
        return false;
    }

    /**
     * @return string|false
     **/
    public function getTableName()
    {
        $pattern1 = '/@table\s?(_|\-|\w+)\s?$/siu'; // - without braces
        $pattern2 = '/@table\s?\(["\'\s]?(.*?)["\'\s]?\).*$/siu'; //with braces and possible quotes
        preg_match($pattern1, $this->_phpdoc, $matches1);
        if (!empty($matches1) && isset($matches1[0][1])) {
            return $matches1[0][1];
        }
        preg_match($pattern2, $this->_phpdoc, $matches2);
        if (!empty($matches2) && isset($matches2[0][1])) {
            return $matches2[0][1];
        }
        return false;
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
        $pattern1 = '/@db\s?(_|\-|\w+)\s?$/siu'; // - without braces
        $pattern2 = '/@db\s?\(["\'\s]?(.*?)["\'\s]?\).*$/siu'; //with braces and possible quotes
        preg_match($pattern1, $this->_phpdoc, $matches1);
        if (!empty($matches1) && isset($matches1[0][1])) {
            return $matches1[0][1];
        }
        preg_match($pattern2, $this->_phpdoc, $matches2);
        if (!empty($matches2) && isset($matches2[0][1])) {
            return $matches2[0][1];
        }
        return false;
    }

    public function getPhpdoc()
    {
        if (!$this->_phpdoc) {
            $this->_phpdoc = $this->classReflection->getDocComment();
        }
        return $this->_phpdoc;
    }

    protected function createClassReflection()
    {
        if ($this->class) {
            $this->classReflection = new \ReflectionClass($this->class);
        }
    }

}