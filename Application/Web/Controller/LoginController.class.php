<?php
namespace Web\Controller;
use Common\Validation;
use Web\Common\ACPopedom;
use Think\Controller;
use Think\Verify;
use Org\Net\Http;
/**
 * 
 * 登陆模块
 * @author HP
 *
 */
class LoginController extends Controller{
	public function index(){
        import('Vendor.Weibo.saetv2');
        $weibo = new \SaeTOAuthV2(WB_APPKEY,WB_SKEY);
        $code_url = $weibo->getAuthorizeURL(WB_CALLBACK_URL);
        $this->assign('code_url',$code_url);
		$this->display();
	}
	
	public function Captcha(){
		$verity = new Verify();
		$verity->length = 4;
		$verity->fontSize = 25;
		$verity->entry();
	}
	
	/**
	 * 
	 * 登陆处理
	 */
	public function Login(){
		$MemInfo['email'] = I('post.email','');
		$MemInfo['pass'] = I('post.password','');
		$code= I('post.captcha');
		$verity = new Verify();
		$result = $verity->check($code);
		//if(!$result) $this->error('验证码不正确',U('Login/index'),2);
        if(!Validation::IsEmailAdress($MemInfo['email'])){
            $this->error('提示：邮箱地址不合法');
        }
        if(!Validation::IsNumAndLetterAndDownLineLengthX2Y($MemInfo['pass'],5,30)){
            $this->error('提示：密码至少为5位数字加字母或者下划线');
        }
		$rs = ACPopedom::login($MemInfo['email'], $MemInfo['pass']);
        if($rs['status']==true){
			$this->error($rs['msg'],U('Web/UserCenter/index'),1);
		}else{
			$this->error($rs['msg'],U('Web/Login/index'),2);
		}
	}
    /**
     *
     * 微博登陆
     */
    public function weibo(){
        $code = I('get.code');
        $rs = ACPopedom::weiboLogin($code);
        if($rs['status']){
            $this->error($rs['msg'],U('Web/UserCenter/index'));
        }else{
            $this->error($rs['msg'],U('Web/Index/index'));
        }
    }

    /**
     *
     * 普通注册
     */
    public function register(){
        $data['username'] = trim(I('post.username',''));
        $data['passwd'] = trim(I('post.password',''));
        $data['email'] = trim(I('post.email',''));
        if(!Validation::IsEmailAdress($data['email'])){
           $this->ajaxReturn(array('status'=>false,'message'=>'提示：邮箱地址不合法'));
        }
        if(!Validation::IsNumAndLetterLengthX2Y($data['username'],5,30)){
            $this->ajaxReturn(array('status'=>false,'message'=>'提示：用户名不能少于5位'));
        }
        if(!Validation::IsNumAndLetterAndDownLineLengthX2Y($data['passwd'],5,30)){
            $this->ajaxReturn(array('status'=>false,'message'=>'提示：密码至少为5位数字加字母或者下划线'));
        }
        $rs = M('Users')->where('email = "'.$data['email'].'"')->find();
        if($rs){
            $this->ajaxReturn(array('status'=>false,'message'=>'提示：该邮箱已经存在'));
        }
        $data['nickname'] = $data['username'];
        $data['salt'] = GeneralRandCode();
        $data['passwd'] = md5(md5($data['salt'].$data['passwd']));
        $data['ip'] = get_client_ip();
        $data['posttime'] = time();
        M("Users")->startTrans();
        $userId = M('Users')->add($data);
        if(!$userId){
            M('Users')->rollback();
            $this->ajaxReturn(array('status'=>false,'message'=>'注册失败'));
        }
        $token = authcode($userId.','.time(), "ENCODE", SESSION_AUTH);
        $this->assign('url',SITE_URL.'/Web/Login/activate?token='.base64_encode($token));
        $this->assign('username',$data['username']);
        $res = ACPopedom::sendEmail($data['email'],$data['nickname'],'注册旅之沙激活邮件',$this->fetch('EmailContent'));
        if($res){
            M('Users')->commit();
            cookie('__activate__',authcode($userId.','.time().','.$data['email'], "ENCODE", SESSION_AUTH),C('ACTIVATION_TIME'));
            $this->ajaxReturn(array('status'=>true,'message'=>'注册成功'));
        }else{
            M('Users')->rollback();
            $this->ajaxReturn(array('status'=>false,'message'=>'注册失败'));
        }
    }

