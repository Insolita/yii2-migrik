<?php
/**
 * Created by solly [13.08.16 13:39]
 */

namespace insolita\migrik\gii;

use Yii;
use yii\db\Connection;

trait GeneratorTrait
{
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
        return str_replace($this->getDbConnection()->tablePrefix, '', strtolower($tableName));
    }
}