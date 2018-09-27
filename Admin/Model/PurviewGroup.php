<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018 年 9 月 27 日 0027
 * Time: 21:38:49
 */

class Model_PurviewGroup extends Model_Base{
    /**
     * mysql配置
     * @var string
     */
    protected $_mysql_use = 'admin';

    /**
     * 数据表
     * @var string
     */
    protected $_table = 'purview_group';

    /**
     * 主键
     * @var String
     */
    protected $_pk = 'id';

}