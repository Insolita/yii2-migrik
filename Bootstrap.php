<?php

namespace insolita\migrik;

use yii\base\Application;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if ($app->hasModule('gii')) {
            if (!isset($app->getModule('gii')->generators['migrik'])) {
                $app->getModule('gii')->generators['migrik'] = 'insolita\migrik\gii\StructureGenerator';
                $app->getModule('gii')->generators['migrikdata'] = 'insolita\migrik\gii\DataGenerator';
                $app->getModule('gii')->generators['migrikdoc'] = 'insolita\migrik\gii\ByModelGenerator';
            }
            \Yii::$container->set('insolita\migrik\contracts\IMigrationTableResolver', 'insolita\migrik\resolver\TableResolver');
            \Yii::$container->set('insolita\migrik\contracts\IPhpdocResolver', 'insolita\migrik\resolver\PhpDocResolver');
            \Yii::$container->set('insolita\migrik\contracts\IModelResolver', 'insolita\migrik\resolver\ModelResolver');
        }
    }
}
