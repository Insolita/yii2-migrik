<?php
/**
 * Created by solly [14.08.16 8:26]
 */

namespace insolita\migrik\resolver;

use yii\db\ColumnSchema;
use yii\db\Schema;
use yii\helpers\StringHelper;

/**
 * Class PgFluentColumnResolver
 * Generate columns in new yii2 fluent style
 *
 * @package insolita\migrik\resolver
 */
class PgFluentColumnResolver extends BaseColumnResolver
{
    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveString(ColumnSchema $column)
    {
        list($type, $size, $default, $nullable, $comment) = $this->resolveCommon($column);

        return $this->buildString([$type.$size, $nullable, $default, $comment]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveCommon(ColumnSchema $column)
    {
        $type = $column->type;
        $intMap = [
            Schema::TYPE_TINYINT => 'tinyInteger',
            Schema::TYPE_SMALLINT => 'smallInteger',
            Schema::TYPE_BIGINT => 'bigInteger',
        ];
        if (isset($intMap[$type])) {
            $type = $intMap[$type];
        }
        $size = $column->size ? '('.$column->size.')' : '()';
        if ($column->allowNull === true && $column->defaultValue === null) {
            $nullable = '!skip';
            $default = '!skip';
        } else {
            $default = $this->buildDefaultValue($column);
            $nullable = $column->allowNull === true ? 'null()' : 'notNull()';
        }

        $comment = $column->comment ? ("comment(".$this->schema->quoteValue($column->comment).")") : '';

        return [$type, $size, $default, $nullable, $comment];
    }

    /**
     * Builds the default value specification for the column.
     *
     * @param ColumnSchema $column
     *
     * @return string string with default value of column.
     */
    protected function buildDefaultValue(ColumnSchema $column)
    {
        if ($column->defaultValue === null) {
            return $column->allowNull === true ? 'defaultValue(null)' : '!skip';
        }

        switch (gettype($column->defaultValue)) {
            case 'integer':
                $string = 'defaultValue('.$column->defaultValue.')';
                break;
            case 'double':
                // ensure type cast always has . as decimal separator in all locales
                $string = 'defaultValue("'.str_replace(',', '.', (string) $column->defaultValue).'")';
                break;
            case 'boolean':
                $string = $column->defaultValue ? 'defaultValue(true)' : 'defaultValue(false)';
                break;
            case 'object':
                $string = 'defaultExpression("'.(string) $column->defaultValue.'")';
                break;
            case 'array':
                $string = (string) 'defaultValue('.json_encode($column->defaultValue).')';
                break;
            default:
                {
                    if (mb_stripos($column->defaultValue, 'NULL::') !== false) {
                        $string = '!skip';
                    } else {
                        $string = "defaultValue('{$column->defaultValue}')";
                    }
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
        $columnParts = array_filter($columnParts, function ($v) {
            return $v !== '!skip';
        });
        array_unshift($columnParts, '$this');

        return implode('->', array_filter(array_map('trim', $columnParts), 'trim'));
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveNumeric(ColumnSchema $column)
    {
        $pk = $this->tableSchema->primaryKey;
        /**
         * fix #35 Skip pk definition build, if $pk is composite
         **/
        if (count($pk) === 1 && in_array($column->name, $pk)) {
            $column->type = ($column->type == Schema::TYPE_BIGINT ? 'bigPrimaryKey' : 'primaryKey');

            return $this->resolvePk($column);
        }
        list($type, $size, $default, $nullable, $comment) = $this->resolveCommon($column);
        if ($column->scale && $column->precision) {
            $size = '('.$column->precision.', '.$column->scale.')';
        } elseif ($column->precision) {
            $size = '('.$column->precision.')';
        }
        $unsigned = $column->unsigned ? 'unsigned()' : '';

        return $this->buildString([$type.$size, $unsigned, $nullable, $default, $comment]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolvePk(ColumnSchema $column)
    {
        list($type, $size, , , $comment) = $this->resolveCommon($column);
        if (in_array($column->type, [Schema::TYPE_BIGPK, Schema::TYPE_UBIGPK])) {
            $type = 'bigPrimaryKey';
        }
        if (in_array($column->type, [Schema::TYPE_PK, Schema::TYPE_UPK])) {
            $type = 'primaryKey';
        }
        $unsigned = ($column->unsigned || in_array($column->type, [
                Schema::TYPE_UBIGPK,
                Schema::TYPE_UPK,
            ])) ? 'unsigned()' : '';

        return $this->buildString([$type.$size, $unsigned, $comment]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveTime(ColumnSchema $column)
    {
        list($type, $size, $default, $nullable, $comment) = $this->resolveCommon($column);
        if (! is_null($column->precision)) {
            $size = '('.$column->precision.')';
        }
        if ($column->defaultValue && (StringHelper::startsWith($column->defaultValue, 'CURRENT') or StringHelper::startsWith($column->defaultValue, 'LOCAL'))) {
            $default = 'defaultExpression("'.$column->defaultValue.'")';
        }

        return $this->buildString([$type.$size, $nullable, $default, $comment]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    protected function resolveOther(ColumnSchema $column)
    {
        list($type, $size, $default, $nullable, $comment) = $this->resolveCommon($column);
        if ($column->precision) {
            $size = '('.$column->precision.')';
        }

        return $this->buildString([$type.$size, $nullable, $default, $comment]);
    }

    /**
     * @param \yii\db\ColumnSchema $column
     * @return string
     */
    protected function resolveJsonType(ColumnSchema $column)
    {
        $default = '';
        $nullable = $column->allowNull ? '' : 'NOT NULL';
        if ($column->allowNull === true && $column->defaultValue === null) {
            $nullable = '';
        } elseif ($column->defaultValue === null) {
            $default = $column->allowNull === true ? ' DEFAULT NULL' : '';
        } elseif (is_array($column->defaultValue)) {
            $default = 'DEFAULT "'.json_encode($column->defaultValue).'"';
        } else {
            $default = "DEFAULT '{$column->defaultValue}'";
        }
        $comment = $column->comment ? ('COMMENT '.$this->schema->quoteValue($column->comment)) : '';

        $default = preg_replace('~[\"]~', '\"', $default);
        $columns = implode(' ', array_filter([$nullable, $default, $comment], 'trim'));

        return '"'.trim('json '.$columns).'"';
    }

    /**
     * @param \yii\db\ColumnSchema $column
     * @return string
     */
    protected function resolveJsonbType(ColumnSchema $column)
    {
        $default = '';
        $nullable = $column->allowNull ? '' : 'NOT NULL';
        if ($column->allowNull === true && $column->defaultValue === null) {
            $nullable = '';
        } elseif ($column->defaultValue === null) {
            $default = $column->allowNull === true ? ' DEFAULT NULL' : '';
        } elseif (is_array($column->defaultValue)) {
            $default = 'DEFAULT "'.json_encode($column->defaultValue).'"';
        } else {
            $default = "DEFAULT '{$column->defaultValue}'";
        }
        $comment = $column->comment ? ('COMMENT '.$this->schema->quoteValue($column->comment)) : '';

        $default = preg_replace('~[\"]~', '\"', $default);
        $columns = implode(' ', array_filter([$nullable, $default, $comment], 'trim'));

        return '"'.trim('jsonb '.$columns).'"';
    }

    /**
     * @param \yii\db\ColumnSchema $column
     * @return string
     */
    protected function resolveArrayType(ColumnSchema $column)
    {
        $default = '';
        $nullable = $column->allowNull ? '' : 'NOT NULL';
        if ($column->allowNull === true && $column->defaultValue === null) {
            $nullable = '';
        } elseif ($column->defaultValue === null) {
            $default = $column->allowNull === true ? ' DEFAULT NULL' : '';
        } elseif (mb_stripos($column->defaultValue, 'array') !== false) {
            $default = 'DEFAULT '.preg_replace('~[\"]~', "'", $column->defaultValue);
        }
        $comment = $column->comment ? ('COMMENT '.$this->schema->quoteValue($column->comment)) : '';
        $type = preg_replace('~([^A-Za-z])~', '', $column->dbType);
        $columns = implode(' ', array_filter([$nullable, $default, $comment], 'trim'));

        return '"'.trim($type.'[] '.$columns).'"';
    }
}
