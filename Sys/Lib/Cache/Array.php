<?php

class Sys_Lib_Cache_Array extends ArrayObject {

    /**
     * 共享缓存对象
     * @var Driver_Cache_Array
     */
    private static $_registry = null;

    /**
     * 获得单例缓存对象
     * @return Driver_Cache_Array
     */
    public static function getInstance() {
        if (self::$_registry === null) {
            self::init();
        }
        return self::$_registry;
    }

    /**
     * 初始化
     * @return void
     */
    protected static function init() {
        self::$_registry = new self;
    }

    /**
     * 析构共享缓存
     * @returns void
     */
    public static function _unsetInstance() {
        self::$_registry = null;
    }

    /**
     * 读取
     * @param string $index
     * @return mixed
     */
    public static function get($index) {
        $instance = self::getInstance();
        return $instance->offsetGet($index);
    }

    /**
     * 设置
     * @param string $index
     * @param mixed $value
     * @return mixed
     */
    public static function set($index, $value) {
        $instance = self::getInstance();
        $instance->offsetSet($index, $value);
        return $value;
    }

    /**
     * 是否存在缓存
     * @param  string $index
     * @return boolean
     */
    public static function exists($index) {
        $instance = self::getInstance();
        return $instance->offsetExists($index);
    }

    /**
     * 构造一个ArrayObject
     * ARRAY_AS_PROPS 允许访问一个数组
     *
     * @param array $array data array
     */
    public function __construct($array = array()) {
        parent::__construct($array);
    }

}
