<?php
namespace Manage\Controller;
use Think\Controller;

class CouponController extends BaseController {


	public function __construct () {
		parent::__construct();
		layout(false);
		$this->model = M("Coupon"); 
	}

	public $code_static = '';

	//显示所有卡券列表
	public function index () {
		$this->checkAuth(2);
		$id = I('get.id',0,'intval');
		if($id)
		{
			$map['merchant_id'] = $id;
			$this->id = $id;
		}
		//查找所有符合创券条件的子商户
		$map['status'] = array('neq',3);
		//第几页
        $this->page = I('get.page') ? I('get.page') : 1;
        //每页条数
        $page_list = 10;
        //总条数
        $count = M("Coupon")->count();
        //页数
        $this->page_num = ceil($count / $page_list);
        $coupon = M("Coupon")->where($map)->page($this->page, $page_list)->order('brand_name')->select();
		$this->coupon = $coupon;
		$this->display();
	}

	// 显示子商户自己的卡券列表
	public function myCoupon () {

		$id = $_SESSION['user']['id'];
		//查找所有符合创券条件的子商户
		$map['status'] = array('neq',3);
		//第几页
        $this->page = I('get.page') ? I('get.page') : 1;
        //每页条数
        $page_list = 10;
        // 根据用户id查找mercha_id
        $merchant_id = M('Merchant')->where(array('uid'=>$id))->getField('id');
        //总条数
		$map['merchant_id'] = $merchant_id;
        $count = M("Coupon")->where($map)->count();
        //页数
        $this->page_num = ceil($count / $page_list);
        $coupon = M("Coupon")->where($map)->page($this->page, $page_list)->order('brand_name')->select();
		$this->coupon = $coupon;
		$this->display();
		
	}


	//更新同步微信审核状态的数据
	public function updateStatus () {
		//查询所有未通过审核的卡券
		$nopass = D('Coupon')->getCheckingCoupon();
		if($nopass)
		{
			$Auth = A('Auth');
			$data = array();
			//若查询到,则用单个card_id调用微信接口查询是否已经通过
			foreach ($nopass as $k => $v) {
				$arr = D('Merchant')->getAppidAndAuthorizer_refresh_token($v['merchant_id']);
				$re = A('Auth')->checkCouponStatus($arr,$v['card_id']);
				//微信返回的状态字段如下
				$status = $re['card'][strtolower($re['card']['card_type'])]['base_info']['status'];
				//如果查询结果不等于CARD_STATUS_NOT_VERIFY,则表示不是待审核中,需要更新本地数据库
				if($status != 'CARD_STATUS_NOT_VERIFY')
				{
					$data['id'] = $v['id'];
					switch ($status) {
						case 'CARD_STATUS_VERIFY_FAIL':
							//审核不通过
							$data['status'] = 1;
							break;
						case 'CARD_STATUS_VERIFY_OK':
							//审核通过
							$data['status'] = 2;
							break;
						case 'CARD_STATUS_DELETE':
							//卡券被商户删除
							$data['status'] = 3;
							break;
						default:
							//在公众平台投放过的卡券
							$data['status'] = 4;
							break;
					}
					$result = M('Coupon')->save($data);
				}
			}
		}
		redirect('index');
	}


	// 显示所有已审核通过并授权的子商户(即:可以创券的子商户列表)
	public function createList () {
		$this->checkAuth(2);
		//查找所有符合创券条件的子商户
		$where = array('status'=>1,'auth_status'=>1);
		//第几页
        $this->page = I('get.page') ? I('get.page') : 1;
        //每页条数
        $page_list = 10;
        //总条数
        $count = M("Merchant")->where($where)->count();
        //页数
        $this->page_num = ceil($count / $page_list);
        $merchant = M("Merchant")->page($this->page, $page_list)->where($where)->order('sort desc')->select();
        if($merchant)
        {
        	foreach ($merchant as $k => $v) {
        		$map['merchant_id'] = $v['id'];
        		//查找到状态不为3的，即没有被用户删除的记录
				$map['status'] = array('neq',3);
    			$merchant[$k]['count'] = M('Coupon')->where($map)->count();
        	}
        }
		$this->merchant = $merchant;
		$this->display();
	}

	


