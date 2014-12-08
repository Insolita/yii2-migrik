<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 08.12.14
 * Time: 7:24
 */
namespace insolita\migrik\cmd;


use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\db\Connection;

class MigrikController extends Controller
{
    /**
     * The name of the dummy migration that marks the beginning of the whole migration history.
     */
    const BASE_MIGRATION = 'm000000_000000_base';

    /**
     * @var Connection|string the DB connection object or the application
     * component ID of the DB connection.
     */
    public $db = 'db';

    public $migrationPath = '@app/migrations';

    public $templateFile='@vendor/insolita/yii2-migration-generator/tpl/migration.php';

    /**
     * @var bool whether to generate and overwrite all files
     */
    public $overwrite = false;

    /**
     * @var array table names for generating migrations
     */
    public $tables = [];

    public $tablePrefix = '';

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            [
                'db',
                'migrationPath',
                'templateFile',
                'overwrite',
                'tables',
                'tablePrefix'
            ]
        );
    }

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $path = Yii::getAlias($this->migrationPath);
            if (!is_dir($path)) {
                FileHelper::createDirectory($path);
            }
            $this->migrationPath = $path;

            if (is_string($this->db)) {
                $this->db = Yii::$app->get($this->db);
            }
            if (!$this->db instanceof Connection) {
                throw new Exception("The 'db' option must refer to the application component ID of a DB connection.");
            }



            $version = Yii::getVersion();
            $this->stdout("Insolita Migration Generator (based on Yii v{$version})\n\n");

            return true;
        } else {
            return false;
        }
    }

    public function actionIndex(){

    }

    /**
     * Creates a new migration.
     *
     * This command creates a new migration using the available migration template.
     * After using this command, developers should modify the created migration
     * skeleton by filling up the actual migration logic.
     *
     * ~~~
     * yii migrate/create create_user_table
     * ~~~
     *
     * @param string $name the name of the new migration. This should only contain
     * letters, digits and/or underscores.
     * @throws Exception if the name argument is invalid.
     */
    public function generate($name)
    {
        $name = 'm' . gmdate('ymd_His') . '_' . $name;
        $file = $this->migrationPath . DIRECTORY_SEPARATOR . $name . '.php';

        if ($this->confirm("Create new migration '$file'?")) {
            $content = $this->renderFile(Yii::getAlias($this->templateFile), ['className' => $name]);
            file_put_contents($file, $content);
            $this->stdout("New migration created successfully.\n", Console::FG_GREEN);
        }
    }
} 