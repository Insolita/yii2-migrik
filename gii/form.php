<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\form\Generator $generator
 */

echo $form->field($generator, 'tableName');
echo $form->field($generator, 'tableIgnore')->textInput(['value'=>'*migrat*']);
echo $form->field($generator, 'db');
echo $form->field($generator, 'migrationPath');
echo $form->field($generator, 'usePrefix')->checkbox();
echo $form->field($generator, 'tableOptions');
echo $form->field($generator, 'genmode')->dropDownList(['single'=>'One file per table','mass'=>'All in one file']);
