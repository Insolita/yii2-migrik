<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('YII_TEST_ENTRY_URL') or define('YII_TEST_ENTRY_URL', \Codeception\Configuration::config()['config']['test_entry_url']);
defined('YII_TEST_ENTRY_FILE') or define('YII_TEST_ENTRY_FILE', dirname(dirname(__DIR__)) . '/web/index-test.php');

require_once(__DIR__ . '/../../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../../vendor/yiisoft/yii2/Yii.php');

\Yii::setAlias('@tests', __DIR__);
