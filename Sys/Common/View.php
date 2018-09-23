<?php
class View {
    
    /**
     * 输出变量
     * @var type 
     */
    static $out = array();
    
    /**
     * 返回变量
     * @var type 
     */
    static $response = array();
    
    /**
     * 指定输出变量
     * @param string $key
     * @param mixed $value
     */
    public static function assign($key, $value) {
        self::$out[$key] = $value;
    }
    
    /**
     * 设置返回变量
     * @param string $key
     * @param mixed $value
     */
    public static function response($key, $value) {
        self::$response[$key] = $value;
    }
    
    /**
     * 获取返回变量
     */
    public static function getResponse() {
        return self::$response;
    }
    
    /**
     * 输出所有
     * @param mixed $key
     */
    public static function display() {
        return self::$out;
    }
    
    /**
     * 输出错误
     * @param mixed $key
     */
    public static function error($key, $value) {
        self::assign('code', $key);
        self::assign('errer_msg', getVal($value));
        exit;
    }
}

