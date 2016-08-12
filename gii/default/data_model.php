<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 *  @var $migrationName string the new migration class name
 *  @var $tableAlias string table_name
 *  @var $tableName string table_name
 *  @var $modelClass string
 *  @var array $columnMap
 *  @var insolita\migrik\gii\DataGenerator $generator
 */

echo "<?php\n";
?>

use yii\db\Schema;
use yii\db\Exception;
use yii\db\Migration;
use yii\helpers\VarDumper;

class <?= $migrationName ?> extends Migration
{
    public function safeUp()
    {
          <?php foreach($columnMap as $attributes):?>
          $model = new <?=\yii\helpers\StringHelper::basename($modelClass)?>();
          $attributes=<?=\yii\helpers\VarDumper::export($attributes)?>;
          $model->setAttributes($attributes);
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
        $this->dropTable('<?= ($generator->usePrefix)?$tableAlias:$tableName ?>');
    }
}
