Ybmall 4.0.6
===============

[官网地址](https://www.ybcms.com/)
[ThinkPHP5 Downloads](https://github.com/top-think/think/releases/latest)

Ybmall开源商城，完全开源 （100%开源）， 采用ThinkPHP5.0 + MySQL 开发语言，完全面向对象的技术架构设计开发。适合企业及个人 二次开发非常方便，代码清晰简洁,通俗易懂，操作简单,安全稳定,是广大用户二次开发的最佳选择。

## 核心功能模块
 + 独立的管理筐架（支持随意扩展）
 + 权限管理模块
 + 数据库管理与备份模块
 + 强大的文章CMS管理机制
 + 灵活的会员管理
 + 易扩展的插件与模块管理
 + 多地区物流管理模块
 + 支付管理模块（微信、支付宝）
 + 微信管理模块（粉丝、菜单、回复、群发、图文）
 + 商品管理模块
 + 促销管理模块
 + 售后管理模块
 + 分销管理模块
 + 广告管理模块
 + 评论管理模块
 + 友情链接模块
 + 在线资询与留言模块
 + 短信管理模块
 + 附件管理模块
 + 地区管理模块

> 具体参照ThinkPHP5详细开发文档参考 [ThinkPHP5完全开发手册](http://www.kancloud.cn/manual/thinkphp5)

## 目录结构

初始的目录结构如下：

~~~
www  WEB部署目录（或者子目录）
├─api
├─application           应用目录
│  ├─admin
│  ├─common             公共模块目录（可以更改）
│  ├─index				主页模块目录
│  ├─mobile				移动模块目录
│  ├─command.php		应用公共文件
│  ├─common.php         公共函数文件
│  ├─config.php         公共配置文件
│  ├─database.php       数据库配置文件
│  ├─function.php		商城公共函数文件
│  ├─function_form.php	表单公共函数文件
│  ├─function_user.php	用户公共函数文件
│  ├─helper.php			兼容TP3.2公共函数文件
│  ├─route.php          路由配置文件
│  ├─system.php			系统配置参数
│  ├─tags.php			应用行为扩展定义文件
│  └─wechat.php			微信公共函数文件
│
├─data					系统数据存储目录
├─extend                扩展类库目录
├─plugins				系统插件目录
├─runtime				应用的运行时目录（可写，可定制）
├─static				系统素材目录
├─thinkphp              框架系统目录
├─uploads				系统上传文件目录
├─vendor                第三方类库目录（Composer依赖库）
│
├─.gitignore			Git的配置
├─.htaccess				伪静态配置文件
├─.project				开发工具项目文件
├─build.php
├─composer.json         composer定义文件
├─composer.lock
├─CONTRIBUTING.md
├─favicon.ico
├─index.php				入口文件
├─LICENSE.txt           授权说明文件
├─README.md             README文件
├─think                 命令行入口文件
~~~


## 系统支持要求

`linux`或`windows`系统均可；

环境要求：

PHP5.4版本以上，支持PHP7.0、MySql数据库版本5.0及以上
支持rewrite伪静态规则
支持php扩展：php_curl,php_gd2

## 参与开发
注册并登录 Github 帐号， fork 本项目并进行改动。

更多细节参阅 [CONTRIBUTING.md](CONTRIBUTING.md)

## 版权信息

Ybmall 遵循 Apache2 开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2010-2017 by Ybcms (http://www.ybcms.com)

All rights reserved。

更多细节参阅 [LICENSE.txt](LICENSE.txt)
