<?php
/**
 * Created by solly [13.08.16 23:51]
 */

namespace insolita\migrik\resolver;


use insolita\migrik\contracts\IMigrationColumnResolver;
use yii\db\ColumnSchema;
use yii\db\ColumnSchemaBuilder;
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
     * @var ColumnSchemaBuilder $builder
     **/
    public $columnSchemaBuilder;
    /**
     * @var TableSchema $tableSchema
     **/
    public $tableSchema;


    /**
     * BaseColumnResolver constructor.
     *
     * @param \yii\db\TableSchema         $tableSchema
     * @param \yii\db\ColumnSchemaBuilder $columnSchemaBuilder
     */
    public function __construct(TableSchema $tableSchema, ColumnSchemaBuilder $columnSchemaBuilder)
    {
        $this->setColumnSchemaBuilder($columnSchemaBuilder);
        $this->setTableSchema($tableSchema);
    }

    /**
     * @param \yii\db\ColumnSchemaBuilder $columnSchemaBuilder
     *
     * @return void
     */
    public function setColumnSchemaBuilder(ColumnSchemaBuilder $columnSchemaBuilder)
    {
        $this->columnSchemaBuilder = $columnSchemaBuilder;
    }

    /**
     * @param \yii\db\TableSchema $tableSchema
     *
     * @return void
     */
    public function setTableSchema(TableSchema $tableSchema)
    {
        $this->tableSchema = $tableSchema;
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
        $column = $this->tableSchema->getColumn($columnName);
        $columnTypeMethod = 'resolve' . ucfirst($column->dbType) . 'Type';
        if (method_exists($this, $columnTypeMethod)) {
            \Yii::trace('try to call customMethod "'.$columnTypeMethod.'"', __METHOD__);
            return call_user_func([$this, $columnTypeMethod], $column);
        } else {
            $columnCategory = ArrayHelper::getValue($this->columnSchemaBuilder->categoryMap, $column->type);
            \Yii::trace('try to call categoryMethod "resolve' . ucfirst($columnCategory).'"', __METHOD__);
            return call_user_func([$this, 'resolve' . ucfirst($columnCategory)], $column);
        }
    }

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return mixed
     */
    abstract protected function resolveString(ColumnSchema $column);

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return mixed
     */
    abstract protected function resolveNumeric(ColumnSchema $column);

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return mixed
     */
    abstract protected function resolveTime(ColumnSchema $column);

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return mixed
     */
    abstract protected function resolvePk(ColumnSchema $column);

    /**
     * @param \yii\db\ColumnSchema $column
     *
     * @return mixed
     */
    abstract protected function resolveOther(ColumnSchema $column);


}