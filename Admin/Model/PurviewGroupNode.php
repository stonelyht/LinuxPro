<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/9
 * Time: 16:26
 */

class Model_PurviewGroupNode extends Model_Base{

    /**
     * mysql配置
     * @var string
     */
    protected $_mysql_use = 'admin';

    /**
     * 数据表
     * @var string
     */
    protected $_table = 'purview_group_node';

    /**
     * 主键
     * @var String
     */
    protected $_pk = 'id';


    public function getAuthNodes($id,$group_id,$show){
        $res = $this->getDb()
            ->from($this->_table)
            ->where('and',"group_id = $group_id")
            ->where('and',"node_id = $id")
            ->fetchOne();
        if ($res){
            $this->updateDbById($res['id'],['show' => $show]);
        }else{
            $this->getDb()
                ->from($this->_table)
                ->insert(['group_id' => $group_id,'node_id' => $id,'show' => $show]);
        }
    }
}