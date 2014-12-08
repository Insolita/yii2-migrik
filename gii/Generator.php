<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 08.12.14
 * Time: 8:03
 */
namespace insolita\migrik\gii;

use yii\db\Connection;
use Yii;
use yii\gii\CodeFile;
use yii\helpers\VarDumper;

class Generator extends \yii\gii\Generator{

    public $db = 'db';
    public $migrationPath = '@app/migrations';
    public $tableName;
    public $usePrefix=true;

    /**
     * @inheritdoc
     */
    public function getName(){

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
        return array_merge(parent::rules(), [
                [['db', 'tableName'], 'filter', 'filter' => 'trim'],
                [['db','tableName'], 'required'],
                [['db'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
                [['tableName'], 'match', 'pattern' => '/^(\w+_)?([\w\*]+)$/', 'message' => 'Only word characters, underscore, and optionally an asterisk are allowed.'],
                [['db'], 'validateDb'],
                [['tableName'], 'validateTableName'],
                ['migrationPath', 'safe'],
                [['usePrefix'], 'boolean'],
            ]);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
                'db' => 'Database Connection ID',
                'tableName' => 'Table Name',
                'migrationPath' => 'Migration Path',
                'usePrefix'=>'Replace table prefix'
            ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
                'db' => 'This is the ID of the DB application component.',
                'tableName' => 'Name of the DB table',
                'migrationPath' => 'Path for save migration file',
                'usePrefix'=>'Use Table Prefix Replacer eg.{{%tablename}} instead of prefix_tablename'
            ]);
    }

    /**
     * @inheritdoc
     */
    public function autoCompleteData()
    {
        $db = $this->getDbConnection();
        if ($db !== null) {
            return [
                'tableName' => function () use ($db) {
                    return $db->getSchema()->getTableNames();
                },
            ];
        } else {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['migration.php'];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['db']);
    }

    /**
     * @inheritdoc
     */
    public function generate(){
        $files = [];
        $db = $this->getDbConnection();
        foreach ($this->getTableNames() as $tableName) {
            $tableSchema = $db->getTableSchema($tableName);
            $tableCaption=$this->getTableCaption($tableName);
            $tableAlias=$this->getTableAlias($tableCaption);
            $tableColumns=$this->generateColumns($tableSchema);
            $tableRelations=$this->generateRelations($tableSchema);
            $tableIndexes=$this->generateIndexes($tableSchema);
            $migrationName='m' . gmdate('ymd_His') . '_' .$tableCaption;
            $params=compact('tableName','tableSchema','tableCaption','tableAlias','tableColumns','tableRelations','tableIndexes');
            $files[] = new CodeFile(
                Yii::getAlias($this->migrationPath) . '/' . $migrationName . '.php',
                $this->render('migration.php', $params)
            );
        }

        return $files;
    }

    public function generateColumns($schema){
        VarDumper::dump($schema,10,true);
        Yii::$app->end();
        return '';
    }
    public function generateRelations($schema){
        return '';
    }
    public function generateIndexes($schema){
        return '';
    }

    public function getTableCaption($tableName){
        $db = $this->getDbConnection();
        return str_replace($db->tablePrefix,'',strtolower($tableName));
    }

    public function getTableAlias($tableCaption){
        return '{{%'.$tableCaption.'}}';
    }

    /**
     * Validates the [[db]] attribute.
     */
    public function validateDb()
    {
        if (!Yii::$app->has($this->db)) {
            $this->addError('db', 'There is no application component named "db".');
        } elseif (!Yii::$app->get($this->db) instanceof Connection) {
            $this->addError('db', 'The "db" application component must be a DB connection instance.');
        }
    }

    /**
     * Validates the [[tableName]] attribute.
     */
    public function validateTableName()
    {
        if (strpos($this->tableName, '*') !== false && substr($this->tableName, -1) !== '*') {
            $this->addError('tableName', 'Asterisk is not allowed as the last character.');

            return;
        }
        $tables = $this->getTableNames();
        if (empty($tables)) {
            $this->addError('tableName', "Table '{$this->tableName}' does not exist.");
        }
    }
    private $_tableNames;

    /**
     * @return array the table names that match the pattern specified by [[tableName]].
     */
    protected function getTableNames()
    {
        if ($this->_tableNames !== null) {
            return $this->_tableNames;
        }
        $db = $this->getDbConnection();
        if ($db === null) {
            return [];
        }
        $tableNames = [];
        if (strpos($this->tableName, '*') !== false) {
            $schema = '';
            $pattern = '/^' . str_replace('*', '\w+', $this->tableName) . '$/';

            foreach ($db->schema->getTableNames($schema) as $table) {
                if (preg_match($pattern, $table)) {
                    $tableNames[] = $table;
                }
            }
        } elseif (($table = $db->getTableSchema($this->tableName, true)) !== null) {
            $tableNames[] = $this->tableName;
        }

        return $this->_tableNames = $tableNames;
    }


    /**
     * @return Connection the DB connection as specified by [[db]].
     */
    protected function getDbConnection()
    {
        return Yii::$app->{$this->db};
    }

    /**
     * Checks if any of the specified columns is auto incremental.
     * @param  \yii\db\TableSchema $table   the table schema
     * @param  array               $columns columns to check for autoIncrement property
     * @return boolean             whether any of the specified columns is auto incremental.
     */
    protected function isColumnAutoIncremental($table, $columns)
    {
        foreach ($columns as $column) {
            if (isset($table->columns[$column]) && $table->columns[$column]->autoIncrement) {
                return true;
            }
        }

        return false;
    }

    public  function getLabelDefaults($labelname, $default){
        $defaults=['active'=>'Активно?','name'=>'Название','title'=>'Заголовок','created'=>'Создано','updated'=>'Обновлено'];
        return isset($defaults[$labelname])?$defaults[$labelname]:$default;
    }
} 