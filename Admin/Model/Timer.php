<?php
class Model_Timer
{
    private $seconds = 1; // 秒数，每隔多少秒会执行一次
    private $callback = ""; // 功能函数，即您要实现的功能
    private $isdaemon = false; // 是否开启守护进程

    // 构造函数，
    // 第一个是 秒数（正整数）
    // 第二个参数是一个 bool，是否开启守护进程
    // 第三个参数为要实现的功能
    public function __construct($seconds, $isdaemon = false, $callback = "")
    {
        if($seconds >= 0) $this->seconds = $seconds;
        $this->isdaemon = (bool)$isdaemon;

        // 保存第三个参数是一个匿名函数
        if($callback instanceof Closure)
        {
            $this->callback = $callback;
        } else {
            $this->callback = function() {};
        }
    }

    // 程序运行
    public function start()
    {
        if($this->isdaemon)
            $this->start_daemon();

        // 安装一个信号处理器
        pcntl_signal(SIGALRM, array($this, 'installHandler'));

        // 启用异步信号处理
        pcntl_async_signals(true);

        // 设置闹钟信号
        pcntl_alarm($this->seconds);

        // 进入死循环，防止程序终止
        while(true);
    }

    // 信号处理函数
    public function installHandler()
    {
        // 调用客户需要操作的函数
        call_user_func($this->callback);

        // 重新设置闹钟
        pcntl_alarm($this->seconds);
    }

    // 开启守护进程
    public function start_daemon()
    {
        if(($pid = pcntl_fork()) < 0) {
            exit("start daemon error()");
        } else if($pid) { // 如果是父进程，则终止程序
            exit();
        } else {

        }
    }
}


// 客户端调用，每隔 1 秒打印一下当前的时间

// 第 1 个参数，表示每隔 1 秒执行一次
// 第 2 个参数，表示 不开启 守护进程
// 第 3 个参数，表示要做的功能
//$timer = new Timer(1, false, function()
//{
//    echo date("Y-m-d H:i:s\n");
//});
//$timer->start();