<?php
/**
 * Created by solly [18.08.16 22:52]
 */

namespace insolita\migrik\resolver;

use insolita\migrik\contracts\IModelResolver;
use yii\base\Exception;
use yii\base\UserException;

/**
 * Class ModelResolver
 *
 * @package insolita\migrik\resolver
 */
class ModelResolver implements IModelResolver
{
    /**
     * @var string
     */
    protected $class;
    /**
     * @var Object
     */
    protected $classInstance;

    /**
     * ModelResolver constructor.
     *
     * @param $class
     */
    public function __construct($class)
    {
        $this->setClass($class);
        $this->createClassInstance();
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
        if ($this->classInstance && method_exists($this->classInstance, 'attributes')) {
            $attrs = call_user_func([$this->classInstance, 'attributes']);
            return $attrs;
        }
        return [];
    }

    /**
     * @return string|false
     **/
    public function getTableName()
    {
        if ($this->classInstance && method_exists($this->classInstance, 'tableName')) {
            return call_user_func([$this->classInstance, 'tableName']);
        }
        return false;
    }

    /**
     * @return array
     **/
    public function getAttributeLabels()
    {
        if ($this->classInstance && method_exists($this->classInstance, 'attributeLabels')) {
            $labels = call_user_func([$this->classInstance, 'attributeLabels']);
            return $labels;
        }
        return [];
    }

    /**
     * @throws \yii\base\UserException
     */
    protected function createClassInstance()
    {
        if ($this->class) {
            try {
                $this->classInstance = \Yii::createObject(['class' => $this->class]);
                if (method_exists($this->classInstance, 'detachBehaviors')) {
                    call_user_func([$this->classInstance, 'detachBehaviors']);
                }
            } catch (Exception $e) {
                throw new UserException('Can`t create instanse of ' . $this->class);
            }
        }
    }

}