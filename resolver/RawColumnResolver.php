<?php
/**
 * Created by solly [14.08.16 1:43]
 */

namespace insolita\migrik\resolver;

use yii\db\ColumnSchema;
use yii\db\Schema;
use yii\helpers\StringHelper;

/**
 * Class RawColumnResolver
 * Resolve columns for migrations as strings with sql definition
 *
 * @package insolita\migrik\resolver
 */
class RawColumnResolver extends BaseColumnResolver
{

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveString(ColumnSchema $column)
    {
        list($type, $size, $default, $nullable, $comment) = $this->resolveCommon($column);
        return $this->buildString([$type, $size, $nullable, $default, $comment]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return array
     */
    protected function resolveCommon(ColumnSchema $column)
    {
        $type = 'Schema::TYPE_' . strtoupper($column->type);
        $size = $column->size ? '(' . $column->size . ')' : '';
        $default = $this->buildDefaultValue($column);
        $nullable = $column->allowNull ? '' : 'NOT NULL';
        $comment = $column->comment ? ("COMMENT " . $this->schema->quoteValue($column->comment)) : '';

        return [$type, $size, $default, $nullable, $comment];
    }

    /**
     * Builds the default value specification for the column.
     *
     * @return string string with default value of column.
     */
    protected function buildDefaultValue(ColumnSchema $column)
    {
        if ($column->defaultValue === null) {
            return $column->allowNull === true ? ' DEFAULT NULL' : '';
        }

        $string = 'DEFAULT ';
        switch (gettype($column->defaultValue)) {
            case 'integer':
                $string .= (string)$column->defaultValue;
                break;
            case 'double':
                // ensure type cast always has . as decimal separator in all locales
                $string .= str_replace(',', '.', (string)$column->defaultValue);
                break;
            case 'boolean':
                $string .= $column->defaultValue ? 'TRUE' : 'FALSE';
                break;
            case 'object':
                $string .= (string)$column->defaultValue;
                break;
            default:
                $string .= "'{$column->defaultValue}'";
        }

        return $string;
    }

    /**
     * @param array $columnParts
     *
     * @return string
     **/
    protected function buildString(array $columnParts)
    {
        $type = array_shift($columnParts);
        $size = array_shift($columnParts);
        $columnParts = implode(' ', array_filter(array_map('trim', $columnParts), 'trim'));
        return (!empty($type) ? $type : '') . (!empty($columnParts) ? '."' . ($size ? $size . ' ' : ' ') . $columnParts
            . '"' : '');
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
            && (StringHelper::startsWith($column->defaultValue, "CURRENT") or StringHelper::startsWith(
                    $column->defaultValue,
                    "LOCAL"
                ))
        ) {
            $default = 'DEFAULT ' . $column->defaultValue;
        }
        return $this->buildString([$type, $size, $nullable, $default, $comment]);
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
        return $this->buildString([$type, $size, $nullable, $default, $comment]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveEnumType(ColumnSchema $column)
    {
        list(, , $default, $nullable, $comment) = $this->resolveCommon($column);
        if ($column->enumValues) {
            $enum = "enum(" . implode(', ', array_map([$this->schema, 'quoteValue'], $column->enumValues)) . ")";
        } else {
            return "";
        }
        $columns = implode(' ', array_filter(array_map('trim', [$nullable, $default, $comment]), 'trim'));
        return '"' . $enum . ' ' . $columns . '"';
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
        $pk = $this->tableSchema->primaryKey;
        if (in_array($column->name, $pk)) {
            if ($column->unsigned) {
                $column->type = ($column->type == Schema::TYPE_BIGINT ? Schema::TYPE_UBIGPK : Schema::TYPE_UPK);
            } else {
                $column->type = ($column->type == Schema::TYPE_BIGINT ? Schema::TYPE_BIGPK : Schema::TYPE_PK);
            }
            return $this->resolvePk($column);
        }
        list($type, $size, $default, $nullable, $comment) = $this->resolveCommon($column);
        if ($column->scale && $column->precision) {
            $size = '(' . $column->scale . ', ' . $column->precision . ')';
        } elseif ($column->precision) {
            $size = '(' . $column->precision . ')';
        }
        $unsigned = $column->unsigned ? 'UNSIGNED' : '';
        return $this->buildString([$type, $size, $unsigned, $nullable, $default, $comment]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolvePk(ColumnSchema $column)
    {
        list($type, , , , $comment) = $this->resolveCommon($column);
        return $type . ($comment ? '." ' . $comment . '"' : '');
    }

}