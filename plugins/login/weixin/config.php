<?php

//回调地址：http://域名/index.php/Home/LoginApi/callback/oauth/weixin
return array(
    'code'=> 'weixin',
    'name' => '微信登陆',
    'version' => '1.0',
    'author' => 'ybcms',
    'desc' => '微信登陆插件 ',
    'icon' => 'logo.png',
    'config' => array(
        array('name' => 'app_id','label'=>'app_id','type' => 'text',   'value' => ''),
        array('name' => 'app_secret','label'=>'app_key','type' => 'text',   'value' => '')
    )
);