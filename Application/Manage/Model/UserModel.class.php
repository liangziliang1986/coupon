<?php
namespace Manage\Model;
use Think\Model;
class UserModel extends Model{


	private $model;

    public function _initialize() {
        parent::_initialize();
        $this->model = M('User');

    }

    public function addOneUser ($data) {
        if(!$data) return FALSE;
        $data['username'] = $data['name'];
        $data['password'] = md5('123456');
        $re = $this->model->add($data);
        return $re;
    }

    /*//根据id查找单条子商户authorizer_refresh_token和appid
    public function getAppidAndAuthorizer_refresh_token ($id) {
        $map = array('id'=>$id);
        $re = $this->model->where($map)->field('appid,authorizer_refresh_token')->find();
        return $re;
    }*/


}


