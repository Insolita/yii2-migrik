<?php
/**
 * @var yii\web\View                      $this
 * @var yii\widgets\ActiveForm            $form
 * @var yii\gii\generators\form\Generator $generator
 */

echo "Yoy can add annotations in your model[not necessary AR or yii\\base\\Model or Object or stdClass]<br/>";
echo "<code>@db (db2)</code> - specify connection id required for migration 'db' - by default<br/>";
echo "<code>@table ({{%my_table}})</code> - specify table for migration<br/>";
echo "Supported column annotations: <br/>";
echo "As separate annotation above class  or above current variable <br/>
<code>@column (name) string|notNull|default('SomeValue')</code><br/>";
echo 'As addition to @property or @var definition <br/><code>
 @var string $created @column datetime(0)|notNull|expr(NOW())
</code><br/>
<code>
@property integer    $id         @column pk()|comment("Id")
</code><br/>
See more documentation on <a href="https://github.com/Insolita/yii2-migrik" target="_blank">GitHub</a>
';

echo $form->field($generator, 'models')->textarea();
echo $form->field($generator, 'db');
echo $form->field($generator, 'migrationPath');
echo $form->field($generator, 'phpdocOnly')->checkbox();
echo $form->field($generator, 'tableOptions');
echo $form->field($generator, 'prefix');
