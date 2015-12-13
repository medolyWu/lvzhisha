<?php
namespace Web\Common;
use Org\Net\Http;
/**
 * 
 * 权限功能检测类
 * @author hp
 *
 */
final class ACPopedom {
	
	/**
	 * 
	 * 功能:判断是否登陆
	 */
	static public function isLogin(){
        if(session(SESSION_TOKEN) && session(SESSION_ID)){
            return true;
        }
        return false;
	}

    /**
     *
     * 功能:微博登陆
     */
    static public function weiboLogin($code){
        import('Vendor.Weibo.saetv2');
        $weibo = new \SaeTOAuthV2(WB_APPKEY,WB_SKEY);
        $params = array('client_id'=>WB_APPKEY,'client_secret'=>WB_SKEY,'grant_type'=>'authorization_code','code'=>$code,'redirect_uri'=>WB_REDIRECT_URI);
        $accessToken = $weibo->getAccessToken('code',$params);
        if(!$accessToken || $accessToken['access_token'] == '' || !$accessToken['uid'] ){
            return array('status'=>false,'msg'=>'登陆失败,获取access_token失败');
        }
        $clientModel = new \SaeTClientV2(WB_APPKEY,WB_SKEY,$accessToken['access_token']);
        $userInfo = $clientModel->show_user_by_id($accessToken['uid']);
        if($userInfo['error_code']){
            return array('status'=>false,'msg'=>$userInfo['error']);
        }
        //判断本地是否有该用户
        $rs = M('Users')->where('weiboid='.$accessToken['uid'])->find();
        if(!$rs){
            $data['username'] = '';
            $data['nickname'] = $userInfo['screen_name'];
            $data['avator'] = $userInfo['avatar_large'];
            $data['salt'] = '';
            $data['passwd'] = '';
            $data['weiboid'] = $accessToken['uid'];
            $data['address'] = $userInfo['location'];
            $data['ip'] = get_client_ip();
            $data['posttime'] = time();
            $userId = M('Users')->add($data);
            if(!$userId){
                return array('status'=>false,'msg'=>'登陆失败');
            }
        }

        session(array("name"=>SESSION_ID,'path'=>"/","expire"=>$accessToken['expires_in']));
        session(SESSION_ID,$rs ? $rs['userid'] : $userId);
        session(array("name"=>SESSION_TOKEN,'path'=>"/","expire"=>$accessToken['expires_in']));
        session(SESSION_TOKEN,$rs ? $rs['userid'] : $userId);
        cookie("__info__",authcode(serialize(array('nickname'=>$userInfo['screen_name'],'userid'=>$rs ? $rs['userid'] : $userId,'avator'=> $userInfo['avatar_large'])),"ENCODE", SESSION_AUTH, $accessToken['expires_in']));
        return array('status'=>true,'msg'=>'登陆成功');;
    }

    /**
     *
     * 功能:微信登陆
     */
    static public function weixinLogin(){

    }
    /**
     *
     * 功能:发送邮件
     */
    static public function sendEmail($toEmail, $toName, $subject = '', $content = '', $attachment = null){
        Vendor('PHPMailer.PHPMailerAutoload');
        $config = C('THINK_EMAIL');
        $mail = new \PHPMailer();
        $mail->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP(); // 设定使用SMTP服务
        $mail->SMTPDebug = 0; // 关闭SMTP调试功能
        $mail->SMTPAuth = true; // 启用 SMTP 验证功能
        $mail->SMTPSecure = 'ssl'; // 使用安全协议
        $mail->Host = $config['SMTP_HOST']; // SMTP 服务器
        $mail->Port = $config['SMTP_PORT']; // SMTP服务器的端口号
        $mail->Username = $config['SMTP_USER']; // SMTP服务器用户名
        $mail->Password = $config['SMTP_PASS']; // SMTP服务器密码
        $mail->SetFrom($config['FROM_EMAIL'], $config['FROM_NAME']);
        $replyEmail = $config['REPLY_EMAIL']?$config['REPLY_EMAIL']:$config['FROM_EMAIL'];
        $replyName = $config['REPLY_NAME']?$config['REPLY_NAME']:$config['FROM_NAME'];
        $mail->AddReplyTo($replyEmail, $replyName);
        $mail->Subject = $subject;
        $mail->AltBody = "为了查看该邮件，请切换到支持 HTML 的邮件客户端";
        $mail->MsgHTML($content);
        $mail->AddAddress($toEmail,$toName);
        if(is_array($attachment)){ // 添加附件
            foreach ($attachment as $file){
                is_file($file) && $mail->AddAttachment($file);
            }
        }
        return $mail->Send() ? true : $mail->ErrorInfo;
    }

    /**
     *
     * 功能：获得账号头像，昵称信息
     */
    static public function getUserInfo(){
        if(self::isLogin ()){
            //获取信息
            $info = unserialize(authcode(cookie('__info__'),"DECODE",SESSION_AUTH));
            if (!is_array($info) || empty($info)){
                return null;
            }
            return $info;
        } else {
            return null;
        }
    }
	
	/**
	 * 
	 * 功能:退出
	 */
	static public function logout(){
        session(SESSION_TOKEN,null);
        session(SESSION_ID,null);
		return true;
	}
	
	/**
	 * 
	 * 功能:登陆
	 * @param $master
	 * @param $pwd
	 */
	static public function login($master,$pwd){//账号，密码
        $rs = M('Users')->where("email='".$master."'")->find();
        if(!$rs){
            return array("status"=>false,"msg"=>'账号不存在');
        }
        if($rs['passwd'] != md5(md5($rs['salt'].$pwd))){
            return array("status"=>false,"msg"=>'密码不正确');
        }
        session(array("name"=>SESSION_ID,'path'=>"/","expire"=>C('COOKIE_EXPIRE')));
        session(SESSION_ID,$rs['userid']);
        session(array("name"=>SESSION_TOKEN,'path'=>"/","expire"=>C('COOKIE_EXPIRE')));
        session(SESSION_TOKEN,$rs['userid']);
        cookie("__info__",authcode(serialize(array('nickname'=>$rs['nickname'],'userid'=>$rs['userid'],'avator'=> $rs['avator'])),"ENCODE", SESSION_AUTH, C('COOKIE_EXPIRE')));
        return array('status'=>true,'msg'=>'登陆成功');;
	}
    /**
     *
     * 功能：得到账号ID
     */
    static public function getID(){
        if (self::isLogin ()) {
            return $_SESSION [SESSION_ID];
        } else {
            return 0;
        }
    }

	
	/**
	 * MD5加密
	 * 参数:$str
	 * 返回:密文
	 */
	static public function mixPass($str) {
		return substr ( md5 ( $str ), 5, 16 );
	}
}
?>