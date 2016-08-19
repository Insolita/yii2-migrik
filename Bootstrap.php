<?php

namespace insolita\migrik;

use insolita\migrik\contracts\IMigrationTableResolver;

use insolita\migrik\contracts\IModelResolver;
use insolita\migrik\contracts\IPhpdocResolver;
use insolita\migrik\resolver\ModelResolver;
use insolita\migrik\resolver\PhpDocResolver;
use insolita\migrik\resolver\TableResolver;
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
            \Yii::$container->set(IMigrationTableResolver::class, TableResolver::class);
            \Yii::$container->set(IPhpdocResolver::class, PhpDocResolver::class);
            \Yii::$container->set(IModelResolver::class, ModelResolver::class);
        }
    }
}
