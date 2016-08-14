<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/** @var $migrationName string the new migration class name
 *  @var array                                  $tableList
 *  @var array                                  $tableRelations
 *  @var insolita\migrik\gii\StructureGenerator $generator
 *
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
        $transaction=$this->db->beginTransaction();
        try{
            <?php foreach ($tableList as $tableData) :?>
            $this->createTable('<?= ($generator->usePrefix)?$tableData['alias']:$tableData['name'] ?>',
               [
                    <?php foreach ($tableData['columns'] as $name => $data) :?>
                    '<?=$name?>'=> <?=$data;?>,
                    <?php endforeach;?>
               ], $tableOptions);
                <?php if (!empty($tableData['indexes']) && is_array($tableData['indexes'])) :?>
                    <?php foreach ($tableData['indexes'] as $name => $data) :?>
                            <?php if ($name!='PRIMARY') :?>
            $this->createIndex('<?=$name?>',
                            '<?=$tableData['alias']?>',
                            '<?=implode(",", array_values($data['cols']))?>',
                            <?=$data['isuniq']?>);
                            <?php endif;?>
                    <?php endforeach;?>
                <?php endif?>
            <?php endforeach;?>
            <?php if (!empty($tableRelations) && is_array($tableRelations)) :?>
                <?php foreach ($tableRelations as $table) :?>
                    <?php foreach ($table['fKeys'] as $i => $rel) :?>
            $this->addForeignKey('fk_<?=$table['tableName']?>_<?=$rel['pk']?>',
                        '<?=$table['tableAlias']?>',
                        '<?=$rel['pk']?>',
                        '<?=$rel['ftable']?>',
                        '<?=$rel['fk']?>');
                    <?php endforeach;?>
                <?php endforeach;?>
            <?php endif?>
            $transaction->commit();
        } catch (Exception $e) {
             echo 'Catch Exception '.$e->getMessage().' and rollBack this';
             $transaction->rollBack();
        }
    }

    public function safeDown()
    {
        $transaction=$this->db->beginTransaction();
        try{
            <?php if (!empty($tableRelations) && is_array($tableRelations)) :?>
                <?php foreach ($tableRelations as $table) :?>
                    <?php foreach ($table['fKeys'] as $i => $rel) :?>
            $this->dropForeignKey('fk_<?=$table['tableName']?>_<?=$rel['pk']?>', '<?=$table['tableAlias']?>');
                    <?php endforeach;?>
                <?php endforeach;?>
            <?php endif?>
            <?php foreach ($tableList as $tableData) :?>
            $this->dropTable('<?= ($generator->usePrefix)?$tableData['alias']:$tableData['name']?>');
            <?php endforeach;?>
            $transaction->commit();
        } catch (Exception $e) {
            echo 'Catch Exception '.$e->getMessage().' and rollBack this';
            $transaction->rollBack();
        }
    }
}
