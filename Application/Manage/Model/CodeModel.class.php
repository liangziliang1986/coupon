<?php
namespace Manage\Model;
use Think\Model;
class CodeModel extends Model{


	private $model;

    public function _initialize() {
        parent::_initialize();
        $this->model = M('Code');

    }


	//根据code查找卡券的card_id
    public function getCardidByCode ($code) {
        $map['code'] = $code;
        $re = $this->model->where($map)->getField('card_id');
        return $re;
    }

    //根据code查找卡券的详情
    public function getOneCodeByCode ($code) {
        $map['code'] = $code;
        $re = $this->model->where($map)->find();
        return $re;
    }


    //根据code查找oldcode
    public function getOldCodeByCode ($code) {
    	$map['code'] = $code;
        $re = $this->model->where($map)->getField('oldcode');
        return $re;
    	
    }

    //根据oldcode查找code
    public function getCodeByOldCode ($oldcode) {
    	$map['oldcode'] = $oldcode;
        $re = $this->model->where($map)->getField('code');
        return $re;
    }

}




