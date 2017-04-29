<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 08.12.14
 * Time: 8:03
 */

namespace insolita\migrik\gii;

use insolita\migrik\contracts\IMigrationColumnResolver;
use insolita\migrik\contracts\IMigrationTableResolver;
use insolita\migrik\resolver\TableResolver;
use Yii;
use yii\db\TableSchema;
use yii\gii\CodeFile;
use yii\helpers\ArrayHelper;

set_time_limit(0);

/**
 * Class StructureGenerator
 *
 * @package insolita\migrik\gii
 */
class StructureGenerator extends \yii\gii\Generator
{
    const MOD_SINGLE = 'single';
    const MOD_BULK = 'bulk';
    const FORMAT_FLUENT = 'fluent';
    const FORMAT_RAW = 'raw';
    
    use GeneratorTrait;
    
    /**
     * @var string
     */
    public $db = 'db';
    
    /**
     * @var string
     */
    public $migrationPath = '@app/migrations';
    
    /**
     * @var
     */
    public $tableName;
    
    /**
     * @var
     */
    public $tableIgnore;
    
    /**
     * @var string
     */
    public $genmode = self::MOD_SINGLE;
    
    /**
     * @var string
     **/
    public $format = self::FORMAT_FLUENT;
    
    /**
     * @var string
     **/
    public $resolverClass = null;
    
    /**
     * @var bool
     */
    public $usePrefix = true;
    
    /**
     * @var string
     */
    public $tableOptions = 'ENGINE=InnoDB';
    
    /**
     * @var string default value for Foreign key ON_UPDATE
     */
    public $fkOnUpdate = 'CASCADE';
    
    /**
     * @var string default value for Foreign key ON_DELETE
     */
    public $fkOnDelete = 'CASCADE';
    
    /**
     * @var array
     */
    private $_ignoredTables = [];
    
    /**
     * @var array
     */
    private $_tables = [];
    
    /**
     * @var null
     */
    private $tableResolver = null;
    
