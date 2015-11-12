<?php
/*vendor('MobileDetect.Mobile_Detect');
require_once('./Functions/pclzip.lib.php');
require_once('./ThinkPHP/Library/Org/Crypt/Crypt.class.php');*/
//设跳转
function set_session_redirect($session_key='show_success',$session_value,$url = null) {
  session($session_key,$session_value);
  if(!$url) {
    $url = I('server.HTTP_REFERER');
  }
  redirect($url);
  exit;
}

//生成唯一订单号
function create_uqid() {
    $uid = uniqid("", true);
    $data = $namespace;
    $data .= $_SERVER['REQUEST_TIME'];
    $data .= $_SERVER['HTTP_USER_AGENT'];
    $data .= $_SERVER['LOCAL_ADDR'];
    $data .= $_SERVER['LOCAL_PORT'];
    $data .= $_SERVER['REMOTE_ADDR'];
    $data .= $_SERVER['REMOTE_PORT'];
    $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
    $qid = substr($hash,  2,  9) . substr($hash, 12,  9);
    return $qid;        
}

//对象转数组
function objectToArray($d) {
    if (is_object($d)) {
        $d = get_object_vars($d);
    }
    if (is_array($d)) {
        return array_map(__FUNCTION__, $d);
    } else {
        return $d;
    }
}

//字符串对象转数组
function objectStrToArray ($str) {
    $obj = json_decode($str);
    return objectToArray($obj);
}


 /**
 * base64转图片
 * @param string $data base64的字符串
 * @return string 文件路径
 */
function base642img ($data, $file = null) {
    preg_match('/^(data:\s*image\/(\w+);base64,)/', $data, $result);
    $file_ext = $result[2];
    $data = base64_decode(str_replace($result[1], '', $data));
    // file_put_contents($new_file, )
    $year = date('Y');
    $month = date('m');
    $day = date('d');
    $sub_file = $year . '-' . $month . '-' . $day;
    $root = '/Public/UploadFiles/' . $sub_file;
    $file = empty($file) ?  dirname(dirname(__FILE__)) . $root : $file;
    if (!file_exists($file)) {
        recursiveMkdir($file);
    }
    $file_name = '/' . get_randomstr(12). '.' . $file_ext;
    $res = file_put_contents($file . $file_name, $data);
    if ($res) {
        return $sub_file . $file_name;
    }
}

 /**
 * 生成随机字符串
 * @param string $lenth 长度
 * @return string 字符串
 */
function get_randomstr($lenth = 6, $type = 'all') {
    if ($type == 'num') {
        $str = '0123456789';
    } else if ($type == 'string') {
        $str = 'abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
    } else {
        $str = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
    }
    return get_random($lenth, $str);
}


