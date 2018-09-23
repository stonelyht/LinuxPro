<?php

/**
 * FsockOpen网络传输
 * 
 */
class Sys_Lib_Network_FsockOpen {

    /**
     * url地址
     * @var url 
     */
    public $url = '/';

    /**
     * 传输方式
     * @var method
     */
    public $method = 'get';

    /**
     * 传输数据
     * @var params
     */
    public $params = '';

    /**
     * 服务器地址
     * @var host
     */
    public $host = 'localhost';

    /**
     * 端口
     * @var port
     */
    public $port = '80';

    /**
     * 超时时间
     * @var timeout
     */
    public $timeout = '30';

    /**
     * 错误描述
     * @var errStr
     */
    public $errStr;

    /**
     * 错误编号
     * @var errno
     */
    public $errno;
    
    /**
     * 是否需要返回数据(false支持异步处理)
     * @var isReturn 
     */
    public $isReturn = true;
    
    /**
     * 返回数据
     * @var response
     */
    public $response;

    public function __construct() {
        
    }
    
    //发送数据请求
    public function send() {
        if ($this->method == 'GET' && strstr($this->url, '?') == 0) {
            $this->url = $this->url . '?' . $this->params;
        } else {
            $this->url = $this->url;
        }
        $sock = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        if (!$sock) {
            $this->errStr = $errstr;
            $this->errno = $errno;
            exit("fsockopen error $this->host:$this->port");
        }
        $out = $this->method . ' ' . $this->url . " HTTP/1.0\r\n";
        $out .= "Host: " . $this->host . "\r\n";
        if ($this->method == 'POST') {
            $out .= "Content-type: application/x-www-form-urlencoded\r\n";
            $out .= "Content-Length: " . strlen($this->params) . "\r\n";
            $out .= "\r\n";
            $out .= $this->params . "\r\n";
        }
        $out .= "Connection: close\r\n\r\n";
        fwrite($sock, $out);

        if ($this->isReturn==true) {
            $headers = '';
            while ($str = trim(fgets($sock, 4096)))
                $headers .= $str . '\n';
            $body = '';

            while (!feof($sock))
                $body .= fgets($sock, 4096);
            $this->response = $body;
        }
        fclose($sock);
    }

}