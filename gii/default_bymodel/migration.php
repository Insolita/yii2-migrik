<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/** @var $migrationName string the new migration class name
 *  @var $table string table_name
 *  @var array $columns
 *  @var string $db
 *  @var string $tableOptions
 */

echo "<?php\n";
?>

use yii\db\Schema;
use yii\db\Migration;

class <?= $migrationName ?> extends Migration
{

    public function init()
    {
        $this->db = '<?=$db?>';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = '<?=$tableOptions?>';

        $this->createTable(
            '<?= $table?>',
            [
<?php foreach ($columns as $name => $data) :?>
                '<?=$name?>'=> <?=$data;?>,
<?php endforeach;?>
            ],$tableOptions
        );
    }

    public function safeDown()
    {
        $this->dropTable('<?= $table?>');
    }
}
