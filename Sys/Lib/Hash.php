<?php

class Sys_Lib_Hash {

    /**
     * 虚拟节点数,解决节点分布不均的问题 
     * @var int 
     */
    private $_replicas = 4;

    /**
     * 使用的hash方法 : md5,crc32 
     * @var object Hash_Hasher 
     */
    private $_hasher;

    /**
     * 节点记数器 
     * @var int 
     */
    private $_targetCount = 0;

    /**
     * 位置对应节点,用于lookup中根据位置确定要访问的节点 
     * @var array { position => target, ... } 
     */
    private $_positionToTarget = array();

    /**
     * 节点对应位置,用于删除节点 
     * @var array { target => [ position, position, ... ], ... } 
     */
    private $_targetToPositions = array();

    /**
     * 是否已排序 
     * @var boolean 
     */
    private $_positionToTargetSorted = false;

    /**
     * 构造函数
     * 确定要使用的hash方法和需拟节点数
     * 虚拟节点数越多,分布越均匀,但程序的分布式运算越慢 
     * @param object $hasher Flexihash_Hasher 
     */
    public function __construct(Hash_Hasher $hasher = null, $replicas = null) {
        $this->_hasher = $hasher ? $hasher : new Hash_Crc32Hasher();
        if (!empty($replicas))
            $this->_replicas = $replicas;
    }

    /**
     * 添加节点
     * 根据虚拟节点数,将节点分布到多个虚拟位置上 
     * @param string $target 
     * @throws Flexihash_Exception
     */
    public function addTarget($target) {
        if (isset($this->_targetToPositions[$target])) {
            throw new Sys_Lib_Exception("Target '$target' already exists.");
        }

        $this->_targetToPositions[$target] = array();
        // hash the target into multiple positions
        for ($i = 0; $i < $this->_replicas; $i++) {
            list($host) = explode(':', $target);
            $position = $this->_hasher->hash($host . $i);
            // lookup
            $this->_positionToTarget[$position] = $target;
            // target removal
            $this->_targetToPositions[$host][] = $position;
        }
        $this->_positionToTargetSorted = false;
        $this->_targetCount++;
    }

    /**
     * 添加Target
     * @param array $targets 
     */
    public function addTargets($targets) {
        foreach ($targets as $target) {
            $this->addTarget($target);
        }
    }

    /**
     * 移除Target
     * @param string $target 
     */
    public function removeTarget($target) {
        if (!isset($this->_targetToPositions[$target])) {
            throw new Sys_Lib_Exception("Target '$target' does not exist.");
        }
        foreach ($this->_targetToPositions[$target] as $position) {
            unset($this->_positionToTarget[$position]);
        }
        unset($this->_targetToPositions[$target]);
        $this->_targetCount--;
        return $this;
    }

    /**
     * A list of all potential targets 
     * @return array 
     */
    public function getAllTargets() {
        return array_keys($this->_targetToPositions);
    }

    /**
     * Looks up the target for the given resource. 
     * @param string $resource 
     * @return string 
     */
    public function lookup($resource) {
        $targets = $this->lookupList($resource, 1);
        if (empty($targets))
            throw new Sys_Lib_Exception('No targets exist');
        return $targets;
    }

    /**
     * Get a list of targets for the resource, in order of precedence. 
     * Up to $requestedCount targets are returned, less if there are fewer in total. 
     * 
     * @param string $resource 
     * @param int $requestedCount The length of the list to return 
     * @return array List of targets 
     * @comment 查找当前的资源对应的节点, 
     *          节点为空则返回空,节点只有一个则返回该节点, 
     *          对当前资源进行hash,对所有的位置进行排序,在有序的位置列上寻找当前资源的位置 
     *          当全部没有找到的时候,将资源的位置确定为有序位置的第一个(形成一个环) 
     *          返回所找到的节点 
     */
    public function lookupList($resource, $requestedCount) {
        if (!$requestedCount)
            throw new Exception('Invalid count requested');

        // handle no targets  
        if (empty($this->_positionToTarget))
            return array();

        // optimize single target  
        if ($this->_targetCount == 1) {
            $positionToTarget = array_values($this->_positionToTarget);
            return $positionToTarget[0];
        }

        //找出在hash中的位置
        $position = $this->_hasher->hash($this->extractKeyTag($resource));
        
        $this->_sortPositionTargets();
        print_r($this->_positionToTarget);
        echo '<p>';
        //遍历找到小于此$position最近的节点
        foreach ($this->_positionToTarget as $key => $value) {
            if ($position < $key) {
                return $value;
            }
        }

        //上面没找到就把第一个节点返回
        foreach ($this->_positionToTarget as $key => $value) {
            return $value;
        }
    }

    private function _sortPositionTargets() {
        if (!$this->_positionToTargetSorted) {
            ksort($this->_positionToTarget, SORT_REGULAR);
            $this->_positionToTargetSorted = true;
        }
    }

    /**
     * 截取字符串中首个{}包括的字符串
     * @param string $key
     * @return string
     */
    protected function extractKeyTag($key) {
        if (false !== $start = strpos($key, '{')) {
            if (false !== ($end = strpos($key, '}', $start)) && $end !== ++$start) {
                $key = substr($key, $start, $end - $start);
            }
        }
        return $key;
    }

}

class Hash_Crc32Hasher implements Hash_Hasher {
    public function hash($string) {
        return crc32($string);
    }
}

class Hash_Md5Hasher implements Hash_Hasher {
    public function hash($string) {
        return substr(md5($string), 0, 8);
    }
}

interface Hash_Hasher {
    public function hash($string);
}
