<?php
namespace Web\Controller;
use Think\Controller;
use Web\Common\ACPopedom;

class IndexController extends Controller {
    public function index(){
        import('Vendor.Weibo.saetv2');
        $weibo = new \SaeTOAuthV2(WB_APPKEY,WB_SKEY);
        $code_url = $weibo->getAuthorizeURL(WB_CALLBACK_URL);
        $this->assign('code_url',$code_url);
        $this->display();exit;
    }
}