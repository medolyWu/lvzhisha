<?php
/**
 * Created by PhpStorm.
 * User: ADSL
 * Date: 2015/8/25
 * Time: 17:16
 */
namespace Web\Controller;
use Think\AuthController;
use Think\Image;
use Think\Upload\Driver\Upyun;
use Web\Common\ACPopedom;

class SettingController extends AuthController{

    /**
     * 上传头像
     */
    public function uploadAvator(){
        $result = UploadFile('',$rootPath="/Attachment/",$savePath="/upload/",$files='',$maxSize=2048000);
        exit(json_encode(array("success"=>$result['picurl'])));
    }

    /**
     * 修改截取后的头像
     */
    public function ModifyAvator(){
        $ShearPhoto["config"]=array(
            "proportional"=>0,
        );
        import('Vendor.ShearPhoto.ShearPhoto');
        $Shear =new \ShearPhoto(ACPopedom::mixPass(ACPopedom::getID()));
        $tmp_name = $Shear->run(json_decode(trim(stripslashes($_POST["JSdate"])),true),$ShearPhoto["config"]);//传入参数运行
        if(!$tmp_name) $this->ajaxReturn(array('erro'=>'头像保存失败'));
        $filename = $Shear->filename.$Shear->imagesuffix;
        //又拍云上传
        import('Vendor.Upyun.UpYunApi');
        $upYunApi = new \UpYunApi(UPYUN_BUCKET,UPYUN_USERNAME,UPYUN_PASSWORD);
        $upYunApi->debug = false;
        $upYunApi->setApiDomain(UPYUN_HOST);
        $fh = fopen($tmp_name,'rb');
        $rsp = $upYunApi->writeFile('/Attachment/face/'.$filename, $fh,true);
        if(file_exists(ini_get("upload_tmp_dir").DIRECTORY_SEPARATOR . $Shear->filename)){
            unlink(ini_get("upload_tmp_dir").DIRECTORY_SEPARATOR . $filename);
        }
        fclose($fh);
        if($rsp){
            $result = M('Users')->where('userid = '.ACPopedom::getID())->save(array('avator'=>UPYUN_BASIC_URL.'/Attachment/face/'.$filename));
            if($result !== false){
                $userinfo = ACPopedom::getUserInfo();
                cookie("__info__",authcode(serialize(array('nickname'=>$userinfo['nickname'],'userid'=>$userinfo['userid'],'avator'=>UPYUN_BASIC_URL.'/Attachment/face/'.$filename)),"ENCODE", SESSION_AUTH, C('COOKIE_EXPIRE')));
                $this->ajaxReturn(array('success'=>'截图成功！如不能立即显示,请多刷新两次','url'=>U('Web/UserCenter/index')));
            }else{
                $this->ajaxReturn(array('erro'=>'头像更新失败'));
            }

        }else{
            $this->ajaxReturn(array('erro'=>'头像更新失败'));
        }
    }

    /**
     *
     * 退出登陆
     */
    public function logout(){
        ACPopedom::logout();
        $this->redirect("Web/Login/index");
    }
}