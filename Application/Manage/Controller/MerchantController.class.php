<?php
namespace Manage\Controller;
use Think\Controller;

class MerchantController extends BaseController {


	public function __construct () {
		parent::__construct();
		layout(false);
		$this->model = M("Merchant"); 
	}

	//单个子商户的信息
	public function info () {
		$uid = $_SESSION['user']['id'];
		$single = D('Merchant')->getOneMerchantByUid($uid);
		// var_dump($merchant);die;
		$this->single = $single;
		$this->display();
	}

	//子商户列表
	public function index () {
		//2代表最高权限，访问此方法需要最高权限,否则提示权限不够
		$this->checkAuth(2);
		if(I('get.nopass'))
		{
			$where = array(
				'status' => 0,
			);
		}elseif(I('get.noauth'))
		{
			$where = array(
				'auth_status' => 0,
			);
		}
		else
		{
			$where = 1;
		}
		//第几页
        $this->page = I('get.page') ? I('get.page') : 1;
        //每页条数
        $page_list = 10;
        //总条数
        $count = $this->model->where($where)->count();
        //页数
        $this->page_num = ceil($count / $page_list);
        $merchant = $this->model->page($this->page, $page_list)->where($where)->order('sort desc')->select();
        $this->merchant = $merchant;
		$this->display();
	}



	//新增子商户资料审核
	public function verify () {
		//2代表最高权限，访问此方法需要最高权限,否则提示权限不够
		$this->checkAuth(2);
		$Auth = A('Auth');
		$cate = $Auth->checkprimary_category_id();
		//一级类目
		$cate1 = $cate['category'];
		foreach ($cate1 as $k => $v) {
			$cate1[$k]['en_name'] = $v['primary_category_id'];
			$cate1[$k]['zh_name'] = $v['category_name'];
		}
		$this->assign('cate1',$cate1);
		//二级类目
		$cate2 = $cate['category'][0]['secondary_category'];
		foreach ($cate2 as $k => $v) {
			$cate2[$k]['en_name'] = $v['secondary_category_id'];
			$cate2[$k]['zh_name'] = $v['category_name'];
		}
		$this->assign('cate2',$cate2);
		$this->display();

	}



	//更新同步微信审核状态的数据
	public function updateStatus () {
		$Auth = A('Auth');
		//查询所有未通过审核的子商户
		$nopass = D('Merchant')->getAllNotPassShop();
		if($nopass)
		{
			$data = array();
			//若查询到,则把单个appid调用微信接口查询是否已经通过
			foreach ($nopass as $k => $v) {
				$re = A('Auth')->checkSonShop($v['appid']);
				//如果查询结果不等于RESULT_CHECKING,则表示不是待审核中,需要更新本地数据库
				if($re['result'] != 'RESULT_CHECKING')
				{
					$data['id'] = $v['id'];
					switch ($re['result']) {
						case 'RESULT_PASS':
							//审核通过,更新数据库
							$data['status'] = 1;
							break;
						case 'RESULT_NOT_PASS':
							//审核不通过,被驳回,更新数据库
							$data['status'] = 2;
							break;
						default:
							//无提审记录,更新数据库
							$data['status'] = 3;
							break;
					}
					$result = M('Merchant')->save($data);
				}
			}
		}
		redirect('index');
	}


	// 修改子商户账户密码
	public function updateInfo () {

		$id = $_SESSION['user']['id'];
		$user = M('User')->where(array('id'=>$id))->find();
		$this->user = $user;
		$this->display();
	}


