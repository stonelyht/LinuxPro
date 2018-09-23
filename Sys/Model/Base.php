<?php
/**
 * Description of Base
 *
 */
class Sys_Model_Base {

    /**
     * 数据库表名
     * @var string
     */
    protected $_table = null;

    /**
     * 主键
     * @var string
     */
    protected $_pk = "id";

    /**
     * 必要查询key
     * @var string
     */
    protected $_selectKey = "character_id";

    protected $_sync_static = "sync_static";

    /**
     * DB Handle
     * @var Sys_Db_Mysql
     */
    protected $_db = null;

    /**
     * Cache Handle
     * @var Sys_Cache_Redis
     */
    protected $_cache = null;

    /**
     * mysql配置
     * @var string
     */
    protected $_mysql_use = '';

    /**
     * redis配置
     * @var string
     */
    protected $_redis_use = '';

    /**
     * redis键名
     * @var string
     */
    protected $_redis_key = '';

    /**
     * 缓存类型
     * @var string
     */
    protected $_redis_type = '';

    /**
     * 构造函数
     * @throws string
     */
    public function __construct() {
        $this->initMysql();
        if ($this->_redis_use) {
            $this->initRedis();
        }
    }

    /**
     * Mysql实例化
     * @param array $config
     */
    private function initMysql() {
        if ($config = conf('mysql', $this->_mysql_use)) {
            $this->_db = Sys_Db_Mysql::getInstance($config);
        }
    }

    /**
     * Redis实例化
     * @param array $config
     */
    private function initRedis() {
        if ($config = conf('redis', $this->_redis_use)) {
            $this->_cache = Sys_Cache_Redis::getInstance($config);
        }
    }

    public function getDb() {
        return $this->_db;
    }

    public function getCache() {
        return $this->_cache;
    }

    public function getTable() {
        return $this->_table;
    }

    /**
     * 获取redis的键名
     * @param string $id
     */
    public function getRedisKey($id) {
        return strtr($this->_redis_key, array('*' => $id));
    }

    /**
     * 获取单个数据
     * string型：单个字段
     * hash型：单条记录
     * @param mixed $id
     */
    public function getRedisFetchOne($id, $key) {
        $redisKey = $this->getRedisKey($id);
        if ($this->_redis_type == 'string')
            $info = $this->getCache()->get($redisKey);
        elseif ($this->_redis_type == 'hash')
            $info = $this->getCache()->hget($redisKey, $key);
        if ($info)
            $data = json_decode($info, true);
        else {
            $dbData = $this->selectDbBySelectKey($id);
            $data = getVal($dbData[0], array());
            $this->setRedisFetchOne($id, $key, $data);
        }
        return $data;
    }

    /**
     * 设置单个数据
     * @param mixed $id
     * @param mixed $key
     * @param array $value
     */
    public function setRedisFetchOne($id, $key, $value) {
        $redisKey = $this->getRedisKey($id);
        $redisVal = json_encode($value);
        if ($this->_redis_type == 'string')
            $this->getCache()->set($redisKey, $redisVal);
        elseif ($this->_redis_type == 'hash')
            $this->getCache()->hset($redisKey, $key, $redisVal);
        $this->getCache()->setExpire($redisKey, 86400);
    }

    /**
     * 数据插入数据库
     * @param array $data
     * @return boolean|int
     */
    public function insertDb($data = array()) {
        $res = $this->getDb()
                ->from($this->getTable())
                ->insert($data);
        $this->addLog();
        return $res;
    }

    /**
     * 数据插入数据库
     * @param array $data
     * @return boolean|int
     */
    public function insertDbBatch($data = array()) {
        $res = $this->getDb()
                ->from($this->getTable())
                ->insertBatch($data);
        $this->addLog();
        return $res;
    }

    /**
     * 根据主键更新数据
     * @param int $id
     * @param array $data
     * @return boolean|int
     */
    public function updateDbById($id, $data = array()) {
        $res = $this->getDb()
                ->from($this->getTable())
                ->where('and', array($this->_pk, '=', $id))
                ->update($data);
        $this->addLog();
        return $res;
    }

    public function updateDbByIds($id_arr, $data = array()) {
        $ids = implode(',', $id_arr);
        $res = $this->getDb()
            ->from($this->getTable())
            ->where('and', array($this->_pk, 'in', "($ids)"))
            ->update($data);
        $this->addLog();
        return $res;
    }

    /**
     * 根据主键删除数据
     * @param int $id
     * @return boolean|int
     */
    public function deleteDbById($id) {
        $res = $this->getDb()
                ->from($this->getTable())
                ->where('and', array($this->_pk, '=', $id))
                ->delete();
        $this->addLog();
        return $res;
    }

    //查询所有数据
    public function selectAll() {
        $res = $this->getDb()
                ->from($this->getTable())
                ->fetchAll();
        return getVal($res, array());
    }

    //查询需要导入的线上地址
    public function selectSyncAll($id) {
        $res = $this->getDb()
                ->from($this->getTable())
                ->where('and', array($this->_sync_static, '=', $id))
                ->fetchAll();
        return getVal($res, array());
    }

    /**
     * 根据主键查询数据
     * @param int $id
     */
    public function selectDbById($id) {
        $res = $this->getDb()
                ->from($this->getTable())
                ->where('and', array($this->_pk, '=', $id))
                ->fetchOne();
        return getVal($res, array());
    }

    /**
     * 根据查询字段查询数据
     * @param mix $key
     */
    public function selectDbBySelectKey($key) {
        $res = $this->getDb()
                ->from($this->getTable())
                ->where('and', array($this->_selectKey, '=', $key))
                ->fetchAll();
        return getVal($res, array());
    }

    public function getDataByConds($where_list=array(), $limit_arr=array(), &$total_records=null, $order='') {
        $db = $this->getDb()->from($this->_table);
        $cond_arr = array();
        foreach($where_list as $str)
        {
            $arr = explode(' ', $str, 4);
//            $db->where('and', array('operator_id', '=', '4001'));
            $db->where($arr[0], array($arr[1], $arr[2], $arr[3]));
            $cond_arr[] = array($arr[0], array($arr[1], $arr[2], $arr[3]));
        }
        $db->select('count(*) as records');
        $data = $db->fetchOne();
        $total_records = $data['records'];

        $db->select('*')->from($this->_table);
        foreach($cond_arr as $arr)
        {
            $db->where($arr[0], $arr[1]);
        }
        if(!empty($limit_arr))
        {
            $db->limit($limit_arr[0], $limit_arr[1]);
        }
        if (!empty($order))
        {
            $db->order($order);
        }
        return $db->fetchAll();
    }

    /**
     * 根据集群规则字段查询数据
     * @param mix $value
     */
    public function selectDbByClusterRule($value) {
        $ruleArr = conf('mysql', 'rules');
        $key = getArrVal($ruleArr, $this->getTable());
        if (!$key) {
            //未找到此数据表对应的集群规则
            throw new Sys_Lib_Exception('not find mysql cluster by table ' . $this->getTable());
        }
        $res = $this->getDb()
                ->from($this->getTable())
                ->where('and', array($key, '=', $value))
                ->fetchAll();
        return getVal($res, array());
    }

    /**
     * 通过条件获取表总数
     * @param array $where
     */
    public function getCounts($where = array()) {
        $db = $this->getDb()
            ->from($this->getTable())
            ->select('count(*) as records');
        if (!empty($where)) {
            foreach ($where as $v) {
                $db->where($v['oper'], $v['cond']);
            }
        }
        $data = $db->fetchOne();
        return $data['records'];
    }

    //操作日志
    public function addLog($params=array()) {
        
    }
    
}
