<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\form\Generator $generator
 */

echo $form->field($generator, 'tableName');
echo $form->field($generator, 'onlyColumns');
echo $form->field($generator, 'exceptColumns');
echo $form->field($generator, 'db');
echo $form->field($generator, 'migrationPath');
echo $form->field($generator, 'usePrefix')->checkbox();
echo $form->field($generator, 'insertMode')
    ->dropDownList([
        \insolita\migrik\gii\DataGenerator::MODE_QUERY=>'as batchInsert',
        \insolita\migrik\gii\DataGenerator::MODE_MODEL=>'as Model instances'
                   ]);
echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'prefix');

