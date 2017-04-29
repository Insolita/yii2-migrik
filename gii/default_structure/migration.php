<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/** @var $migrationName string the new migration class name
 *  @var $tableAlias string table_name
 *  @var $tableName string table_name
 *  @var array $tableColumns
 *  @var array $tableIndexes
 *  @var array $tablePk
 *  @var insolita\migrik\gii\StructureGenerator $generator
 */

echo "<?php\n";
?>

use yii\db\Schema;
use yii\db\Migration;

class <?= $migrationName ?> extends Migration
{

    public function init()
    {
        $this->db = '<?=$generator->db?>';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = '<?=$generator->tableOptions?>';

        $this->createTable(
            '<?= ($generator->usePrefix)?$tableAlias:$tableName ?>',
            [
<?php foreach ($tableColumns as $name => $data) :?>
                '<?=$name?>'=> <?=$data;?>,
<?php endforeach;?>
            ],$tableOptions
        );
<?php if (!empty($tableIndexes) && is_array($tableIndexes)) : ?>
<?php foreach ($tableIndexes as $name => $data) :?>
<?php if ($name!='PRIMARY') : ?>
        $this->createIndex('<?=$name?>','<?=$tableAlias?>',['<?=implode("','", array_values($data['cols']))?>'],<?=$data['isuniq']?'true':'false'?>);
<?php endif;?>
<?php endforeach;?>
<?php endif?>
<?php if (!empty($tablePk)) : ?>
        $this->addPrimaryKey('pk_on_<?=$tableName?>','<?=$tableAlias?>',['<?=implode("','", $tablePk)?>']);
<?php endif?>

    }

    public function safeDown()
    {
<?php if (!empty($tablePk)) : ?>
    $this->dropPrimaryKey('pk_on_<?=$tableName?>','<?=$tableAlias?>');
<?php endif?>
<?php if (!empty($tableIndexes) && is_array($tableIndexes)) : ?>
<?php foreach ($tableIndexes as $name => $data) :?>
<?php if ($name!='PRIMARY') : ?>
        $this->dropIndex('<?=$name?>', '<?=$tableAlias?>');
<?php endif;?>
<?php endforeach;?>
<?php endif?>
        $this->dropTable('<?= ($generator->usePrefix)?$tableAlias:$tableName ?>');
    }
}
