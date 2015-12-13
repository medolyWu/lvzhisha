<?php
/**
 * Created by PhpStorm.
 * User: ADSL
 * Date: 2015/8/26
 * Time: 14:03
 */
namespace Web\Widget;

use Think\AuthController;
use Web\Common\ACPopedom;

class NavWidget extends AuthController{

    public function test(){
       $this->assign('userinfo',ACPopedom::getUserInfo());
       $this->display('Public:nav');
    }
    public function Apitest(){
        echo 1111;
    }
}

