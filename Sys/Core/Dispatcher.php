<?php

class Sys_Core_Dispatcher {
    public static $controller = '';
    public static $action = '';

    //控制器调度
    public static function dispatch() {
        $app = self::analyse();
        $closeApp = conf('default', 'CLOSE_APP');
        if ($closeApp == false) {
            //自定义路由返回
            $route_id = filter_input(INPUT_GET, 'route_id');
            if ($route_id) {
                $route = conf('route', $route_id);
                $app = self::analyse($route);
            }
            self::$controller = $app['controller'];
            self::$action = $app['action'];

            //实例化Controller
            $controller = self::getController($app);
            self::getAction($controller, $app);
        } else {
            header('HTTP/1.1 404 Not Found');
            exit;
        }
    }

    /**
     * PATH_INFO分析
     * @return array
     */
    public static function analyse($path = '') {
        //获取路由信息（http请求域名后面的/xxx/xxx）
        $pathInfo = $path ? $path : filter_input(INPUT_SERVER, 'PATH_INFO');
        /*
        //兼容nginx代理
        $pos = strpos($pathInfo, '?');
        if ($pos !== false) {
            $pathInfo = substr($pathInfo, 0, $pos);
        }
        */
        //将PATH_INFO拆分
        $paramUrl = explode('/', trim($pathInfo, '/'));
        $paramNum = $pathInfo ? count($paramUrl) : 0;
        switch ($paramNum) {
            case 0 :
                $conName = 'Index';
                $actName = 'index';
                break;
            case 1 :
                $conName = getArrVal($paramUrl, 0);
                $actName = 'index';
                break;
            default :
                $conName = getArrVal($paramUrl, 0);
                $actName = getArrVal($paramUrl, 1);
        }
        $data = array(
            'controller' => Sys_Lib_Cache_Array::set('Controller', $conName),
            'action' => Sys_Lib_Cache_Array::set('Action', $actName)
        );
        return $data;
    }
    
    /**
     * 验证控制器是否存在,并实例化控制器
     * @param $app
     * @param array $config
     */
    private static function getController($app) {
        $controller_name = 'Controller_' . ucfirst($app['controller']);
        if (class_exists($controller_name)) {
            return new $controller_name();
        } else {
            header('HTTP/1.1 404 Not Found');
            exit;
        }
    }

    /**
     * 验证控制器中方法是否存在并调用
     * @param $controller
     * @param string $app
     */
    private static function getAction($controller, $app) {
        if (method_exists($controller, $app['action'])) {
            $controller->{$app['action']}();
        } else {
            header('HTTP/1.1 404 Not Found');
            exit;
        }
    }
}