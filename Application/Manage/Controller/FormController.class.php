<?php
namespace Manage\Controller;
use Think\Controller;
class FormController extends BaseController {
    public function _initialize () {
        layout(false);
    }

    public function index () {
        $this->list = array(
            array(
                'zh_name' => '哈哈',
                'en_name' => 'haha',
            ),
            array(
                'zh_name' => '嘻嘻',
                'en_name' => 'xixi',
            ),
            array(
                'zh_name' => '嘎嘎',
                'en_name' => 'gaga',
            ),
        );
        $this->muloptions = array(
            array(
                'key' => 'kkk',
                'value' => 'vvvv',
                'selected' => false,
            ),
            array(
                'key' => 'Kingdom',
                'value' => 'Kingdom',
                'selected' => true,
            ),
            array(
                'key' => 'ccc',
                'value' => 'ccc',
                'selected' => false,
            ),
        );
        $this->single_options = array(
            array(
                'key' => 'kkk',
                'value' => '中文名',
            ),
            array(
                'key' => 'Kingdom',
                'value' => 'Kingdom',
                'selected' => true,
            ),
            array(
                'key' => 'ccc',
                'value' => '艺术名',
            ),
        );
        $this->display();
    }
}