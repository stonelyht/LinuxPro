<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018 年 9 月 23 日 0023
 * Time: 14:03:12
 */

/**
 * Class Controller_Base
 */
class Controller_Base extends Sys_Core_Controller{

    /**
     * 用户id
     * @var string
     */
    protected $_user_id = 0;

    /**
     * 分组id
     * @var string
     */
    protected $_group_id = 0;

    protected $_time;

    /**
     * 用户信息
     * @var string
     */
    protected $_user_info;

    /**
     * 页面标题
     * @var string
     */
    protected $_page_title = '';

    /**
     * 控制器
     * @var string
     */
    protected $controller;

    /**
     * 方法
     * @var string
     */
    protected $action;

    /**
     * Controller_Base constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init(){
        //登陆检查
        $this->_time = time();
        $this->controller = Sys_Lib_Cache_Array::get('Controller');
        $this->action = Sys_Lib_Cache_Array::get('Action');
        $expire = getHttpVal('expire', 'SESSION');
        if (ucwords($this->controller) != "Login" || ucwords($this->action) != "Login"){
            if (!$this->_user_id || $expire < time()) {
                session_destroy();
                $this->redirect("/Login/Login");
            }
            $this->_group_id = getHttpVal('group_id', 'SESSION');
            if (!$this->_group_id) {
                $this->redirect("/Login/Login");
            }
            Sys_Lib_Cache_Array::set('operator_id', $this->_group_id);
            $account_m = new Model_Account();
            $this->_user_info = $account_m->getByUserid($this->_user_id);
            $_SESSION['expire'] = time() + 7200 * 3;
        }

    }

    /**
     * @throws Sys_Lib_Exception
     */
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $Controller = Sys_Lib_Cache_Array::get('Controller');
        $Action = Sys_Lib_Cache_Array::get('Action');
        if (ucwords("$Controller") !='Login' && ucwords("$Action") !='Login'){
            $this->assign('page_title', $this->_page_title);
            $this->assign('menu', $this->fetch('../menu.' . conf('default', 'View_Suffix')));
            $this->assign('content', $this->fetch());
            $this->display('../index.html');
        }

    }
}