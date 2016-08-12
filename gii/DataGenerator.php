<?php
/**
 * Created by solly [12.08.16 22:29]
 */

namespace insolita\migrik\gii;


use yii\db\Connection;
use yii\gii\CodeFile;
use yii\gii\Generator;

class DataGenerator extends Generator
{
    const MODE_QUERY = 'query';
    const MODE_MODEL = 'model';

    public $db = 'db';
    public $migrationPath = '@app/migrations';
    public $tableName;
    public $onlyColumns;
    public $insertMode = self::MODE_QUERY;
    public $usePrefix = true;
    public $modelClass;

    protected $rawData;
    protected $tableColumns;
    protected $tableCaption;
    protected $tableAlias;

    /**
     * @return string name of the code generator
     */
    public function getName()
    {
        return 'Table-Data migrations';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates migration file for insert data from table.';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'db' => 'Database Connection ID',
                'tableName' => 'Table Name',
                'onlyColumns' => 'Column List',
                'migrationPath' => 'Migration Path',
                'usePrefix' => 'Replace table prefix',
                'insertMode' => 'Insert Mode',
                'modelClass' => 'Model class'
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
                'db' => 'This is the ID of the DB application component.',
                'tableName' => 'table name',
                'onlyColumns' => 'List of columns used in migration, separated with comma [By default all columns used]',
                'migrationPath' => 'Path for save migration file',
                'usePrefix' => 'Use Table Prefix Replacer eg.{{%tablename}} instead of prefix_tablename',
                'modelClass' => 'Full class with namespace like app\models\MyModel'
            ]
        );
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
     * @return Connection the DB connection as specified by [[db]].
     */
    protected function getDbConnection()
    {
        return Yii::$app->{$this->db};
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['db', 'tableName', 'onlyColumns', 'modelClass'], 'filter', 'filter' => 'trim'],
                [['db', 'tableName', 'insertMode'], 'required'],
                [['db'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
                [
                    ['tableName'],
                    'match',
                    'pattern' => '/[^\w\*_\,\-\s]/',
                    'not' => true,
                    'message' => 'Only word characters, underscore, comma,and optionally an asterisk are allowed.'
                ],
                [
                    ['modelClass'],
                    'match',
                    'pattern' => '/^[\w\\\\]+$/',
                    'message' => 'Only word characters and backslashes 
            are allowed.'
                ],
                [['modelClass'], 'validateClass'],
                [['db'], 'validateDb'],
                [['tableName'], 'validateTableName'],
                ['migrationPath', 'safe'],
                [['usePrefix'], 'boolean'],
                [['insertMode'], 'in', 'range' => [self::MODE_MODEL, self::MODE_QUERY]],
            ]
        );
    }

    public function validateClass($attribute, $params)
    {
        if ($this->insertMode == self::MODE_MODEL) {
            return parent::validateClass($attribute, $params);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['data_batch.php', 'data_model.php'];
    }

    /**
     *
     * @return CodeFile[] a list of code files to be created.
     */
    public function generate()
    {
        $this->tableColumns = $this->prepareTableColumns();
        $this->rawData = $this->getTableData($this->tableName, $this->tableColumns);
        return call_user_func($this, $this->insertMode . 'ModeGenerate');

    }

    /**
     * @return array
    **/
    protected function prepareTableColumns(){
        $tableSchema = $this->getDbConnection()->getTableSchema($this->tableName);
        $schemaColumns = $tableSchema->getColumnNames();
        $this->onlyColumns = preg_replace('/\s/u','',$this->onlyColumns);
        if(empty($this->onlyColumns)){
            return $schemaColumns;
        }
        $neededColumns = array_filter(explode(',',$this->onlyColumns),'trim');
        return array_intersect($neededColumns, $schemaColumns);
    }

    /**
     * Get Table name without prefix
     * @param string $tableName
     * @return string
     **/
    public function getTableCaption($tableName)
    {
        return str_replace($this->getDbConnection()->tablePrefix, '', strtolower($tableName));
    }


    /**
     * Get yii-like table alias
     * @param string $tableCaption
     * @return string
     **/
    public function getTableAlias($tableCaption)
    {
        return '{{%' . $tableCaption . '}}';
    }

    /**
     *
     * @return CodeFile[] a list of code files to be created.
     */
    protected function queryModeGenerate()
    {
        $migrationName='m' . gmdate('ymd_Hi0') . '_'.$this->tableCaption.'DataInsert';
        return [new CodeFile([
                                 Yii::getAlias($this->migrationPath) . '/' . $migrationName . '.php',
                                 $this->render('data_batch.php', [
                                     'generator'=>$this,
                                     'migrationName'=>$migrationName
                                 ])
                             ])];
    }

    /**
     *
     * @return CodeFile[] a list of code files to be created.
     */
    protected function modelModeGenerate()
    {
        $migrationName='m' . gmdate('ymd_Hi0') . '_'.$this->tableCaption.'ModelInsert';
        return [new CodeFile([
                                 Yii::getAlias($this->migrationPath) . '/' . $migrationName . '.php',
                                 $this->render('data_model.php', [
                                     'generator'=>$this,
                                     'migrationName'=>$migrationName
                                 ])
                             ])];
    }

    /**
     * @param string $tableName
     * @param array  $columns
     *
     * @return array
     **/
    protected function getTableData($tableName, $columns = [])
    {
        $select = !empty($columns) ? $columns : '*';
        $query = (new Query())->select($select)->from($tableName);
        $data = $query->all($this->getDbConnection());
        if (!empty($data)) {
            return $data;
        } else {
            $tableSchema = $this->getDbConnection()->getTableSchema($tableName);
            foreach ($tableSchema->columns as $column) {
                $data[$column->name] = "";
            }
            return [$data];
        }
    }


}