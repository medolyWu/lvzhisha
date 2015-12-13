<?php
/**
 * Created by PhpStorm.
 * User: ADSL
 * Date: 2015/8/26
 * Time: 15:13
 */
namespace WeChat\Controller;

use Think\Controller;
use WeChat\Common\ACPopedom;
use Org\Net\Http;
use Think\WeiXinAuthController;

class WayController extends Controller{

    public function index(){

        $this->display();
    }

    public function add(){
        $this->assign('lines',S('lines')?S('lines'):json_encode(array()));
        $this->assign('points',S('points')?S('points'):json_encode(array()));
        $sign = ACPopedom::getWechatSign();
        $this->assign('wxSdk',$sign);
        $this->display();
    }

    public function upload(){
        $i  = I('post.i');
        $id= ACPopedom::getID()?ACPopedom::getID():43543;
        $subName = substr(md5($id),-1);
        $result = UploadFile($subName,"/Attachment/",'/upload/');
        $result['i'] = $i;
        $this->ajaxReturn($result);
    }

    public function submitMarkerInfo(){


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
        //$res = M('Users')->where('userid = '.ACPopedom::getID())->save(array('longitude'=>$baiduLongLat['result'][0]['x'],'latitude'=>$baiduLongLat['result'][0]['y']));
        //if(!$res){
           // $this->ajaxReturn(array('status'=>false,'msg'=>'保存地址失败'));
        //}
        $this->ajaxReturn(array('status'=>true,'msg'=>'地理位置已更新','address'=>$location['result']['formatted_address'],'lng'=>$baiduLongLat['result'][0]['x'],'lat'=>$baiduLongLat['result'][0]['y']));
    }

}