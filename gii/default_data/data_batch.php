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

    public function init()
    {
        $this->db = 'db';
        parent::init();
    }

    public function safeUp()
    {
        $this->batchInsert('<?= ($generator->usePrefix)?$generator->tableAlias:$generator->tableName ?>',
                           ["<?= implode('", "', $generator->tableColumns) ?>"],
                            <?= \yii\helpers\VarDumper::export($generator->rawData) ?>

        );
    }

    public function safeDown()
    {
        //$this->truncateTable('<?= ($generator->usePrefix)?$generator->tableAlias:$generator->tableName ?> CASCADE');
    }
}
