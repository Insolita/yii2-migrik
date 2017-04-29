<?php
/**
 * Created by solly [18.08.16 23:58]
 */

namespace insolita\migrik\tests\data;

use yii\db\ActiveRecord;

/**
 * @db (db2)
 * @table({{%somenew}})
 *
 * @property integer    $id         @column pk()|comment("Id")
 * @property string     $username   @column string(100)|notNull()|defaultValue("Vasya")
 * @property string     $email      @column string(200)|null()|defaultValue(null)
 * @property string     $password
 * @property string     $remember_token
 * @property string     $access_token
 * @property string     $created_at
 * @property string     $updated_at
 * @property string     $role
**/
class TestModel extends ActiveRecord
{
    public $verifyPassword;
    public $verifyCaptcha;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Логин',
            'email' => 'Email',
            'password' => 'Пароль',
            'verifyCaptcha' => 'Анти-бот',
            'remember_token' => 'Токен',
            'created_at' => 'Создан',
            'updated_at' => 'Изменен',
            'role' => 'Роль',
            'verifyPassword' => 'Повторите пароль'
        ];
    }

    public static function tableName()
    {
        return 'migrik_model';
    }
}