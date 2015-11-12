<?php
namespace Manage\Controller;
use Think\Controller;
class BaseController extends Controller {
    public function __construct(){
        $user = session('user');
        if (!$user['username']) {
            cookie_redirect('error', '请先登录', '/manage/login');
        } else if (!$user['id']) {
            cookie_redirect('error', '请先登录', '/manage/login/lock');
        }
        parent::__construct();
        $id = $_SESSION['user']['id'];
        $is_super = M('User')->where(array('id'=>$id))->getField('is_super');
        $this->is_super = $is_super;
        $this->template = C('TEMPLATE');
        /*$this->template = C('PRIMARY_NAV');
        var_dump($this->template);die;*/
        $get_left_nav = get_left_nav();
        $this->primary_nav = $get_left_nav;
        /*var_dump($this->primary_nav);
        die;*/
    }

    public function checkAuth ($state) {
        if($this->is_super < $state)
        {
            redirect('info',1, '权限不够');die;
        }
    }
}