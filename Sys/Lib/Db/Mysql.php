<?php
Class Sys_Lib_Db_Mysql {

    /**
     * 连接对象
     * @var PDOStatement
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
     * hash一致性类
     * @var type
     */
    private $_hash = null;

    /**
     * 集群规则
     * @var type
     */
    private $_rule = null;

    /**
     * 集群节点
     * @var type
     */
    public $node = 0;
    
    /**
     * 数据库名
     * @var string 
     */
    private $_database;

    /**
     * sql语句构建
     * @var array
     */
    private $_query = array();

    /**
     * 构造函数
     * @param array $config
     */
    public function __construct($config) {
        if (!$config['conn'])
            throw new Sys_Lib_Exception('mysql connection error');
        $mode = $config['mode'];
        $conn = $config['conn'];
        switch ($mode) {
            case 'normal' :
                $this->_setNormal($conn);
                break;
            case 'cluster' :
                $this->_setCluster($conn, $config['rule']);
                break;
            case 'replication' :
                $this->_setReplication($conn);
                break;
            default :
                throw new Sys_Lib_Exception('mysql config mode error');
        }
    }

    /**
     * 设置节点
     * @param string $data
     */
    private function _setNode($data) {
        if ($this->_connMode == 'cluster') {
            $this->_getClutserNode($data);
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
     * @param array $rule
     */
    private function _setCluster($conn, $rule) {
        $this->_connMode = 'cluster';
        $this->_connNode = $conn;
        $this->_hash = new Sys_Lib_Hash();
        $this->_hash->addTargets($conn);
        $this->_rule = $rule;
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
     * @param array $data
     */
    private function _getClutserNode($data) {
        $ruleKey = $this->_rule[$this->_query['from']];
        $key = $data[$ruleKey];
        if (!$key)
            throw new Sys_Lib_Exception('mysql cluster not found hash key');
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
     * 获取集群规则
     */
    public function getClusterRule() {
        $ruleArr = conf('mysql', 'rules');
        $ruleKey = getArrVal($ruleArr, $this->_query['from']);
        return $ruleKey;
    }

    /**
     * 连接mysql
     * @param string $data
     */
    private function _connect($data = array()) {
        $this->_setNode($data);
        if (!$this->_handler[$this->_currNode]) {
            $node = explode(':', $this->_currNode);
            $option = array(
                PDO::ATTR_CASE => PDO::CASE_LOWER,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                PDO::ATTR_AUTOCOMMIT => 1,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                //PDO::ATTR_PERSISTENT => true,
            );
            try {
                $dsn = "mysql:host={$node[0]};port={$node[1]};dbname={$node[4]}";
                $this->_database = $node[4];
                $this->_handler[$this->_currNode] = new PDO($dsn, $node[2], $node[3], $option);
            } catch (PDOException $e) {
                throw $e;
            }
        }
    }
    
    /**
     * 连接对象
     */
    public function handler() {
        $this->_connect();
        return $this->_handler[$this->_currNode];
    }

    /**
     * 初始化query
     */
    public function initQuery() {
        $this->_query = array();
    }

    /**
     * 有返回的查询操作
     * @param string $sql
     * @return PDOStatement
     */
    private function query($sql) {
        $this->initQuery();
        return $this->_handler[$this->_currNode]->query($sql);
    }

    /**
     * 无返回的执行操作
     * @param string $sql
     * @return mix
     */
    private function exec($sql) {
        $this->initQuery();
        $this->_connect();
        return $this->_handler[$this->_currNode]->exec($sql);
    }

    /**
     * 获得最后插入主键
     * @return int
     */
    public function lastInsertId() {
        return $this->_handler[$this->_currNode]->lastInsertId();
    }

    /**
     * 链式操作：From
     * @param string $from
     */
    public function from($from) {
        $this->_query['from'] = $from;
        return $this;
    }

    /**
     * 链式操作：Select
     * @param array|string $select
     */
    public function select($select) {
        if (is_array($select)) {
            array_walk($select, function(&$value, &$key) {
                        $value = "$value";
                    })
            ;
            $select = implode(' , ', $select);
        }
        $this->_query['select'] = $select;
        return $this;
    }

    /**
     * 链式操作：Join
     * @param array|string $join
     */
    public function join($join) {
        if (is_array($join)) {
            $this->_query['join'] .= ' LEFT JOIN '.$join[0].' ON '.$join[1].' = '.$join[2].' ';
        } else if (is_string($join)) {
            $this->_query['join'] = $join;
        }
        return $this;
    }

    /**
     * 链式操作：Where
     * @var array('id', '>', '1')
     * @param array $where
     */
    public function where($oper, $where) {
        $this->_query['where'][] = array(
            'oper' => $oper,
            'cond' => $where
        );
        return $this;
    }

    /**
     * 链式操作：Group
     * @param array|string $group
     */
    public function group($group) {
        if (is_array($group)) {
            array_walk($group, function(&$value, &$key) {
                        $value = "`$value`";
                    })
            ;
            $group = implode(' , ', $group);
        }
        if ($group)
            $this->_query['group'] = 'GROUP BY ' . $group;
        return $this;
    }

    /**
     * 链式操作：Order
     * @param array|string $order
     */
    public function order($order) {
        if (is_array($order)) {
            array_walk($order, function(&$value, &$key) {
                        $value = "`$key` $value";
                    })
            ;
            $order = implode(' , ', $order);
        }
        if ($order)
            $this->_query['order'] = 'ORDER BY ' . $order;
        return $this;
    }

    /**
     * 链式操作：Limit
     * @param int $skip
     * @param int $take
     */
    public function limit($skip, $take) {
        $this->_query['limit'] = "LIMIT $skip,$take";
        return $this;
    }

    /**
     * 链式操作：Insert
     * @param array $data
     * @return int
     */
    public function insert($data) {
        $this->_connect($data);
        foreach ($data as $k => $d) {
            $set[] = "`$k` = '".addslashes($d)."'";
        }
        $set = implode(' , ', $set);
        $sql = "INSERT INTO {$this->_query['from']} SET $set";
        $this->exec($sql);
        return $this->lastInsertId();
    }

    /**
     * 链式操作：批量Insert
     * @param array $code_list
     * @return int
     */
    public function insertBatch($code_list) {
        $this->_connect($code_list);
        $vals = array();
        foreach($code_list as $data){
            foreach ($data as $k => $d) {
                $data[$k] = "'".addslashes($d)."'";
            }
            $vals[] = '('.implode(' , ',$data).')';
        }
        $keys = array_keys($data);
        $str = ' (`'.implode('` , `',$keys).'`) VALUES '.implode(' , ',$vals);
        $sql = "INSERT INTO {$this->_query['from']} ".$str;
        $this->exec($sql);
        return true;
    }

    /**
     * 链式操作：Update
     * @param array $data
     * @param bool $all
     * @return int
     */
    public function update($data, $all = false) {
        if (is_array($data)) {
            foreach ($data as $k => $d) {
                $set[] = "`$k` = '".addslashes($d)."'";
            }
            $set = implode(' , ', $set);
        } else if (is_string($data)) {
            $set = $data;
        }
        $sql = "UPDATE " . $this->_from() . " SET $set " . $this->_where($all);
        return $this->exec($sql);
    }

    /**
     * 链式操作：Delete
     * @param bool $all
     * @return int
     */
    public function delete($all = false) {
        $sql = "DELETE FROM " . $this->_from() . ' ' . $this->_where($all);
        return $this->exec($sql);
    }

    /**
     * 链式操作：Delete All
     * @return int
     */
    public function deleteAll() {
        $sql = "DELETE FROM " . $this->_from();
        return $this->exec($sql);
    }

    /**
     * 链式操作：FetchAll
     * @param string $sql
     * @return array
     */
    public function fetchAll() {
        $return = $this->_makeSql()->fetchAll();
        return $return;
    }

    /**
     * 链式操作：FetchOne
     * @param string $sql
     * @return array
     */
    public function fetchOne() {
        $return = $this->_makeSql()->fetch();
        return $return;
    }
    
    /**
     * 获取表字段名
     * @return type
     */
    public function fetchColumn() {
        $stmt = $this->handler()->prepare('DESC ' . $this->_from());  
        $stmt->execute();
        $table_fields = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $table_fields;
    }
    
    /**
     * 获取所有表
     * @return type
     */
    public function tableList() {
        $tables = array();
        $stmt = $this->handler()->prepare("SHOW TABLES");
        $stmt->execute();
        while ($r = $stmt->fetch()) {
            $tables[] = $r["tables_in_$this->_database"];
        }
        return $tables;
    }

    /**
     * 组合sql语句
     * @return PDOStatement
     */
    private function _makeSql() {
        $select = getArrVal($this->_query, 'select', '*');
        $from = $this->_from();
        $where = $this->_where();
        $group = getArrVal($this->_query, 'group');
        $order = getArrVal($this->_query, 'order');
        $limit = getArrVal($this->_query, 'limit');
        $join = getArrVal($this->_query, 'join');
        $sql = "SELECT $select FROM `$from` $join $where $group $order $limit";
        return $this->query($sql);
    }

    /**
     * 解析from
     */
    private function _from() {
        $from = $this->_query['from'];
        if (!$from)
            throw new Sys_Lib_Exception('not found mysql table');
        return $from;
    }

    /**
     * 解析where
     * @param bool $allowNo
     */
    private function _where($allowNo = true) {
        $whereQuery = '';
        $whereArr = array();
        $where = $this->_query['where'];
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $oper = $value['oper'];
                $cond = $value['cond'];
                if ($key == 0)
                    $whereQuery .= 'WHERE ';
                if ($key != 0)
                    $whereQuery .= " $oper ";
                if (is_array($cond)) {
                    if ($cond[1] == 'in' || $cond[1] == 'not in') {
                        $whereQuery .= "{$cond[0]}" . " {$cond[1]} " . "{$cond[2]}";
                    } else {
                        $whereQuery .= "{$cond[0]}" . " {$cond[1]} " . "'{$cond[2]}'";
                    }
                } else {
                    $whereQuery .= $cond;
                }
                $whereArr[$cond[0]] = $cond[2];
            }
        } else if($allowNo == false) {
            //防止无条件删除整张表，where不允许不存在
            throw new Sys_Lib_Exception('not found mysql where condition');
        }
        $this->_connect($whereArr);
        return $whereQuery;
    }

    /**
     * 创建一个事物
     * @return boolean
     */
    public function transactionBegin() {
        $this->_connect();
        $this->_TRANSCATION = $this->_handler[$this->_currNode];
        $this->_TRANSCATION->beginTransaction();
    }

    /**
     * 提交事务
     * @return boolean
     */
    public function transactionCommit() {
        $this->_TRANSCATION->commit();
    }

    /**
     * 事务回滚
     * @return boolean
     */
    public function transactionRollBack() {
        return $this->_TRANSCATION->rollBack();
    }
    
    /**
     * 初始化表（<b>慎用：无法回滚</b>）
     * @return boolean
     */
    public function truncate() {
        return $this->exec('TRUNCATE ' . $this->_from());
    }

    /**
     * 关闭连接
     */
    public function close() {
        $this->_handler = array();
    }

    /**
     * 通过sql语句查询
     *
     * @method fetchBySql
     *
     * @param  [string]     $sql [sql语句]
     *
     * @return [array]          [结果集]
     */
    public function fetchBySql($sql){
        return $this->query($sql)->fetchAll();
    }

}
