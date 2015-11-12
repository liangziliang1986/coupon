<?php
namespace Manage\Controller;
use Think\Controller;
Vendor('WechatMsgCrypt.wxBizMsgCrypt');
import('Vendor.Wechat.Wechat');
class AuthController extends Controller {

	private $component_appid;
	private $component_appsecret;
	private $token;
	private $encodingAesKey;

	public function __construct () {
		$this->component_appid = 'wx4b0fb3d2064af390';
		$this->component_appsecret = '0c79e1fa963cd80cc0be99b20a18faeb';
		$this->token = '3b7a7jh1STh8viEcfQ8F';
		$this->encodingAesKey = '8c714ca700d39f62b6faf2e096aa03d28c714ca700d';
		parent::__construct();
		layout(false);
	}

    public function index () {
    	//获取component_access_token
    	$re = $this->get_component_access_token();
        //获取预授权码,用于获取真正的授权码
        $pre_auth_code = $this->get_pre_auth_code();
    	//获取真正的授权码
    	$auth_link = "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=" . $this->component_appid . "&pre_auth_code=" . $pre_auth_code . "&redirect_uri=" . urlencode('http://' . $_SERVER['HTTP_HOST'] . '/Manage/Auth/auth_callback');
    	$this->assign('auth_link',$auth_link);
    	$this->display();

    }


    //获取component_access_token
    public function get_component_access_token () {
    	if(!S('component_access_token'))
    	{
            $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
            $param = array(
                "component_appid" => $this->component_appid,
                "component_appsecret" => $this->component_appsecret, 
                "component_verify_ticket" => S('component_verify_ticket'), 
            );
            $result = http_post($url, json_encode($param));
            $json = json_decode($result,true);
            S('component_access_token',$json['component_access_token'],7200);
    	}
    	return S('component_access_token');
    }

    //获取预授权码，用于引导用户点击链接时获取真正授权码。
    public function get_pre_auth_code () {
    	if(!S('pre_auth_code'))
    	{
	        $component_access_token = S('component_access_token')?S('component_access_token'):$this->get_component_access_token();
	        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=' . $component_access_token;
	        $param = array(
	            "component_appid" => $this->component_appid,
	        );
	        $res = http_post($url, json_encode($param));
	        $json = json_decode($res, true);
	        S('pre_auth_code',$json['pre_auth_code'],$json['expires_in']);
    	}
    	return S('pre_auth_code');
    }
    
    public function auth_callback () {
        $auth_code = $_GET['auth_code'];
        $res = $this->getAuthInfo($auth_code);
        //把authorizer_refresh_token写入数据库
        // if(isset($_SESSION['uid']))
        if(isset($res))
        {
            $data['authorizer_refresh_token'] = $res['authorizer_refresh_token'];
            $re = M('Merchant')->where(array('uid'=>$_SESSION['user']['id']))->save($data);
        }
        //查看授权方是否已经开通卡券功能，若没有则调用强授权接口确认授权接口
        $authorizer_appid = M('Merchant')->where(array('uid'=>$_SESSION['user']['id']))->getField('appid');
        writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/authorizer_appid.php',$authorizer_appid);
        $result = $this->get_auth_account_info($authorizer_appid);
        writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/result.php',$result);
        if(isset($result['authorization_info']['func_info'][7]['confirm_info']))
        {
            //调用确认接口完成强授权
            $data = array(
                    'component_appid' => $this->component_appid,
                    'authorizer_appid' => $authorizer_appid,
                    'funcscope_category_id' => 8,
                    'confirm_value' => 1,
                );
            $finalresult = $this->confirmAuth($data);
            writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/finalresult.php',$finalresult);
        }
        redirect('?m=Manage&c=Merchant&a=info');
        /*$authorization_code = $this->authorization_code($auth_code);
        redirect('/home/index/auth_success');*/
    }


    //设置测试白名单

    public function setTestName () {
        $component_access_token = $this->get_component_access_token();
        writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/component_access_token.php',$component_access_token);
        $url = 'https://api.weixin.qq.com/card/testwhitelist/set?access_token=' . $component_access_token;
        $param = array(
                    'openid' => array('o8w38s1rk-Tf0mLD8sbSzV0IaD1I'),
                    'username' => array('路飞'),
                );
        var_dump(json_encode($param));die;
        $res = http_post($url, json_encode($param));
        writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/confirmAuth.php',$res);
        $json = json_decode($res, true);
        return $json;
    }