    /**
     * 注册激活
     */
    public function activate(){
        $token = I('get.token','');
        if($token){
            $token = explode(',',authcode(base64_decode($token),"DECODE",SESSION_AUTH));
            if(intval($token[0]) <= 0){
                $this->error('提示：激活链接无效1',U('Web/Login/index'));
            }
            if((intval($token[1]) + C('ACTIVATION_TIME') ) < time()){
                $this->error('提示：激活链接已过期',U('Web/Login/index'));
            }
            $res = M('Users')->where('userid = '.intval($token[0]))->find();
            if(!$res){
                $this->error('提示：激活链接无效2',U('Web/Login/index'));
            }
            if($res['isverfy'] == 1){
                $this->error('提示：用户已经激活,可以直接登录了',U('Web/Login/index'));
            }
            $rs = M('Users')->where('userid = '.intval($token[0]))->save(array('isverfy'=>1));
            if($rs){

                $this->error('提示：用户激活成功',U('Web/Login/index'));
            }else{
                $this->error('提示：用户激活失败',U('Web/Login/index'));
            }
        }
        $this->redirect(U('Web/Login/index'));
    }

    /**
     * 注册步骤界面
     */
    public function step(){
        if(strlen(cookie('__activate__')) > 0){
            $info = explode(',',authcode(cookie('__activate__'),"DECODE",SESSION_AUTH));
            if(intval($info[0]) <=0 || !Validation::IsEmailAdress($info[2])){
                $this->redirect('Login/index');
            }
            if((intval($info[1]) + C('ACTIVATION_TIME') ) < time()){
                $this->assign('tips','提示:激活时间已过期请重新发送激活邮件');
            }
            $rs = M('Users')->find(array('where'=>'userid = '.$info[0].' AND isverfy = 1'));
            if($rs){
                $this->assign('tips','提示:您已经激活该账号了,请直接登录');
            }
            $this->assign('info',$info);
            $this->display();
        }else{
            $this->redirect('Login/index');
        }
    }

    /**
     *
     * 检测用户名是否存在
     *
     */
    public function checkEmail(){
        $email = I('post.email','');
        if(!Validation::IsEmailAdress($email)){
            $this->ajaxReturn(array('status'=>false,'message'=>'邮箱地址不合法'));
        }
        $rs = M('Users')->where('email = "'.$email.'"')->find();
        if(!$rs){
            $this->ajaxReturn(array('status'=>true,'message'=>'邮箱可用'));
        }else{
            $this->ajaxReturn(array('status'=>false,'message'=>'当前邮箱已经存在'));
        }
    }

    /**
     *
     */
    public function getbackPasswd(){
        if(IS_POST){
            $data['captcha'] = I('post.captcha','');
            $data['email'] = I('post.email','');
            $verify = new Verify();
            $checkRs = $verify->check($data['captcha']);
            if(!$checkRs){
                $this->ajaxReturn(array('status'=>false,'message'=>'提示：验证码不正确'));
            }
            if(!Validation::IsEmailAdress($data['email'])){
                $this->ajaxReturn(array('status'=>false,'message'=>'提示：邮箱地址不合法'));
            }
            $rs = M('Users')->where("email = '".$data['email']."'")->find();
            if(!$rs){
                $this->ajaxReturn(array('status'=>false,'message'=>'提示：该账号不存在'));
            }
            $token = authcode($rs['userid'].','.time().','.$data['email'], "ENCODE", SESSION_AUTH);
            $this->assign('url',SITE_URL.'/Web/Login/modifyPasswd?token='.base64_encode($token));
            $this->assign('username',$rs['nickname']);
            $res = ACPopedom::sendEmail($data['email'],$rs['nickname'],'旅之沙修改密码邮件',$this->fetch('modifyPasswdEmailContent'));
            if($res){
                cookie('__getback__',authcode($rs['userid'].','.time().','.$data['email'], "ENCODE", SESSION_AUTH),C('ACTIVATION_TIME'));
                $this->ajaxReturn(array('status'=>true,'message'=>'提示：邮件发送成功','url'=>SITE_URL.U('Web/Login/gbStep2')));
            }else{
                $this->ajaxReturn(array('status'=>false,'message'=>'提示：发送邮件失败'));
            }

        }
        $this->display('getbackPasswdStep1');
    }