/**
* 产生随机字符串
*
* @param    int        $length  输出长度
* @param    string     $chars   可选的 ，默认为 0123456789
* @return   string     字符串
*/
function get_random($length, $chars = '0123456789') {
    $hash = '';
    $max = strlen($chars) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

/**
* 
* @param name string 要保存的名
* @param value string 设置的值（可选）
* @param expire int 过期时间 （可选）
* @return name的值
*/
//设置session过期
function expire_session ($name = '', $value = '', $expire = 60) {
    if ($value === null) {
        session($name, null);
        return null;
    } else if (!empty($value)) {
        $tmp['old_time'] = time();
        $tmp['key'] = $value;
        $tmp['expire'] = $expire;
        session($name, $tmp);
        $res = session($name);
    } else {
        $now = time();
        $v = session($name);
        $old_time = $v['old_time'];
        $expire = $v['expire'];
        if ($v && $now > $old_time + $expire) {
            session($name, null);
            return null;
        } else {
            $res = session($name);
            return $res['key'];
        }
    }
}



//将一维数组转换为二位数组
function oneDimensionalToTwo ($data, $len = 4, $padding = false) {
    $count = count($data);
    $num = 0;
    for ($i = 0; $i <= $count; $i++) {
        if ($i%$len == 0 && $i != 0 || $i == count($data)) {
            $tem[] = $array;
            $array = array();
            $array[$i] = $data[$i];
            $num++;
        } else {
            $array[$i] = $data[$i];
        }
    }
    if ($padding) {
        $remain = $len - $count%$len;
        for ($j = 0; $j < $remain; $j++) {
            $tem[$num - 1][] = array();
        }
    }
    return $tem;
}

//中英文字符串统计
function strLength($str, $arg){
    $length = strlen(preg_replace('/[\x00-\x7F]/', '', $str));
 
    $arr['en'] = strlen($str) - $length;
    $arr['cn'] = intval($length / 3);//编码GBK，除以2
    if ($arg == 'all') {
        return $arr['en'] + $arr['cn'];
    }
    return $arr;
}

//中英文字符串截取
function abs_substr($str, $start=0, $length, $charset="utf-8", $suffix=true) {  
    if(function_exists("mb_substr"))  
        return mb_substr($str, $start, $length, $charset);  
    elseif(function_exists('iconv_substr')) {  
        return iconv_substr($str,$start,$length,$charset);  
    }  
    $re['utf-8']   = "/[/x01-/x7f]|[/xc2-/xdf][/x80-/xbf]|[/xe0-/xef][/x80-/xbf]{2}|[/xf0-/xff][/x80-/xbf]{3}/";  
    $re['gb2312'] = "/[/x01-/x7f]|[/xb0-/xf7][/xa0-/xfe]/";  
    $re['gbk']    = "/[/x01-/x7f]|[/x81-/xfe][/x40-/xfe]/";  
    $re['big5']   = "/[/x01-/x7f]|[/x81-/xfe]([/x40-/x7e]|/xa1-/xfe])/";  
    preg_match_all($re[$charset], $str, $match);  
    $slice = join("",array_slice($match[0], $start, $length));  
    if($suffix) return $slice."…";  
    return $slice;  
}

//递归删除文件
function deldir($path){  
    if(!is_dir($path)){  
        return null;  
    }  
    $fh = opendir($path);  
    while(($row = readdir($fh)) !== false){  
        if($row == '.' || $row == '..'){  
            continue;  
        }  
        if(!is_dir($path.'/'.$row)){  
            unlink($path.'/'.$row);  
        }  
        deldir($path.'/'.$row);  
          
    }  
    closedir($fh);  
    if(!rmdir($path)){  
        echo $path.'无权限删除<br>';  
    }  
    return true;  
}

//递归创建文件
function recursiveMkdir($path) {
  if (!file_exists($path)) {
    recursiveMkdir(dirname($path));
    @mkdir($path, 0777);
  }
}

/**
 * 功能：计算文件大小
 * @param int $bytes
 * @return string 转换后的字符串
 */
function get_byte($bytes) {
    if (empty($bytes)) {
        return '--';
    }
    $sizetext = array(" B", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . $sizetext[$i];
}

/**
 * 获取文件目录列表
 * @param string $pathname 路径
 * @param integer $fileFlag 文件列表 0所有文件列表,1只读文件夹,2是只读文件(不包含文件夹)
 * @param string $pathname 路径
 * @return array
 */
function get_file_folder_List($pathname,$fileFlag = 0, $pattern='*') {
    $fileArray = array();
    $pathname = rtrim($pathname,'/') . '/';
    $list   =   glob($pathname.$pattern);
    foreach ($list  as $i => $file) {
        switch ($fileFlag) {
            case 0:
                $fileArray[]=basename($file);
                break;
            case 1:
                if (is_dir($file)) {
                    $fileArray[]=basename($file);
                }
                break;

            case 2:
                if (is_file($file)) {                    
                    $fileArray[]=basename($file);
                }
                break;
            
            default:
                break;
        }
    }    

    if(empty($fileArray)) $fileArray = NULL;
    return $fileArray;
}

//被get_short依赖
function csubstr($str,$start,$len) {
  $strlen = strlen($str);
  $clen = 0;
  for($i=0; $i<$strlen; $i++,$clen++) {
    if ($clen >= $start+$len) {
      break;
    }
    if(ord(substr($str,$i,1))>0xa0) {
      if ($clen>=$start) {
        $tmpstr.=substr($str,$i,3);
      }
      $i = $i+2;
      $clen++;
    } else {
      if ($clen >= $start)
      $tmpstr .= substr($str,$i,1);
    }
  }
  return $tmpstr;
}

//中文截取并填充，依赖于csubstr方法
function get_short($str,$len, $ending="...") {
  $tempstr = csubstr($str,0,$len);
  if ($str<>$tempstr) {
    $tempstr .= $ending;
  }
  return $tempstr; 
}

//实例化redis
function getRedis ($db=5) {
    if(C('REDIS_ON')) {
        $GLOBALS['redis'][$db];
        if (!$GLOBALS['redis'][$db]) {
            $GLOBALS['redis'][$db] = new Redis();
            $GLOBALS['redis'][$db]->connect(C('REDIS_HOST'), C('REDIS_PORT'));
            $GLOBALS['redis'][$db]->select($db);
        }
        return $GLOBALS['redis'][$db];
    }
    include_once('./Functions/noredis.class.php');
    return new Noredis();
}

/**
 * 解压缩
 */
function unZip($path,$file) {
  $zip =  new ZipArchive;
  if ($zip->open($path.$file)) {
    $zip->extractTo('./Buffer/la/');
    $zip->close();
    return true;
  } else {
    return false;
  }
}

/**
 * 用php pclphp进行压缩处理
 */
function unZipPcl($path,$extract_to) {

  $zip = new PclZip($path);
  $list = $zip->extract(PCLZIP_OPT_PATH, $extract_to);
  return $list;
}

/**
 * 查询解压缩文件的内容
 * $path example './zips/' 文件路径后面的斜线也请写上
 * $file example 1.zip
 */
function getFileFromZip($path) {
  $zip = new PclZip($path);
  $list = $zip->listContent();
  return $list;
}

/*生成二维码*/  
  function codeimg($url){
    vendor("phpqrcode.phpqrcode");
    $QRcode = new \QRcode ();
    $level = 'L';
    $size = 2.5;
    $margin= 0;
    $path="/Uploads/code/".md5(microtime()).".jpg";
    $filename ='.'.$path;
    $QRcode::png($url, $filename, $level, $size, $margin);
    return $path;
  }


  /**
 * 获取拼音信息
 * @param     string  $str  字符串
 * @param     int  $ishead  是否为首字母
 * @param     int  $isclose  解析后是否释放资源
 * @param     int  $lang  语言
 * @return    string
 * 用法：$data['EnglishName'] = $this->get_pinyin(iconv('utf-8','gbk//ignore',$utfstr),0);
 */
function get_pinyin($str, $ishead=0, $isclose=1, $lang = 'zh-cn') {
    //global $pinyins;
    $pinyins = array();
    $restr = '';
    $str = trim($str);
    $slen = strlen($str);
    //$str=iconv("UTF-8","gb2312",$str);
    //echo $str;
    if($slen < 2)
    {
        return $str;
    }
    $file = './Data/pinyin-'.$lang.'.dat';
    if (!file_exists($file)) {
        $file = './Data/pinyin-zh-cn.dat';
    }
    if(count($pinyins) == 0)
    {
        $fp = fopen($file, 'r');
        if (false == $fp) {
            return '';
        }
        while(!feof($fp))
        {
            $line = trim(fgets($fp));
            $pinyins[$line[0].$line[1]] = substr($line, 3, strlen($line)-3);
        }
        fclose($fp);
    }


    
    for($i=0; $i<$slen; $i++)
    {
        if(ord($str[$i])>0x80)
        {
            $c = $str[$i].$str[$i+1];
            $i++;
            if(isset($pinyins[$c]))
            {
                if($ishead==0)
                {
                    $restr .= $pinyins[$c];
                }
                else
                {
                    $restr .= $pinyins[$c][0];
                }
            }else
            {
                $restr .= "x";//$restr .= "_";
            }
        }else if( preg_match("/[a-z0-9]/i", $str[$i]) )
        {
            $restr .= $str[$i];
        }
        else
        {
            $restr .= "x";//$restr .= "_";
        }
    }
    if($isclose==0)
    {
        unset($pinyins);
    }
    return $restr;
}

//id加密
function encrypt_id($id, $prefix="LXE", $clear=false) {
    $crypt = new \Crypt();
    $x = $prefix . $crypt->en(intval($id));
    if(!$clear) return $x;

    if(strpos($x, '=')!==false || strpos($x, '-')!==false) {
      // PLog::write("Not a problem: Try again for no '= or -' in encrypt string", "INFO");
      return encrypt_id($id, $prefix, $clear);
    } else {
      return $x;
    }
}

//id解密
  function decrypt_id($eid, $prefix="LXE") {
    // 默认ID都加密了，不允许ID直接访问
    if($_GET['id'] && is_numeric($_GET['id'])) {
      return 0;
    }
    if(strpos($eid, $prefix) === 0) {
      $eid = substr($eid, 3);
      return intval(Crypt::de($eid));
    }
    return $eid;
  }

//curl_get访问远程地址
function http_get($url){
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        return false;
    }
}

/**
 * POST 请求
 * @param string $url
 * @param array $param
 * @param boolean $post_file 是否文件上传
 * @return string content
 */
//curl_post访问远程地址
function http_post($url,$param,$post_file=false){
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    if (is_string($param) || $post_file) {
        $strPOST = $param;
    } else {
        $aPOST = array();
        foreach($param as $key=>$val){
            $aPOST[] = $key."=".urlencode($val);
        }
        $strPOST =  join("&", $aPOST);
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($oCurl, CURLOPT_POST,true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        return false;
    }
}

//加密
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {   
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙   
    $ckey_length = 4;   
    // 密匙   
    $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);   
    // 密匙a会参与加解密   
    $keya = md5(substr($key, 0, 16));   
    // 密匙b会用来做数据完整性验证   
    $keyb = md5(substr($key, 16, 16));   
    // 密匙c用于变化生成的密文   
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): 
    substr(md5(microtime()), -$ckey_length)) : '';   
    // 参与运算的密匙   
    $cryptkey = $keya.md5($keya.$keyc);   
    $key_length = strlen($cryptkey);   
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)， 
    //解密时会通过这个密匙验证数据完整性   
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确   
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :  
    sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;   
    $string_length = strlen($string);   
    $result = '';   
    $box = range(0, 255);   
    $rndkey = array();   
    // 产生密匙簿   
    for($i = 0; $i <= 255; $i++) {   
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);   
    }   
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度   
    for($j = $i = 0; $i < 256; $i++) {   
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;   
        $tmp = $box[$i];   
        $box[$i] = $box[$j];   
        $box[$j] = $tmp;   
    }   
    // 核心加解密部分   
    for($a = $j = $i = 0; $i < $string_length; $i++) {   
        $a = ($a + 1) % 256;   
        $j = ($j + $box[$a]) % 256;   
        $tmp = $box[$a];   
        $box[$a] = $box[$j];   
        $box[$j] = $tmp;   
        // 从密匙簿得出密匙进行异或，再转成字符   
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));   
    }   
    if($operation == 'DECODE') {  
        // 验证数据有效性，请看未加密明文的格式   
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&  
            substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {   
            return substr($result, 26);   
        } else {   
            return '';   
        }   
    } else {   
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因   
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码   
        return $keyc.str_replace('=', '', base64_encode($result));   
    }   
} 

