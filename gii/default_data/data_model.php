<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 *  @var $migrationName string the new migration class name
 *  @var insolita\migrik\gii\DataGenerator $generator
 */

echo "<?php\n";
?>

use yii\db\Exception;
use yii\db\Migration;
use yii\helpers\VarDumper;
use yii\helpers\Console;
use \yii\base\Event;
use <?=$generator->modelClass?>;

class <?= $migrationName ?> extends Migration
{
    public function init()
    {
        $this->db = 'db';
        parent::init();
    }

    public function safeUp()
    {
        /**
        Uncomment this block for detach model behaviors
        Event::on(<?=$generator->modelBasename?>::className(), <?=$generator->modelBasename?>::EVENT_INIT,
                 function(Event $event ){
                     $event->sender->detachBehavior('someBehaviorName');
        });
        **/
<?php foreach($generator->rawData as $attributes):?>
        $model = new <?=$generator->modelBasename?>();
        $model->setAttributes(
            <?=\yii\helpers\VarDumper::export($attributes)?>,
        false);
        if(!$model->save()){
            $this->stderr('Fail save model with attributes '
                .VarDumper::dumpAsString($model->getAttributes()).' with errors '
                .VarDumper::dumpAsString($model->getErrors()));
                throw new Exception('Fail save $model');
        }
<?php endforeach;?>
    }

    public function safeDown()
    {
        //$this->truncateTable('<?= ($generator->usePrefix)?$generator->tableAlias:$generator->tableName ?> CASCADE');
        //<?=$generator->modelBasename?>::deleteAll([]);
    }

    protected function stderr($message)
    {
        Console::output(Console::ansiFormat($message, [Console::FG_PURPLE]));
    }
}
