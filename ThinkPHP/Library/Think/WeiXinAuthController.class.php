<?php
/**
 *
 * 物管总控制端
 * @author hp
 *
 */
namespace Think;
use \Tools\Wechat;
use WeChat\Common\ACPopedom;

class WeiXinAuthController extends Controller{
    protected $_wechat = null;
    public function _initialize() {
        import("Vendor.WeiXin.Wechat");
        $this->_wechat = new Wechat(array('token'=>C('TOKEN'),"appid"=>C('AUTH_APPID'),"appsecret"=>C('AUTH_APPSECRET'),'debug'=>true));
        // 验证微信推送的签名
        //$this->_wechat->valid();
        //$this->_wechat->checkAuth();
       $this->_wechat->getRev();
       switch($this->_wechat->getRevType()){
            //文字类型
            case Wechat::MSGTYPE_TEXT:
                $this->_wechat->text('客官，请切换到菜单模式，更多精彩等着你。')->reply();
                //$this->replyText($this->_wechat->getRevContent());
                break;
            //事件推送类型
            case Wechat::MSGTYPE_EVENT:
                $this->replyEvent($this->_wechat->getRevEvent());
                break;
            case Wechat::MSGTYPE_VOICE:
                $this->_wechat->text('客官,语音暂时还未开通,请切换到菜单模式体验更多精彩')->reply();
                break;
            case Wechat::MSGTYPE_IMAGE:
                $this->_wechat->text('客官,图片暂时还未开通,请切换到菜单模式体验更多精彩')->reply();
                break;
           case Wechat::MSGTYPE_LOCATION://上报地理位置
               //$data = $this->_wechat->getRevEventGeo();
               //$this->_wechat->text('客官,你的地理位置所在经度为：'.$data['x'].',纬度为：'.$data['y'])->reply();
               break;
        }
       /* cookie('__WX_UID__',null);
        cookie('__WX_AVATOR__',null);
        cookie('__WX_NICKNAME__',null);
        cookie('__WX_AUTH_TOKEN__',null);
        exit;*/
        if(!ACPopedom::isLogin()){
            $oauthurl = $this->_wechat->getOauthRedirect(SITE_URL.'/index.php/WeChat/Auth/auth',C("AUTH_STATUS"),'snsapi_userinfo');
            cookie("__refer__",authcode(SITE_URL.$_SERVER['REQUEST_URI'], "ENCODE", SESSION_AUTH, C('EXPIRE_TIME')));
            header("Location:".$oauthurl);
        }
    }
    /**
     * 回复文字类型
     * $content string
     */
    private function replyText($content){
        //根据内容回复信息
        switch($content){
//            case '帮助':
//                $newsdata = array(
//                    0=>array(
//                        'title'=>'帮助信息',
//                        'Description'=>'帮助信息描述',
//                        'PicUrl'=>SITE_ATTACHMENT_URL.'/face/2015/05/1431919038kfnbkzep1q4v7pdjd9vq_thumb.jpeg',
//                        'Url'=>'http://www.lifeq.com.cn/weixin/xiaoqu4.0/pages/blog.php?classid=2'
//                    )
//                );
//                $this->_wechat->news($newsdata)->reply();
//                break;
            default:
                $this->_wechat->text('客官，请切换到菜单模式，更多精彩等着你。')->reply();
                break;
        }
    }
    /**
     * 回复推送事件类型
     * $eventArray array()
     */
    private function replyEvent($eventType){
        //获取推送类型
        switch($eventType['event']){
            //关注推送
            case Wechat::EVENT_SUBSCRIBE:
                $newsdata = array(
                    0=>array(
                        'title'=>'欢迎来到旅之沙',
                        'Description'=>'欢迎来到旅之沙',
                        'PicUrl'=>'http://lvzhishaimage.b0.upaiyun.com/Attachment/face/5e080a295f8076b1.jpeg',
                        'Url'=>SITE_URL.'/index.php/WeChat/Tool/index/'
                    )
                );
                $this->_wechat->news($newsdata)->reply();
                break;
            //取消关注推送
            case 'unsubscribe':
                break;
            //用户点击自定义菜单推送
            case Wechat::EVENT_MENU_CLICK:
                switch($eventType['key']){
                    default:
                        $this->_wechat->text('客官，你点击的菜单暂无信息')->reply();
                        break;
                }
                break;
            //用户推送地理位置推送
            case Wechat::EVENT_LOCATION:
                //$this->_wechat->text('客官，你点击的菜单暂无信息')->reply();
               // $data = $this->_wechat->getRevEventGeo();
               // $this->_wechat->text('客官,你的地理位置所在经度为：'.$data['x'].',纬度为：'.$data['y'])->reply();
                break;
            //网页跳转
            case Wechat::EVENT_MENU_VIEW:
                break;
        }
    }
}