    /**
     * @inheritdoc
     */
    public function getName()
    {
        
        return 'Migration Generator';
    }
    
    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates migration file for the specified database table.';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['db', 'tableName', 'tableIgnore', 'resolverClass'], 'filter', 'filter' => 'trim'],
                [['db', 'tableName', 'format', 'prefix'], 'required'],
                [['db'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
                [
                    ['tableName', 'tableIgnore'],
                    'match',
                    'pattern' => '/[^\w\*_\,\-\s]/',
                    'not'     => true,
                    'message' => 'Only word characters, underscore, comma,and optionally an asterisk are allowed.',
                ],
                [['db'], 'validateDb'],
                ['migrationPath', 'safe'],
                ['tableOptions', 'safe'],
                [['usePrefix'], 'boolean'],
                [
                    ['resolverClass'],
                    'validateClass',
                    'params'      => ['extends' => 'insolita\migrik\contracts\IMigrationColumnResolver'],
                    'skipOnEmpty' => true,
                ],
                [['genmode'], 'in', 'range' => [self::MOD_SINGLE, self::MOD_BULK]],
                [['format'], 'in', 'range' => [self::FORMAT_FLUENT, self::FORMAT_RAW]],
                [['tableName'], 'validateTableName'],
                [['fkOnUpdate', 'fkOnDelete'], 'default', 'value' => 'CASCADE'],
                [['prefix', 'fkOnUpdate', 'fkOnDelete'], 'string'],
            ]
        );
    }
    
    /**
     * @inheritdoc
     */
    public function formView()
    {
        $class = new \ReflectionClass($this);
        
        return dirname($class->getFileName()) . '/form_structure.php';
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'db'            => 'Database Connection ID',
                'tableName'     => 'Table Name',
                'tableIgnore'   => 'Ignored tables',
                'migrationPath' => 'Migration Path',
                'usePrefix'     => 'Replace table prefix',
                'genmode'       => 'Generation Mode',
                'tableOptions'  => 'Table Options',
                'format'        => 'Format of column definition',
                'resolverClass' => 'Custom RawColumnResolver class',
                'prefix'        => 'Primary prefix for migrations filenames',
                'fkOnUpdate'    => 'Default action ON_UPDATE for addForeignKey method',
                'fkOnDelete'    => 'Default action ON_DELETE for addForeignKey method',
            ]
        );
    }
    
    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(
            parent::hints(),
            [
                'db'            => 'This is the ID of the DB application component.',
                'tableName'     => 'Use "*" for all table, mask support - as "tablepart*", or you can separate table names by comma ',
                'tableIgnore'   => 'You can separate some table names by comma, for ignor ',
                'migrationPath' => 'Path for save migration file',
                'usePrefix'     => 'Use Table Prefix Replacer eg.{{%tablename}} instead of prefix_tablename',
                'genmode'       => 'All tables in separated files, or all in one file',
                'tableOptions'  => 'Table Options',
                'format'        => 'fluent - like $this->text()->notNull()->defaultValue("foo") or raw "TEXT NOT NULL DEFAULT 
                \"foo\"" if custom resolver class configured, this option will be ignored',
                'resolverClass' => 'Full-qualified class name for custom implementation of 
                \insolita\migrik\contracts\IMigrationColumnResolver',
                'prefix'        => 'For correct migration names; format: \'m\' . date(\'ymd_His\'); Don`t change it, if you not sure! ',
            ]
        );
    }
    
    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['migration.php', 'relation.php', 'mass.php'];
    }
    
    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(
            parent::stickyAttributes(),
            [
                'db',
                'migrationPath',
                'usePrefix',
                'tableOptions',
                'tableIgnore',
                'resolverClass',
                'fkOnUpdate',
                'fkOnDelete',
            ]
        );
    }
    
    /**
     * @inheritdoc
     */
    public function generate()
    {
        if ($this->genmode == self::MOD_SINGLE) {
            return $this->generateSingleMigration();
        } else {
            return $this->generateBulkMigration();
        }
    }
    
    /**
     * @inheritdoc
     */
    public function defaultTemplate()
    {
        $class = new \ReflectionClass($this);
        
        return dirname($class->getFileName()) . '/default_structure';
    }
    
    /**
     * @return array
     */
    public function getIgnoredTables()
    {
        return $this->_ignoredTables;
    }
    
    /**
     * @return array
     */
    public function getTables()
    {
        return $this->_tables;
    }
    
    /**
     * Validates the [[tableName]] attribute.
     */
    public function validateTableName()
    {
        $tables = $this->prepareTables();
        if (empty($tables)) {
            $this->addError('tableName', "Table '{$this->tableName}' does not exist, or all tables was ignored");
            return false;
        }
        return true;
    }
    
    /**
     * @return CodeFile[]
     */
    protected function generateSingleMigration()
    {
        $files = [];
        $allRelations = [];
        foreach ($this->getTables() as $tableName) {
            list(
                $tableCaption, $tableAlias, $tableIndexes, $tableColumns, $tableRelations, $tablePk
                )
                = $this->collectTableInfo($tableName);
            if (!empty($tableRelations)) {
                $allRelations[] = $tableRelations;
            }
            $migrationName = $this->nextPrefix . '_' . $tableCaption;
            $params = compact(
                'tableName',
                'tableCaption',
                'tableAlias',
                'migrationName',
                'tableColumns',
                'tableIndexes',
                'tablePk'
            );
            $files[] = new CodeFile(
                Yii::getAlias($this->migrationPath) . '/' . $migrationName . '.php',
                $this->render('migration.php', $params)
            );
            $this->refreshNextPrefix();
        }
        if (!empty($allRelations)) {
            $migrationName = $this->nextPrefix . '_Relations';
            $params = [
                'tableRelations' => $allRelations,
                'migrationName'  => $migrationName,
                'fkProps'        => ['onUpdate' => $this->fkOnUpdate, 'onDelete' => $this->fkOnDelete],
            ];
            $files[] = new CodeFile(
                Yii::getAlias($this->migrationPath) . '/' . $migrationName . '.php',
                $this->render('relation.php', $params)
            );
        }
        return $files;
    }
    
    /**
     * @return CodeFile[]
     */
    protected function generateBulkMigration()
    {
        $files = [];
        $allRelations = [];
        $allTables = [];
        foreach ($this->getTables() as $tableName) {
            list(
                , $tableAlias, $tableIndexes, $tableColumns, $tableRelations, $tablePk
                )
                = $this->collectTableInfo($tableName);
            
            if (!empty($tableRelations)) {
                $allRelations[] = $tableRelations;
            }
            $allTables[] = [
                'alias'       => $tableAlias,
                'indexes'     => $tableIndexes,
                'columns'     => $tableColumns,
                'tablePk'     => $tablePk,
                'name'        => $tableName,
            ];
        }
        
        $suffix = 'Mass';
        if (($tables = $this->getTables()) && sizeof($tables) == 1) {
            $suffix = $tables[0];
        }
        
        $migrationName = $this->prefix . '_' . $suffix;
        $params = [
            'tableList'      => $allTables,
            'tableRelations' => $allRelations,
            'migrationName'  => $migrationName,
            'fkProps'        => ['onUpdate' => $this->fkOnUpdate, 'onDelete' => $this->fkOnDelete],
        ];
        $files[] = new CodeFile(
            Yii::getAlias($this->migrationPath) . '/' . $migrationName . '.php', $this->render('mass.php', $params)
        );
        return $files;
    }
    
    /**
     * @param $tableName
     *
     * @return array
     */
    protected function collectTableInfo($tableName)
    {
        $tableCaption = $this->getTableCaption($tableName);
        $tableAlias = $this->getTableAlias($tableCaption);
        $tableIndexes = $this->getTableResolver()->getIndexes($tableName);
        $tableColumns = $this->buildColumnDefinitions($tableName);
        $tablePk = $this->getTableResolver()->getPrimaryKeys($tableName);
        if (count($tablePk) == 1 && $this->checkIfPkColumnIsInteger($tablePk[0], $tableColumns) === true) {
            //prevent pk duplication, if it set in column definition
            $tablePk = [];
        }
        $relations = $this->getTableResolver()->getRelations($tableName);
        array_walk($relations, function (&$value){
            $value['ftable'] = $this->getTableAlias($this->getTableCaption($value['ftable']));
        });
        $tableRelations = !empty($relations) ? [
            'fKeys'      => $relations,
            'tableAlias' => $tableAlias,
            'tableName'  => $tableName,
        ] : [];
        return [$tableCaption, $tableAlias, $tableIndexes, $tableColumns, $tableRelations, $tablePk];
    }
    
    /**
     * @param $pk
     * @param $tableColumns
     *
     * @return bool
     */
    protected function checkIfPkColumnIsInteger($pk, $tableColumns)
    {
        $pkColumn = ArrayHelper::getValue($tableColumns, $pk);
        $check = false;
        if ($this->format === self::FORMAT_FLUENT) {
            if (mb_strpos($pkColumn, 'primaryKey') !== false || mb_strpos($pkColumn, 'bigPrimaryKey') !== false) {
                $check = true;
            }
        } else {
            if (mb_strpos($pkColumn, 'TYPE_PK') !== false
                || mb_strpos($pkColumn, 'TYPE_UPK') !== false
                || mb_strpos($pkColumn, 'TYPE_BIGPK') !== false
                || mb_strpos($pkColumn, 'TYPE_UBIGPK') !== false
            ) {
                $check = true;
            }
        }
        return $check;
    }
    
    /**
     * @return IMigrationTableResolver|TableResolver
     **/
    protected function getTableResolver()
    {
        if (!$this->tableResolver) {
            /**
             * @var IMigrationTableResolver
             **/
            $this->tableResolver = Yii::createObject(
                ['class' => 'insolita\migrik\contracts\IMigrationTableResolver'],
                [$this->getDbConnection()]
            );
        }
        return $this->tableResolver;
    }
    
    /**
     * @param string $tableName
     *
     * @return array
     */
    protected function buildColumnDefinitions($tableName)
    {
        $cols = [];
        $tableSchema = $this->getTableResolver()->getTableSchema($tableName);
        $resolver = $this->createColumnResolver($tableSchema);
        foreach ($tableSchema->columns as $column) {
            $type = $resolver->resolveColumn($column->name);
            $cols[$column->name] = $type;
        }
        return $cols;
    }
    
    /**
     * @param TableSchema $tableSchema
     *
     * @return IMigrationColumnResolver
     **/
    protected function createColumnResolver(TableSchema $tableSchema)
    {
        $params = [
            $this->getDbConnection()->schema,
            $tableSchema,
        ];
        if ($this->resolverClass) {
            return Yii::createObject(['class' => $this->resolverClass], $params);
        } elseif ($this->format == 'fluent') {
            switch ($this->getDbConnection()->driverName){
                case 'pgsql': {
                    return Yii::createObject(['class' => 'insolita\migrik\resolver\PgFluentColumnResolver'], $params);
                }
                case 'mysql':
                default: {
                    return Yii::createObject(['class' => 'insolita\migrik\resolver\FluentColumnResolver'], $params);
                }
            }
        } else {
            switch ($this->getDbConnection()->driverName){
                case 'pgsql': {
                    return Yii::createObject(['class' => 'insolita\migrik\resolver\PgRawColumnResolver'], $params);
                }
                case 'mysql':
                default: {
                    return Yii::createObject(['class' => 'insolita\migrik\resolver\RawColumnResolver'], $params);
                }
            }
        }
    }
    
    /**
     * List of table names that match the pattern specified by [[tableName]].
     *
     * @return array
     */
    protected function prepareTables()
    {
        $tables = [];
        $this->prepareIgnored();
        if ($this->tableName) {
            if (strpos($this->tableName, ',') !== false) {
                $tables = explode(',', $this->tableName);
            } else {
                $tables[] = $this->tableName;
            }
        }
        if (empty($tables)) {
            return [];
        }
        foreach ($tables as $goodTable) {
            $prepared = $this->getTableResolver()->findTablesByPattern($goodTable);
            $this->_tables = !empty($prepared) ? array_merge($this->_tables, $prepared) : [];
        }
        foreach ($this->_tables as $i => $t) {
            if (in_array($t, $this->_ignoredTables)) {
                unset($this->_tables[$i]);
            }
        }
        return $this->_tables;
    }
    
    /**
     * List of table names that match the pattern specified by [[tableName]].
     *
     * @return array
     */
    protected function prepareIgnored()
    {
        $ignors = [];
        if ($this->tableIgnore) {
            if (strpos($this->tableIgnore, ',') !== false) {
                $ignors = explode(',', $this->tableIgnore);
            } else {
                $ignors[] = $this->tableIgnore;
            }
        }
        $ignors = array_filter($ignors, 'trim');
        if (!empty($ignors)) {
            foreach ($ignors as $ignoredTable) {
                $prepared = $this->getTableResolver()->findTablesByPattern($ignoredTable);
                if (!empty($prepared)) {
                    $this->_ignoredTables = array_merge($this->_ignoredTables, $prepared);
                }
            }
        }
        return $this->_ignoredTables;
    }
}
