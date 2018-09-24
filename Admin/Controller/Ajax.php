<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018 年 9 月 24 日 0024
 * Time: 16:28:29
 */


class Controller_Ajax extends Controller_Base{

    public function init() {
        parent::init();
        $this->_display = true;
    }
    //修改密码
    public function changePwd(){
        $user_id = getHttpVal('user_id','SESSION');
        $new_pwd = getHttpVal('password');
        $account_m = new Model_Account();
        $acc_info = $account_m->selectDbById($user_id);
        if ($acc_info && $new_pwd){
            $update = [
                'password' => md5($new_pwd)
            ];
            $line = $account_m->updateDbById($user_id,$update);
            if ($line == 1){
                session_destroy();
                $this->redirect('/Login/Login');
            }
        }


    }
}