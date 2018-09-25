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
}