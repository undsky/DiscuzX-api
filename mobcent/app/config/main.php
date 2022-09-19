<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.

/*
 * 此文件为Yii的配置选项，请不要修改!!!
 * 如果你想按照你的需求修改配置项，请复制一份这个文件到相同目录，并且命名为my_main.php.
 * 然后就可以在my_main.php中，按照说明进行相应的配置.
 * 新建my_mobcent.php文件不会随插件发布更新,请自行维护好！
 * 
 */
$currentDir = dirname(__FILE__);

require_once($currentDir . '/main_include.php');

return array(
    'basePath'=> $currentDir.DIRECTORY_SEPARATOR.'..',
    'name'=>'Mobcent App',
    'runtimePath' => MOBCENT_RUNTIME_PATH,
    'defaultController' => 'index',
    'language' => 'zh_cn',
    'charset' => $discuzParams['globals']['charset'],
    'timeZone' => 'Asia/Shanghai',

    'preload'=>array(
        'log',
        'dbDz',
        'dbDzUc',
    ),

    // autoloading model and component classes
    'import'=>array(
        'application.models.*',
        'application.components.*',
        'application.components.db.*',
        'application.components.web.*',

        'application.modules.admin.models.*',
        
        'ext.mobcent.components.*',
        'ext.mobcent.components.db.*',
        'ext.mobcent.components.utils.*',
        'ext.mobcent.components.web.*',

        'ext.mobile_detect.*',
        'ext.qiniu.*',
        'ext.qiniu.http.*',
        'ext.qiniu.storage.*',
        'ext.qiniu.processing.*'
    ),

    'modules'=>array_merge(array('admin','api','plugs'),$pluginConfig),

    // application components
    'components'=>array(
        'user'=>array(
            // enable cookie-based authentication
            'allowAutoLogin'=>true,
        ),
        'session'=>array(
            'timeout'=>3600,
        ),
        // uncomment the following to enable URLs in path-format
        'urlManager'=>array(
            // 'urlFormat'=>'path',
            'urlFormat'=>'get',
            // 'urlFormat'=> isset($_GET['sdkVersion']) && ($_GET['sdkVersion'] > '1.0.0') ? 'get' : 'path',
            'rules'=>array(
                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ),
        ),

        'db' => $dbConfig['default'],
        'dbDz' => $dbConfig['discuz'],
        'dbDzUc' => $dbConfig['discuzUcenter'],

        'errorHandler'=>array(
            // use 'site/error' action to display errors
            'errorAction'=>'index/error',
        ),
        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                // array(
                //  'class'=>'CFileLogRoute',
                //  'levels'=>'error, warning',
                // ),
                // uncomment the following to show log messages on web pages

                // array(
                //  'class'=>'CWebLogRoute',
                // ),

                // array(
                //  'class' => 'CProfileLogRoute',
                // )
            ),
        ),
        'cache' => array(
            // file cache
            'class' => 'CFileCache',
            'cachePath' => MOBCENT_CACHE_PATH,

            // memcache
            // 'class' => 'CMemCache',
            // 'servers' => array(
            //  array(
            //      'host' => 'server1',
            //      'port' => 11211,
            //  ),
            // ),

            // redis
            // 'class' => 'CRedisCache',
            // 'hostname' => 'localhost',
            // 'port' => 6379,
            // 'database' => 0,
        ),
        'messages'=>array(
            'class' => 'CPhpMessageSource',
            'basePath' => dirname(__FILE__).'/../data/messages',
        ),
    ),

    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params'=>array(
        // this is used in contact page
        'adminEmail'=>'webmaster@example.com',

        'discuz' => $discuzParams,
        'mobcent' => $mobcentConfig,
        'qiniu' => $qiniuConfig,
        'msgurl' => getenv('SERVER_STATUS')=='DEV'?'http://test-smsapi.app.xiaoyun.com/GpSmsApi/smsApi/sendsms.do':'http://smsapi.app.xiaoyun.com/GpSmsApi/smsApi/sendsms.do',
    ),
);
