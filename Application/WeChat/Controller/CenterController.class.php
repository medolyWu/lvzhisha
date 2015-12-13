<?php
/**
 * Created by PhpStorm.
 * User: ADSL
 * Date: 2015/9/6
 * Time: 10:15
 */
namespace WeChat\Controller;

use Think\Controller;
use Think\WeiXinAuthController;
use WeChat\Common\ACPopedom;

class CenterController extends  Controller{

    public function __construct(){
        parent::__construct();
    }

    public function mobileIndex(){
        $aa = 13324;
        $id = $_GET['id'];
        $this->display('index5');exit;
        /*$sign = ACPopedom::getWechatSign();
        $this->assign('wxSdk',$sign);
        $this->display();*/
    }
    public function test(){
        $sign = ACPopedom::getWechatSign();
        $this->assign('wxSdk',$sign);
        $this->display('aside');
    }
}
