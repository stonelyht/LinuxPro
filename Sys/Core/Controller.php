<?php

class Sys_Core_Controller {
    
    /**
     * 使用视图
     * @var bool
     */
    protected $_display = false;
    
    /**
     * 输出类型 json|array
     * @var string
     */
    protected $_type = '';
    
    /**
     * 视图
     * @var Smarty
     */
    protected $_view = null;

    /**
     * 构造函数
     * @param array $app
     */
    public function __construct() {
        //是否开启视图
        if (conf('default', 'Enable_View') == true) {
            session_start();
            $this->enableView();
        } else {
            $this->setType('json');
        }
        $this->init();
    }
    
    public function init() {
        
    }
    
    /**
     * 使用视图
     * @return Utility_Core_Controller
     */
    public function enableView() {
        require_once LIB_PATH . '/Smarty/Smarty.class.php';
        $smarty = new Smarty();
        $smarty->caching = false;
        $smarty->unmuteExpectedErrors();
        $smarty->setCompileDir(RUNTIME_PATH . '/compile');
        $smarty->setCacheDir(RUNTIME_PATH . '/cache');
        $viewDir = APP_PATH . '/Views';
        if (isMobile() && is_dir(APP_PATH . '/ViewsWap')) {
            $viewDir = APP_PATH . '/ViewsWap';
        }
        $smarty->setTemplateDir($viewDir . '/' . Sys_Lib_Cache_Array::get('Controller'));
        $smarty->left_delimiter = "<{";
        $smarty->right_delimiter = "}>";
        $this->_view = $smarty;
        return $this;
    }
    
    /**
     * 标记一个模板变量
     * @param array | string $key
     * @param mixed $value
     */
    protected function assign($key, $value) {
        $this->_view->assign($key, $value);
    }

    /**
     * 渲染并显示页面
     * @param string $template
     */
    protected function display($template = null) {
        if (!is_null($this->_view)) {
            $viewSuffix = conf('default', 'View_Suffix');
            $action = Sys_Lib_Cache_Array::get('Action');
            $template = is_null($template) ? $action . '.' . $viewSuffix : $template;
            return $this->_view->display($template);
        } else {
            throw new Sys_Lib_Exception('not enable view');
        }
        
    }

    /**
     * 返回已渲染页面HTML
     * @param string $template
     * @param bool $display
     * @return string
     */
    protected function fetch($template = null, $display = false) {
        if (!is_null($this->_view)) {
            $viewSuffix = conf('default', 'View_Suffix');
            $action = Sys_Lib_Cache_Array::get('Action');
            $template = is_null($template) ? $action . '.' . $viewSuffix : $template;
            return $this->_view->fetch($template, null, null, null, $display);
        } else {
            throw new Sys_Lib_Exception('not enable view');
        }
        
    }

    /**
     * 获得请求参数
     * @return mixed
     */
    public function getParams() {
        return getHttpVal('params');
    }

    /**
     * 设置输出类型
     * @param string $type
     */
    public function setType($type) {
        $this->_type = $type;
    }
    
    /**
     * 返回输出类型
     * @return string
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * 返回输出字符串
     * @return string
     */
    public function gameFetch() {
        $result = '';
        if (!$this->_display) {
            $out = View::display();
            switch ($this->_type) {
                case 'json':
                    $result = $out ? my_json_encode($out) : '';
                    break;
                case 'array':
                    $result = $out;
                    break;
            }
        }
        return $result;
    }
    
    /**
     * 页面跳转
     * @param string $url
     */
    public function redirect($url) {
        $url = conf('default', 'Path_Prefix') . $url;
        header("Location: $url");
        exit;
    }
    
    /**
     * 析构函数
     */
    public function __destruct() {
        if (!$this->_display) {
            return $this->gameFetch();
        }
    }

}