/**
* 获取主题
* @return string 
*/
function get_theme () {
    $theme = I('theme');
    if ($theme == 'Mobile') {
        session('is_pc', null);
    } else if ($theme == 'Computer') {
        session('is_pc', true);
    }
    $is_pc = session('is_pc');
    if ($is_pc) {
        // C('DEFAULT_THEME', 'Computer');
        return 'Computer';
    } else {
        $detect = new \Mobile_Detect;
        // $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
        $deviceType = $detect->isMobile() ? 'Mobile' : 'Computer';
        // $is_mobile = $deviceType == 'Mobile' ? true : false;
        // var_dump($deviceType);
        // C('DEFAULT_THEME', $deviceType);
        return $deviceType;
    }
}

/**
 * @path string 地址路径
 * @param string $param
 * @param array 要写入的数组
 */
function writeArray ($path, $array) {
    if (!$path) {
         $path = '/home/wwwroot/coupon/Application/Runtime/Logs/Home/' . date('d-H-i-s') . 'test.php';
    }
    file_put_contents($path, print_r($array, true));
}

/**
 * @param string $telephone 手机号
 * @param string 带有*号的手机号码：134***76
 */
function get_hide_telephone ($telephone, $length = 3) {
    if (empty($telephone)) {
        return false;
    }
    return substr($user['tel'], 0, $length) . '***' . substr($user['tel'], -$length);
}