	//创建新卡券表单
	public function createCoupon () {
		
		$this->checkAuth(2);
		$id = I('get.id');
		$this->id = $id;
		//分配卡券类型,优惠券和礼品券比较少用,但唐总要求加上,所以全部显示
		$card_type = array(array('en_name'=>'GENERAL_COUPON','zh_name'=>'优惠券'),array('en_name'=>'GROUPON','zh_name'=>'团购券'),array('en_name'=>'CASH','zh_name'=>'代金券'),array('en_name'=>'DISCOUNT','zh_name'=>'折扣券'),array('en_name'=>'GIFT','zh_name'=>'礼品券'));
		/*$card_type = array(array('en_name'=>'GROUPON','zh_name'=>'团购券'),array('en_name'=>'CASH','zh_name'=>'代金券'),array('en_name'=>'DISCOUNT','zh_name'=>'折扣券'));*/
		$this->card_type = $card_type;
		// 分配展示类型
		$code_type = array(array('en_name'=>'CODE_TYPE_TEXT','zh_name'=>'文本'),array('en_name'=>'CODE_TYPE_BARCODE','zh_name'=>'一维码'),array('en_name'=>'CODE_TYPE_QRCODE','zh_name'=>'二维码'),array('en_name'=>'CODE_TYPE_ONLY_QRCODE','zh_name'=>'二维码无code显示'),array('en_name'=>'CODE_TYPE_ONLY_BARCODE','zh_name'=>'一维码无code显示'));
		$this->code_type = $code_type;
		// 分配颜色
		$color = array(array('en_name'=>'Color010','zh_name'=>'浅绿'),array('en_name'=>'Color020','zh_name'=>'绿色'),array('en_name'=>'Color030','zh_name'=>'浅蓝'),array('en_name'=>'Color040','zh_name'=>'蓝色'),array('en_name'=>'Color050','zh_name'=>'紫色'),array('en_name'=>'Color060','zh_name'=>'浅黄'),array('en_name'=>'Color070','zh_name'=>'黄色'),array('en_name'=>'Color080','zh_name'=>'橙色'),array('en_name'=>'Color090','zh_name'=>'浅红'),array('en_name'=>'Color100','zh_name'=>'红色'));
		$this->color = $color;
		// 分配使用时间的类型
		$type = array(array('en_name'=>'DATE_TYPE_FIX_TIME_RANGE','zh_name'=>'固定日期区间'),array('en_name'=>'DATE_TYPE_FIX_TERM','zh_name'=>'固定时长'));
		$this->type = $type;
		// 分配使用时间的类型
		$can_share = array(array('en_name'=>'true','zh_name'=>'能'),array('en_name'=>'false','zh_name'=>'不能'));
		$this->can_share = $can_share;
		// 分配使用时间的类型
		$can_give_friend = array(array('en_name'=>'true','zh_name'=>'能'),array('en_name'=>'false','zh_name'=>'不能'));
		$this->can_give_friend = $can_give_friend;
		// (0:GENERAL_COUPON1:GROUPON2:CASH3:DISCOUNT4:GIFT)
		$this->display();

	}


