<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/13
 * Time: 21:30
 */
namespace Web\Controller;
use Web\Common\ACPopedom;
use Think\AuthController;

class UploadController extends AuthController{

    /**
     *上传活动封面图
     *
     */
    public function upload(){
        $userinfo = ACPopedom::getUserInfo();
        $subName = substr(md5($userinfo['userid']),-1);
        $result = UploadFile($subName,"/Attachment/",'upload/');
        $this->ajaxReturn($result);
    }
}