<?php
/**
 * Created by PhpStorm.
 * User: ADSL
 * Date: 2015/9/2
 * Time: 17:57
 */
namespace WeChat\Controller;

use Think\Controller;
use \Tools\Wechat;

class AuthController  extends Controller{
    private  $_wechat = null;
    public function _initialize() {
        import("Vendor.WeiXin.Wechat");
        $this->_wechat = new Wechat(array('token'=>C('TOKEN'),"appid"=>C('AUTH_APPID'),"appsecret"=>C('AUTH_APPSECRET'),'debug'=>true));
    }

    public function auth(){
        $this->_wechat->checkAuth();
        $token = $this->_wechat->getOauthAccessToken();
        $userinfo = $this->_wechat->getUserInfo($token['openid']);
        $result = M('Users')->where('openid = "'.$token['openid'].'"')->find();
        if($result){//用户已存在，更新信息，，暂时不更新
            cookie('__WX_UID__',authcode($result['userid'], "ENCODE", SESSION_AUTH),C('COOKIE_EXPIRE'));
            cookie('__WX_AVATOR__',authcode($result['avator'], "ENCODE", SESSION_AUTH),C('COOKIE_EXPIRE'));
            cookie('__WX_NICKNAME__',authcode($result['nickname'], "ENCODE", SESSION_AUTH),C('COOKIE_EXPIRE'));
            cookie('__WX_AUTH_TOKEN__',authcode($token['openid'], "ENCODE", SESSION_AUTH),C('COOKIE_EXPIRE'));
        }else{
            $data['username'] = '';
            $data['nickname'] = $userinfo['nickname']?$userinfo['nickname']:'';
            //此处头像应该做本地化处理。。。。。
            $data['avator'] = $userinfo['headimgurl']?$userinfo['headimgurl']:'';
            $data['address'] = '';
            $data['salt'] = '';
            $data['passwd'] = '';
            $data['posttime'] = time();
            $data['openid'] = $token['openid'];
            $data['unionid'] = $userinfo['unionid']?$userinfo['unionid']:'';
            $data['ip'] = get_client_ip();
            $rs = M('Users')->add($data);
            if($rs){
                cookie('__WX_UID__',authcode($rs, "ENCODE", SESSION_AUTH),C('COOKIE_EXPIRE'));
                cookie('__WX_AVATOR__',authcode($userinfo['headimgurl'], "ENCODE", SESSION_AUTH),C('COOKIE_EXPIRE'));
                cookie('__WX_NICKNAME__',authcode($userinfo['nickname'], "ENCODE", SESSION_AUTH),C('COOKIE_EXPIRE'));
                cookie('__WX_AUTH_TOKEN__',authcode($token['openid'], "ENCODE", SESSION_AUTH),C('COOKIE_EXPIRE'));
            }
        }
        //
        header("Location:".authcode(cookie('__refer__'),"DECODE",SESSION_AUTH));
    }

}