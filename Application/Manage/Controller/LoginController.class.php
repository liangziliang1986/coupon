<?php
namespace Manage\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function _initialize () {
        layout('login_layout');
    }   
    public function index () {
        $this->name = C('PROJECT_NAME');
        $this->display();
    }

    public function verify () {
        $config =    array(
            'fontSize'    =>    20,    // 验证码字体大小
            'length'      =>    4,     // 验证码位数
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }

    public function register () {
        $this->name = C('PROJECT_NAME');
        $this->display();
    }

    public function logout () {
        session('user', null);
        redirect('/manage/login');
    }

    public function lock () {
        $user = session('user');
        unset($user['id']);
        session('user', $user);
        $this->user = $user;
        $this->display();
    }

    public function do_login () {
        $user = session('user');
        $code = I('verify_code');
        $lock = I('lock');
        if (!$lock && !$this->check_verify($code)) {
            cookie_redirect('error', '验证码不正确', '/manage/login');
        }
        $username = I('post.login-username') ? I('post.login-username') : $user['username'];
        $password = md5(I('post.login-password'));
        $user = M('User')->where(array('username' => $username, 'password' => $password))->find();
        if (!$username) {
            cookie_redirect('error', '用户名与密码不匹配', '/manage/login');
        } else if ($user) {
            session('user', $user);
            redirect('/manage');
        } else {
            cookie_redirect('error', '用户名与密码不匹配');
        }
    }

    function check_verify($code, $id = ''){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

    public function do_register () {
        $data['username'] = I('post.register-username');
        $data['password'] = md5(I('post.register-password'));
        $data['verify_password'] = md5(I('post.register-password-verify'));
        $data['email'] = I('post.register-email');
        ;
        if (empty($data['username']) || empty($data['password']) || empty($data['verify_password']) || empty($data['email'])) {
            cookie_redirect('error', '请完整填写表单');
        }
        if (!is_email($data['email'])) {
            cookie_redirect('error', '请输入正确的邮件格式');
        } else if ($data['password'] !== $data['verify_password']) {
            cookie_redirect('error', '两次密码不一致');
        } else if (M('User')->getByUsername($data['username'])) {
            cookie_redirect('error', '用户名已存在');
        } else {
            $data['id'] = M('User')->add($data);
            if ($data['id']) {
                session('user', $data);
                redirect('/manage');
            } else {
                cookie_redirect('error');
            }
        }
    }
}