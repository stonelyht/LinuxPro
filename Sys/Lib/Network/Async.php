<?php

/**
 * Description of Async
 *
 * @author hujun
 */
class Sys_Lib_Network_Async {

    public static function Post($url, $data) {
        $post = http_build_query($data);
        $len = strlen($post);
        $url_info = parse_url($url);
        $host = $url_info['host'];
        $path = $url_info['path'];
        $fp = fsockopen($host, 80, $errno, $errstr, 30);
        if (!$fp) {
//            echo "$errstr ($errno)\n";
        } else {
            $out = "POST $path HTTP/1.1\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Content-type: application/x-www-form-urlencoded\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Content-Length: $len\r\n";
            $out .= "\r\n";
            $out .= $post . "\r\n";
            fwrite($fp, $out);
            fclose($fp);
        }
    }

    public static function Get($url, $data) {
        $get = http_build_query($data);
        $url_info = parse_url($url);
        $host = $url_info['host'];
        $path = $url_info['path'];
        $path .= "?$get";
        $fp = fsockopen($host, 80, $errno, $errstr, 30);
        if (!$fp) {
//            echo "$errstr ($errno)\n";
        } else {
            $out = "GET " . $path . " HTTP/1.1\r\n";
            $out .= "Host: " . $host . "\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cookie: \r\n\r\n";
            fwrite($fp, $out);
            fclose($fp);
        }
    }

}