<?php
/**
 * Created by solly [18.08.16 23:10]
 */

namespace insolita\migrik\resolver;

use insolita\migrik\contracts\IPhpdocResolver;
use yii\debug\models\search\Debug;

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
        $pattern1 = '/(?:@property|@var)\s{1,}(?:\w+)\s{1,}\$(?P<id>\w+)\s{1,}@column\s{1,}(?P<col>.*?)$/siu';
        $pattern2 = '/@column\s?\(["\'\s]?(?P<id>.*?)["\'\s]?\)\s{1,}(?P<col>.*?)$/siu';
        $result = [];
        $doclines = preg_split('/\n/su', $this->getPhpdoc());
        foreach ($doclines as $line) {
            if (!preg_match('/\w+/', $line)) {
                continue;
            }
            preg_match($pattern1, $line, $matches1);
            if (!empty($matches1) && !empty($matches1['id'])) {
                $result[$matches1['id']] = $matches1['col'];
            }
            preg_match($pattern2, $line, $matches2);
            if (!empty($matches2) && !empty($matches2['id'])) {
                $result[$matches2['id']] = $matches2['col'];
            }
        }
        return $result;
    }

    /**
     * @return string|false
     **/
    public function getTableName()
    {
        $pattern = '/@table\s?\(["\'\s]?(.*?)["\'\s]?\).*$/siu'; //with braces and possible quotes
        preg_match($pattern, $this->getPhpdoc(), $matches);
        if (!empty($matches) && isset($matches[1])) {
            return $matches[1];
        }
        return false;
    }

    /**
     * @return string|false
     **/
    public function getConnectionName()
    {
        $pattern = '/@db\s?\(["\'\s]?(.*?)["\'\s]?\).*$/siu'; //with braces and possible quotes
        preg_match($pattern, $this->getPhpdoc(), $matches);
        if (!empty($matches) && isset($matches[1])) {
            return $matches[1];
        }
        return false;
    }

    public function getPhpdoc()
    {
        if (!$this->_phpdoc) {
            $this->_phpdoc = $this->classReflection->getDocComment();
            $attrs = $this->classReflection->getProperties(\ReflectionProperty::IS_PUBLIC);
            if (!empty($attrs)) {
                foreach ($attrs as $attr) {
                    $this->_phpdoc .= $attr->getDocComment();
                }
            }
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