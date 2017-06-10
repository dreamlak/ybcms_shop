<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
header("Content-type: text/html; charset=utf-8");//正式使用时可清除
// 自写义常量
define('API_PATH', __DIR__ . '/API/');
define('DATA_PATH', __DIR__ . '/data/');
define('IMG_PATH', __DIR__ . '/static/images/');
define('JS_PATH', __DIR__ . '/static/js/');
define('CSS_PATH', __DIR__ . '/static/css/');
define('PLUGIN_PATH', __DIR__ . '/plugins/');
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('CACHE_TIME',31104000); // 缓存时间  31104000
define('SITE_URL','http://'.$_SERVER['HTTP_HOST']); // 网站域名
// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';
