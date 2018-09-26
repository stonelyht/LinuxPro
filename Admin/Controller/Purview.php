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
}