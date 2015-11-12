<?php
namespace Manage\Model;
use Think\Model;
class MerchantModel extends Model{


	private $model;

    public function _initialize() {
        parent::_initialize();
        $this->model = M('Merchant');

    }

    // 查找所有未通过审核的子商户
    public function getAllNotPassShop () {
    	$data = $this->model->where(array('status'=>0))->select();
    	return $data;
    }

    //根据$id删除子商户资料
    public function deleteOneShop ($id) {
		
		$map = array('id'=>$id);
		$re = $this->model->where($map)->delete();
    	return $re;
    }

    //根据id查找单条子商户的name和appid
    public function getOneMerchant ($id) {
		$map = array('id'=>$id);
    	$re = $this->model->where($map)->field('name,appid')->find();
    	return $re;
    }

    

    //根据uid查找单条子商户资料
    public function getOneMerchantByUid ($uid) {
		$map = array('uid'=>$uid);
    	$re = $this->model->where($map)->find();
    	return $re;
    }

    //根据id查找单条子商户uid
    public function getUidById ($id) {
		$map = array('id'=>$id);
    	$re = $this->model->where($map)->getField('uid');
    	return $re;
    }

    /*//查找所有已审核通过并授权的子商户(即:可以创券的子商户列表)
    public function getAllPassAndAuth () {
    	$where = array('status'=>1,'auth_status'=>1);
		//第几页
        $this->page = I('get.page') ? I('get.page') : 1;
        //每页条数
        $page_list = 10;
        //总条数
        $count = $this->model->where($where)->count();
        //页数
        $this->page_num = ceil($count / $page_list);
        $merchant = $this->model->page($this->page, $page_list)->where($where)->order('sort desc')->select();
        return $merchant;
    	
    }*/

    //根据id查找单条子商户authorizer_refresh_token和appid
    public function getAppidAndAuthorizer_refresh_token ($id) {
        $map = array('id'=>$id);
        $re = $this->model->where($map)->field('appid,authorizer_refresh_token')->find();
        return $re;
    }

    //根据uid查找单条子商户appid和refresh_token
    public function getAppidAndRefreshTokenByUid ($uid) {
        $map = array('uid'=>$uid);
        $re = $this->model->where($map)->field('appid,authorizer_refresh_token')->find();
        return $re;
    }

}


