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
class Controller_Login extends Sys_Core_Controller{

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
        if (!empty($username) || !empty($password)|| !empty($formhash) || $formhash != $this->_formhash){
            $account_m = new Model_Account();
            $account_info = $account_m->getUser($username,md5($password));
            if ($account_info){
                $user_id = $account_info['id'];
                $group_id = $account_info['group_id'];
                $_SESSION['user_id'] = $user_id;
                $_SESSION['group_id'] = $group_id;
                $this->_user_id = $user_id;
                $this->_group_id = $group_id;
                $_SESSION['expire'] = time() + 7200 * 3;
                $updata = [
                    'login_time' => time(),
                    'login_ip' => getIp()
                ];
                $account_m->updateDbById($user_id,$updata);
                $this->cacheNodeAuth($user_id,$group_id);
                $this->redirect('/');
            }else{
                $this->redirect('/Login/Login');
            }
        }
    }

    public function loginout(){
        session_destroy();
        $this->redirect('/Login/Login');
    }

    public function cacheNodeAuth($user_id,$group_id){
        $nodeAuth_m = new Model_PurviewNode();
        $group_nodes = $nodeAuth_m->getNodeAuth($group_id);
        $auth_cache = '-';
        foreach ($group_nodes as $group_node){
            $auth_cache .= $group_node['controller']. '/' . $group_node['action'] . '-';
        }
        //缓存权限
        $redis = new Redis();
        $redisCfg = conf('Redis','queue');
        $redis->connect($redisCfg['host'],$redisCfg['port']);
        $redis->auth('747596');
        $redis->set('nodeAuth:'.$user_id,$auth_cache,86400);
    }
}