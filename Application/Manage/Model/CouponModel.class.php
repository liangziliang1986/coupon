<?php
namespace Manage\Model;
use Think\Model;
class CouponModel extends Model{


	private $model;

    public function _initialize() {
        parent::_initialize();
        $this->model = M('Coupon');

    }

    // 查找到所有待审核的卡券
    public function getCheckingCoupon () {
        $re = $this->model->where(array('status'=>0))->select();
        return $re;
    }

    //根据id查找单条子商户的merchant_id和card_id
    public function getMerchantidAndCardIdById ($id) {
        $map = array('id'=>$id);
        $re = $this->model->where($map)->field('merchant_id,card_id')->find();
        return $re;
    }


    //根据id查找单条子商户的card_id
    public function getmerchant_idById ($id) {
        $map = array('id'=>$id);
        $re = $this->model->where($map)->getField('merchant_id');
        return $re;
    }

    //根据id更新单条卡券
    public function updateOneCoupon ($id) {
        $data['status'] = 3;
        $data['id'] = $id;
        //所谓的删除只是把数据库字段的status修改为3
        $re = $this->model->save($data);
        return $re;
    }


    


}