	//提交资料创建卡券
	public function postCreateCoupon () {

		/*验证不能为空*/
		if(!$_POST['logo_url_a'] || !$_POST['id'] || !$_POST['card_type'] || !$_POST['brand_name'] || !$_POST['code_type'] || !$_POST['title'] || !$_POST['sub_title'] || !$_POST['color'] || !$_POST['notice'] || !$_POST['description'] || !$_POST['type'] || !$_POST['quantity'])
		{
			$data['status'] = 1;
			$data['content'] = '资料不能为空';
			$this->ajaxReturn($data);die;
		}
		//根据merchant_id查找appid和refresh_token
		$id = $_POST['id'];
		$arr = D('Merchant')->getAppidAndAuthorizer_refresh_token($id);
		// 用refresh_token和appid制券
        writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/arr.php',$arr);
		$Auth = A('Auth');
		$re = $Auth->createCoupon($arr);
		if($re['errcode'] != 0)
		{
			$data['status'] = 1;
			$data['content'] = $re['errmsg'];
			$this->ajaxReturn($data);die;
		}
		else
		{
			// 如果制券成功,则把卡券id存入数据库
        	writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/_POST.php',$_POST);
        	$begin_timestamp = strtotime($_POST['begin_timestamp']);
        	$end_timestamp = strtotime($_POST['end_timestamp']);
			$_POST['begin_timestamp'] = $begin_timestamp;
			$_POST['end_timestamp'] = $end_timestamp;
			$_POST['card_id'] = $re['card_id'];
			$_POST['logo_url'] = $_POST['logo_url_a'];
			$_POST['merchant_id'] = $_POST['id'];
			unset($_POST['logo_url_a']);
			unset($_POST['id']);
			$result = M('Coupon')->add($_POST);
        	writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/result.php',$result);
			if($result)
			{
				$data['status'] = 0;
				$data['content'] = '制券成功';
				$this->ajaxReturn($data);die;
			}
		}
	}



	//上传卡券Logo
	public function uploadLogo () {
		if(IS_POST)
		{
	        $filename = $_SERVER['DOCUMENT_ROOT'] . $_POST['filename'];
	        $id = $_POST['id'];
	        //根据id查找到子商户的authorizer_refresh_token和appid
			$arr = D('Merchant')->getAppidAndAuthorizer_refresh_token($id);
			$Auth = A('Auth');
			$res = $Auth->uploadLogoImg($filename,$arr);
			if(isset($res['url']))
			{
				// 先写到隐藏域,不要写入数据库,因为写入数据库的话要读取麻烦
				$data['status'] = 0;
				$data['logo_url'] = $res['url'];
				$this->ajaxReturn($data);die;
			}
		}

	}


	//投放卡券二维码(默认为投放永久二维码先)
	public function dispatchQRCode () {

		$id = I('id');
		$menchant_and_card = D('Coupon')->getMerchantidAndCardIdById($id);
		$arr = D('Merchant')->getAppidAndAuthorizer_refresh_token($menchant_and_card['merchant_id']);
		$Auth = A('Auth');
		$re = $Auth->getQRCode($arr,$menchant_and_card['card_id']);
		if($re['errcode'] != 0)
		{
			$returnData['status'] = 1;
			$returnData['content'] = $re['errmsg'];
			$this->ajaxReturn($returnData);die;
		}
		else
		{
			// 把ticket更新到数据库
			$ticket = urlencode($re['ticket']);
			$data['id'] = $id;
			$data['ticket'] = $ticket;
			$result = M('Coupon')->save($data);
			if($result)
			{
				$returnData['status'] = 0;
				$returnData['content'] = '投放成功！';
				$this->ajaxReturn($returnData);die;
			}
			else
			{
				$returnData['status'] = 1;
				$returnData['content'] = '投放失败！';
				$this->ajaxReturn($returnData);die;
			}
		}
	}


	//删除卡券
	public function deleteCoupon () {
		$id = I('get.id',0,'intval');
		$result = D('Coupon')->getMerchantidAndCardIdById($id);
		$arr = D('Merchant')->getAppidAndAuthorizer_refresh_token($result['merchant_id']);
		//先删除微信端的卡券
		$Auth = A('Auth');
		$res = $Auth->delCoupon($arr,$result['card_id']);
		if($res['errcode'] != 0)
		{
			$data['state'] = 'error';
			$data['msg'] = $res['errmsg'];
		}
		else
		{
			/*array(2) { ["errcode"]=> int(-1) ["errmsg"]=> string(30) "system error hint: [x0089ent2]"}*/
			//再更新本地服务器的卡券
			$re = D('Coupon')->updateOneCoupon($id);
			if($re)
			{
				$data['state'] = 'success';
				$data['msg'] = '删除成功！';
			}
			// var_dump(D("Merchant")->getLastSql());
		}
		echo json_encode($data);die;
	}

