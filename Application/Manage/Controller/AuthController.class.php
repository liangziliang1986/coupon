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
    	$this->get_component_access_token();
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
    
    public function auth_callback () {
    	$auth_code = $_GET['auth_code'];
        $expires_time = $_GET['expires_in'];
        $this->getAuthInfo($auth_code);
        /*$authorization_code = $this->authorization_code($auth_code);
        redirect('/home/index/auth_success');*/
    }


    //使用授权码换取公众号的授权信息
    public function getAuthInfo ($auth_code) {
    	$component_access_token = $this->get_component_access_token();
    	$url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=' . $component_access_token;
    	$param = array(
	            "component_appid" => $this->component_appid,
	            "authorization_code" => $auth_code,
	        );
        $res = http_post($url, json_encode($param));
        $json = json_decode($res, true);
        S('authorizer_access_token',$json['authorization_info']['authorizer_access_token'],$json['authorization_info']['expires_in']);
        return S('authorizer_access_token');
        //获取到威信特公众号的信息，按照道理authorizer_access_token是会过期的,而authorizer_refresh_token不会过期,所以应该把authorizer_access_token存入缓存,而authorizer_refresh_token存入数据库即可
        /*Array
(
    [authorization_info] => Array
        (
            [authorizer_appid] => wx81545a79d30778f1
            [authorizer_access_token] => ljTkxGTjDVnPJxB2ARqtVnDMgUoTCAqjd8xDs4TRxdsxmCnD6FiLZwiBBD3ERCZvhJCRU2hW2EnXeZm5_eqR2hhmAPSFiM7stXD_a3CsYicywNRpYFTsgTY4mQCmyNQP
            [expires_in] => 7200
            [authorizer_refresh_token] => refreshtoken@@@HiwdXe2VH794Zo-zHlML-KfpitL0qEq7T5t44ESKYeo
            [func_info] => Array
                (
                    [0] => Array
                        (
                            [funcscope_category] => Array
                                (
                                    [id] => 1
                                )
                        )
                    [1] => Array
                        (
                            [funcscope_category] => Array
                                (
                                    [id] => 2
                                )
                        )
                    [2] => Array
                        (
                            [funcscope_category] => Array
                                (
                                    [id] => 3
                                )
                        )
                    [3] => Array
                        (
                            [funcscope_category] => Array
                                (
                                    [id] => 4
                                )
                        )
                    [4] => Array
                        (
                            [funcscope_category] => Array
                                (
                                    [id] => 5
                                )
                        )
                    [5] => Array
                        (
                            [funcscope_category] => Array
                                (
                                    [id] => 6
                                )
                        )
                    [6] => Array
                        (
                            [funcscope_category] => Array
                                (
                                    [id] => 7
                                )
                        )
                    [7] => Array
                        (
                            [funcscope_category] => Array
                                (
                                    [id] => 8
                                )

                            [confirm_info] => Array
                                (
                                    [need_confirm] => 1
                                    [already_confirm] => 0
                                    [can_confirm] => 1
                                )
                        )
                    [8] => Array
                        (
                            [funcscope_category] => Array
                                (
                                    [id] => 11
                                )
                        )
                    [9] => Array
                        (
                            [funcscope_category] => Array
                                (
                                    [id] => 12
                                )
                        )
                    [10] => Array
                        (
                            [funcscope_category] => Array
                                (
                                    [id] => 13
                                )
                        )
                    [11] => Array
                        (
                            [funcscope_category] => Array
                                (
                                    [id] => 10
                                )
                        )
                )
        )
)
*/
    }


    //获取（刷新）授权公众号的令牌
    public function get_authorizer_access_token () {
    	//若authorizer_access_token不过期，则直接返回,过期,则需要重新刷新
    	if(!S('authorizer_access_token'))
    	{

    		$component_access_token = $this->get_component_access_token();
	        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=' . $component_access_token; 
	        $param = array(
	            "component_appid" => $this->component_appid,
	            //暂时写死,迟点需要根据数据库或者缓存取出来
	            "authorizer_appid" => 'wx81545a79d30778f1',
	            //暂时写死,迟点需要根据数据库或者缓存取出来
	            "authorizer_refresh_token" => 'refreshtoken@@@nglFn44mWoc4spO_JtjIMymFVGZ_k25-g4LbnZH86NM',
	        );
	        $res = http_post($url, json_encode($param));
	        $json = json_decode($res, true);
	        S('authorizer_access_token',$json['authorizer_access_token'],$json['expires_in']);
    	}
    	return S('authorizer_access_token');
    }


    //获取授权方的账户信息
    public function get_auth_account_info () {
    	$component_access_token = $this->get_component_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=' . $component_access_token; 
        $param = array(
            "component_appid" => $this->component_appid,
            //暂时写死,迟点需要根据数据库或者缓存取出来
            "authorizer_appid" => 'wx81545a79d30778f1',
        );
        $res = http_post($url, json_encode($param));
        $json = json_decode($res, true);
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
 		       	S('component_verify_ticket',$message['ComponentVerifyTicket'],600);
 		       	echo 'success';
 		       	return 'success';
        	}
        	//若是取消授权
        	elseif ($message['InfoType'] == 'unauthorized') 
        	{
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


    //获取预授权码，用于引导用户点击链接时获取真正授权码。
    public function get_pre_auth_code () {
    	if(!S('pre_auth_code'))
    	{
	        $component_access_token = S('component_access_token');
	        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=' . $component_access_token;
	        $param = array(
	            "component_appid" => 'wx4b0fb3d2064af390',
	        );
	        $res = http_post($url, json_encode($param));
	        $json = json_decode($res, true);
	        S('pre_auth_code',$json['pre_auth_code'],1700);
    	}
    	return S('pre_auth_code');
    }
 

    public function receive2 () {
    	$from_xml = file_get_contents("php://input");
    	$message = $this->openDecrypt($from_xml);
		$textTpl = '<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                <FuncFlag>0</FuncFlag>
            </xml>';
    	$content = '您好';
        $text = sprintf($textTpl,$message['FromUserName'],$message['ToUserName'],time(),'text', $content);
        //加密
		$re = $this->enterDecrypt($text);
		if($re['errCode'] == 0)
		{
			echo $re['encryptMsg'];die;
		}
    }



    /*消息接收*/
    public function receive () 
    {
    	$from_xml = file_get_contents("php://input");
        if($from_xml)
        {
        	
        	$appid = $_GET['appid'];
        	//消息解密
        	$message = $this->openDecrypt($from_xml);
			// writeArray('/home/wwwroot/coupon/Application/Runtime/Logs/Home/message.php',$message);

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
        }
    }

    /**
     * 发送客服消息
     * @param array $data 消息结构{"touser":"OPENID","msgtype":"news","news":{...}}
     * @return boolean|array
     */
    public function sendOpenCustomMessage ($data, $auth_code = '') {
        $access_token = S('authorizer_access_token')?S('authorizer_access_token'):$this->getAuthInfo($auth_code);
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


}