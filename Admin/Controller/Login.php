<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018 年 9 月 23 日 0023
 * Time: 15:14:23
 */

/**
 * Class Controller_Login
 */
class Controller_Login extends Controller_Base{

    /**
     * @throws Sys_Lib_Exception
     */
    public function Login(){
        $this->display();
    }

    /**
     * 登陆
     */
    public function Land(){
        $this->_display = true;
        $username = getHttpVal('username');
        $password = getHttpVal('password');
        $formhash = getHttpVal('formhash');
        if ($username || $password || $formhash){
            $account_m = new Model_Account();
            $account_info = $account_m->getUser($username,md5($password));
            if ($account_info){
                $userid = $account_info['id'];
                $group_id = $account_info['group_id'];
            }else{
                $this->redirect('Login/Login');
            }
        }
    }
}