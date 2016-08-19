<?php
/**
 * Created by solly [18.08.16 22:46]
 */

namespace insolita\migrik\contracts;

/**
 * interface for fetch migration properties from model methods
**/
interface IModelResolver
{
    /**
     * @param string $class
     **/
    public function setClass($class);

    /**
     * @return array
     **/
    public function getAttributes();

    /**
     * @return string|false
     **/
    public function getTableName();

    /**
     * @return array
     **/
    public function getAttributeLabels();

}