<?php
/**
 * Created by PhpStorm.
 * User: ADSL
 * Date: 2015/8/26
 * Time: 15:13
 */
namespace Web\Controller;

use Think\AuthController;

class WayController extends AuthController{

    public function index(){

        $this->display();
    }
}