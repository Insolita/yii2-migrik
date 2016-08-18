<?php
/**
 * Created by solly [19.08.16 0:25]
 */

namespace insolita\migrik\gii;

use yii\gii\CodeFile;
use yii\gii\Generator;

class ByModelGenerator extends Generator
{
    public $db = 'db';
    public $migrationPath = '@app/migrations';
    public $models;
    public $phpdocOnly = false;
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
                'models' => 'FQN model class or classes - one per line'
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
                [['db', 'models', 'migrationPath'], 'filter', 'filter' => 'trim'],
                [['db', 'models', 'migrationPath'], 'required'],
                [['db'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
                [['models'], 'validateModels', 'skipOnEmpty' => false],
                [['db'], 'validateDb'],
                ['migrationPath', 'safe'],
                [['phpdocOnly'], 'boolean'],
            ]
        );
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
        // TODO: Implement generate() method.
    }

    public function validateModels()
    {
        $models = explode($this->models, '\r\n');
        $models = array_map(
            function ($v) {
                return trim($v, '\t\n\r\0\xOB\,\\/');
            },
            $models
        );
        $models = array_filter($models, 'trim');
        if (empty($models)) {
            $this->addError($models, 'Require at least one model');
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