	// 修改子商户登录账户
	public function editMerchant () {

		$header_img = I('header_img_b');
		$username = I('username');
		$old_password = I('old_password');
		$new_password = I('new_password');
		$confirm_password = I('confirm_password');
		$email = I('email','','trim');
		// 验证为空
		if(!$username || !$old_password || !$new_password || !$confirm_password)
		{
			$data['status'] = 1;
			$data['content'] = '资料不能为空！';
			$this->ajaxReturn($data);die;
		}
		// 验证新旧密码是否相同(相同则提示没必要更改密码)
		if($old_password == $new_password)
		{
			$data['status'] = 1;
			$data['content'] = '新旧密码不能设置为一样！';
			$this->ajaxReturn($data);die;
		}
		// 验证两次密码是否一致
		if($new_password != $confirm_password)
		{
			$data['status'] = 1;
			$data['content'] = '两次密码输入不一致！';
			$this->ajaxReturn($data);die;
		}
		// 验证旧密码
		$password = M('User')->where(array('id'=>$_SESSION['user']['id']))->getField('password');
		if($password != md5($old_password))
		{
			$data['status'] = 1;
			$data['content'] = '旧密码输入错误！';
			$this->ajaxReturn($data);die;
		}
		// 验证邮箱
		if($email)
		{
			$preg = "/^[0-9a-z]+[-_]?[0-9a-z]+@[0-9a-z]{2,7}\.(com|cn|com\.cn)$/i";
			$bool = preg_match($preg, $email); 
			if(!$bool)
			{
				$data['status'] = 1;
				$data['content'] = '邮箱格式不正确!';
				$this->ajaxReturn($data);die;
			}
		}
		// 修改数据库
		$data['header_img'] = $header_img;
		$data['username'] = $username;
		$data['password'] = md5($new_password);
		$data['email'] = $email;
		$re = M('User')->where(array('id'=>$_SESSION['user']['id']))->save($data);
		if($re)
		{
			// 更新session
			$user = M('User')->where(array('id'=>$_SESSION['user']['id']))->find();
			session('user',$user);
			$data['status'] = 0;
			$data['content'] = '修改成功!';
			$this->ajaxReturn($data);die;
		}
	}

	//修改子商户资料
	public function edit () {
		$id = I('get.id',0,'intval');
		$merchant = $this->model->where(array('id'=>$id))->find();
		$this->merchant = $merchant;
		$Auth = A('Auth');
		$cate = $Auth->checkprimary_category_id();
		//一级类目
		$cate1 = $cate['category'];
		$cate2 = '';
		foreach ($cate1 as $k => $v) {
			$cate1[$k]['en_name'] = $v['primary_category_id'];
			$cate1[$k]['zh_name'] = $v['category_name'];
			if($v['primary_category_id'] == $merchant['primary_category_id'])
			{
				$cate1[$k]['selected'] = 1;
				$cate2 = $v['secondary_category'];
			}
		}
		$this->assign('cate1',$cate1);
		//二级类目
		foreach ($cate2 as $k => $v) {
			$cate2[$k]['en_name'] = $v['secondary_category_id'];
			$cate2[$k]['zh_name'] = $v['category_name'];
			if($v['secondary_category_id'] == $merchant['secondary_category_id'])
			{
				$cate2[$k]['selected'] = 1;
			}
		}
		$this->assign('cate2',$cate2);
		$this->assign('id',$id);
		$this->display();
	}

	//删除子商户资料(只能删除被驳回的和无提审记录的子商户资料)
	public function deleteShop () {
		$id = I('get.id',0,'intval');
		$re = D('Merchant')->deleteOneShop($id);
		if($re)
		{
			$data['state'] = 'success';
			$data['msg'] = '删除成功！';
		}
		// var_dump(D("Merchant")->getLastSql());
		echo json_encode($data);die;
		
	}