    //强授权确认接口
    public function confirmAuth ($param) {

        $component_access_token = $this->get_component_access_token();
        writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/component_access_token.php',$component_access_token);
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_confirm_authorization?component_access_token=' . $component_access_token;
        $res = http_post($url, json_encode($param));
        writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/confirmAuth.php',$res);
        $json = json_decode($res, true);
        return $json;
    }

    //使用授权码换取公众号的授权信息,为了获取authorizer_refresh_token并存入数据库
    public function getAuthInfo ($auth_code) {
        $component_access_token = $this->get_component_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=' . $component_access_token;
        $param = array(
                "component_appid" => $this->component_appid,
                "authorization_code" => $auth_code,
            );
        $res = http_post($url, json_encode($param));
        writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/getAuthInfo.php',$res);
        $json = json_decode($res, true);
        if($json)
        {
            return $json['authorization_info'];
        }

        // S('authorizer_access_token',$json['authorization_info']['authorizer_access_token'],$json['authorization_info']['expires_in']);
        //获取到威信特公众号的信息，按照道理authorizer_access_token是会过期的,而authorizer_refresh_token不会过期,所以应该把authorizer_access_token存入缓存,而authorizer_refresh_token存入数据库即可
        
    }


    //获取（刷新）授权公众号的令牌
    public function get_authorizer_access_token ($arr) {
            $component_access_token = $this->get_component_access_token();
            $url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=' . $component_access_token; 
            $param = array(
                "component_appid" => $this->component_appid,
                //暂时写死,迟点需要根据数据库或者缓存取出来
                "authorizer_appid" => $arr['appid'],
                //暂时写死,迟点需要根据数据库或者缓存取出来
                "authorizer_refresh_token" => $arr['authorizer_refresh_token'],
            );
            $res = http_post($url, json_encode($param));
            $json = json_decode($res, true);
            return $json;
    }

    //获取授权方的账户信息
    public function get_auth_account_info ($authorizer_appid) {
        $component_access_token = $this->get_component_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=' . $component_access_token; 
        $param = array(
            "component_appid" => $this->component_appid,
            //暂时写死,迟点需要根据数据库或者缓存取出来
            "authorizer_appid" => $authorizer_appid,
        );
        $res = http_post($url, json_encode($param));
        $json = json_decode($res, true);
        // var_dump($json);die;
        return $json;
        // var_dump($json);die;
    }


    //获取授权方的选项设置信息
    public function get_auth_setting_info () {
        $component_access_token = $this->get_component_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_option?component_access_token=' . $component_access_token; 
        $param = array(
            "component_appid" => $this->component_appid,
            //暂时写死,迟点需要根据数据库或者缓存取出来
            "authorizer_appid" => 'wx81545a79d30778f1',
            "option_name" => 'location_report',
        );
        $res = http_post($url, json_encode($param));
        $json = json_decode($res, true);
        return $json;
    }


    //获取component_verify_ticket，用于获取component_access_token，每10分钟推送一次
    public function ticket () {
        $from_xml = file_get_contents("php://input");
        if($from_xml)
        {

            $message = $this->openDecrypt($from_xml);
            //若是推送ticket
            if($message['InfoType'] == 'component_verify_ticket')
            {
                // writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/component_verify_ticket.php',$message);
                S('component_verify_ticket',$message['ComponentVerifyTicket']);
                echo 'success';
                return 'success';
            }
            //若是取消授权
            elseif ($message['InfoType'] == 'unauthorized') 
            {
                /*writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/unauthorized.php',$message);
                Array
                (
                    [AppId] => wx4b0fb3d2064af390
                    [CreateTime] => 1446697179
                    [InfoType] => unauthorized
                    [AuthorizerAppid] => wx81545a79d30778f1
                ) */  
                echo '取消成功';
                return '取消成功';
                // var_dump($message);die;
            }
        }

    }


