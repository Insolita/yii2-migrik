<?php
/**
 * Created by solly [13.08.16 13:39]
 */

namespace insolita\migrik\gii;

use Yii;
use yii\db\Connection;
use yii\base\UserException;

/**
 * Class GeneratorTrait
 *
 * @package insolita\migrik\gii
 */
trait GeneratorTrait
{

    /**
     * @var string - Primary prefix for migrations filenames
     */
    public $prefix;

    /**
     * @var string = internal prefix iterator
     */
    protected $nextPrefix;

    /**
     * @return Connection the DB connection as specified by [[db]].
     */
    protected function getDbConnection()
    {
        return Yii::$app->{$this->db};
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
     * Get yii-like table alias
     * @param string $tableCaption
     * @return string
     **/
    public function getTableAlias($tableCaption)
    {
        return '{{%' . $tableCaption . '}}';
    }

    /**
     * Get Table name without prefix
     *
     * @param string $tableName
     *
     * @return string
     **/
    public function getTableCaption($tableName)
    {
        $prefix = $this->getDbConnection()->tablePrefix;
        if($prefix && mb_strpos($tableName, $prefix)===0){
            return mb_substr($tableName, mb_strlen($prefix));
        }
        return $tableName;
    }

    /**
     * Generate next migration prefix
     *
     * @param string $tableName
     *
     * @return string
     **/
    protected function createNextPrefix($prefix)
    {
        try {
            $uPrefix = preg_replace('~[^0-9]~', '', $prefix);
            $uPrefix = \DateTime::createFromFormat('ymdHis', $uPrefix)->add(new \DateInterval('PT1S'))->format(
                    'ymd_His'
                );
            return 'm' . $uPrefix;
        }catch(\Exception $e)
        {
            \Yii::error($e->geMessage.' '.$e->getTraceAsString(), __METHOD__);
            throw new UserException('Incorrect prefix format; Use only yii2-migration compatible m[ymd_His]');
        }
    }

    /**
     *
     */
    protected function refreshNextPrefix()
    {
        $this->nextPrefix = $this->createNextPrefix($this->nextPrefix);
    }

    /**
     *
     */
    protected function refreshPrefix()
    {
        $this->prefix = 'm' . gmdate('ymd_His');
        $this->nextPrefix = $this->createNextPrefix($this->prefix);
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
     * @inheritdoc
     */
    public function afterValidate()
    {
        parent::afterValidate();
        $this->nextPrefix = $this->createNextPrefix($this->prefix);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!$this->prefix) {
            $this->prefix = 'm' . gmdate('ymd_His');
            $this->nextPrefix = $this->createNextPrefix($this->prefix);
        }
    }
}
