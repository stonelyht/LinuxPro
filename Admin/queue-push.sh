#!/bin/bash
step=1 #间隔的秒数，不能大于60

for((i=0;i<60;i=(i+step))); do
	$(curl 'https://bbs.svw-volkswagen.com/forum.php')
	sleep $step
done

#防止shell脚本重复运行
while [ `ps x |grep -v grep|grep queue-push.sh|wc -l` -gt 0 ];
do
        exit
done