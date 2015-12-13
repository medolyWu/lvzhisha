<?php
/**
 * Created by PhpStorm.
 * User: ADSL
 * Date: 2015/7/21
 * Time: 13:36
 */
namespace Web\Controller;

use Think\AuthController;
use Web\Common\ACPopedom;

class UserCenterController extends AuthController{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        \Predis\Autoloader::register();
        $redis = new \Predis\Client();
        $feedList = M('Feed')->alias('f')->field('f.feedid,f.posttime,f.show,f.content,f.picpath,f.appid,u.avator,u.nickname')->join(array('__USERS__ AS u ON f.userid =  u.userid '))->where('f.userid = '.ACPopedom::getID())->select();
        foreach($feedList as $key=>$value){
            $feedList[$key]['posttime'] = beforeTime($value['posttime']);
            if($value['picpath']){
                $feedList[$key]['picpath'] = explode('#$',$value['picpath']);
                $feedList[$key]['pic-col'] = count($feedList[$key]['picpath']) > 4 ? 3 : 12 / count($feedList[$key]['picpath']);
            }
            //取出点赞数
            $praise = $redis->hget('feed:'.$value['feedid'],'praise');
            $feedList[$key]['praise'] = $praise?$praise:0;
        }
        //var_dump($feedList);
        $this->assign('feedlist',$feedList);
        $this->assign('userinfo',ACPopedom::getUserInfo());
        $this->display();
    }


}