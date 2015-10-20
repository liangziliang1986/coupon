<?php
namespace Manage\Controller;
use Think\Controller;
class PluginController extends BaseController {
    public function _initialize () {
        layout(false);
    }

    public function index () {
        $this->display();
    }
}