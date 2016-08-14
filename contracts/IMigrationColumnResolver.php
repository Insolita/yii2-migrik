<?php
/**
 * Created by solly [13.08.16 23:48]
 */

namespace insolita\migrik\contracts;

use yii\db\Schema;
use yii\db\TableSchema;

/**
 * Interface IMigrationColumnResolver
 *
 * @package insolita\migrik\contracts
 */
interface IMigrationColumnResolver
{
    /**
     * Base constructor.
     *
     * @param \yii\db\Schema $schema
     * @param \yii\db\TableSchema $tableSchema
     */
    public function __construct(Schema $schema, TableSchema $tableSchema);

    /**
     * Method must return string of representation of  part of migration for current column name
     * @expect "string(255) NOT NULL DEFAULT 'example' COMMENT 'bla-bla'"
     * or "$this->string(255)->notNull()->defaultValue('example')->comment('bla-bla')"
     * @param string $columnName
     * @return string
    **/
    public function resolveColumn($columnName);

}