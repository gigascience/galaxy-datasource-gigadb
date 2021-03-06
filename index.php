<?php

$here = dirname(__FILE__);
$yii = $here . '/../yii/framework/yii.php';
$config = $here . '/protected/config/main.php';

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

date_default_timezone_set('Asia/Hong_Kong'); 

require_once($yii);
Yii::createWebApplication($config)->run();
