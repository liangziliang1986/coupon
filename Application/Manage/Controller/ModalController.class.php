<?php
namespace Manage\Controller;
use Think\Controller;
class ModalController extends BaseController {
    public function _initialize () {
        layout(false);
    }

    public function index () {
        $this->display();
    }

    public function dialog () {
        layout(false);
        $this->dialog_title = '测试';
        $this->post_url = '/Manage/Modal/test';
        // $this->road_num = 'bill_name';
        $this->dialog_content = $this->fetch('Modal/dialog_content');
        $html = $this->fetch('Modal/dialog_form');
        $html = compress_html($html);
        $array = array(
            'dialog' => 'callBack(\'' . $html . '\')',
            'eval' => 'eva("a")',
        );
        echo json_encode($array);
    }

    public function test () {
        var_dump($_POST);
    }
}