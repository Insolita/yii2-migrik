<?php
/**
 * Created by solly [14.08.16 1:43]
 */

namespace insolita\migrik\resolver;

use yii\db\ColumnSchema;
use yii\db\Schema;
use yii\helpers\StringHelper;

/**
 * Class PgRawColumnResolver
 * Resolve columns for migrations as strings with sql definition
 *
 * @package insolita\migrik\resolver
 */
class PgRawColumnResolver extends BaseColumnResolver
{
    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveString(ColumnSchema $column)
    {
        list($type, $size, $default, $nullable) = $this->resolveCommon($column);

        return $this->buildString([$type, $size, $nullable, $default]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     * IGNORE comment - not suported in raw mode
     * @return array
     */
    protected function resolveCommon(ColumnSchema $column)
    {
        $type = 'Schema::TYPE_'.strtoupper($column->type);
        $size = $column->size ? '('.$column->size.')' : '';
        if ($column->allowNull === true && $column->defaultValue === null) {
            $nullable = '';
            $default = '';
        } else {
            $default = $this->buildDefaultValue($column);
            $nullable = $column->allowNull ? '' : 'NOT NULL';
        }

        return [$type, $size, $default, $nullable];
    }

    /**
     * Builds the default value specification for the column.
     *
     * @param \yii\db\ColumnSchema $column
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
                $string .= (string) $column->defaultValue;
                break;
            case 'double':
                // ensure type cast always has . as decimal separator in all locales
                $string .= str_replace(',', '.', (string) $column->defaultValue);
                break;
            case 'boolean':
                $string .= $column->defaultValue ? 'TRUE' : 'FALSE';
                break;
            case 'object':
                $string .= (string) $column->defaultValue;
                break;
            case 'array':
                $string .=  "'".json_encode($column->defaultValue)."'";
                break;
            default:
                if (mb_stripos($column->defaultValue, 'NULL::') !== false) {
                    $string = '';
                } elseif (mb_stripos($column->defaultValue, 'array') !== false) {
                    $string .= preg_replace('~[\"]~', "'", $column->defaultValue);
                } else {
                    $string .= "'{$column->defaultValue}'";
                }
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

        return (! empty($type) ? $type : '').(! empty($columnParts) || $size ? '."'.($size ? $size.' ' : ' ').$columnParts.'"' : '');
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveTime(ColumnSchema $column)
    {
        list($type, $size, $default, $nullable) = $this->resolveCommon($column);
        if (! is_null($column->precision)) {
            $size = '('.$column->precision.')';
        }
        if ($column->defaultValue && (StringHelper::startsWith($column->defaultValue, "CURRENT") or StringHelper::startsWith($column->defaultValue, "LOCAL"))) {
            $default = 'DEFAULT '.$column->defaultValue;
        }

        return $this->buildString([$type, $size, $nullable, $default]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     * @return string
     */
    protected function resolveJsonType(ColumnSchema $column)
    {
        list($type, $size, $default, $nullable) = $this->resolveCommon($column);

        $default = preg_replace('~[\"]~', '\"', $default);
        $columns = implode(' ', array_filter([$nullable, $default], 'trim'));

        return '"'.trim('json '.$columns).'"';
    }

    /**
     * @param \yii\db\ColumnSchema $column
     * @return string
     */
    protected function resolveJsonbType(ColumnSchema $column)
    {
        list($type, $size, $default, $nullable) = $this->resolveCommon($column);
        if (is_array($default)) {
            $default = "'".json_encode($default)."'";
        }
        $default = preg_replace('~[\"]~', '\"', $default);
        $columns = implode(' ', array_filter([$nullable, $default], 'trim'));

        return '"'.trim('jsonb '.$columns).'"';
    }

    /**
     * @param \yii\db\ColumnSchema $column
     * @return string
     */
    protected function resolveArrayType(ColumnSchema $column)
    {
        list($type, $size, $default, $nullable) = $this->resolveCommon($column);
        $type = preg_replace('~([^A-Za-z])~', '', $column->dbType);
        $columns = implode(' ', array_filter([$nullable, $default], 'trim'));

        return '"'.trim($type.'[] '.$columns).'"';
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
        list($type, $size, $default, $nullable) = $this->resolveCommon($column);
        if ($column->precision) {
            $size = '('.$column->precision.')';
        }

        return $this->buildString([$type, $size, $nullable, $default]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveNumeric(ColumnSchema $column)
    {
        $pk = $this->tableSchema->primaryKey;
        if (count($pk) === 1 && in_array($column->name, $pk)) {
            if ($column->unsigned) {
                $column->type = ($column->type == Schema::TYPE_BIGINT ? Schema::TYPE_UBIGPK : Schema::TYPE_UPK);
            } else {
                $column->type = ($column->type == Schema::TYPE_BIGINT ? Schema::TYPE_BIGPK : Schema::TYPE_PK);
            }

            return $this->resolvePk($column);
        }
        list($type, $size, $default, $nullable) = $this->resolveCommon($column);
        if ($column->scale && $column->precision) {
            $size = '('.$column->precision.', '.$column->scale.')';
        } elseif ($column->precision) {
            $size = '('.$column->precision.')';
        }
        $unsigned = $column->unsigned ? 'UNSIGNED' : '';

        return $this->buildString([$type, $size, $unsigned, $nullable, $default]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolvePk(ColumnSchema $column)
    {
        list($type, , ,) = $this->resolveCommon($column);

        return $type;
    }
}