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

    public function ueditor () {
        $this->display();
    }

    public function uploadifive () {
        $this->display();
    }

    public function test () {
        $this->display();   
    }
}