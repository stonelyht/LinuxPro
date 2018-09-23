<?php

class Sys_Cache_Redis extends Sys_Lib_Cache_Redis {

    /**
     * 单例
     * @var Sys_Cache_Redis
     */
    private static $_instance = array();

    /**
     * 构造函数
     * @param array $config 
     */
    public function __construct($config) {
        parent::__construct($config);
    }

    /**
     * 获得单例实例
     * @param array $config
     * @return Sys_Cache_Redis
     */
    public static function getInstance($config) {
        $res = self::$_instance;
        $resHash = md5(json_encode($config));
        if (!(isset($res[$resHash]) && $res[$resHash] instanceof self)) {
            self::$_instance[$resHash] = new self($config);
        }
        return self::$_instance[$resHash];
    }
    
}