	//子商户资料审核post提交
	public function verifyPost () {
		if(IS_POST)
		{
			$appid = I('appid');
			$name = I('name');
			$logo_media_id = I('logo_a');
			$business_license_media_id = I('license_a');
			$agreement_file_media_id = I('auth_a');
			$operator_id_card_media_id = I('identity_aa');
			$primary_category_id = I('primary_category_id');
			$secondary_category_id = I('secondary_category_id');
			// 验证不能为空
			if(!$appid || !$name || !$logo_media_id || !$business_license_media_id || !$agreement_file_media_id || !$primary_category_id || !$secondary_category_id)
			{
				$res['status'] = 1;
				$res['content'] = '资料不能为空！';
				$this->ajaxReturn($res);die;
			}
			$data = array(
				'appid' => $appid,
				'name' => $name,
				'logo_media_id' => $logo_media_id,
				'business_license_media_id' => $business_license_media_id,
				'agreement_file_media_id' => $agreement_file_media_id,
				'operator_id_card_media_id' => $operator_id_card_media_id,
				'primary_category_id' => $primary_category_id,
				'secondary_category_id' => $secondary_category_id,
			);
			/*string(415) "{"appid":"wx81545a79d30778f1","name":"威信特传媒","logo_media_id":"kQ9h6fHsTJz5g9NtOgznDfzW0Vc-5WGzpxQQSL182gwj2Rtrr0MCaGVhPaO6txeR","business_license_media_id":"47ABkcV8ys0aL4ld1oPM2kl_DReoLc5JUhTjjmRwNc-NKgZb-4deO6lHRgMy3Ko0","agreement_file_media_id":"JZ99lKnspZlOkbTrRuBwOPw-LXxypOpYe_2zG6654PTOOT3CqtP8KYhhI-N58Pxx","operator_id_card_media_id":"","primary_category_id":"11","secondary_category_id":"1102"}"*/
			$Auth = A('Auth');
			$res = $Auth->submitSonShop($data);
			if($res['errcode'] == 0)
			{
				// 如果微信端提交成功,则写入本地数据库(本地不写入media_id,写入实际图片路径)
				$data['logo'] = I('logo_b');	
				$data['license'] = I('license_b');
				$data['agreement'] = I('auth_b');
				$data['identity'] = I('identity_b');
				$data['createtime'] = time();
				$data['sort'] = I('sort');
				$model = $this->model;
				$model->data($data)->add();
				$res['status'] = 0;
				$res['content'] = '提交成功！';
			}
			else
			{
				// 否则不写入本地,直接返还错误
				$res['status'] = 1;
				$res['content'] = $res['errmsg'];

			}
			$this->ajaxReturn($res);die;
		}
	}


	//修改子商户审核资料
	public function editPost()
	{
		if(IS_POST)
		{
			$appid = I('appid');
			$name = I('name');
			$logo_media_id = I('logo_a');
			$business_license_media_id = I('license_a');
			$agreement_file_media_id = I('auth_a');
			$operator_id_card_media_id = I('identity_aa');
			$primary_category_id = I('primary_category_id');
			$secondary_category_id = I('secondary_category_id');
			// 验证不能为空
			if(!$appid || !$name || !$logo_media_id || !$business_license_media_id || !$agreement_file_media_id || !$primary_category_id || !$secondary_category_id)
			{
				$res['status'] = 1;
				$res['content'] = '资料不能为空！';
				$this->ajaxReturn($res);die;
			}
			$data = array(
				'appid' => $appid,
				'name' => $name,
				'logo_media_id' => $logo_media_id,
				'business_license_media_id' => $business_license_media_id,
				'agreement_file_media_id' => $agreement_file_media_id,
				'operator_id_card_media_id' => $operator_id_card_media_id,
				'primary_category_id' => $primary_category_id,
				'secondary_category_id' => $secondary_category_id,
			);
			//由于微信端审核不通过,所以微信端只能重新提交,不能修改
			$Auth = A('Auth');
			$res = $Auth->submitSonShop($data);
			if($res['errcode'] == 0)
			{
				// 如果微信端提交成功,则更新本地数据库(本地不写入media_id,写入实际图片路径)
				$data['id'] = I('id');	
				$data['logo'] = I('logo_b');	
				$data['license'] = I('license_b');
				$data['agreement'] = I('auth_b');
				$data['identity'] = I('identity_b');
				$data['createtime'] = time();
				$data['sort'] = I('sort');
				$model = $this->model;
				$model->save($data);
				$res['status'] = 0;
				$res['content'] = '修改成功！';
			}
			else
			{
				$res['status'] = 1;
				$res['content'] = $res['errmsg'];

			}
			$this->ajaxReturn($res);die;
		}
	}



