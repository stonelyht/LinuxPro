<?php

/**
 * 获取Config中某个文件内容
 * @param string $name 配置名
 * @param string $key  配置的Key
 */
function conf($name, $key) {
    static $conf = array();
    $file = ucfirst($name);
    if (!$conf[$file]) {
        $path = APP_PATH . "/Config/{$file}.php";
        $conf[$file] = require $path;
    }
    return !empty($key) ? getArrVal($conf[$file], $key, '') : $conf[$file];
}

/**
 * 获取语言包翻译
 * @param string $id
 * @param string $params
 */
function lang($id = null, $params = array()) {
    static $lang = array();
    if (!$lang) {
        $file = conf('default', 'LANGUAGE');
        $path = APP_PATH . "/Lang/{$file}.php";
        $lang = require $path;
    }
    //空参数返回所有定义
    if (!empty($id)) {
        $content = getArrVal($lang, $id, $id);
        if ($params) {
            $replace = array();
            foreach ($params as $k => $v) {
                $replace += array('{' . $k . '}' => $v);
            }
            $content = strtr($content, $replace);
        }
        return $content;
    }
}

/**
 * 获得IP地址
 * @return string
 */
function getIP() {
    $realip = getHttpVal('REMOTE_ADDR', 'SERVER');
    if (!$realip) {
        $realip = getenv("REMOTE_ADDR");
    }
    return $realip;
}

/**
 * 二维数组转一维数组，用于查询
 * e.x.  array(
 *          [0]=>array("id"=>1),
 *          [1]=>array("id"=>2),
 *          [2]=>array("id"=>3)
 *       )
 *       =====>
 *       array(1,2,3)
 * @param Array $array
 * @param String $key
 * @return Array
 */
function array_2dTo1d($array, $key = '') {
    $return = array();
    foreach ($array as $k => $value) {
        if (empty($key)) {
            $v = array_shift($value);
        } else {
            $v = $value[$key];
        }
        $return[$k] = $v;
    }
    return $return;
}

/**
 * 二维数据转一维数据，key为新的key，val为新的val
 * @param array $array
 * @param string $key
 * @param string $val
 */
function array_2dTo1d_kTv($array, $key, $val) {
    $return = array();
    foreach ($array as $v) {
        $return[$v[$key]] = $v[$val];
    }
    return $return;
}

/**
 * 二维数组以某一列为Key进行格式化
 * @param array $array
 * @param string $key
 * @return array
 */
function array_ValToKey($array, $key = '') {
    $return = array();
    foreach ($array as $value) {
        $return[$value[$key]] = $value;
    }
    return $return;
}

/**
 * 获取http数据，post，get，request
 * @param string $params
 * @param string $type
 */
function getHttpVal($params = '', $type = 'REQUEST') {
    $dataArr = array();
    switch (strtoupper($type)) {
        case 'REQUEST' :
            $dataArr = $_REQUEST;
            break;
        case 'POST' :
            $dataArr = $_POST;
            break;
        case 'GET' :
            $dataArr = $_GET;
            break;
        case 'SERVER' :
            $dataArr = $_SERVER;
            break;
        case 'SESSION' :
            $dataArr = $_SESSION;
    }
    return $params ? getArrVal($dataArr, $params) : $dataArr;
}

/**
 * 未知变量值
 * @param mixed $value
 */
function getVal($value, $default = '') {
    return $value ? $value : $default;
}

/**
 * 获取数组的值
 * @param array $array
 * @param mixed $value
 * @param mixed $default
 */
function getArrVal($array, $value, $default = '') {
    return isset($array[$value]) ? $array[$value] : $default;
}

/**
 * json编码转换
 * @param type $str
 * @return type
 */
function jsonCoding($str) {
    return preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $str);
}

function my_json_encode($arr) {
    //convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
    array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
    return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
}

/**
 * 字符串随机
 * @param int $size
 * @return string
 */
function stringRand($size = 8) {
    $code = '';
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    for ($i = 0; $i < $size; $i++) {
        $code .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $code;
}

/**
 * 把错误信息写入文件
 * @param string $error_type
 * @param string $error_msg
 * @param string $file
 * @param string $line
 * @param string $trace
 */
function writeErrorInFile($error_type, $error_msg, $file, $line, $trace = '') {
    $fp = fopen(APP_PATH . '/Log/error', 'a');
    $urlInfo = 'http://' . getHttpVal('HTTP_HOST', 'SERVER')
            . getHttpVal('PHP_SELF', 'SERVER') . ' ' . getHttpVal('route_id');
    $content = date("Y/m/d H:i:s", time()) .
            " cid:" . Sys_Lib_Cache_Array::get("Cid") .
            " $urlInfo " . Sys_Lib_Cache_Array::get('Params');
    $content .= "\r\n";
    if ($error_type == 'User Notice')
        $content .= "$error_type: $error_msg";
    else
        $content .= "$error_type: $error_msg in $file on line $line";
    if ($trace) {
        $content .= "\r\n";
        $content .= $trace;
    }
    $content .= "\r\n\r\n";
    fwrite($fp, $content);
    fclose($fp);
}

/*移动端判断*/
function isMobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}
