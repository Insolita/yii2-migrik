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
echo $form->field($generator, 'resolverClass');
echo $form->field($generator, 'format')->dropDownList(['fluent'=>'Fluent','raw'=>'Raw']);
echo $form->field($generator, 'genmode')->dropDownList(['single'=>'One file per table','bulk'=>'All in one file']);
echo $form->field($generator, 'prefix');
$relOpts = \yii\helpers\Html::tag('div',$form->field($generator, 'fkOnUpdate'));
$relOpts.= \yii\helpers\Html::tag('div',$form->field($generator, 'fkOnDelete'));
echo \yii\helpers\Html::tag('div',$relOpts,['style'=>'display:grid; grid-gap:30px; grid-template-columns: 1fr 1fr;']);
