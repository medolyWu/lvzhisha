<?php
/**
 * Created by PhpStorm.
 * User: ADSL
 * Date: 2015/9/1
 * Time: 10:57
 */
namespace WeChat\Controller;

use Org\Net\Http;
use Think\Controller;
use Think\WeiXinAuthController;
use Tools\Wechat;
use WeChat\Common\ACPopedom;

class ToolController extends WeiXinAuthController{
//class ToolController extends Controller{

    public function __construct(){
        parent::__construct();
       /* if(!isWeiXinBrowser()){
            $this->assign('tips','请在微信浏览器中打开');
            $this->display('WeiXin:result');
            exit;
        }*/
    }

    /**
     * 安全中心首页
     */
    public function index(){
        $sign = ACPopedom::getWechatSign();
        $this->assign('wxSdk',$sign);
        $this->assign('userinfo',array('nickname'=>ACPopedom::getNickname(),'avator'=>ACPopedom::getAvator()));
        $this->display();
    }

    /**
     * 更新地理位置
     */
    public function getLocation(){
        $longitude  = I('get.longitude');
        $latitude = I('get.latitude');
        $baiduLongLat  = json_decode(Http::CurlRequst('http://api.map.baidu.com/geoconv/v1/',array('coords'=>$longitude.','.$latitude,'from'=>3,'to'=>5,'ak'=>'mcFKx7aa0WB73SAW6b2IYQAP'),'get'),true);
        if($baiduLongLat['status'] !== 0){
            $this->ajaxReturn(array('status'=>false,'msg'=>'获取地址失败1'));
        }
        $location = json_decode(Http::CurlRequst('http://api.map.baidu.com/geocoder/v2/',array('location'=>$baiduLongLat['result'][0]['x'].','.$baiduLongLat['result'][0]['y'],'output'=>'json','pois'=>0,'ak'=>'mcFKx7aa0WB73SAW6b2IYQAP'),'get'),true);
        if($location['status'] !== 0){
            $this->ajaxReturn(array('status'=>false,'msg'=>'获取地址失败2'));
        }
        //存放坐标
        $res = M('Users')->where('userid = '.ACPopedom::getID())->save(array('longitude'=>$baiduLongLat['result'][0]['x'],'latitude'=>$baiduLongLat['result'][0]['y']));
        if(!$res){
            $this->ajaxReturn(array('status'=>false,'msg'=>'保存地址失败'));
        }
        $this->ajaxReturn(array('status'=>true,'msg'=>'地理位置已更新','address'=>$location['result']['formatted_address'],'lng'=>$baiduLongLat['result'][0]['x'],'lat'=>$baiduLongLat['result'][0]['y']));
    }
}