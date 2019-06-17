<?php
/**
 * Created by solly [19.08.16 0:25]
 */

namespace insolita\migrik\gii;

use insolita\migrik\contracts\IModelResolver;
use insolita\migrik\contracts\IPhpdocResolver;
use yii\gii\CodeFile;
use yii\gii\Generator;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use Yii;

/**
 * Class ByModelGenerator
 *
 * @package insolita\migrik\gii
 */
class ByModelGenerator extends Generator
{
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
    public $models;
    /**
     * @var bool
     */
    public $phpdocOnly = false;

    /**
     * @var string
     */
    public $tableOptions = 'ENGINE=InnoDB';

    /**
     * @var array
     */
    protected $modelClasses = [];


    /**
     * @return string name of the code generator
     */
    public function getName()
    {
        return 'Model and PhpDoc migrations';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'Generate migration files by model properties and annotations';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'db' => ' Actual Database Connection ID for models',
                'migrationPath' => 'Migration Path',
                'phpdocOnly' => 'Only by phpdoc annotation',
                'models' => 'FQN model class or classes - one per line',
                'tableOptions' => 'Table Options',
                'prefix'        => 'Primary prefix for migrations filenames'
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
                'db' => 'Actual database connection for models[needed you can set via phpdoc @db]',
                'migrationPath' => 'Path for save migration file',
                'phpdocOnly' => 'If false, Information will be collected from the properties of the model - attributes, labels, 
                tableName and merged with phpdoc [Annotations have a higher priority!];
                 if true - only phpdoc info  will be used',
                'models' => 'Fully qualified model names like app\models\MyModel, one or more; each must start from 
                new line',
                'prefix' => 'For correct migration names; format: \'m\' . date(\'ymd_His\'); Don`t change it, if you not sure! '
            ]
        );
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
        return array_merge(
            parent::stickyAttributes(),
            ['db', 'migrationPath','prefix']
        );
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

        return dirname($class->getFileName()) . '/form_bymodel.php';
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

        return dirname($class->getFileName()) . '/default_bymodel';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['db', 'migrationPath'], 'filter', 'filter' => 'trim'],
                [['db', 'models', 'migrationPath'], 'required'],
                [['db'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
                [['models'], 'validateModels', 'skipOnEmpty' => false],
                [['db'], 'validateDb'],
                ['migrationPath', 'safe'],
                ['tableOptions', 'safe'],
                [['phpdocOnly'], 'boolean'],
                [['prefix'], 'string'],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function afterValidate()
    {
        parent::afterValidate();
        $this->nextPrefix = $this->createNextPrefix($this->prefix);
    }

    /**
     * Generates the code based on the current user input and the specified code template files.
     * This is the main method that child classes should implement.
     * Please refer to [[\yii\gii\generators\controller\Generator::generate()]] as an example
     * on how to implement this method.
     *
     * @return CodeFile[] a list of code files to be created.
     */
    public function generate()
    {
        $files = [];
        $i = 10;
        if (!empty($this->modelClasses)) {
            foreach ($this->modelClasses as $model) {
                $modelInfo = $this->phpdocOnly ? [] : $this->getInfoFromModel($model);
                $phpdocInfo = $this->getInfoFromPhpdoc($model);
                $migrationName = $this->prefix . '_'
                    . Inflector::tableize(StringHelper::basename($model));
                $params = [
                    'db' => $phpdocInfo['db'] ? $phpdocInfo['db'] : 'db',
                    'table' => $phpdocInfo['table']
                        ? $phpdocInfo['table'] : ($this->phpdocOnly ? '{{%'
                            . Inflector::tableize(StringHelper::basename($model)) . '}}'
                            : $modelInfo['table']),
                    'columns' => $this->prepareColumns($modelInfo, $phpdocInfo),
                    'migrationName' => $migrationName,
                    'tableOptions' => $this->tableOptions
                ];
                $files[] = new CodeFile(
                    Yii::getAlias($this->migrationPath) . '/' . $migrationName . '.php',
                    $this->render('migration.php', $params)
                );
                $i++;
            }
        }
        return $files;
    }

    /**
     * @param \yii\gii\CodeFile[] $files
     * @param array               $answers
     * @param string              $results
     *
     * @return bool
     */
    public function save($files, $answers, &$results)
    {
        $this->refreshPrefix();
        return parent::save($files, $answers, $results);
    }

    /**
     *
     */
    public function validateModels()
    {
        $models = preg_split('/\s/siu', $this->models);
        $models = array_map(
            function ($v) {
                return trim($v, "\t\n\r\0\xOB\,\\/");
            },
            $models
        );
        $models = array_filter($models, 'trim');
        if (empty($models)) {
            $this->addError('models', 'Require at least one model ');
        } else {
            $isvalid = true;
            foreach ($models as $model) {
                $isvalid = $this->validateModelClass($model);
                if (!$isvalid) {
                    break;
                }
            }
            if ($isvalid) {
                $this->modelClasses = $models;
            }
        }
    }

    /**
     * @param $modelInfo
     * @param $phpdocInfo
     *
     * @return array
     */
    protected function prepareColumns($modelInfo, $phpdocInfo)
    {
        $prepared = [];
        if (!empty($phpdocInfo['columns'])) {
            foreach ($phpdocInfo['columns'] as $name => $definition) {
                $prepared[$name] = $this->prepareColumnDefinition($definition);
            }
        }
        if (!$this->phpdocOnly && !empty($modelInfo['columns'])) {
            foreach ($modelInfo['columns'] as $column) {
                if (in_array($column, array_keys($prepared))) {
                    continue;
                }
                $lower = strtolower($column);
                if ($lower == 'id') {
                    $type = 'pk()';
                } elseif (strpos($lower, 'id') !== false) {
                    $type = 'integer()';
                } else {
                    $type = 'string()';
                }
                $comment = isset($modelInfo['labels'][$column]) ? $modelInfo['labels'][$column] : '';
                $prepared[$column] = implode('->', ['$this', $type, "comment('$comment')"]);
            }
        }
        return $prepared;
    }

    /**
     * @param string $definition
     *
     * @return string
     **/
    protected function prepareColumnDefinition($definition)
    {
        $arr = explode('|', $definition);
        $result = ['$this'];
        foreach ($arr as $el) {
            $el = trim($el);
            if (!StringHelper::endsWith($el, ')')) {
                $el .= '()';
            }
            if (StringHelper::startsWith($el, 'pk(')) {
                $el = str_replace('pk(', 'primaryKey(', $el);
            }
            if (StringHelper::startsWith($el, 'default(')) {
                $el = str_replace('default(', 'defaultValue(', $el);
            }
            if (StringHelper::startsWith($el, 'expr(')) {
                $el = str_replace('expr(', 'defaultExpression(', $el);
            }
            $result[] = $el;
        }
        unset($arr);
        return implode('->', $result);
    }

    /**
     * @param $model
     *
     * @return array
     */
    protected function getInfoFromModel($model)
    {
        /**
         * @var IModelResolver $modelResolver
         **/
        $modelResolver = \Yii::createObject(['class' => 'insolita\migrik\contracts\IModelResolver'], [$model]);
        $info = [
            'table' => $modelResolver->getTableName(),
            'columns' => $modelResolver->getAttributes(),
            'labels' => $modelResolver->getAttributeLabels()
        ];
        if ($info['table'] == false) {
            $info['table'] = Inflector::tableize(StringHelper::basename($model));
        }
        return $info;
    }

    /**
     * @param $model
     *
     * @return array
     */
    protected function getInfoFromPhpdoc($model)
    {
        /**
         * @var IPhpdocResolver $modelResolver
         **/
        $modelResolver = \Yii::createObject(['class' => 'insolita\migrik\contracts\IPhpdocResolver'], [$model]);
        $info = [
            'db' => $modelResolver->getConnectionName(),
            'table' => $modelResolver->getTableName(),
            'columns' => $modelResolver->getAttributes(),
        ];
        return $info;
    }

    /**
     * @param $modelClass
     *
     * @return bool
     */
    protected function validateModelClass($modelClass)
    {
        try {
            if (!class_exists($modelClass)) {
                $this->addError('models', "Class '$modelClass' does not exist or has syntax error.");
                return false;
            }
        } catch (\Exception $e) {
            $this->addError('models', "Class '$modelClass' does not exist or has syntax error.");
            return false;
        }
        return true;
    }


}