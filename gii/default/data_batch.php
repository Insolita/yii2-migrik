<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/** @var $migrationName string the new migration class name
 *  @var insolita\migrik\gii\DataGenerator $generator
 */

echo "<?php\n";
?>

use yii\db\Schema;
use yii\db\Migration;

class <?= $migrationName ?> extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('<?= ($generator->usePrefix)?$generator->tableAlias:$generator->tableName ?>',
                            <?= \yii\helpers\VarDumper::export($generator->tableColumns) ?>,
                            <?= \yii\helpers\VarDumper::export($generator->rawData) ?>,
         );
    }

    public function safeDown()
    {
        //$this->dropTable('<?= ($generator->usePrefix)?$generator->tableAlias:$generator->tableName ?>');
    }
}
