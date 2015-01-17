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
use yii\db\Expression;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\db\TableSchema;

class Generator extends \yii\gii\Generator{


    public $db = 'db';
    public $migrationPath = '@app/migrations';
    public $tableName;
    public $tableIgnore;
    public $genmode='schema';
    public $usePrefix=true;
    public $tableOptions='ENGINE=InnoDB';

    private $_ignoredTables = [];
    private $_tables = [];

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
                [['db', 'tableName','tableIgnore'], 'filter', 'filter' => 'trim'],
                [['db','tableName'], 'required'],
                [['db'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
                [
                    ['tableName','tableIgnore'], 'match', 'pattern' =>'/[^\w\*_\,\-\s]/','not'=>true,
                    'message' => 'Only word characters, underscore, comma,and optionally an asterisk are allowed.'
                ],
                [['db'], 'validateDb'],
                [['tableName'], 'validateTableName'],
                ['migrationPath', 'safe'],
                ['tableOptions', 'safe'],
                [['usePrefix'], 'boolean'],
                [['genmode'],'in','range'=>['single','mass']],
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
                'tableIgnore' => 'Ignored tables',
                'migrationPath' => 'Migration Path',
                'usePrefix'=>'Replace table prefix',
                'genmode'=>'Generation Mode',
                'tableOptions'=>'Table Options'
            ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
                'db' => 'This is the ID of the DB application component.',
                'tableName' => 'Use "*" for all table, mask support - as "tablepart*", or you can separate table names by comma ',
                'tableIgnore' => 'You can separate some table names by comma, for ignor ',
                'migrationPath' => 'Path for save migration file',
                'usePrefix'=>'Use Table Prefix Replacer eg.{{%tablename}} instead of prefix_tablename',
                'genmode'=>'All tables in separated files, or all in one file',
                'tableOptions'=>'Table Options'
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
        return ['migration.php','relation.php','mass.php'];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['db','migrationPath','usePrefix','tableOptions','tableIgnore']);
    }
    public function getIgnoredTables()
    {
        return $this->_ignoredTables;
    }

    public function getTables()
    {
        return $this->_tables;
    }
    /**
     * @inheritdoc
     */
    public function generate(){
        $files =$tableRelations=$tableList= [];
        $db = $this->getDbConnection();
        $i=10;
        if($this->genmode=='single'){
            foreach ($this->getTables() as $tableName) {
                $i++;
                $tableSchema = $db->getTableSchema($tableName);
                $tableCaption=$this->getTableCaption($tableName);
                $tableAlias=$this->getTableAlias($tableCaption);
                $tableIndexes=$this->genmode=='schema'?null:$this->generateIndexes($tableName);
                $tableColumns=$this->columnsBySchema($tableSchema);
                $tableRelations[]=['fKeys'=>$this->generateRelations($tableSchema),'tableAlias'=>$tableAlias,'tableName'=>$tableName];
                $migrationName='m' . gmdate('ymd_Hi'.$i) . '_' .$tableCaption;
                $params=compact('tableName','tableSchema','tableCaption','tableAlias','migrationName','tableColumns','tableIndexes');
                $files[] = new CodeFile(
                    Yii::getAlias($this->migrationPath) . '/' . $migrationName . '.php',
                    $this->render('migration.php', $params)
                );
            }
            $i++;

            /**Костыль.. иначе gii глючит при попытке просмотра **/
            $migrationName='m' . gmdate('ymd_Hi'.$i) . '_Relations';
            //$migrationName='m' . gmdate('ymd_His') . '_Relations';
            $params=['tableRelations'=>$tableRelations,'migrationName'=>$migrationName];
            $files[] = new CodeFile(
                Yii::getAlias($this->migrationPath) . '/' . $migrationName . '.php',
                $this->render('relation.php', $params)
            );
        }else{
            foreach ($this->getTableNames() as $tableName) {
                $i++;
                $tableSchema = $db->getTableSchema($tableName);
                $tableCaption=$this->getTableCaption($tableName);
                $tableAlias=$this->getTableAlias($tableCaption);
                $tableIndexes=$this->generateIndexes($tableName);
                $tableColumns=$this->columnsBySchema($tableSchema);
                $tableRelations[]=['fKeys'=>$this->generateRelations($tableSchema),'tableAlias'=>$tableAlias,'tableName'=>$tableName];
                $tableList[]=['alias'=>$tableAlias,'indexes'=>$tableIndexes,'columns'=>$tableColumns,'name'=>$tableName];
            }
            $i++;
            $migrationName='m' . gmdate('ymd_His') . '_Mass';
            //$migrationName='m' . gmdate('ymd_Hi'.$i) . '_Mass';
            $params=['tableList'=>$tableList,'tableRelations'=>$tableRelations,'migrationName'=>$migrationName];
            $files[] = new CodeFile(
                Yii::getAlias($this->migrationPath) . '/' . $migrationName . '.php',
                $this->render('mass.php', $params)
            );
        }


         return $files;
    }