    /**
     *
     */
    public function gbStep2(){
        if(strlen(cookie('__getback__')) > 0){
            $info = explode(',',authcode(cookie('__getback__'),"DECODE",SESSION_AUTH));
            if(intval($info[0]) <=0 || !Validation::IsEmailAdress($info[2])){
                $this->redirect('Login/index');
            }
            if((intval($info[1]) + C('ACTIVATION_TIME') ) < time()){
                $this->assign('tips','提示:时间已过期请重新发送修改密码邮件');
            }
            $this->assign('info',$info);
            $this->display('getbackPasswdStep2');
        }else{
            $this->redirect('Login/index');
        }
    }
    /**
     *
     */
    public function modifyPasswd(){
        $token = I('get.token','');
        if(!$token){
            $this->redirect(U('Web/Login/index'));
        }
        $token = explode(',',authcode(base64_decode($token),"DECODE",SESSION_AUTH));
        if(intval($token[0]) <= 0){
            $this->error('提示：无效的链接',U('Web/Login/index'));
        }
        if((intval($token[1]) + C('ACTIVATION_TIME') ) < time()){
            $this->error('提示：链接已过期',U('Web/Login/getbackPasswd'));
        }
        $res = M('Users')->where('userid = '.intval($token[0])." AND email = '".$token[2]."'")->find();
        if(!$res){
            $this->error('提示：该账号不存在',U('Web/Login/getbackPasswd'));
        }
        if(IS_POST){
            $email = I('post.email','');
            $passwd = I('post.password','');
            $repasswd = I('post.repassword');
            if(!Validation::IsEmailAdress($email)){
                $this->ajaxReturn(array('status'=>false,'message'=>'提示：邮箱地址不合法'));
            }
            if(!Validation::IsNumAndLetterAndDownLineLengthX2Y($passwd,5,30)){
                $this->ajaxReturn(array('status'=>false,'message'=>'提示：密码至少为5位数字加字母或者下划线'));
            }
            if($passwd != $repasswd){
                $this->ajaxReturn(array('status'=>false,'message'=>'提示：确认密码不一致'));
            }
            $data['salt'] = GeneralRandCode();
            $data['passwd'] =  md5(md5($data['salt'].$passwd));
            $rs = M('Users')->where('userid = '.intval($token[0])." AND email = '".$token[2]."'")->save($data);
            //写日志
            if($rs){
                $this->ajaxReturn(array('status'=>true,'message'=>'提示：新密码设置成功'));
            }else{
                $this->ajaxReturn(array('status'=>false,'message'=>'提示：新密码设置失败'));
            }
        }
        $this->display('getbackPasswdStep3');
    }

    /*
     * QR Code
     */
    public function QRcode(){
        \Predis\Autoloader::register();
        $redis = new \Predis\Client();
        //生成二维码
        $numberid = M('randnumber')->add(array('openid'=>''));
        $token = base64_encode(authcode($numberid, "ENCODE", SESSION_AUTH));
        //设置标志有效时间
        $redis->setex('__WX_NUMBERID_'.$numberid,300,'true');
        $rs = json_decode(Http::CurlRequst("http://api.wwei.cn/wwei",array("data"=>$token,'apikey'=>20150828111302),"GET"),true);
        $this->assign('erweima',$rs['data']['qr_filepath']);
        $this->assign('token',$token);
        $this->display();
    }
}
?>