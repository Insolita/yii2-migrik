<?php
/**
 * Created by solly [13.08.16 23:48]
 */

namespace insolita\migrik\contracts;
use yii\db\ColumnSchemaBuilder;
use yii\db\TableSchema;


/**
 * Interface IMigrationColumnResolver
 *
 * @package insolita\migrik\contracts
 */
interface IMigrationColumnResolver
{
    /**
     * Method must return string of representation of  part of migration for current column name
     * @expect "string(255) NOT NULL DEFAULT 'example' COMMENT 'bla-bla'"
     * or "$this->string(255)->notNull()->defaultValue('example')->comment('bla-bla')"
     * @param string $columnName
     * @return string
    **/
     public function resolveColumn($columnName);

    /**
     * @param \yii\db\TableSchema $tableSchema
     *
     * @return mixed
     */
    public function setTableSchema(TableSchema $tableSchema);

    /**
     * @param \yii\db\ColumnSchemaBuilder $columnSchemaBuilder
     *
     * @return mixed
     */
    public function setColumnSchemaBuilder(ColumnSchemaBuilder $columnSchemaBuilder);
}