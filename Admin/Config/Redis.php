<?php
/**
 * Redis Connection
 * mode：
 * 一般模式 normal
 * conn => array(
 *  'host:port'
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
 */
return array(
    'admin' => array(
        'mode' => 'normal',
        'conn' => array(
            '47.106.238.224:6379'
        ),
    ),
    'queue' => array(
        'host' => '47.106.238.224',
        'port' => '6379'
    )
);