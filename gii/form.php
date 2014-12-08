<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\form\Generator $generator
 */

echo $form->field($generator, 'tableName');
echo $form->field($generator, 'db');
echo $form->field($generator, 'migrationPath');
echo $form->field($generator, 'usePrefix')->checkbox();


