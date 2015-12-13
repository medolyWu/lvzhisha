<?php
/**
 * Created by PhpStorm.
 * User: ADSL
 * Date: 2015/9/2
 * Time: 10:54
 */
namespace WeChat\Controller;

use Think\Controller;
use WeChat\Common\ACPopedom;

class LoginController extends Controller{

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
    }

    public function login(){
        $nmberid = authcode(base64_decode(trim(I('get.token'))),"DECODE",SESSION_AUTH);
        $startTime = time();
        while($this->redis->get($this->wx_numberid_name.$nmberid)){
            if(time() >= $startTime + 27){ // 超时重新轮询
                header( "HTTP/1.1 204" );
                return;
            }
            if($avator = $this->redis->get($this->wx_login_name.$nmberid)){//存在跳出，返回返回给浏览器同时删除标志
                $this->redis->del($this->wx_login_name.$nmberid);
                header( "HTTP/1.1 201" );
                $this->ajaxReturn(array('avator'=>$avator));
            }
            if($userid = $this->redis->get($this->wx_auth_name.$nmberid)){//存在跳出，返回返回给浏览器同时删除标志
                session(array("name"=>SESSION_ID,'path'=>"/","expire"=>C('COOKIE_EXPIRE')));
                $userinfo = M('Users')->where('userid = '.intval($userid))->find();
                session(SESSION_ID,$userinfo['userid']);
                session(array("name"=>SESSION_TOKEN,'path'=>"/","expire"=>C('COOKIE_EXPIRE')));
                session(SESSION_TOKEN,$userinfo['userid']);
                cookie("__info__",authcode(serialize(array('nickname'=>$userinfo['nickname'],'userid'=>$userinfo['userid'],'avator'=> $userinfo['avator'])),"ENCODE", SESSION_AUTH, C('COOKIE_EXPIRE')));
                $this->redis->del($this->wx_auth_name.$nmberid);
                $this->redis->del($this->wx_numberid_name.$nmberid);
                header( "HTTP/1.1 200" );
                $this->ajaxReturn(array('url'=>U('Web/UserCenter/index')));
            }
        }
        header( "HTTP/1.1 400" );
    }
}