    //消息解密
    public function openDecrypt ($from_xml) {
        $token = $this->token;
        $encodingAesKey = $this->encodingAesKey;
        $appid = $this->component_appid;
        $signature = $_GET['msg_signature'];
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $pc = new \WXBizMsgCrypt($token, $encodingAesKey, $appid);
        $msg = '';
        $errCode = $pc->decryptMsg($signature, $timestamp, $nonce, $from_xml, $msg);
        $array = (array)simplexml_load_string($msg, 'SimpleXMLElement', LIBXML_NOCDATA);
        return $array;
    }


    //消息加密 
    public function enterDecrypt ($from_xml) {
        $token = $this->token;
        $encodingAesKey = $this->encodingAesKey;
        $appid = $this->component_appid;
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $pc = new \WXBizMsgCrypt($token, $encodingAesKey, $appid);
        $encryptMsg = '';
        $errCode = $pc->encryptMsg($from_xml, $timestamp, $nonce,$encryptMsg);
        $data = array(
            'errCode' => $errCode,
            'encryptMsg' => $encryptMsg,
            );
        return $data;
    }



    /*消息接收*/
    public function receive () 
    {
    	$from_xml = file_get_contents("php://input");
        if($from_xml)
        {
            $message = $this->openDecrypt($from_xml);
			writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/message.php',$message);
            $appid = $_GET['appid'];
            //消息解密
            $message = $this->openDecrypt($from_xml);

        	//判断是否微信的测试账户
        	//威信特的appid = wx81545a79d30778f1; 微信测试号的appid = wx570bc396a51b8ff8;
        	if($appid == 'wx570bc396a51b8ff8')
        	{
        		$from = $message['ToUserName'];
        		$to = $message['FromUserName'];
        		$textTpl = '<xml>
	                <ToUserName><![CDATA[%s]]></ToUserName>
	                <FromUserName><![CDATA[%s]]></FromUserName>
	                <CreateTime>%s</CreateTime>
	                <MsgType><![CDATA[%s]]></MsgType>
	                <Content><![CDATA[%s]]></Content>
	                <FuncFlag>0</FuncFlag>
	            </xml>';

        		if($message['Event'])
        		{
	        		$content = $message['Event'] . 'from_callback';
        			$text = sprintf($textTpl,$to,$from,time(),'text', $content);
        			//消息加密
					$re = $this->enterDecrypt($text);
					if($re['errCode'] == 0)
					{
						echo $re['encryptMsg'];die;
					}
	        		
        		}
        		elseif ($message['Content'] == 'TESTCOMPONENT_MSG_TYPE_TEXT')
        		{
	        		$content = 'TESTCOMPONENT_MSG_TYPE_TEXT_callback';
	        		$text = sprintf($textTpl,$to,$from,time(),'text', $content);
        			//消息加密
					$re = $this->enterDecrypt($text);
					if($re['errCode'] == 0)
					{
						echo $re['encryptMsg'];die;
					}
        		}
        		elseif (strpos($message['Content'], 'QUERY_AUTH_CODE') !== false)
        		{

        			//直接回复空串（指字节长度为0的空字符串，而不是XML结构体中content字段的内容为空）
        			echo '';

        			//回复空字符串（指XML结构体中content字段的内容为空）
        			/*$content = '';
	        		$text = sprintf($textTpl,$to,$from,time(),'text', $content);
        			//消息加密
					$re = $this->enterDecrypt($text);
					if($re['errCode'] == 0)
					{
						echo $re['encryptMsg'];
					}*/
	        		//获取auth_code进行客服回复
	        		$auth_code = substr($message['Content'], 16);
	        		$send = array(
                    "touser" => $message['FromUserName'],
                    "msgtype" => "text",
                    "text" => array(
                        "content" => $auth_code . "_from_api",
                        ),
                    );
                	$result = $this->sendOpenCustomMessage($send, $auth_code);
        		}
        	}
            else
            {
                //若不是微信的测试账户
                // 子商户资质审核事件
                if($message['Event'] == 'card_merchant_auth_check_result')
                {
                        // 通过时
                    if ($message['IsPass']) {
                        M('Merchant')->where(array('appid'=>$message['appid']))->save(array('status'=>1));
                    }
                    else
                    {
                        // 不通过时,被驳回,数据库中记录被驳回的原因
                        M('Merchant')->where(array('appid'=>$message['appid']))->save(array('status'=>2,'rejectreason'=>$message['Reason']));

                    }
                }
                // 生成的卡券通过审核时
                elseif($message['Event'] == 'card_pass_check')
                {
                    $re = M('Coupon')->where(array('card_id'=>$message['CardId']))->save(array('status'=>2));
                    writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/card_pass_check.php',$re);
                }
                // 生成的卡券不能通过审核时
                elseif($message['Event'] == 'card_not_pass_check')
                {
                    $re = M('Coupon')->where(array('card_id'=>$message['CardId']))->save(array('status'=>1));
                    writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/card_not_pass_check.php',$re);
                }
                // 卡券被用户领取时
                elseif($message['Event'] == 'user_get_card')
                {
                    $data['code'] = $message['UserCardCode'];
                    $data['card_id'] = $message['CardId'];
                    $data['receiver'] = $message['FromUserName'];
                    $data['donator'] = isset($message['FriendUserName'])?$message['FriendUserName']:'';
                    $data['createtime'] = time();
                    $data['IsGiveByFriend'] = isset($message['IsGiveByFriend'])?$message['IsGiveByFriend']:0;
                    $data['oldcode'] = isset($message['OldUserCardCode'])?$message['OldUserCardCode']:'';
                    $data['OuterId'] = isset($message['OuterId'])?$message['OuterId']:0;
                    $data['oldcode'] = $message['OldUserCardCode'];
                    //新增到卡券详情表
                    $re = M('Code')->add($data);
                    //如果是转赠的,则修改卡券表的最终拥有者字段
                    if($data['IsGiveByFriend'])
                    {
                        M('Code')->where(array('code'=>$data['oldcode']))->save(array('finalget'=>0));
                    }
                    //如果不是转赠的,则修改卡券表的库存
                    else
                    {

                        $result = M('Coupon')->where(array('card_id'=>$message['CardId']))->setInc('collected');
                    }
                }
                // 卡券被用户删除时
                elseif($message['Event'] == 'user_del_card')
                {
                    $re = M('Code')->where(array('code'=>$message['UserCardCode']))->save(array('isdelete'=>1));

                }
                // 卡券被核销时
                elseif($message['Event'] == 'user_consume_card')
                {
                    switch ($message['ConsumeSource']) 
                    {
                        case 'FROM_API':
                            $data['ConsumeSource'] = 1;
                            break;
                        case 'FROM_API':
                            $data['FROM_MP'] = 2;
                            break;
                        case 'FROM_MOBILE_HELPER':
                            $data['ConsumeSource'] = 3;
                            break;
                        default:
                            # code...
                            break;
                    }
                    $data['isconsumed'] = 1;
                    $re = M('Code')->where(array('code'=>$message['UserCardCode']))->save($data);
                }
            }
        }
    }

