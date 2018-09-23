<?php
/**
 * Description of Exception
 */
class Sys_Lib_Exception extends Exception {

    /**
     * 错误级别
     * 0 输出型错误 （会中断程序运行，注意回滚） 将不记录数据库
     * 1 小异常 （用户操作导致） 将不记录数据库
     * 2 异常 （程序异常）
     * 3 次重量级异常 （需改正的异常）
     * 4 重量级异常 （将会邮件通知管理员）
     * @var int
     */
    protected $level = 0;

    public function __construct($message = '', $code = 0, $level = 0) {
        set_exception_handler("exception_handler");
        $this->setLevel($level);
        parent::__construct($message, $code, null);
    }

    /**
     * 获得错误级别
     * @return int
     */
    public function getLevel() {
        return $this->level;
    }

    /**
     * 设置错误级别
     * @param int $level
     */
    public function setLevel($level) {
        $this->level = $level;
    }

    /**
     * Exception2Array
     * @return array
     */
    public function toArray() {
        $return["message"] = $this->getMessage();
        $return["code"] = $this->getCode();
        $return["file"] = $this->getFile();
        $return["line"] = $this->getLine();
        $return["level"] = $this->getLevel();
        $return["trace"] = $this->getTrace();
        $return["traceString"] = $this->getTraceAsString();
        return $return;
    }

    /**
     * Exception2Json
     * @return string
     */
    public function toJson() {
        return json_encode($this->toArray());
    }

}

/**
 * 异常handle
 * @param Sys_Lib_Exception $exception
 */
function exception_handler(Sys_Lib_Exception $exception) {
    switch ($exception->getLevel()) {
        case 0:
            //0 输出型错误 （会中断程序运行，注意回滚）客户端有相应操作，不记录数据库
            echo json_encode(array('Exception' => $exception->getMessage()));
            break;
        case 1:
            //1 小异常 （用户操作导致） 将不记录数据库
            break;
        case 2:
            //2 异常 （程序异常）
            break;
        case 3:
            //3 次重量级异常 （需改正的异常）
            break;
        case 4:
            //4 重量级异常 （将会邮件通知管理员）
            break;
        case 5:
            //5 服务器处于维护
            echo json_encode(array('Maintain' => $exception->getMessage()));
            break;
    }
    exit;
}