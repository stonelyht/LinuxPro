<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018 年 9 月 23 日 0023
 * Time: 19:48:15
 */

class Model_Account extends Model_Base{

    /**
     * mysql配置
     * @var string
     */
    protected $_mysql_use = 'admin';

    /**
     * 数据表
     * @var string
     */
    protected $_table = 'Account';

    /**
     * 主键
     * @var String
     */
    protected $_pk = 'id';

    /**
     * 查询用户名和密码
     * @param string $username
     * @param string $password
     * @return array
     */
    public function getUser($username,$password){
        $res = $this->getDb()
            ->from($this->_table)
            ->where('and',array('username','=',$username))
            ->where('and',array('password','=',$password))
            ->fetchOne();
        return $res;
    }


    public function getAccount(){
        $res = $this->getDb()
            ->from($this->_table)
            ->select("$this->_table.*,purview_group.name")
            ->join(array("purview_group","$this->_table.group_id","purview_group.id"))
            ->fetchAll();
        return $res;
    }
}