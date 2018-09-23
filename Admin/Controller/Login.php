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

    protected $_formhash = '5abb5d21';
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
        if ($username || $password || $formhash != $this->_formhash){
            $account_m = new Model_Account();
            $account_info = $account_m->getUser($username,md5($password));
            if ($account_info){
                $user_id = $account_info['id'];
                $group_id = $account_info['group_id'];
                $_SESSION['user_id'] = $user_id;
                $_SESSION['group_id'] = $group_id;
                $this->_user_id = $user_id;
                $this->_group_id = $group_id;
                $updata = [
                    'login_time' => time(),
                    'login_ip' => getIp()
                ];
//                var_dump($account_info);die;
                $account_m->updateDbById($user_id,$updata);
                $this->redirect('/Index/Index');
            }else{
                $this->redirect('/Login/Login');
            }
        }
    }
}