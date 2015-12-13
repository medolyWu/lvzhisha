<?php
return array(
    /* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  '55b6f5f9c1e2c.gz.cdb.myqcloud.com', // 服务器地址
    'DB_NAME'               =>  'lvzhisha',          // 数据库名
    'DB_USER'               =>  'cdb_outerroot',      // 用户名
    'DB_PWD'                =>  'lvzhisha2016',          // 密码
    'DB_PORT'               =>  '11556',        // 端口
    'DB_PREFIX'             =>  'lzs_',    // 数据库表前缀
    //'DB_DSN'				=>	'mysql:host=115.28.54.196;dbname=lifeq_new',
    /* redis设置 */
    //'DB_LOACL'=>array(
    //    'DB_TYPE'  => 'redis',
    //    'REDIS_HOST'=>'182.254.246.83',
    //    'REDIS_PORT'=>6379,
    //    'REDIS_AUTH'=>123456,
    //    'REDIS_DB_PREFIX'=>'',
    //),

    /* COOKIE 设置*/
    //'COOKIE_DOMAIN' => 'deteline.cn',
    'COOKIE_DOMAIN'=>'lvzhisha.com',
    'COOKIE_PATH'	=> '/',
    'COOKIE_PREFIX' => '',
    'COOKIE_EXPIRE' => 86400,
    /* 邮件时间设置*/
    'ACTIVATION_TIME'=>7200, //用户注册激活有效时间
    'AGAIN_SEND_TIME'=>60,  //再次发送邮件的时间

    /* 邮件发件人 设置*/
    'THINK_EMAIL' => array(
        'SMTP_HOST' => 'smtp.126.com', //SMTP服务器

        'SMTP_PORT' => '465', //SMTP服务器端口

        'SMTP_USER' => 'lvzhisha@126.com', //SMTP服务器用户名

        'SMTP_PASS' => 'clppdrswyiwwaoee', //SMTP服务器密码

        'FROM_EMAIL' => 'lvzhisha@126.com', //发件人EMAIL

        'FROM_NAME' => 'lvzhisha', //发件人名称

        'REPLY_EMAIL' => '', //回复EMAIL（留空则为发件人EMAIL）

        'REPLY_NAME' => '', //回复名称（留空则为发件人名称）
    ),
);
/*
* 设定：一些特定的SESSION
*/
define("SESSION_ID", "lvzhisha@id");
define("SESSION_HASH", SESSION_ID."_hash");
define("SESSION_AUTH", "lifeq@admin");
define("SESSION_TOKEN", "lvzhisha@token");
