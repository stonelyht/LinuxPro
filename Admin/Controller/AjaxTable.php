<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018 年 9 月 25 日 0025
 * Time: 22:17:13
 */

class Controller_AjaxTable extends Controller_Base{

    public function init() {
        parent::init();
        $this->_display = true;
    }
    /**
     * 获取节点列表
     */
    public function nodes(){
        //页数
        $pageNum = getHttpVal('pageNum');
        $node_m = new Model_PurviewNode();
        $node_info = $node_m->selectAll();
        if ($node_info){
            //获取记录条数
            $totalItem = count($node_info);
            //分页数
            $pageSize = 10;
            //该记录共分几页
            $totalPage = ceil($totalItem/$pageSize);
            //开始页数
            $startItem = ($pageNum-1) * $pageSize;
            $arr['totalItem'] = $totalItem;
            $arr['pageSize'] = $pageSize;
            $arr['totalPage'] = $totalPage;
            //分页数据
            $dataInfo = $node_m->getInfoAll($startItem,$pageSize);
            $arr['data_content'] = $dataInfo;
            echo json_encode($arr);
        }else{
            $arr['code'] = '401';
            echo json_encode($arr);
        }
    }

    /**
     * 删除节点
     */
    public function delNode(){
        $id = getHttpVal('id');
        $node_m = new Model_PurviewNode();
        $node_m->deleteDbById($id);
        $arr['code'] = '201';
        echo json_encode($arr);
    }

    /**
     * 修改节点
     */
    public function editNode(){
        $id = getHttpVal('id');
        $node_m = new Model_PurviewNode();
        $node_info = $node_m->selectDbById($id);
        echo json_encode($node_info);
    }

}
