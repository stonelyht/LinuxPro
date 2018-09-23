<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018 年 9 月 23 日 0023
 * Time: 14:05:37
 */
class Controller_Index extends Controller_Base{

    public function Index(){
        $this->display = true;
        print_r(phpinfo());
    }
}