<?php
// 应用入口文件

// 检测PHP环境
/*require(dirname(__FILE__).'/ThinkPHP/Library/Vendor/WeiXin/Wechat.class.php');
//import('Vendor.WeiXin.Wechat');
$wechat = new \Tools\Wechat(array('token'=>'lvzhisha','appid'=>'wx57f2001c4abf318d','secret'=>'4b6f0dd5fa9c5504ec96af66e4a81437','debug'=>true));
$wechat->valid();exit;*/
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);
// 定义应用目录
define('APP_PATH','./Application/');

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单