    /**
     * 发送客服消息
     * @param array $data 消息结构{"touser":"OPENID","msgtype":"news","news":{...}}
     * @return boolean|array
     */
    public function sendOpenCustomMessage ($data, $auth_code = '') {
        $res = $this->getAuthInfo($auth_code);
        $access_token = $res['authorizer_access_token'];
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $access_token;
        
        $result = http_post($url, json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
        }
        return false;
    }



    //母商户资质审核查询接口
    public function checkparentauth () {
    	
    	$component_access_token = $this->get_component_access_token();
    	$url = 'http://api.weixin.qq.com/cgi-bin/component/check_card_agent_qualification?access_token=' . $component_access_token;
    	$result = http_get($url);
        $json = json_decode($result,true);
        return $json;
    	// var_dump($json);die;
    	// array(1) { ["result"]=> string(11) "RESULT_PASS" }
    }


    //查询威信特是否通过子商户审核 
    public function checkvsontor () {
    	$appid = 'wx81545a79d30778f1';
    	$re = $this->checksonauth($appid);
    }



	//子商户资质审核查询接口  注意，用母商户账号去调用接口，但接口内传入的是子商户的公众号AppID
    public function checksonauth ($appid) {

    	$component_access_token = $this->get_component_access_token();
    	$url = 'http://api.weixin.qq.com/cgi-bin/component/check_card_merchant_qualification?access_token=' . $component_access_token;
    	$param = array(
	            "appid" => $appid,
	        );
        $res = http_post($url, json_encode($param));
        var_dump($res);die;
        $json = json_decode($res, true);
        return $json;
    	// var_dump($json);die;
    	// array(1) { ["result"]=> string(11) "RESULT_PASS" }
    }


