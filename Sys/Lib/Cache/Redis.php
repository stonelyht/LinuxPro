<?php
Class Sys_Lib_Cache_Redis {

    /**
     * 连接对象
     * @var type 
     */
    protected $_handler = array();
    
    /**
     * 事物对象
     * @var type 
     */
    protected $_TRANSCATION = null;
    
    /**
     * 连接模式
     * @var type 
     */
    protected $_connMode;

    /**
     * 连接节点
     * @var type 
     */
    protected $_connNode;
    
    /**
     * 当前节点
     * @var type 
     */
    protected $_currNode;
    
    /**
     * 操作模式
     * @var type 
     */
    protected $operate;

    /**
     * 超时时间
     * @var type 
     */
    private $_timeout = 5;
    
    /**
     * hash一致性类
     * @var type 
     */
    private $_hash = null;

    /**
     * 构造函数
     * @param array $config
     */
    public function __construct($config) {
        if (!$config['conn'])
            throw new Sys_Lib_Exception('redis connection error');
        $mode = $config['mode'];
        $conn = $config['conn'];
        switch ($mode) {
            case 'normal' :
                $this->_setNormal($conn);
                break;
            case 'cluster' :
                $this->_setCluster($conn);
                break;
            case 'replication' :
                $this->_setReplication($conn);
                break;
            default :
                throw new Sys_Lib_Exception('redis config mode error');
        }
    }
    
    /**
     * 设置节点
     * @param string $key
     */
    private function _setNode($key) {
        if ($this->_connMode == 'cluster') {
            $this->_getClutserNode($key);
        } elseif ($this->_connMode == 'replication') {
            $this->_getReplicationNode();
        }
    }
    
    /**
     * 设置普通模式
     * @param string $conn
     */
    private function _setNormal($conn) {
        $this->_connMode = 'normal';
        $this->_connNode = $conn;
        $this->_currNode = $conn[0];
    }
    
    /**
     * 设置集群模式
     * @param array $conn
     */
    private function _setCluster($conn) {
        $this->_connMode = 'cluster';
        $this->_connNode = $conn;
        $this->_hash = new Sys_Lib_Hash();
        $this->_hash->addTargets($conn);
    }
    
    /**
     * 设置主从模式
     * @param array $conn
     */
    private function _setReplication($conn) {
        $this->_connMode = 'replication';
        $this->_connNode = $conn;
    }
    
    /**
     * 获取集群模式节点
     * @param string $key
     */
    private function _getClutserNode($key) {
        $this->_currNode = $this->_hash->lookup($key);
    }
    
    /**
     * 获取主从模式节点
     */
    private function _getReplicationNode() {
        static $node;
        if ($node) {
            $this->_currNode = $node;
            return;
        }
        if (!$this->_connNode)
            throw new Sys_Lib_Exception('redis config connection error');
        //当只有主节点或不需要从节点时设置为主节点
        if (count($this->_connNode) == 1 || $this->operate != 'slave') {
            $this->_currNode = $this->_connNode[0];
            return;
        }
        //删除主节点
        array_shift($this->_connNode);
        $randKey = array_rand($this->_connNode);
        $this->_currNode = $this->_connNode[$randKey];
        $node = $this->_currNode;
        return;
    }
    
    /**
     * 连接redis
     * @param string $key
     */
    private function _connect($key) {
        $this->_setNode($key);
        if (!$this->_handler[$this->_currNode]) {
            list($host, $port) = explode(':', $this->_currNode);
            try {
                $redis = new Redis();
                $redis->connect($host, $port, $this->_timeout);
                $this->_handler[$this->_currNode] = $redis;
            } catch (RedisException $e) {
                throw $e;
            }
        }
        return $this->_handler[$this->_currNode];
    }
    
    /**
     * 判断某个key是否存在
     * @param mixed $key
     * @return bool
     */
    public function exists($key) {
        $this->operate = 'slave';
        $cache = $this->_connect($key);
        return $cache->exists($key);
    }
    
    /**
     * 查询某个key的生存时间
     * @param mixed $key
     * @return int
     */
    public function ttl($key) {
        $this->operate = 'slave';
        $cache = $this->_connect($key);
        return $cache->ttl($key);
    }
    
    /**
     * 删除某个key
     * @param mixed $key
     * @return bool
     */
    public function delete($key) {
        $cache = $this->_connect($key);
        return $cache->set($key, $val);
    }
    
    /**
     * 对某个key进行更名
     * 集群操作时改名后的key规则必须一致
     * @param string $key
     * @param string $newkey
     * @return bool
     */
    public function rename($key, $newkey){
        $cache = $this->_connect($key);
        $cache->rename($key, $newkey);
    }
    
    /**
     * 设置某个key的过期时间(秒),
     * (EXPIRE bruce 1000：设置bruce这个key1000秒后系统自动删除)
     * @param string $key
     * @param int $ttl
     * @return bool
     */
    public function setExpire($key, $ttl) {
        $cache = $this->_connect($key);
        return $cache->expire($key, $ttl);
    }
    
    /**
     * 设置某个key的过期时间(秒)
     * @param string $key
     * @param int $time 时间戳
     * @return bool
     */
    public function setExpireAt($key, $time) {
        $cache = $this->_connect($key);
        return $cache->expireAt($key, $time);
    }
    
    //+++-------------------------string-------------------------+++//
    
    /**
     * string set
     * @param mixed $key
     * @param mixed $val
     * @return bool
     */
    public function set($key, $val, $ttl = 0) {
        $cache = $this->_connect($key);
        if ($ttl > 0) {
            return $cache->setex($key, $ttl, $val);
        } else {
            return $cache->set($key, $val);
        }
    }
    
    /**
     * string get
     * @param mixed $key
     * @return mixed
     */
    public function get($key) {
        $this->operate = 'slave';
        $cache = $this->_connect($key);
        return $cache->get($key);
    }
    
    /**
     * key值自增
     * @param mixed $key
     * @param int $val
     * @return int
     */
    public function incr($key, $val) {
        $cache = $this->_connect($key);
        return $cache->incrBy($key, intval($val));
    }
    
    /**
     * key值自减
     * @param mixed $key
     * @param int $val
     * @return int
     */
    public function decr($key, $val) {
        $cache = $this->_connect($key);
        return $cache->decrBy($key, intval($val));
    }
    
    //+++-------------------------hash-------------------------+++//

    /**
     * 将key->value写入hash表中
     * @param mixed $key
     * @param mixed $hkey
     * @param mixed $hval
     * @return bool
     */
    public function hSet($key, $hkey, $hval) {
        $cache = $this->_connect($key);
        return $cache->hSet($key, $hkey, $hval);
    }
    
    /**
     * 将多个key->value写入hash表中
     * @param mix $key
     * @param array $data
     * @return array
     */
    public function hMSet($key, $data) {
        if (!is_array($data))
            return false;
        $cache = $this->_connect($key);
        return $cache->hMset($key, $data);
    }

    /**
     * 获取hash表中某个key的数据
     * @param mixed $key
     * @param mixed $hkey
     * @return array
     */
    public function hGet($key, $hkey) {
        $this->operate = 'slave';
        $cache = $this->_connect($key);
        return $cache->hGet($key, $hkey);
    }
    
    /**
     * 获取hash表中多个key的数据
     * @param mix $key
     * @param array $hkeys
     * @return array
     */
    public function hMGet($key, $hkeys) {
        $this->operate = 'slave';
        $cache = $this->_connect($key);
        $cache->hMget($key, $hkeys);
    }
    
    /**
     * 获取hash表中所有key的数据
     * @param mixed $key
     * @return array
     */
    public function hGetAll($key) {
        $this->operate = 'slave';
        $cache = $this->_connect($key);
        return $cache->hGetAll($key);
    }
    
    /**
     * hash delete
     * @param mixed $key
     * @param mixed $hkey
     * @return bool
     */
    public function hDel($key, $hkey) {
        $cache = $this->_connect($key);
        return $cache->hDel($key, $hkey);
    }
    
    /**
     * 查询hash表中某个key是否存在
     * @param mixed $key
     * @param mixed $hkey
     * @return bool
     */
    public function hExists($key, $hkey) {
        $this->operate = 'slave';
        $cache = $this->_connect($key);
        return $cache->hExists($key, $hkey);
    }
    
    /**
     * 自增hash表中某个key的值
     * @param mixed $key
     * @param mixed $hkey
     * @param int $val
     * @return int
     */
    public function hIncr($key, $hkey, $val) {
        $cache = $this->_connect($key);
        return $cache->hIncrBy($key, $hkey, intval($val));
    }
    
    /**
     * 获取hash表中元素个数
     * @param mix $key
     * @return int
     */
    public function hSize($key) {
        $this->operate = 'slave';
        $cache = $this->_connect($key);
        return $cache->hLen($key);
    }
    
    //+++-------------------------set-------------------------+++//
    
    /**
     * 向名称为key的set中添加元素value
     * @param mix $key
     * @param mix $val
     * @return bool
     */
    public function sSet($key, $val) {
        $cache = $this->_connect($key);
        return $cache->sAdd($key, $val);
    }
    
    /**
     * 返回名称为key的set的所有元素
     * @param type $key
     * @return array
     */
    public function sGetAll($key) {
        $this->operate = 'slave';
        $cache = $this->_connect($key);
        return $cache->sGetMembers($key);
    }
    
    /**
     * 随机返回名称为key的set中一个元素
     * @return mix
     */
    public function sGetRand($key) {
        $this->operate = 'slave';
        $cache = $this->_connect($key);
        return $cache->sRandMember($key);
    }
    
    /**
     * 删除名称为key的set中的元素value
     * @param mix $key
     * @param mix $val
     * @return bool
     */
    public function sDel($key, $val) {
        $cache = $this->_connect($key);
        return $cache->srem($key, $val);
    }
    
    /**
     * 名称为key的集合中查找是否有value元素
     * @param mix $key
     * @param mix $val
     * @return bool
     */
    public function sExists($key, $val) {
        $this->operate = 'slave';
        $cache = $this->_connect($key);
        return $cache->sismember($key, $val);
    }
    
    /**
     * 返回名称为key的set的元素个数
     * @param mix $key
     * @return int
     */
    public function sSize($key) {
        $this->operate = 'slave';
        $cache = $this->_connect($key);
        return $cache->sSize($key);
    }
    
    //+++-------------------------other-------------------------+++//
    
    /**
     * 监测一个key,一般用于事务前操作
     * @param string $key
     */
    public function watch($key) {
        $cache = $this->_connect($key);
        $cache->watch($key);
    }
    
    /**
     * 取消对所有key的监控
     */
    public function unwatch($key) {
        $cache = $this->_connect($key);
        $cache->unwatch();
    }
    
    /**
     * 开始进入事务操作
     * @param bool $usePipelining 是否使用PIPELINE管道
     * @return object $return 事务对象
     */
    public function tranStart($usePipelining = false) {
        $cache = $this->_connect($key);
        $this->_TRANSCATION = $usePipelining ? $cache->multi(Redis::PIPELINE) : $cache->multi();
    }

    /**
     * 提交完成事务
     * @return boolean
     */
    public function tranCommit() {
        return $this->_TRANSCATION->exec();
    }

    /**
     * 回滚事务
     * @return boolean 事务执行失败 回滚操作
     */
    public function tranRollback() {
        return $this->_TRANSCATION->discard();
    }

}
