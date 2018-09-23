<?php
/**
 * Mysql Connection
 * mode：
 * 一般模式 normal
 * conn => array(
 *  'host:port:usname:passwd:dbname'
 * )
 * 主从模式 replication
 * conn => array(
 *  master,
 *  salve,
 *  salve
 *  ....
 * )
 * 集群模式 cluster
 * conn => array(
 *  cluster1,
 *  cluster2,
 *  cluster3
 *  ....
 * )
 * rule => array(
 *  table => column
 * )
 */
return array(
    //后台DB
    'ly_sdk' => array(
        'mode' => 'normal',
        'conn' => array(
//            '127.0.0.1:3306:root::ly_sdk'
            '47.106.238.224:3306:root:111qqqpwd:Linux-admin'
        ),
    )
);