    //卡券开放类目查询接口
    public function checkprimary_category_id () {
        
        $component_access_token = $this->get_component_access_token();
        $url = 'https://api.weixin.qq.com/card/getapplyprotocol?access_token=' . $component_access_token;
        $result = http_get($url);
        $json = json_decode($result,true);
        return $json;
        // array(1) { ["result"]=> string(11) "RESULT_PASS" }
    }

    // 上传临时素材
    public function uploadTempImg($filename){
        $component_access_token = $this->get_component_access_token();
        $url  = "https://api.weixin.qq.com/cgi-bin/media/upload";
        $filename2 = realpath($filename);
        $param = array(
            'access_token' => $component_access_token,
            'type'         => 'image'
        );
        $file = array(
                "media" => "@{$filename2}",
            );
        

        $res = self::http($url, $param, $file, 'POST');
        $json = json_decode($res, true);
        return $json;
    }


    public function uploadLogoImg ($filename,$arr) {
        if(!S($arr['appid']))
        {
            //根据authorizer_refresh_token和appid查找authorizer_access_token
            $re = $this->get_authorizer_access_token($arr);
            $authorizer_access_token = $re['authorizer_access_token'];
            $expires_in = $re['expires_in'];
            S($arr['appid'],$authorizer_access_token,$expires_in);
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/media/uploadimg';
        $filename2 = realpath($filename);
        $param = array(
            'access_token' => S($arr['appid']),
            'type'         => 'image'
        );
        $file = array(
                "media" => "@{$filename2}",
            );
        $res = self::http($url, $param, $file, 'POST');
        // var_dump($res);die;
        // string(132) "{"url":"http:\/\/mmbiz.qpic.cn\/mmbiz\/tApx2yBKVOfc0KqKCBsrXgK5RRmFZYfbtiatRjfMeQA0t0yU17mNT2ZB8XsBHEwr2zWVYYLLepDWA9EibN0rel6g\/0"}"
        // $res = http_post($url, json_encode($param));
        //威信特 logo 的media_id : 
        // lfY_R18sBVYxy5Q7GLpfE-hS0BfPExvK037yhsEY-jxC1q0WGUe0ZxuA3G4uOkUn
        // kQ9h6fHsTJz5g9NtOgznDfzW0Vc-5WGzpxQQSL182gwj2Rtrr0MCaGVhPaO6txeR

        //威信特 营业执照 的media_id : 
        // 6587MzaEEyA4UK1MkA3vMIWebBvlG5OTEj3u_9O8J8QQKGaek7gFgrMoxd8sjO5Y
        // 47ABkcV8ys0aL4ld1oPM2kl_DReoLc5JUhTjjmRwNc-NKgZb-4deO6lHRgMy3Ko0

        //威信特 授权函 的media_id : 
        // A1aHBbl_pti4cL8P7GCmqID4z_MkIAlnB6hwxl-twdm19BVwdWnOTZMJFX8RLPfz
        // JZ99lKnspZlOkbTrRuBwOPw-LXxypOpYe_2zG6654PTOOT3CqtP8KYhhI-N58Pxx
        $json = json_decode($res, true);
        return $json;
    }


    //协助制券
    public function createCoupon ($arr) {
        if(!S($arr['appid']))
        {
            //根据authorizer_refresh_token和appid查找authorizer_access_token
            $re = $this->get_authorizer_access_token($arr);
            $authorizer_access_token = $re['authorizer_access_token'];
            $expires_in = $re['expires_in'];
            S($arr['appid'],$authorizer_access_token,$expires_in);
        }
        
        $url = 'https://api.weixin.qq.com/card/create?access_token=' . S($arr['appid']); 
        
        /*$param = '{ 
"card": {
   "card_type": "GROUPON",
   "groupon": {
       "base_info": {
           "logo_url": 
"http://mmbiz.qpic.cn/mmbiz/iaL1LJM1mF9aRKPZJkmG8xXhiaHqkKSVMMWeN3hLut7X7hicFNjakmxibMLGWpXrEXB33367o7zHN0CwngnQY7zb7g/0",
           "brand_name":"海底捞",
           "code_type":"CODE_TYPE_TEXT",
           "title": "132元双人火锅套餐",
           "sub_title": "周末狂欢必备",
           "color": "Color010",
           "notice": "使用时向服务员出示此券",
           "service_phone": "020-88888888",
           "description": "不可与其他优惠同享\n如需团购券发票，请在消费时向商户提出\n店内均可使用，仅限堂食",
           "date_info": {
               "type": "DATE_TYPE_FIX_TIME_RANGE",
               "begin_timestamp": 1397577600 ,
               "end_timestamp": 1422724261
           },
           "sku": {
               "quantity": 500000
           },
           "get_limit": 3,
           "use_custom_code": false,
           "bind_openid": false,
           "can_share": true,
         "can_give_friend": true,
           "location_id_list" : [123, 12321, 345345],
           "custom_url_name": "立即使用",
           "custom_url": "http://www.qq.com",
           "custom_url_sub_title": "6个汉字tips",
           "promotion_url_name": "更多优惠",
         "promotion_url": "http://www.qq.com",
           "source": "大众点评"   
       },
       "deal_detail": "以下锅底2选1（有菌王锅、麻辣锅、大骨锅、番茄锅、清补凉锅、酸菜鱼锅可选）：\n大锅1份 12元\n小锅2份 16元 "}
 }
}';*/

        $begin_timestamp = strtotime($_POST['begin_timestamp']);
        $end_timestamp = strtotime($_POST['end_timestamp']);
        $can_share = $_POST['can_share'] == 'true'?true:false;
        $can_give_friend = $_POST['can_give_friend'] == 'true'?true:false;
        $param = array(
                'card' => array(
                        'card_type' => $_POST['card_type'],
                        strtolower($_POST['card_type']) => array(
                                'base_info' => array(
                                    'logo_url' => $_POST['logo_url_a'],
                                    "brand_name" => $_POST['brand_name'],
                                    'code_type' => $_POST['code_type'],
                                    'title' => $_POST['title'],
                                    'sub_title' => $_POST['sub_title'],
                                    'color' => $_POST['color'],
                                    'notice' => $_POST['notice'],
                                    'service_phone' => $_POST['service_phone'],
                                    'description' => $_POST['description'],
                                    'date_info' => array(
                                            'type' => $_POST['type'],
                                            'begin_timestamp' => $begin_timestamp,
                                            'end_timestamp' => $end_timestamp,
                                            'fixed_term' => $_POST['fixed_term'],
                                            'fixed_begin_term' => 0,
                                    ),
                                    'sku' => array(
                                            'quantity' => $_POST['quantity']
                                    ),
                                    'get_limit' => $_POST['get_limit'],
                                    'can_share' => $can_share,
                                    'can_give_friend' => $can_give_friend,
                                ),
                                'deal_detail' => $_POST['deal_detail'],
                                'least_cost' => $_POST['least_cost'],
                                'reduce_cost' => $_POST['reduce_cost'],
                                'discount' => $_POST['discount'],
                                'gift' => $_POST['gift'],
                                'default_detail' => $_POST['default_detail'],
                        ),
                    ),
            );

        //把空字段删除,否则会报错
        foreach ($param['card'][strtolower($_POST['card_type'])] as $k => $v) {
            if(!$v)
            {
                unset($param['card'][strtolower($_POST['card_type'])][$k]);
            }
        }

/*$param = '{
"card":{
    "card_type":"GROUPON",
    "groupon":
        {"base_info":{
            "logo_url":"",
            "brand_name":"\u5546\u6237\u540d\u5b57",
            "code_type":"CODE_TYPE_QRCODE",
            "title":"\u6807\u9898",
            "sub_title":"\u526f\u6807\u9898",
            "color":"Color030","notice":"\u5361\u5238\u4f7f\u7528\u63d0\u9192",
            "service_phone":"\u5ba2\u670d\u7535\u8bdd",
            "description":"\u5361\u5238\u4f7f\u7528\u8bf4\u660e",
            "date_info":{
                "type":"DATE_TYPE_FIX_TERM",
                "begin_timestamp":"1",
                "end_timestamp":"2"
            },
            "sku":{
                "quantity":"500"
            },
            "get_limit":"1",
            "can_share":"true",
            "can_give_friend":"true"
        },
        "deal_detail":"\u56e2\u8d2d\u8be6\u60c5"}
    }}';

$param = '{ 
"card": {
   "card_type": "GROUPON",
   "groupon": {
       "base_info": {
           "logo_url": 
"http://mmbiz.qpic.cn/mmbiz/tApx2yBKVOfc0KqKCBsrXgK5RRmFZYfbtiatRjfMeQA0t0yU17mNT2ZB8XsBHEwr2zWVYYLLepDWA9EibN0rel6g/0",
           "brand_name":"威信特传媒",
           "code_type":"CODE_TYPE_TEXT",
           "title": "58元h5大讲堂",
           "sub_title": "大师与你面对面探讨h5魅力",
           "color": "Color010",
           "notice": "使用时向服务员出示此券",
           "service_phone": "13552588026",
           "description": "不可与其他优惠同享\n如需团购券发票，请在消费时向商户提出",
           "date_info": {
               "type": "DATE_TYPE_FIX_TIME_RANGE",
               "begin_timestamp": 1446624578 ,
               "end_timestamp": 1446912000
           },
           "sku": {
               "quantity": 5000
           },
           "get_limit": 3,
           "use_custom_code": false,
           "bind_openid": false,
           "can_share": true,
           "can_give_friend": true,
           "custom_url_name": "立即使用",
           "custom_url": "http://www.qq.com",
           "custom_url_sub_title": "6个汉字tips",
           "promotion_url_name": "更多优惠",
           "promotion_url": "http://www.qq.com",
           "source": "大众点评"   
       },
       "deal_detail": "凭此团购券到珠海大会堂参加讲座,有机会抽取幸运大奖,先到先得哦~"}
 }
}';*/
        writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/param.php',$param);
        writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/json_encodeparam.php',json_encode($param));

        // var_dump(json_encode($param));die;
        $res = http_post($url, json_encode($param,JSON_UNESCAPED_UNICODE));
// 第一次制作成功的团购券string(68) "{"errcode":0,"errmsg":"ok","card_id":"p8w38s2Ho1EZljnlHmPjlF3nIFRI"}"
        $json = json_decode($res, true);
        writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/json.php',$json);
        return $json;
    }






    //删除卡券string(68)  误操作而制作的团购券,跟第一次的券应该是重复了,但删除不了,显示没有权限"{"errcode":0,"errmsg":"ok","card_id":"p8w38s3unj6ZHzSfkEe1kVM1gM-U"}"
    public function delCoupon ($arr,$card_id='') {
        if(!S($arr['appid']))
        {
            //根据authorizer_refresh_token和appid查找authorizer_access_token
            $re = $this->get_authorizer_access_token($arr);
            $authorizer_access_token = $re['authorizer_access_token'];
            $expires_in = $re['expires_in'];
            S($arr['appid'],$authorizer_access_token,$expires_in);
        }
        $url = 'https://api.weixin.qq.com/card/delete?access_token=' . S($arr['appid']); 
        $param = array(
            "card_id" => $card_id,
        );
        $res = http_post($url, json_encode($param));
        $json = json_decode($res, true);
        return $json;
    }


    //查询卡券详情
    public function checkCouponStatus ($arr,$card_id) {
        if(!S($arr['appid']))
        {
            //根据authorizer_refresh_token和appid查找authorizer_access_token
            $re = $this->get_authorizer_access_token($arr);
            $authorizer_access_token = $re['authorizer_access_token'];
            $expires_in = $re['expires_in'];
            S($arr['appid'],$authorizer_access_token,$expires_in);
        }
        $url = 'https://api.weixin.qq.com/card/get?access_token=' . S($arr['appid']);
        $param = array(
                "card_id" => $card_id,
            );
        $res = http_post($url, json_encode($param));
        


        $json = json_decode($res, true);
        return $json;

    }


    //获取二维码(默认为永久二维码)
    public function getQRCode ($arr,$card_id) {
        if(!S($arr['appid']))
        {
            //根据authorizer_refresh_token和appid查找authorizer_access_token
            $re = $this->get_authorizer_access_token($arr);
            $authorizer_access_token = $re['authorizer_access_token'];
            $expires_in = $re['expires_in'];
            S($arr['appid'],$authorizer_access_token,$expires_in);
        }
        $url = 'https://api.weixin.qq.com/card/qrcode/create?access_token=' . S($arr['appid']);
        $param = array(
                "action_name" => 'QR_CARD',
                // "expire_seconds" => 6048000,
                "action_info" => array(
                        'card' => array(
                                'card_id' => $card_id,
                                'code' => '',
                                'openid' => '',
                                'is_unique_code' => false,
                            )
                    )
            );
        /*$param = '{"action_name": "QR_CARD","expire_seconds":1800,"action_info": {"card": {"card_id": "p8w38s2Ho1EZljnlHmPjlF3nIFRI", "code": "","openid": "","is_unique_code": false ,"outer_id" : 13}}}';*/
        
        $res = http_post($url, json_encode($param));
        $json = json_decode($res, true);
        return $json;
    }

    protected static function http($url, $param, $data = '', $method = 'GET'){
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        );

        /* 根据请求类型设置特定参数 */
        $opts[CURLOPT_URL] = $url . '?' . http_build_query($param);

        if(strtoupper($method) == 'POST'){
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $data;
            
            if(is_string($data)){ //发送JSON数据
                $opts[CURLOPT_HTTPHEADER] = array(
                    'Content-Type: application/json; charset=utf-8',  
                    'Content-Length: ' . strlen($data),
                );
            }
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        //发生错误，抛出异常
        if($error) throw new \Exception('请求发生错误：' . $error);

        return  $data;
    }


    //子商户资质提交接口
    public function submitSonShop ($param = '') {

        $component_access_token = $this->get_component_access_token();
        $url = 'http://api.weixin.qq.com/cgi-bin/component/upload_card_merchant_qualification?access_token=' . $component_access_token;
        $res = http_post($url, json_encode($param));
        /*string(25) "{"errcode":0,"errmsg":""}"*/
        /*再次点击提交时: string(66) "{"errcode":61022,"errmsg":"can't resubmit hint: [CI9kfa0839vr18]"}"*/
        $json = json_decode($res, true);
        return $json;
        // var_dump($json);die;
        // array(1) { ["result"]=> string(11) "RESULT_PASS" }
    }


    //子商户资质审核查询接口
    public function checkSonShop ($appid = '') {
        $component_access_token = $this->get_component_access_token();
        $url = 'http://api.weixin.qq.com/cgi-bin/component/check_card_merchant_qualification?access_token=' . $component_access_token;
        $param = array(
            'appid' => $appid
        );
        $res = http_post($url, json_encode($param));
        //string(28) "{"result":"RESULT_CHECKING"}"
        $json = json_decode($res, true);
        return $json;
    }


    // 查询卡券状态能否被核销
    public function checkCouponConsumeOrNot ($arr,$code) {
        if(!S($arr['appid']))
        {
            //根据authorizer_refresh_token和appid查找authorizer_access_token
            $re = $this->get_authorizer_access_token($arr);
            $authorizer_access_token = $re['authorizer_access_token'];
            $expires_in = $re['expires_in'];
            S($arr['appid'],$authorizer_access_token,$expires_in);
        }
        $url = 'https://api.weixin.qq.com/card/code/get?access_token=' . S($arr['appid']); 
        $param = array(
            "code" => $code,
            //写死为true
            "check_consume" => true,
        );
        $res = http_post($url, json_encode($param));
        $json = json_decode($res, true);
        return $json;
    }

    // 核销卡券
    public function consumeCoupon ($arr,$code) {
        if(!S($arr['appid']))
        {
            //根据authorizer_refresh_token和appid查找authorizer_access_token
            $re = $this->get_authorizer_access_token($arr);
            $authorizer_access_token = $re['authorizer_access_token'];
            $expires_in = $re['expires_in'];
            S($arr['appid'],$authorizer_access_token,$expires_in);
        }
        $url = 'https://api.weixin.qq.com/card/code/consume?access_token=' . S($arr['appid']); 
        $param = array(
            "code" => $code,
        );
        $res = http_post($url, json_encode($param));
        $json = json_decode($res, true);
        return $json;
    }

}