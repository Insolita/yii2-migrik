<?php
/**
 * Created by solly [14.08.16 23:02]
 */

namespace insolita\migrik\resolver;

use insolita\migrik\contracts\IMigrationTableResolver;
use yii\db\Connection;
use yii\db\TableSchema;

/**
 * Class TableResolver
 *
 * @package insolita\migrik\resolver
 */
class TableResolver implements IMigrationTableResolver
{
    /**
     * @var \yii\db\Schema
     */
    public $schema;
    
    /**
     * @var array
     */
    protected $tableSchemas = [];
    
    /**
     * @var \yii\db\Connection
     */
    private $connection;
    
    /**
     * Base constructor.
     *
     * @param Connection $connection
     */
    public function __construct(\yii\db\Connection $connection)
    {
        $this->connection = $connection;
        $this->schema = $this->connection->getSchema();
    }
    
    /**
     * @param string $tablePattern
     *
     * @return array
     **/
    public function findTablesByPattern($tablePattern)
    {
        $founds = [];
        $tablePattern = trim($tablePattern);
        if ($tablePattern == '*') {
            foreach ($this->getTableNames() as $table) {
                $founds[] = $table;
            }
        } elseif (strpos($tablePattern, '*') !== false) {
            $pattern = '/^' . str_replace('*', '\w+', $tablePattern) . '$/';
            foreach ($this->getTableNames() as $table) {
                if (preg_match($pattern, $table)) {
                    $founds[] = $table;
                }
            }
            
        } elseif ($this->getTableSchema($tablePattern) !== null) {
            $founds[] = $tablePattern;
        }
        return $founds;
    }
    
    /**
     * @return array
     **/
    public function getTableNames()
    {
        return $this->schema->tableNames;
    }
    
    /**
     * @param $tableName
     *
     * @return TableSchema
     */
    public function getTableSchema($tableName)
    {
        if (!isset($this->tableSchemas[$tableName])) {
            $this->tableSchemas[$tableName] = $this->schema->getTableSchema($tableName);
        }
        return $this->tableSchemas[$tableName];
    }
    
    /**
     * @param $tableName
     *
     * @return \string[]
     */
    public function getPrimaryKeys($tableName)
    {
        $tableSchema = $this->getTableSchema($tableName);
        return $tableSchema->primaryKey;
    }
    
    /**
     * @param string $tableName
     *
     * @return array
     **/
    public function getRelations($tableName)
    {
        
        $tableSchema = $this->getTableSchema($tableName);
        $relations = [];
        if (!empty($tableSchema->foreignKeys)) {
            foreach ($tableSchema->foreignKeys as $i => $constraint) {
                foreach ($constraint as $pk => $fk) {
                    if (!$pk) {
                        $relations[$i]['ftable'] = $fk;
                    } else {
                        $relations[$i]['pk'] = $pk;
                        $relations[$i]['fk'] = $fk;
                    }
                }
            }
        }
        return $relations;
    }
    
    /**
     * @param string $tableName
     *
     * @return array
     **/
    public function getIndexes($tableName)
    {
        
        $tableSchema = $this->getTableSchema($tableName);
        $indexes = [];
        if ($this->connection->driverName == 'mysql') {
            $query = $this->connection->createCommand('SHOW INDEX FROM [[' . $tableName . ']]')->queryAll();
            if ($query) {
                foreach ($query as $i => $index) {
                    $indexes[$index['Key_name']]['cols'][$index['Seq_in_index']] =
                        trim($index['Column_name'],'\'"');
                    $indexes[$index['Key_name']]['isuniq'] = ($index['Non_unique'] == 1) ? false : true;
                }
            }
        } elseif ($this->connection->driverName == 'pgsql') {
            $schemaIndexes = $this->fetchPqSqlIndexes($tableSchema->schemaName, $tableName);
            if (!empty($schemaIndexes)) {
                foreach ($schemaIndexes as $i => $columns) {
                    if (!$columns['ispk']) {
                        $indexes[$columns['indexname']]['cols'][] = trim($columns['columnname'],'\'"');
                        $indexes[$columns['indexname']]['isuniq'] = $columns['isuniq'] ? true : false;
                    }
                }
            }
        } elseif (method_exists($this->schema, 'findUniqueIndexes')) {
            $schemaIndexes = call_user_func([$this->schema, 'findUniqueIndexes'], $tableSchema);
            if (!empty($schemaIndexes)) {
                foreach ($schemaIndexes as $indexName => $columns) {
                    $indexes[$indexName]['cols'] = array_walk($columns, function (&$v){$v=trim($v,'\'"');});
                    $indexes[$indexName]['isuniq'] = 1;
                }
            }
        }
        return $indexes;
    }
    
    protected function fetchPqSqlIndexes($schemaName, $tableName)
    {
        $sql
            = <<<SQL
SELECT
    i.relname as indexname, idx.indisprimary as ispk,  idx.indisunique  as isuniq,
    pg_get_indexdef(idx.indexrelid, k + 1, TRUE) AS columnname
FROM (
  SELECT *, generate_subscripts(indkey, 1) AS k
  FROM pg_index
) idx
INNER JOIN pg_class i ON i.oid = idx.indexrelid
INNER JOIN pg_class c ON c.oid = idx.indrelid
INNER JOIN pg_namespace ns ON c.relnamespace = ns.oid
WHERE  c.relname = :tableName  AND ns.nspname = :schemaName
ORDER BY i.relname, k
SQL;
        return $this->connection->createCommand(
            $sql,
            [
                ':schemaName' => $schemaName,
                ':tableName'  => $tableName,
            ]
        )->queryAll();
    }
    
}