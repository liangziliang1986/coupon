<?php
namespace Manage\Controller;
use Think\Controller;
class BaseController extends Controller {
    public function _initialize(){
        $this->template = C('TEMPLATE');
        $this->primary_nav = C('PRIMARY_NAV');
    }

    public function ajax_display ($view) {
        layout(false);
        $this->html = $this->fetch($view);
        // sleep(5);
        $this->display();
    }
}