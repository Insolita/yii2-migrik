<?php
/**
 * Created by solly [13.08.16 23:48]
 */

namespace insolita\migrik\contracts;

use yii\db\Connection;

/**
 * Interface IMigrationTableResolver
 *
 * @package insolita\migrik\contracts
 */
interface IMigrationTableResolver
{
    /**
     * Base constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection);

    /**
     * @param string $tablePattern
     *
     * @return array
     **/
    public function findTablesByPattern($tablePattern);

    /**
     * @return array
     **/
    public function getTableNames();

    /**
     * @param $tableName
     *
     * @return \yii\db\TableSchema
     */
    public function getTableSchema($tableName);
    
    /**
     * @param $tableName
     *
     * @return \string[]
     */
    public function getPrimaryKeys($tableName);

    /**
     * @param string $tableName
     * @return array
    **/
    public function getRelations($tableName);

    /**
     * @param string $tableName
     * @return array
    **/
    public function getIndexes($tableName);

}