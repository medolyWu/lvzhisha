<?php
namespace WeChat\Common;
use Org\Net\Http;
use \Tools\Wechat;
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
        if(cookie('__WX_UID__') && cookie('__WX_AUTH_TOKEN__')){
            return true;
        }
        return false;
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
     * 功能:获取微信JSSDK签名
     */
    static public function getWechatSign(){
        import("Vendor.WeiXin.Wechat");
        //微信公众号的appid token appsecret
        $configs = array(
            'token' => C("TOKEN"),
            'appid' => C("AUTH_APPID"),
            'appsecret' => C("AUTH_APPSECRET"),
        );
        $wechatModel = new Wechat($configs);
        $wechatModel->checkAuth();
        $wechatModel->getJsTicket();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = $protocol.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];
        $sign = $wechatModel->getJsSign($url);
        return $sign;
    }
    /**
     *
     * 功能：得到账号ID
     */
    static public function getID(){
        if (self::isLogin ()) {
            return authcode(cookie('__WX_UID__'),"DECODE",SESSION_AUTH);
        } else {
            return false;
        }
    }

    /**
     *
     * 功能：得到用户昵称
     */
    static public function getNickname(){
        if (self::isLogin ()) {
            return authcode(cookie('__WX_NICKNAME__'),"DECODE",SESSION_AUTH);
        } else {
            return false;
        }
    }

    /**
     *
     * 功能：得到用户头像
     */
    static public function getAvator(){
        if (self::isLogin ()) {
            return authcode(cookie('__WX_AVATOR__'),"DECODE",SESSION_AUTH);;
        } else {
            return false;
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