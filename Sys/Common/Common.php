<?php
class Common {

    //注册自动加载类文件方法
    public static function registerAutoLoad() {
        spl_autoload_register(array('Common', 'autoLoad'));
    }

    /**
     * 自动加载类
     * @param string $className
     */
    public static function autoLoad($className) {
        $pathArray = explode("_", $className);
        $path = ROOTDIR;
        if ($pathArray[0] != 'Sys' && $pathArray[0] != 'Protobuf')
            $path .= '/' . APP_NAME;
        foreach ($pathArray as $v) {
            $path .= "/$v";
        }
        $path .= '.php';
        if (file_exists($path)) {
            require_once "$path";
        }
    }
    
    /**
     * 构建分页HTML
     * @param int $cur_page 当前页
     * @param int $count 总数据量
     * @param int $per_page 每页几条数据
     * @return string
     */
    public static function page($cur_page, $count, $per_page) {
        $previous_btn = true;
        $next_btn = true;
        $first_btn = true;
        $last_btn = true;
        $no_of_paginations = ceil($count / $per_page);
        if ($cur_page >= 7) {
            $start_loop = $cur_page - 3;
            if ($no_of_paginations > $cur_page + 3)
                $end_loop = $cur_page + 3;
            else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
                $start_loop = $no_of_paginations - 6;
                $end_loop = $no_of_paginations;
            } else {
                $end_loop = $no_of_paginations;
            }
        } else {
            $start_loop = 1;
            if ($no_of_paginations > 7)
                $end_loop = 7;
            else
                $end_loop = $no_of_paginations;
        }
        /* ----------------------------------------------------------------------------------------------------------- */
        $msg = "<ul class='pagination' style='margin:0px 0px 0px 0px'>";

        // FOR ENABLING THE FIRST BUTTON
        if ($first_btn && $cur_page > 1) {
            $msg .= "<li><a href='javascript:;' p='1'>&laquo;&laquo;</a></li>";
        } else if ($first_btn) {
            $msg .= "<li class='disabled'><a href='javascript:;' p='1'>&laquo;&laquo;</a></li>";
        }

        // FOR ENABLING THE PREVIOUS BUTTON
        //$pre = $cur_page;
        if ($previous_btn && $cur_page > 1) {
            $pre = $cur_page - 1;
            $msg .= "<li><a href='javascript:;' p='$pre'>&laquo;</a></li>";
        } else if ($previous_btn) {
            $msg .= "<li class='disabled'><a href='javascript:;'>&laquo;</a></li>";
        }
        for ($i = $start_loop; $i <= $end_loop; $i++) {
            if ($cur_page == $i)
                $msg .= "<li class='disabled'><a href='javascript:;' p='$i'>{$i}</a></li>";
            else
                $msg .= "<li><a href='javascript:;' p='$i'>{$i}</a></li>";
        }

        // TO ENABLE THE NEXT BUTTON
        //$nex = $cur_page;
        if ($next_btn && $cur_page < $no_of_paginations) {
            $nex = $cur_page + 1;
            $msg .= "<li><a href='javascript:;' p='$nex'>&raquo;</a></li>";
        } else if ($next_btn) {
            $msg .= "<li class='disabled'><a href='javascript:;'>&raquo;</a></li>";
        }

        // TO ENABLE THE END BUTTON
        if ($last_btn && $cur_page < $no_of_paginations) {
            $msg .= "<li><a href='javascript:;' p='$no_of_paginations'>&raquo;&raquo;</a></li>";
        } else if ($last_btn) {
            $msg .= "<li class='disabled'><a href='javascript:;' p='$no_of_paginations'>&raquo;&raquo;</a></li>";
        }
        $total_string = "<li class='disabled'><span a='$no_of_paginations'> <b>" . $cur_page . "</b>/<b>$no_of_paginations</b>  <b style='color:red'> $count </b></span></li>";
        $msg = $msg . $total_string . "</ul>";  // Content for pagination
        return $msg;
    }

}