/**
 * @param string $email 邮箱
 * @param string 带有*号的邮箱：313***758@qq.com
 */
function get_hide_email ($email, $length = 3) {
    if (empty($email)) {
        return false;
    }
    $email_num = explode('@', $email);
    $l = strlen($email_num[0]);
    $len = ceil($l/$length);
    return substr($email_num[0], 0, $len) . '***' . substr($email_num[0], 1 - $len) . $email_num[1];
}





//验证手机
function is_mobile ($tel) {
 return preg_match('/^1[0-9][0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/', $tel);
}


/**
 * 检查是否是邮箱和检查邮件域所属DNS中的MX记录
 * @param  [string]  $email   [邮箱地址]
 * @param  boolean $test_mx [你需要检查邮件域所属DNS中的MX记录，将函数参数$test_mx 设置为true]
 * @return boolean          [如果是邮件，则返回true]
 */
function is_email ($email, $test_mx = false) {
    if(eregi("^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email))
        if($test_mx)
        {
            list($username, $domain) = split("@", $email);
            return getmxrr($domain, $mxrecords);
        }
        else
            return true;
    else
        return false;
}

function cookie_redirect($key = 'error', $value = '对不起，操作失败', $url = null) {
    cookie($key, $value);
    if(!$url) {
        $url = I('server.HTTP_REFERER');
    }
    redirect($url);
}

//获取远程图片并保持到本地
function download_remote_file_with_fopen($file_url, $save_to)
    {
        $in=    fopen($file_url, "rb");
        $out=   fopen($save_to, "wb");
 
        while ($chunk = fread($in,8192))
        {
            fwrite($out, $chunk, 8192);
        }
 
        fclose($in);
        fclose($out);
    }

//查看数组是否为空(二维数组)
function checkEmptyArray ($data) {
    if(!is_array($data)) return false;
    foreach ($data as $k => $v) {
        if(trim($v))
        {
            return true;
        }
    }
    return false;
}
        