    public function columnsBySchema($schema){
        $cols=[];
        /**@var TableSchema $schema**/
        foreach($schema->columns as $column){
            $type=$this->getColumnType($column);
            $cols[$column->name]=$type;
        }
        return $cols;
    }


    public function getColumnType($col){
        $coldata=$append='';
        /**@var \yii\db\ColumnSchema $col**/
        if($col->autoIncrement){
            $coldata = $col->type!==Schema::TYPE_BIGINT?'Schema::TYPE_PK':'Schema::TYPE_BIGPK';
        }elseif(strpos($col->dbType,'set(')!==false){
            $coldata ='"'.$col->dbType.'"';
        }elseif(strpos($col->dbType,'enum(')!==false){
            $coldata ='"'.$col->dbType.'"';
        }elseif($col->dbType === 'tinyint(1)'){
            $coldata ='Schema::TYPE_BOOLEAN';
        }else{
            $coldata='Schema::TYPE_'.strtoupper($col->type);
        }

        if($col->size && !$col->autoIncrement){
            $append.=($col->scale)?'('.$col->size.','.$col->scale.')':'('.$col->size.')';
        }
        $append.=(!$col->allowNull && !$col->autoIncrement)?' NOT NULL':'';
        $append.=($col->unsigned && !$col->autoIncrement)?' unsigned':'';

        if(!is_null($col->defaultValue)){
            $append.=' DEFAULT '.($col->defaultValue instanceof Expression?$col->defaultValue->expression:'"'.$col->defaultValue.'"');
        }
        if(!empty($col->comment)){
            $append.=' COMMENT "'.$col->comment.'"';
        }

        return $coldata.".'".$append."'";
    }

    public function generateRelations($schema){
        /**@var TableSchema $schema**/
        $rels=[];
        if(!empty($schema->foreignKeys)){
            foreach($schema->foreignKeys as $i=>$constraint){
                foreach($constraint as $pk=>$fk){
                    if(!$pk){
                      $rels[$i]['ftable']=$fk;
                    }else{
                        $rels[$i]['pk']=$pk;
                        $rels[$i]['fk']=$fk;
                    }
                }
            }
        }
        //return [VarDumper::dumpAsString($schema->foreignKeys)];
        return $rels;
    }
    public function generateIndexes($tableName){
        $indexes=[];
        $query=Yii::$app->db->createCommand('SHOW INDEX FROM '.$tableName)->queryAll();
        if($query){
            foreach($query as $i=>$index){
                $indexes[$index['Key_name']]['cols'][$index['Seq_in_index']]=$index['Column_name'];
                $indexes[$index['Key_name']]['isuniq']=($index['Non_unique']==1)?0:1;
            }
        }

        return $indexes;
    }

    public function generatePure($tableName){
        $query=Yii::$app->db->createCommand('SHOW CREATE TABLE '.$tableName)->queryOne();
        return isset($query['Create Table'])?:'';
        /**
         * @TODO
        **/
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
        $tables=$this->prepareTables();

        if (empty($tables)) {
            $this->addError('tableName', "Table '{$this->tableName}' does not exist, or all tables was ignored");
            return false;
        }
        return true;
    }

    /**
     * @return array the table names that match the pattern specified by [[tableName]].
     */
    public function prepareIgnored()
    {
        $ignors = [];
        if ($this->tableIgnore) {
            if (strpos($this->tableIgnore, ',') !== false) {
                $ignors = explode(',', $this->tableIgnore);
            } else {
                $ignors[] = $this->tableIgnore;
            }
        }
        if (!empty($ignors)) {
            foreach ($ignors as $ignoredTable) {
                $prepared = $this->prepareTableName($ignoredTable);
                if (!empty($prepared)) {
                    $this->_ignoredTables=array_merge($this->_ignoredTables, $prepared);
                }
            }
        }
        return $this->_ignoredTables;
    }

    public function prepareTableName($tableName)
    {
        $prepared = [];
        $tableName=trim($tableName);
        $db = $this->getDbConnection();
        if ($db === null) {
            return $prepared;
        }
        if ($tableName == '*') {
            foreach ($db->schema->getTableNames() as $table) {
                $prepared[] = $table;
            }
        } elseif (strpos($tableName, '*') !== false) {
            $schema = '';
            $pattern = '/^' . str_replace('*', '\w+', $tableName) . '$/';

            foreach ($db->schema->getTableNames($schema) as $table) {
                if (preg_match($pattern, $table)) {
                    $prepared[] = $table;
                }
            }
        } elseif (($table = $db->getTableSchema($tableName, true)) !== null) {
            $prepared[] = $tableName;
        }
        return $prepared;
    }


    /**
     * @return array the table names that match the pattern specified by [[tableName]].
     */
    public function prepareTables()
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
        if (!empty($tables)) {
            foreach ($tables as $goodTable) {
                $prepared = $this->prepareTableName($goodTable);
                if (!empty($prepared)) {
                    $this->_tables=array_merge($this->_tables, $prepared);
                }
            }
            foreach($this->_tables as $i=>$t){
                if(in_array($t, $this->_ignoredTables)){
                    unset($this->_tables[$i]);
                }
            }
        }

        return $this->_tables;
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