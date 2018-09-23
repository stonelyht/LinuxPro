<?php
class Error {
    
    // 记录错误
    public static function Set($error) {
        Sys_Lib_Cache_Array::set('error', $error);
    }
    
    //获取错误
    public static function Get() {
        return Sys_Lib_Cache_Array::get('error');
    }
}