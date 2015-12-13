<?php
/**
 * Created by PhpStorm.
 * User: ADSL
 * Date: 2015/8/27
 * Time: 11:21
 */
namespace WeChat\Controller;
use Think\WeiXinAuthController;
use Tools\Wechat;
use WeChat\Common\ACPopedom;

class WeiXinController extends WeiXinAuthController{

    private $redis = null;
    private $wx_login_name;
    private $wx_auth_name;
    private $wx_numberid_name;

    public function __construct(){
        \Predis\Autoloader::register();
        $this->redis = new \Predis\Client();
        $this->wx_auth_name = '__WX_AUTH_';
        $this->wx_login_name = '__WX_LOGIN_';
        $this->wx_numberid_name = '__WX_NUMBERID_';
        parent::__construct();
        if(!isWeiXinBrowser()){
            $this->assign('tips','请在微信浏览器中打开');
            $this->display('WeiXin:result');
            exit;
        }
    }
    public function QRcode(){
        $this->_wechat->checkAuth();
        $this->_wechat->getJsTicket();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = $protocol.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];
        $sign = $this->_wechat->getJsSign($url);
        $this->assign('wxSdk',$sign);
        $this->assign('url',SITE_URL.'/index.php/WeChat/WeiXin/confirm?token=');
        $this->display();
    }

    public function confirm(){
        if(IS_POST){
            if($this->redis->get($this->wx_numberid_name.intval(I('post.numberid'))) && I('post.confirm') == 'true'){
                $this->redis->setex($this->wx_auth_name.intval(I('post.numberid')),150,ACPopedom::getID());
                //提示登陆成功
                header( "HTTP/1.1 200" );
                exit;
            }else{
                header( "HTTP/1.1 400" );
                exit;
            }
        }
        $nmberid = authcode(base64_decode(trim(I('get.token'))),"DECODE",SESSION_AUTH);
        if(!intval($nmberid)){
            //解析不成功
            $this->ajaxReturn(array('status'=>false,'msg'=>'无效的二维码'));
        }
        if(!$this->redis->get($this->wx_numberid_name.$nmberid)){
            //解析不成功
            $this->ajaxReturn(array('status'=>false,'msg'=>'此二维码已过期,请刷新页面再扫！'));
        }
        //生成扫码通过标识，并绑定扫码带来的numberid
        $this->redis->setex($this->wx_login_name.$nmberid,150,ACPopedom::getAvator());
        $this->ajaxReturn(array('status'=>true,'numberid'=>$nmberid));
    }
}