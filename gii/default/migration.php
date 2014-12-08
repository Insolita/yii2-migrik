<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/** @var $className string the new migration class name
 *  @var $tableAlias string table_name
 *  @var $tableColumns string
 *  @var $tableIndexes string
 */

echo "<?php\n";
?>

use yii\db\Schema;
use yii\db\Migration;

class <?= $className ?> extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        $this->createTable('<?= $tableAlias ?>', [
        <?= $tableColumns?>
        ], $tableOptions);

        <?= $tableIndexes?>
    }

    public function down()
    {
            $this->dropTable('<?= $tableAlias ?>');
    }
}
