<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/30
 * Time: 14:49
 */

class Controller_Queue{

    /**
     * @var Redis
     */
    protected $_redis;

    public function __construct(){
        $this->_redis = new Redis();
        $this->_redis->connect('47.106.238.224','6379');
        $this->_redis->auth('747596');
    }

    public function pushList(){
        $key  = 'Task:';
        $rand = rand(1001,9999);
        $drand = rand(10000,99999).time().md5(time()).':Task';
        $this->_redis->rPush($key.$rand,$drand);
    }
}