	//核销卡券
	public function consume () {

		$this->display();
		
	}

	//Ajax提交核销卡券
	public function AjaxConsume () 
	{
		$code = $_POST['code'];
		$preg = "/[\n\r]+/";;
		$data = preg_split($preg, $code);
		if($data)
		{
			//查看数组是否为空
			$isEmpty = checkEmptyArray($data);
			if($isEmpty)
			{
				$arr = D('Merchant')->getAppidAndRefreshTokenByUid($_SESSION['user']['id']);
				$Auth = A('Auth');
				foreach ($data as $k => $v) 
				{
					//去除空数组
					if(trim($v))
					{
						$code = trim($v);
						//先调用查询Code接口,查看此code是否可以核销
						$re = $Auth->checkCouponConsumeOrNot($arr,$code);
						// 只要有一张不能被核销,则全部都不核销,需要全部重新填写
						if($re['errcode'] != 0)
						{
							$returnData['status'] = 1;
							$returnData['content'] = $code . ':' . $re['errmsg'];
							$this->ajaxReturn($returnData);die;
						}
					}
				}

				// 如果查询过全部都可以核销,则循环核销
				foreach ($data as $k => $v) {
					if(trim($v))
					{
						$code = trim($v);
						//查找到card_id
						$card_id = D('Code')->getCardidByCode($code);
						//先核销微信端
						$result = $Auth->consumeCoupon($arr,$code);
						// 再更新本地数据库,把code表的消费字段改为1
						M('Code')->where(array('code'=>$code))->save(array('isconsumed'=>1));
						// 再更新本地数据库,把coupon表的已消费总数量增加1
                    	M('Coupon')->where(array('card_id'=>$card_id))->setInc('consumed');
					}
				}
				$returnData['status'] = 0;
				$returnData['content'] = '核销完成';
				$this->ajaxReturn($returnData);die;
			}
			else
			{
				$returnData['status'] = 1;
				$returnData['content'] = '内容不能为空';
				$this->ajaxReturn($returnData);die;
			}
		}
	}

	//查看单张卡券详情
	public function codeDetails () {

		$consumed = I('get.consumed','','trim');
		if($consumed)
		{
			$map['isconsumed'] = 1;
		}
		$title = I('get.title','');
		$this->title = $title;
		//根据card_id查找卡券的所有详情(只查找最终持有的,不查找转赠的..)
		$card_id = I('get.card_id','');
		$map['card_id'] = $card_id;
        $map['finalget'] = 1;
		//第几页
        $this->page = I('get.page') ? I('get.page') : 1;
        //每页条数
        $page_list = 10;
        //总条数
        $count = M("Code")->where($map)->count();
        //页数
        $this->page_num = ceil($count / $page_list);
        $code = M("Code")->where($map)->page($this->page, $page_list)->select();
        //递归找赠送者
        if($code)
        {
        	/*foreach ($code as $k => $v) {
        		if($v['isgivebyfriend'])
        		{
        			array_splice($code,$k,0,array($v));
        			var_dump($code);die;
        		}
        	}*/
        }
		$this->code = $code;
		$this->display();


	}

	//ajax点击加号时查看转赠的详情
	public function checkPresent () {

		$code = I('code','');
		//递归查找此code对应的所有code
		// var_dump($code);
		$this->getAllCode($code,$code);
		// var_dump($allCode);die;
		$m = '<tr><td scope="row">' . $this->code_static . '</td><td></td><td></td><td>已赠送</td></tr>';

		$data['status'] = 0;
		$data['content'] = $m;
		$this->ajaxReturn($data);die;
		
	}

	//递归查找所有转赠的code
	public function getAllCode ($code,$re) {
		$oldcode = D('Code')->getOldCodeByCode($code);
		// var_dump($oldcode);
		if($oldcode)
		{
			$this->code_static = $oldcode . '->' . $re;
			$this->getAllCode($oldcode,$this->code_static);
		} 
	}

}





