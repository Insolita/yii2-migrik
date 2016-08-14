<?php
/**
 * Created by solly [14.08.16 1:43]
 */

namespace insolita\migrik\resolver;

use yii\db\ColumnSchema;
use yii\db\Schema;
use yii\helpers\StringHelper;

/**
 * Class ColumnResolver
 * Resolve columns for migrations as strings with sql definition
 *
 * @package insolita\migrik\resolver
 */
class ColumnResolver extends BaseColumnResolver
{

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return array
     */
    protected function resolveCommon(ColumnSchema $column)
    {
        $type = $column->type;
        $size = $column->size ? '(' . $column->size . ')' : '';
        $default = $this->buildDefaultValue($column);
        $nullable = $column->allowNull ? '' : 'NOT NULL';
        $comment = $column->comment ? ("COMMENT " . $this->schema->quoteValue($column->comment)) : '';

        return [$type, $size, $default, $nullable, $comment];
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveString(ColumnSchema $column)
    {
        list($type, $size, $default, $nullable, $comment) = $this->resolveCommon($column);
        return $this->buildString([$type . $size, $nullable, $default, $comment]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveTime(ColumnSchema $column)
    {
        list($type, $size, $default, $nullable, $comment) = $this->resolveCommon($column);
        if (!is_null($column->precision)) {
            $size = '(' . $column->precision . ')';
        }
        if ($column->defaultValue
            && (StringHelper::startsWith($column->defaultValue, "CURRENT")
                or StringHelper::startsWith($column->defaultValue, "LOCAL")
            )) {
            $default = 'DEFAULT '.$column->defaultValue;
        }
        return $this->buildString([$type . $size, $nullable, $default, $comment]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolvePk(ColumnSchema $column)
    {
        list($type, , , , $comment) = $this->resolveCommon($column);
        return $this->buildString([$type , $comment]);
    }

    /**
     * Resolve for Binary type
     *
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveOther(ColumnSchema $column)
    {
        list($type, $size, $default, $nullable, $comment) = $this->resolveCommon($column);
        if ($column->precision) {
            $size = '(' . $column->precision . ')';
        }
        return $this->buildString([$type . $size, $nullable, $default, $comment]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveEnumType(ColumnSchema $column)
    {
        list(, $size, $default, $nullable, $comment) = $this->resolveCommon($column);
        $type = 'enum';
        if ($column->enumValues) {
            $schema = $this->schema;
            $size = "(" . implode(', ', array_map([$schema, 'quoteValue'], $column->enumValues)) . ")";
        }
        return $this->buildString([$type . $size, $nullable, $default, $comment]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveSetType(ColumnSchema $column)
    {
        list(, $size, $default, $nullable, $comment) = $this->resolveCommon($column);
        $type = 'set';
        if ($column->enumValues) {
            $size = "('" . implode("','", $column->enumValues) . "')";
        }
        return $this->buildString([$type . $size, $nullable, $default, $comment]);
    }


    /**
     * Resolve tinyint(1) as boolean
     *
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveTinyintType(ColumnSchema $column)
    {
        if ($column->size == 1) {
            $column->type = Schema::TYPE_BOOLEAN;
        }
        return $this->resolveNumeric($column);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveNumeric(ColumnSchema $column)
    {
        list($type, $size, $default, $nullable, $comment) = $this->resolveCommon($column);
        if ($column->scale && $column->precision) {
            $size = '(' . $column->scale . ', ' . $column->precision . ')';
        } elseif (!is_null($column->precision)) {
            $size = '(, ' . $column->precision . ')';
        }
        $unsigned = $column->unsigned ? 'UNSIGNED' : '';
        return $this->buildString([$type . $size, $unsigned, $nullable, $default, $comment]);
    }

    /**
     * @param array $columnParts
     *
     * @return string
     **/
    protected function buildString(array $columnParts)
    {
        return implode(' ', array_filter(array_map('trim', $columnParts), 'trim'));
    }

}