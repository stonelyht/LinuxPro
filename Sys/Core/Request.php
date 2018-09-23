<?php

/**
 * Description of Request
 *
 */
class Sys_Core_Controller_Request {

    /**
     * app名
     * @var string
     */
    protected $_app = "";

    /**
     * Controller名
     * @var string
     */
    protected $_controller = "";

    /**
     * Action名
     * @var string
     */
    protected $_action = "";

    public function getAppName() {
        return $this->_app;
    }

    public function setAppName($app) {
        $this->_app = $app;
    }

    public function getControllerName() {
        return $this->_controller;
    }

    public function getControllerKey() {
        return $this->_controller;
    }

    public function setControllerName($controller) {
        $this->_controller = $controller;
    }

    public function getActionName() {
        return $this->_action;
    }

    public function getActionKey() {
        return substr($this->_action, 0, -6);
    }

    public function setActionName($action) {
        $this->_action = $action;
    }

    public function toArray() {
        return array(
            "app" => $this->_app,
            "controller" => $this->_controller,
            "action" => $this->_action
        );
    }

}