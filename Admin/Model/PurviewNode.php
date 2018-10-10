<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018 年 9 月 25 日 0025
 * Time: 22:15:00
 */

class Model_PurviewNode extends Model_Base{

    /**
     * mysql配置
     * @var string
     */
    protected $_mysql_use = 'admin';

    /**
     * 数据表
     * @var string
     */
    protected $_table = 'purview_node';

    /**
     * 主键
     * @var String
     */
    protected $_pk = 'id';

    /**
     * @param $startItem
     * @param $pageSize
     * @return array
     */
    public function getInfoAll($startItem,$pageSize){
        $res = $this->getDb()
            ->from($this->_table)
            ->limit($startItem,$pageSize)
            ->fetchAll();
        return $res ? $res : array();
    }

    public function getNodeAuth($group_id){
        $res = $this->getDb()
            ->from($this->_table)
            ->select("$this->_table.*")
            ->join(array("purview_group_node","$this->_table.id","purview_group_node.node_id"))
            ->where('and',"purview_group_node.group_id = $group_id")
            ->where('and',"purview_group_node.show = 1")
            ->fetchAll();
        return $res;
    }
    
    public function getOrderNode(){
        $res = $this->getDb()
            ->from($this->_table)
            ->where('and',"`show` = 1")
            ->order(array("pid" => "asc"))
            ->fetchAll();
        return $res;
    }
}