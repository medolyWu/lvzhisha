<?php
/**
 * Created by PhpStorm.
 * User: ADSL
 * Date: 2015/8/13
 * Time: 17:38
 */
namespace Web\Controller;
use Think\AuthController;
use Web\Common\ACPopedom;

class FeedController extends AuthController{
    private $_Model = null;

    public function __construct(){
        $this->_Model = D("Feed");
        parent::__construct();
    }
    /**
     * 新生产一条feed
     */
    public function add(){
        $data['userid'] =ACPopedom::getID();
        $data['action'] = C('FEED_ACTION_SEND');
        $data['feedtype'] = 1;
        $data['posttime'] = time();
        $data['appid'] = isMobile()?C('FEED_APPID_PC'):C('FEED_APPID_MOBILE');
        $data['show'] = 1;
        $data['content'] = trim(I('post.content',''));
        $data['picpath'] = empty($_POST['picpath'])?'':implode('#$',$_POST['picpath']);
        $data['video'] = '';
        if(!$data['content'] && !$data['picpath']){
            $this->ajaxReturn(array('status'=>false,'message'=>'太懒了,说点什么吧'));
        }
        $result = $this->_Model->add($data);
        $this->ajaxReturn($result?array('status'=>true,'message'=>'动态发表成功了,么么哒'):array('status'=>false,'message'=>'由于服务器君罢工,发表动态失败啦..'));
    }

    /**
     * 点赞一条feed
     */
    public function praise(){
        $feedid = intval(I('post.feedid',''));
        \Predis\Autoloader::register();
        $redis = new \Predis\Client();
        if($redis->zscore('feed:'.$feedid.':praise',ACPopedom::getID())){
            $res = $redis->zrem('feed:'.$feedid.':praise',ACPopedom::getID());
            $rs = $redis->hincrby('feed:'.$feedid,'praise',-1);
            $this->ajaxReturn($res && $rs?array('status'=>true):array('status'=>false,'message'=>'由于服务器君罢工,取消点赞失败失败啦..'));
        }else{
            $rs = $redis->zadd('feed:'.$feedid.':praise',time(),ACPopedom::getID());
            $result = $redis->hincrby('feed:'.$feedid,'praise',1);
            $this->ajaxReturn($result && $rs?array('status'=>true,'message'=>'点赞成功了,么么哒'):array('status'=>false,'message'=>'由于服务器君罢工,点赞失败失败啦..'));
        }

    }


}