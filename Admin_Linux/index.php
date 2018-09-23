<?php

/*
 * 入口
 * _______________        ______________
 * |  index.php  | -----> | Dispatcher |
 * ~~~~~~~~~~~~~~~        ~~~~~~~~~~~~~~
 */

/* 显示错误信息级别 */
error_reporting(E_ALL ^ E_NOTICE);

/* 显示错误信息（正式环境关闭） */
ini_set('display_errors', 1);

/* 防止socket断开连接 */
ini_set('default_socket_timeout', -1);

/* 记录开始时间 */
define('S_TIME', microtime(true));

/* 根目录 */
define('ROOTDIR', dirname(dirname(__FILE__)));

/* 项目名称 */
define('APP_NAME', 'Admin_Linux');

/* 项目目录 */
define('APP_PATH', ROOTDIR . '/' . APP_NAME);

/* 日志目录 */
define('LOG_PATH', APP_PATH . '/Log');

/* 系统目录 */
define('SYS_PATH', ROOTDIR . '/Sys');

/* 基础设施目录 */
define('LIB_PATH', ROOTDIR . '/Sys/Lib');

/* 文件缓存目录 */
define('RUNTIME_PATH', APP_PATH . '/Runtime');

/* 公共类库 */
include (SYS_PATH . '/Common/Common.php');

/* 公共函数库 */
include (SYS_PATH . '/Common/Func.php');

/* 视图库 */
include (SYS_PATH . '/Common/View.php');

/* Error */
include (SYS_PATH . '/Common/Error.php');

/* UTF-8 */
header('Content-Type: text/html;charset=utf-8');

/* 类自动加载 */
Common::registerAutoLoad();

/* 设置时区 */
$timeZone = conf('default', 'TIME_ZONE');
date_default_timezone_set($timeZone);

/* 错误捕获 */
Sys_Core_Application::initError();

/* 程序入口 */
Sys_Core_Application::run();