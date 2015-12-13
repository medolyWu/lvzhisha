<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/25
 * Time: 22:35
 */
namespace Web\Controller;

use Think\AuthController;

class QAController extends AuthController{

    public function index(){

        $this->display();
    }
}