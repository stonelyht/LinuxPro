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
    
    public function updateAccount(){
        $this->_display = true;
        $data = getHttpVal();
        $id = $data['id'];
        $account_m = new Model_Account();
        unset($data['id']);
        if ($id){
            $account_m->updateDbById($id,$data);
        }else{
            $data['password'] = md5($data['password']);
            $data['create_time'] = time();
            $account_m->insertDb($data);
        }
        $this->redirect('account');
    }

    public function nodeAuth(){
        $group_id = getHttpVal('group_id');
        $group_m = new Model_PurviewGroup();
        $group_info = $group_m->selectDbById($group_id);
        $this->_page_title = $group_info['name'].'-节点权限';
        $purviewNode_m = new Model_PurviewNode();
        $purviewNode_info = $purviewNode_m->getNodeAuth($group_id);
        $orderNode = $purviewNode_m->getOrderNode();
        if (is_array($orderNode)){
            foreach ($orderNode as $node) {
                $data[$node['pid']][] = $node;
            }
        }
        $this->assign('group_id',$group_id);
        $this->assign('orderNode',$data);
        $this->assign('purviewNode',json_encode($purviewNode_info));
    }
}