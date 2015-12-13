<?php
/**
 * 
 * 后台总控制端
 * @author hp
 *
 */
namespace Think;
use Web\Common\ACPopedom;

class AuthController extends Controller{
    function _initialize() {
        if(!ACPopedom::isLogin()){
	         $this->redirect('Web/Login/index');
        }

    }
}
