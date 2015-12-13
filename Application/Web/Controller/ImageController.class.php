<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/25
 * Time: 22:45
 */
namespace Web\Controller;

use Think\AuthController;

class ImageController extends AuthController{

    public function index(){
        $this->assign('lines',S('lines')?S('lines'):json_encode(array()));
        $this->assign('points',S('points')?S('points'):json_encode(array()));
        //var_dump(S('points'));
        $this->display();
    }

    public function add(){
        //var_dump(json_decode($_POST['points'],true));exit;
        S('lines',$_POST['lines']);
        S('points',$_POST['points']);
        //var_dump(json_encode(array(array('lng'=>1243,'lat'=>45646),array('lng'=>44444,'lat'=>99999))));
        //var_dump(json_decode($_POST['lines'],true));
    }
}
