<?php
namespace Manage\Controller;
use Think\Controller;
class IndexController extends BaseController {

    public function index(){
        $this->display();
    }

    public function test () {
        $a = $this->parsetReceivePostData();
        layout(false);
        $this->ajax_display('test');
    }


    public function parsetReceivePostData()
    {
        $post = file_get_contents('php://input');
        var_dump($post);die;
        // $post = $GLOBALS['HTTP_RAW_POST_DATA'];
        $data = simplexml_load_string($post,'SimpleXMLElement',LIBXML_NOCDATA);

        if($data !==false)
        {
            $this->receive = $data;
        }

        return $data;
    }


    public function test1 () {
        layout(false);
        $this->html = $this->fetch('test1');
        // sleep(5);
        $this->display();
    }

    public function ajax_fetch () {
        
        $this->ajax_display('ajax_fetch');
    }
    public function test2 () {
        layout(false);
        $this->dialog_title = '测试';
        $this->dialog_content = '测试内容';
        $html = $this->fetch('dialog');
        $html = compress_html($html);
        $array = array(
            'dialog' => 'callBack(\'' . $html . '\')',
            // 'eval' => 'eva("a")',
        );
        echo json_encode($array);
    }
}