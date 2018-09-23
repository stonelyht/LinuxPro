<?php

/**
 * Curl网络传输
 */
class Sys_Lib_Network_Curl {

    /**
     * Curl handle
     * @var curl 
     */
    private $_handle;

    /**
     * CurlError error
     * @var curlError
     */
    private $_error;

    public function __construct($data) {
        $this->init($data);
    }

    private function init($data) {
        //初始化curl
        $curl = curl_init();
        if(isset($data['params']['data']['file'])){
            foreach ($data['params']['data']['file'] as $key => $value) {
                $data['params'][$key] = $value;
            }
        }
        //以get或以post方式发送请求
        if (isset($data['method']) && $data['method'] == 'get') {
            curl_setopt($curl, CURLOPT_URL, "{$data['url']}?{$data['params']}");
        } else {
            curl_setopt($curl, CURLOPT_URL, $data['url']);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:'));
            curl_setopt($curl, CURLOPT_POST, true);
            if(isset($data['params']['data']['file'])){
                unset($data['params']['data']['file']);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data['params']);
            }else{
                curl_setopt($curl, CURLOPT_POSTFIELDS, !is_array($data['params']) ? $data['params'] : http_build_query($data['params']));
            }
        }
        if (isset($data['useragent'])) {
            curl_setopt($curl, CURLOPT_USERAGENT, $data['useragent']);
        }
        if (isset($data['cainfo'])) {
            curl_setopt($curl, CURLOPT_USERAGENT, $data['cainfo']);
        }
        if (isset($data['ssl'])) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        //设置header
        curl_setopt($curl, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //设置等待时间
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
        //运行curl
        $this->_handle = curl_exec($curl);
        //获取错误信息
        $this->_error = curl_error($curl);
        //关闭curl
        curl_close($curl);
    }

    public function getData() {
        return $this->_handle;
    }

    public function getError() {
        return $this->_error;
    }

}