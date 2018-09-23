<?php

class Sys_Core_Application {

    /**
     * 初始入口
     */
    public static function run() {
        Sys_Core_Dispatcher::dispatch();
    }

    /**
     * 自定义错误，存储日志
     * 错误级别 大于等于 警告级别
     * @throws
     */
    public static function initError() {
        function error_fatal() {
            if (is_null($e = error_get_last()) === false) {
                if ($e['type'] == 1) {
                    //把错误信息写文件
                    writeErrorInFile('Fatal Error', $e['message'], $e['file'], $e['line']);
                    exit;
                }
            }
        }
        function error($error_level, $error_msg, $file, $line) {
            $EXIT = FALSE;
            switch ($error_level) {
                //提醒级别
                case E_NOTICE:
                    $error_type = 'Notice';
                    break;
                case E_USER_NOTICE:
                    $error_type = 'User Notice';
                    break;
                //警告级别
                case E_WARNING:
                case E_USER_WARNING:
                    $error_type = 'Warning';
                    break;
                //错误级别
                case E_ERROR:
                case E_USER_ERROR:
                    $error_type = 'Fatal Error';
                    $EXIT = TRUE;
                    break;
                //其他未知错误
                default:
                    $error_type = 'Unknown';
                    $EXIT = TRUE;
                    break;
            }
            writeErrorInFile($error_type, $error_msg, $file, $line);
            //遇到致命错误程序停止
            if ($error_type == 'Fatal Error') {
                exit;
            }
        }
        set_error_handler('error', E_ALL ^ E_NOTICE);
        register_shutdown_function('error_fatal');
    }
}