	//上传临时图片素材
	public function uploadTempImg1 () {
		if(IS_POST)
		{
			$Auth = A('Auth');
	        $filename = $_SERVER['DOCUMENT_ROOT'] . $_POST['filename'];
			$res = $Auth->uploadTempImg($filename);
			if(isset($res['media_id']))
			{
				$res['status'] = 0;
			}
			else
			{
				$res['status'] = 1;
			}
			$this->ajaxReturn($res);die;
		}

	}


	//ajax查询二级类目
	public function ajaxCheckSecondCate () {
		if(IS_AJAX)
		{
			$primary_category_id = I('id','');
			$Auth = A('Auth');
			$cate = $Auth->checkprimary_category_id();
			//一级类目
			$cate1 = $cate['category'];
			$secondary_category = '';
			foreach ($cate1 as $k => $v) {
				if($v['primary_category_id'] == $primary_category_id)
				{
					$secondary_category = $v['secondary_category'];				
				}
			}
			//从组select框的option选项并分配到页面
			$html = $this->setSecondCateOption($secondary_category);
			if($html)
			{
				$data['status'] = 0;
				$data['content'] = $html;
				$this->ajaxReturn($data);die;
			}
			else
			{
				$data['status'] = 1;
				$data['content'] = '获取失败！';
				$this->ajaxReturn($data);die;
			}
		}
	}


	//组合ajax的option值
	public function setSecondCateOption ($secondary_category) {
		if(!$secondary_category) return false;
		$m = '<option value="">选择二级类目</option>';
		foreach ($secondary_category as $k => $v) {
              $m .= '<option data-id="' . $v['secondary_category_id'] . '" value="' . $v['secondary_category_id'] . '">' . $v['category_name'] . '</option>';
		}
		return $m;
		// var_dump($m);die;
	}


	static function json_encode($arr) {
        $parts = array ();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys ( $arr );
        $max_length = count ( $arr ) - 1;
        if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
                if ($i != $keys [$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        foreach ( $arr as $key => $value ) {
            if (is_array ( $value )) { //Custom handling for arrays
                if ($is_list)
                    $parts [] = self::json_encode ( $value ); /* :RECURSION: */
                else
                    $parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
            } else {
                $str = '';
                if (! $is_list)
                    $str = '"' . $key . '":';
                //Custom handling for multiple data types
                if (!is_string ( $value ) && is_numeric ( $value ) && $value<2000000000)
                    $str .= $value; //Numbers
                elseif ($value === false)
                $str .= 'false'; //The booleans
                elseif ($value === true)
                $str .= 'true';
                else
                    $str .= '"' . addslashes ( $value ) . '"'; //All other things
                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                $parts [] = $str;
            }
        }
        $json = implode ( ',', $parts );
        if ($is_list)
            return '[' . $json . ']'; //Return numerical JSON
        return '{' . $json . '}'; //Return associative JSON
    }


    //一键生成子商户的账户密码 
    public function createAccount () {
    	// 查找单条子商户资料
    	$id = I('id');
    	$data = D('Merchant')->getOneMerchant($id);
    	// 生成单条子商户的账户和密码
    	$uid = D('User')->addOneUser($data);
    	//更新子商户表,使其uid与user表的uid对应关联
    	$arr['id'] = $id;
    	$arr['uid'] = $uid;
    	$re = M('Merchant')->save($arr);
    	if($re)
		{
			$content = '生成成功！用户名为:' . $data['name'] . ',密码为:123456,请尽快修改！';
			$result['status'] = 0;
			$result['content'] = $content;
		}
		else
		{
			$result['status'] = 1;
			$result['content'] = '生成失败！';
		}
		// var_dump(D("Merchant")->getLastSql());
		$this->ajaxReturn($result);die;
    }


}



