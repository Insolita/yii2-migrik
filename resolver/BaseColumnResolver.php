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
     * Other params for inherited class
     * @var array $config
    **/
    protected $config = [];


    /**
     * BaseColumnResolver constructor.
     *
     * @param \yii\db\Schema $schema
     * @param \yii\db\TableSchema $tableSchema
     * @param array $config  additional data
     */
    public function __construct(Schema $schema, TableSchema $tableSchema, array $config = [])
    {
        $this->schema = $schema;
        $this->tableSchema = $tableSchema;
        $this->columnSchemaBuilder = $schema->createColumnSchemaBuilder('');
        $this->config = $config;
    }

    /**
     * Method must return string of representation of  part of migration for current column name
     * @expect "string(255) NOT NULL DEFAULT 'example' COMMENT 'bla-bla'"
     * or "$this->string(255)->notNull()->defaultValue('example')->comment('bla-bla')"
     *
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
        $columnTypeMethod = 'resolve' . ucfirst($column->dbType) . 'Type';
        if (method_exists($this, $columnTypeMethod)) {
            \Yii::trace('try to call customMethod "' . $columnTypeMethod . '"', __METHOD__);
            return call_user_func([$this, $columnTypeMethod], $column);
        } else {
            $columnCategory = ArrayHelper::getValue($this->getColumnSchemaBuilder()->categoryMap, $column->type);
            \Yii::trace('try to call categoryMethod "resolve' . ucfirst($columnCategory) . '"', __METHOD__);
            return call_user_func([$this, 'resolve' . ucfirst($columnCategory)], $column);
        }
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    abstract protected function resolveString(ColumnSchema $column);

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    abstract protected function resolveNumeric(ColumnSchema $column);

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    abstract protected function resolveTime(ColumnSchema $column);

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    abstract protected function resolvePk(ColumnSchema $column);

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return string
     */
    abstract protected function resolveOther(ColumnSchema $column);


    /**
     * @return \yii\db\ColumnSchemaBuilder
     */
    protected function getColumnSchemaBuilder()
    {
        return $this->columnSchemaBuilder;
    }

    /**
     * @return \yii\db\TableSchema
     */
    protected function getTableSchema()
    {
        return $this->tableSchema;
    }
}