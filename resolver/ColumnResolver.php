<?php
/**
 * Created by solly [14.08.16 1:43]
 */

namespace insolita\migrik\resolver;


use yii\db\ColumnSchema;

/**
 * Class ColumnResolver
 * Resolve columns for migrations as strings with sql definition
 * @package insolita\migrik\resolver
 */
class ColumnResolver extends BaseColumnResolver
{
    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return mixed
     */
    protected function resolveString(ColumnSchema $column)
    {
        $res ='';
        return $res;
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return mixed
     */
    protected function resolveNumeric(ColumnSchema $column)
    {
        $res ='';
        return $res;
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return mixed
     */
    protected function resolveTime(ColumnSchema $column)
    {
        $res ='';
        return $res;
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return mixed
     */
    protected function resolvePk(ColumnSchema $column)
    {
        $res ='';
        return $res;
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return mixed
     */
    protected function resolveOther(ColumnSchema $column)
    {
        $res ='';
        return $res;
    }

}