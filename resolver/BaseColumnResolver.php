<?php
/**
 * Created by solly [13.08.16 23:51]
 */

namespace insolita\migrik\resolver;

use insolita\migrik\contracts\IMigrationColumnResolver;
use yii\db\ColumnSchema;
use yii\db\ColumnSchemaBuilder;
use yii\db\Schema;
use yii\db\TableSchema;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * Class BaseColumnResolver
 * Abstraction Class for construct migrations for columns
 *
 * @package insolita\migrik\resolver
 */
abstract class BaseColumnResolver implements IMigrationColumnResolver
{
    /**
     * @var Schema $schema
     **/
    public $schema;

    /**
     * @var ColumnSchemaBuilder $builder
     **/
    protected $columnSchemaBuilder;

    /**
     * @var TableSchema $tableSchema
     **/
    protected $tableSchema;

    /**
     * BaseColumnResolver constructor.
     *
     * @param \yii\db\Schema $schema
     * @param \yii\db\TableSchema $tableSchema
     */
    public function __construct(Schema $schema, TableSchema $tableSchema)
    {
        $this->schema = $schema;
        $this->tableSchema = $tableSchema;
        $this->columnSchemaBuilder = $schema->createColumnSchemaBuilder('');
    }

    /**
     * Method must return string of representation of  part of migration for current column name
     * @expect "string(255) NOT NULL DEFAULT 'example' COMMENT 'bla-bla'"
     * or "$this->string(255)->notNull()->defaultValue('example')->comment('bla-bla')"
     * Method offer ability to create string representation for each database-type method
     * create function named like 'resolve' . ucfirst($column->dbType) . 'Type'
     *
     * @example
     *   resolveEnumType(ColumnSchema $column) or
     *   resolvePolygonType(ColumnSchema $column) or
     *   resolveArrayType(ColumnSchema $column) ... etc
     * @param string $columnName
     *
     * @return string
     **/
    public function resolveColumn($columnName)
    {
        /**
         * @var ColumnSchema $column
         **/
        $column = $this->getTableSchema()->getColumn($columnName);
        if (! $column->comment) {
            $column->comment = $this->defaultCommentsByColumnName($column->name);
        }
        $columnTypeMethod = 'resolve'.ucfirst($column->dbType).'Type';
        if (StringHelper::startsWith($column->dbType, 'enum(')) {
            $columnTypeMethod = 'resolveEnumType';
        }
        if (StringHelper::startsWith($column->dbType, 'set(')) {
            $columnTypeMethod = 'resolveSetType';
        }
        if (StringHelper::startsWith($column->dbType, '_')) {
            $columnTypeMethod = 'resolveArrayType';
        }

        if (method_exists($this, $columnTypeMethod)) {
            \Yii::trace('try to call customMethod "'.$columnTypeMethod.'"', __METHOD__);

            return call_user_func([$this, $columnTypeMethod], $column);
        } else {
            $columnCategory = ArrayHelper::getValue($this->getColumnSchemaBuilder()->categoryMap, $column->type);
            \Yii::trace('try to call categoryMethod "resolve'.ucfirst($columnCategory).'"', __METHOD__);

            return call_user_func([$this, 'resolve'.ucfirst($columnCategory)], $column);
        }
    }

    /**
     * @return \yii\db\TableSchema
     */
    protected function getTableSchema()
    {
        return $this->tableSchema;
    }

    /**
     * Override this method for preset default comment based on column name
     *
     * @param string $name
     *
     * @return string
     **/
    protected function defaultCommentsByColumnName($name)
    {
        $defaults = [];

        return ArrayHelper::getValue($defaults, $name, '');
    }

    /**
     * @return \yii\db\ColumnSchemaBuilder
     */
    protected function getColumnSchemaBuilder()
    {
        return $this->columnSchemaBuilder;
    }

    /**
     * Resolve string-category columns
     *
     * @see ColumnSchemaBuilder->$categoryMap
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    abstract protected function resolveString(ColumnSchema $column);

    /**
     * Resolve Numeric-category columns
     *
     * @see ColumnSchemaBuilder->$categoryMap
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    abstract protected function resolveNumeric(ColumnSchema $column);

    /**
     * Resolve time-category columns
     *
     * @see ColumnSchemaBuilder->$categoryMap
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    abstract protected function resolveTime(ColumnSchema $column);

    /**
     * Resolve pk-category columns
     *
     * @see ColumnSchemaBuilder->$categoryMap
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    abstract protected function resolvePk(ColumnSchema $column);

    /**
     * Resolve other-category columns
     *
     * @see ColumnSchemaBuilder->$categoryMap
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    abstract protected function resolveOther(ColumnSchema $column);
}