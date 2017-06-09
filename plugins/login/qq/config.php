<?php
//申请地址：https://connect.qq.com/index.html
//回调地址：http://域名/index.php/home/login_api/callback/oauth/qq
return array(
    'code'=> 'qq',
    'name' => 'QQ登陆',
    'version' => '1.0',
    'author' => 'ybcms',
    'desc' => 'QQ登陆插件 ',
    'icon' => 'logo.jpg',
    'config' => array(
        array('name' => 'app_id','label'=>'app_id','type' => 'text',   'value' => ''),
        array('name' => 'app_secret','label'=>'app_key','type' => 'text',   'value' => '')
    )
);