<?php
/**
 * Created by solly [12.08.16 22:29]
 */

namespace insolita\migrik\gii;

use Yii;
use yii\db\Query;
use yii\gii\CodeFile;
use yii\gii\Generator;
use yii\helpers\StringHelper;

/**
 * Class DataGenerator
 *
 * @package insolita\migrik\gii
 */
class DataGenerator extends Generator
{
    use GeneratorTrait;

    const MODE_QUERY = 'query';
    const MODE_MODEL = 'model';

    public $db = 'db';

    public $migrationPath = '@app/migrations';

    public $tableName;

    public $onlyColumns;

    public $exceptColumns;

    public $insertMode = self::MODE_QUERY;

    public $usePrefix = true;

    public $modelClass;

    public $modelBasename = null;

    public $rawData;

    public $tableColumns;

    public $tableCaption;

    public $tableAlias;

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
                'db'            => 'Database Connection ID',
                'tableName'     => 'Table Name',
                'onlyColumns'   => 'Column List',
                'exceptColumns' => 'Ignore Column List',
                'migrationPath' => 'Migration Path',
                'usePrefix'     => 'Replace table prefix',
                'insertMode'    => 'Insert Mode',
                'modelClass'    => 'Model class',
                'prefix'        => 'Primary prefix for migrations filenames']
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
                'tableName'     => 'table name',
                'onlyColumns'   => 'List of columns used in migration, separated with comma [By default all columns used]',
                'exceptColumns' => 'List of columns skipped in migration, separated with comma [By default all columns 
                used]',
                'migrationPath' => 'Path for save migration file',
                'usePrefix'     => 'Use Table Prefix Replacer eg.{{%tablename}} instead of prefix_tablename',
                'modelClass'    => 'Full class with namespace like app\models\MyModel',
                'prefix'        => 'For correct migration names; format: \'m\' . date(\'ymd_His\'); Don`t change it, if you not sure! ']
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['db', 'tableName', 'onlyColumns', 'exceptColumns', 'modelClass'], 'filter', 'filter' => 'trim'],
                [['db', 'tableName', 'insertMode'], 'required'],
                [['db'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
                [
                    ['tableName'],
                    'match',
                    'pattern' => '/[^\w\*_\,\-\s]/',
                    'not'     => true,
                    'message' => 'Only word characters, underscore, comma,and optionally an asterisk are allowed.'],
                [
                    ['modelClass'],
                    'match',
                    'pattern' => '/^[\w\\\\]+$/',
                    'message' => 'Only word characters and backslashes 
            are allowed.'],

                [['modelClass'], 'validateClass', 'skipOnEmpty' => false],

                [['db'], 'validateDb'],
                ['migrationPath', 'safe'],
                [['usePrefix'], 'boolean'],
                [['insertMode'], 'in', 'range' => [self::MODE_MODEL, self::MODE_QUERY]],
                [['prefix'], 'string'],]
        );
    }


    /**
     * @param string $attribute
     * @param array  $params
     *
     * @return bool
     */
    public function validateClass($attribute, $params)
    {
        if ($this->insertMode == self::MODE_MODEL) {

            if (empty($this->$attribute)) {
                $this->addError($attribute, 'Model Class Required in current insert Mode!');

                return false;
            }
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
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(
            parent::stickyAttributes(),
            ['db', 'migrationPath']
        );
    }

    /**
     * @return CodeFile[] a list of code files to be created.
     */
    public function generate()
    {
        $this->tableCaption = $this->getTableCaption($this->tableName);
        $this->tableAlias = $this->getTableAlias($this->tableCaption);
        $this->tableColumns = $this->prepareTableColumns();
        $this->rawData = $this->getTableData($this->tableName, $this->tableColumns);
        return call_user_func([$this, $this->insertMode . 'ModeGenerate']);

    }

    /**
     * Returns the view file for the input form of the generator.
     * The default implementation will return the "form.php" file under the directory
     * that contains the generator class file.
     *
     * @return string the view file for the input form of the generator.
     */
    public function formView()
    {
        $class = new \ReflectionClass($this);

        return dirname($class->getFileName()) . '/form_data.php';
    }

    /**
     * Returns the root path to the default code template files.
     * The default implementation will return the "templates" subdirectory of the
     * directory containing the generator class file.
     *
     * @return string the root path to the default code template files.
     */
    public function defaultTemplate()
    {
        $class = new \ReflectionClass($this);

        return dirname($class->getFileName()) . '/default_data';
    }

    /**
     * @return array
     **/
    protected function prepareTableColumns()
    {
        $tableSchema = $this->getDbConnection()->getTableSchema($this->tableName);
        $schemaColumns = $tableSchema->getColumnNames();
        $this->onlyColumns = preg_replace('/\s/u', '', $this->onlyColumns);
        $this->exceptColumns = preg_replace('/\s/u', '', $this->exceptColumns);
        if (empty($this->onlyColumns) && empty($this->exceptColumns)) {
            return $schemaColumns;
        }
        if (!empty($this->onlyColumns)) {
            $neededColumns = array_filter(explode(',', $this->onlyColumns), 'trim');
            $schemaColumns = array_intersect($neededColumns, $schemaColumns);
        }
        if (!empty($this->exceptColumns)) {
            $neededColumns = array_filter(explode(',', $this->exceptColumns), 'trim');
            $schemaColumns = array_diff($schemaColumns, $neededColumns);
        }
        return $schemaColumns;
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
                if (in_array($column->name, $columns)) {
                    $data[$column->name] = "";
                }
            }
            return [$data];
        }
    }

    /**
     * @return CodeFile[] a list of code files to be created.
     */
    protected function queryModeGenerate()
    {
        $migrationName = $this->prefix . '_' . $this->tableCaption . 'DataInsert';
        return [
            new CodeFile(

                Yii::getAlias($this->migrationPath) . '/' . $migrationName . '.php', $this->render(
                'data_batch.php',
                [
                    'generator'     => $this,
                    'migrationName' => $migrationName]
            )

            )];
    }

    /**
     * @return CodeFile[] a list of code files to be created.
     */
    protected function modelModeGenerate()
    {
        $this->modelBasename = StringHelper::basename($this->modelClass);
        $migrationName = $this->prefix . '_' . $this->tableCaption . 'ModelInsert';
        return [
            new CodeFile(

                Yii::getAlias($this->migrationPath) . '/' . $migrationName . '.php', $this->render(
                'data_model.php',
                [
                    'generator'     => $this,
                    'migrationName' => $migrationName]
            )

            )];
    }

}