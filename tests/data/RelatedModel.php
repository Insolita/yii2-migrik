<?php
/**
 * Created by solly [18.08.16 23:58]
 */

namespace insolita\migrik\tests\data;

use solly\chat\models\Chatroom;
use solly\chat\models\Users;
use yii\db\ActiveRecord;

/**
 * @db (db2)
 * @table({{%somenew}})
 *
 * @property integer    $user_id     @fk {{%users}}|id|RESTRICT|NO ACTION
 * @property string     $room_id     @fk {{%chatroom}}|id|SET NULL|CASCADE
 * @property string     $color        @column string(20)|null()|defaultValue(null)
**/
class RelatedModel extends ActiveRecord
{

    public static function tableName()
    {
        return 'it_chat_access';
    }

    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'room_id' => 'Room ID',
            'color' => 'Цвет',
            ''
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoom()
    {
        return $this->hasOne(Chatroom::className(), ['id' => 'room_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(TestModel::className(), ['id' => 'user_id']);
    }
}