<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018 年 9 月 23 日 0023
 * Time: 14:03:12
 */
class Controller_Base extends Sys_Core_Controller{

    protected $_page_title = '';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws Sys_Lib_Exception
     */
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->assign('page_title', $this->_page_title);
        $this->assign('menu', $this->fetch('../menu.' . conf('default', 'View_Suffix')));
        $this->assign('content', $this->fetch());
        $this->display('../index.html');
    }
}