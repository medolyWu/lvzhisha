<?php
/*
 * 资源路径
*/
defined('SITE_URL') 			or define('SITE_URL', 'http://test.lvzhisha.com');
//defined('SITE_URL') 			or define('SITE_URL', 'http://m.lvzhisha.com');
defined('SITE_STYLE_URL') 		or define('SITE_STYLE_URL', SITE_URL.'/Public/style');
defined('SITE_JAVASCRIPT_URL') 	or define('SITE_JAVASCRIPT_URL', SITE_URL.'/Public/js');
defined('SITE_IMAGE_URL') 		or define('SITE_IMAGE_URL', SITE_URL.'/Public/img');
defined('SITE_IMAGE_URL') 		or define('SITE_FONT_URL', SITE_URL.'/Public/font');
defined('SITE_PLUGIN_URL') 		or define('SITE_PLUGIN_URL', SITE_URL.'/Public/plugin');
/*
 * 微博登陆配置 authorization_code
*/
define( "WB_APPKEY" , '473662427' );
define( "WB_SKEY" , '7fa8fbb4e91c881556ba9cb15fabced6' );
define( "WB_CALLBACK_URL" , SITE_URL.'/index.php/Web/Login/weibo' );
define( "WB_REDIRECT_URI" , SITE_URL );
define( "WB_GRANT_TYPE" , 'authorization_code' );

//又拍云设置
defined("FILE_UPLOAD_TYPE") 		or define("FILE_UPLOAD_TYPE", "Upyun");
defined("UPYUN_HOST") 				or define("UPYUN_HOST", "v0.api.upyun.com");
defined("UPYUN_USERNAME") 			or define("UPYUN_USERNAME", "lvzhisha");
defined("UPYUN_PASSWORD") 			or define("UPYUN_PASSWORD", "lvzhisha2016");
defined("UPYUN_BUCKET") 			or define("UPYUN_BUCKET", "lvzhishaimage");
defined("UPYUN_TIMEOUT") 			or define("UPYUN_TIMEOUT", 90);
defined("UPYUN_URL") 				or define("UPYUN_URL", "http://lvzhishaimage.b0.upaiyun.com/Attachment");
defined("UPYUN_BASIC_URL") 			or define("UPYUN_BASIC_URL", "http://lvzhishaimage.b0.upaiyun.com");
return array(
    'DB_LOACL'=>array(
        'DB_TYPE'  => 'redis',
        'REDIS_HOST'=>'182.254.246.83',
        //'REDIS_HOST'=>'127.0.0.1',
        'REDIS_PORT'=>6379,
        'REDIS_AUTH'=>123456,
        'REDIS_DB_PREFIX'=>'',
    ),
    /* 动态类型*/
    'FEED_TYPE' => array(
        'TEXT' => 1, //单文字
        'IMAGE' => 2, //单图片
        'TEXT_IMAGE' => 3, //图文
        'VIDEO' => 4, //单视频
        'TEXT_VIDEO' => 5, //视频文字
    ),
    /* 产生动态action类型*/
   'FEED_ACTION_SEND'=>'更新了动态',
   'FEED_ACTION_AVATOR'=>'更新了头像',
    /* 产生动态设备*/
   'FEED_APPID_PC'=>'来自网站',
   'FEED_APPID_MOBILE'=>'来自移动设备',

    /*微信公众号设置*/
    "EXPIRE_TIME"           => 259200,
    "TOKEN"                  =>  "lvzhisha",
    "AUTH_APPID"            =>  "wx57f2001c4abf318d",
    "AUTH_APPSECRET"        =>  "4b6f0dd5fa9c5504ec96af66e4a81437",
     "AUTH_STATUS"           =>  'lvzhishaauth'
);
