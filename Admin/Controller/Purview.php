<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018 年 9 月 25 日 0025
 * Time: 20:50:49
 */

class Controller_Purview extends Controller_Base{

    public function nodes(){
        $this->_page_title = '节点列表';
    }

    /**
     * 更新节点，添加新节点
     */
    public function updateNode(){
        $this->_display = true;
        $data = getHttpVal();
        $id  = $data['id'];
        $node_m = new Model_PurviewNode();
        $node_info = $node_m->selectDbById($id);
        if ($node_info){
            unset($data['id']);
            $node_m->updateDbById($id,$data);
        }else{
            $node_m->insertDb($data);
        }
        $this->redirect('nodes');
    }

    public function group(){
        $this->_page_title = '权限分组';
    }

    public function updateGroup(){
        $this->_display = true;
        $data = getHttpVal();
        $id = $data['id'];
        $group_m = new Model_PurviewGroup();
        $group_info = $group_m->selectDbById($id);
        if ($group_info){
            unset($data['id']);
            $group_m->updateDbById($id,$data);
        }else{
            $group_m->insertDb($data);
        }
        $this->redirect('group');
    }

    public function account(){
        $this->_page_title = 'GM管理';
    }
}