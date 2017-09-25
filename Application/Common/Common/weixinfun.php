<?php

/**
 * 获取或更新微信access_token
 * autor:vjin
 */
function getWeixinToken() {
    $db = M();
    $info = $db->table(C('DB_PREFIX').'config')->where("name='WX_ACCESS_TOKEN'")->getField('value');
    if(!empty($info)){
        $info = json_decode($info,true);
    }
    $expire = time() - intval($info['addtime']);
    if(empty($info['access_token']) || $expire > 6000){
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".C('APPID')."&secret=".C('APPSECRET');
        $res = httpPost('',$url);
        $res=json_decode($res);
        $info['access_token'] = $res->access_token;
        $info['addtime'] = time();
        $str = json_encode($info);
        // S('WX_ACCESS_TOKEN', $str,array('expire'=>6000));
        M()->table(C('DB_PREFIX').'config')->where('name="WX_ACCESS_TOKEN"')->save(array('value'=>$str));
    }
    return $info['access_token'];
}

/**
 * 获取微信jsapi_ticket 
 * [getJsApiTicket description]
 * @return [type] [description]
 */
function getJsApiTicket() {
    $url = "http://test.erpcoo.cn/index.php/Api/Weixin/jsapi_ticket";
    $res=file_get_contents($url);
    return trim($res);
}

/**
 * 微信随机值
 * [createNonceStr description]
 * @param  integer $length [description]
 * @return [type]          [description]
 */
function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

/**
 * 微信jssdk 签名配置值
 * [getSignPackage description]
 * @return [type] [description]
 */
function getSignPackage() {
    $jsapiTicket = getJsApiTicket();
    // 注意 URL 一定要动态获取，不能 hardcode.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $timestamp = time();
    $nonceStr = createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
    $signature = sha1($string);
    $signPackage = array(
      "appId"     => "wx470593af01f9253c",// wx4ef466851d8d86c3 
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
}

/**
 * 下载微信图片到指定目录
 * [showExternalPic description]
 * @param  [type] $openid [description]
 * @param  string $url    [description]
 * @return [type]         [description]
 */
function showExternalPic($openid,$url=""){
    if(file_exists(ROOT."/Uploads/Avatar/$openid.jpg")){
        return "http://gf.erpcoo.cn/Uploads/Avatar/$openid.jpg";
    }
    if(empty($url)){
        return "";
    }
    $types = array(
        'gif'=>'image/gif',
        'jpeg'=>'image/jpeg',
        'jpg'=>'image/jpeg',
        'jpe'=>'image/jpeg',
        'png'=>'image/png',
        );
    $imgData = S('token_'.$openid);
    if (!$imgData){
        $url=urldecode($url);
        $dir = pathinfo($url);
        $host = $dir['dirname'];
        $refer = 'http://www.qq.com/';
        $ch = curl_init($url);
        curl_setopt ($ch, CURLOPT_REFERER, $refer);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        $ext = strtolower(substr(strrchr($url,'.'),1,10));
        $ext='jpg';
        $type = $types[$ext] ? $types[$ext] : 'image/jpeg';
        //header("Content-type: ".$type);
        //echo  $data;
        file_put_contents(ROOT."/Uploads/Avatar/$openid.jpg",$data);
        return "http://gf.erpcoo.cn/Uploads/Avatar/$openid.jpg";
    }else {
        $ext='jpg';
        $type = $types[$ext] ? $types[$ext] : 'image/jpeg';
        //header("Content-type: ".$type);
        //echo  $imgData;
        file_put_contents(ROOT."/Uploads/Avatar/$openid.jpg",$imgData);
        return "http://gf.erpcoo.cn/Uploads/Avatar/$openid.jpg";
    }
}

/**
 * [httpPost description]
 * @param  [type]  $args    [description]
 * @param  [type]  $url     [description]
 * @param  integer $timeout [description]
 * @return [type]           [description]
 */
function httpPost($args, $url, $timeout = 30) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if(is_array($args)){
        $postdata = http_build_query($args);
    }else{
        $postdata=$args;
    }
    if( strpos($url,'https://')!==false ){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
    }
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)'); // 模拟用户使用的浏览器
  
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    // curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $passwd);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

/**
 * 提交数据
 * [socket_post description]
 * @param  [type] $url  [description]
 * @param  [type] $post [description]
 * @return [type]       [description]
 */
function socket_post($url, $post,$access_token = '') {
    $urls = parse_url($url);
    if (!isset($urls['port'])) {
        $urls['port'] = 80;
    }
    if(!empty($urls['query'])){
        $urls['path'] .= "?".$urls['query'];
    }

    $fp = fsockopen($urls['host'], $urls['port'], $errno, $errstr);
    if (!$fp) {
        echo "$errno, $errstr";
        exit();
    }

    $post = json_encode($post);
	
    $length = strlen($post);

    $header = <<<HEADER
POST {$urls['path']} HTTP/1.1
Host: {$urls['host']}
Content-Type: application/json
client_id:2000
client_secret:7534187e1b7acdb0
access_token:{$access_token}
Content-Length: {$length}
Connection: close

{$post}
HEADER;

    fwrite($fp, $header);
    $result = '';
    while (!feof($fp)) {
        // receive the results of the request
        $result .= fread($fp, 512);
    }
    $result = explode("\r\n\r\n", $result, 2);

    $start = strpos($result[1],'{');
    $end = strrpos($result[1],'}');
    $length = $end - $start + 1;

    return substr($result[1],$start,$length);
}

/**
 * curl模拟上传图片
 * [curlPostFile description]
 * @param  [type] $curlPost [description]
 * @param  [type] $url      [description]
 * @return [type]           [description]
 */
function curlPostFile($curlPost,$url){
    header('Content-type:text/html; charset=utf-8'); //声明编码
    //模拟POST上传图片和数据
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true); //POST提交
    curl_setopt($ch, CURLOPT_POSTFIELDS,$curlPost);
    $data =curl_exec($ch);
    curl_close($ch);
    
    return $data;
}

/**
 * 获取指定日期所在月份的第一天和最后一天
 * [getdays description]
 * @param  [type] $day [description]
 * @return [type]      [description]
 */
function getdays($day){
	$firstday = date('Ym01',strtotime($day));
	$lastday = date('Ymd',strtotime("$firstday +1 month -1 day"));
	return array($firstday,$lastday);
}

/**
 * 模拟Post请求
 * [post_curl description]
 * @param  [type] $param [description]
 * @param  string $type  [description]
 * @param  string $url   [description]
 * @return [type]        [description]
 */
function post_curl($param, $type = "post", $url )  //常规流量详情
{
    $o = "";
    foreach ($param as $k => $v) {
        $o .= "$k=" . urlencode($v) . "&";
    }
    $post_data = substr($o, 0, -1);
// echo $url."?".$post_data;
    if ($type == 'get') {
        $content = file_get_contents($url . "?" . $post_data);
        return $content;
    }
    $ch = curl_init();
    curl_setopt_array($ch,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_data
        )
    );
    $content = curl_exec($ch);

    return $content;
}


/**
 * 获取毫秒时间戳
 * [msectime description]
 * @return [type] [description]
 */
function msectime() {
    list($tmp1, $tmp2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);
}

/**
 * 获取星期，7是星期天
 * [getWeek description]
 * @param  [type] $year  [description]
 * @param  [type] $month [description]
 * @param  [type] $day   [description]
 * @return [type]        [description]
 */
function getWeek($year,$month,$day){
    $week=date("w",mktime(0,0,0,$month,$day,$year));//获得星期
    $week = empty($week)?7:$week;
    return $week;//获得星期
} 

/**
 * 生成指定长度随机串
 * [getRandChar description]
 * @param  [type] $length [description]
 * @return [type]         [description]
 */
function getRandChar($length,$type = 0){
    $str = null;
    if(empty($type)){
        $strPol = "0123456789abcdefghijkmnpqrstuvwxyz";
    }else{
        $strPol = "0123456789";
    }
    
    $max = strlen($strPol)-1;

    for($i=0;$i<$length;$i++){
        $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }

    return $str;
}

/**
 * 获取微信弹窗授权--openid 
 * [getNewOpenid description]
 * @return [type] [description]
 */
function getNewOpenid($scope='snsapi_base') {
    $backurl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
    $url="http://test.erpcoo.cn/index.php/Api/Weixin/auth?scope=$scope&redirect_uri=$backurl";
    //$url="http://h5.gemevv.com/h5item/index.php/Api/Weixin/auth?scope=$scope&redirect_uri=$backurl";
    redirect($url);
}


function getAccessToken() {
    $reqtime=time();
    $mdsign=md5('reqtime'.$reqtime.'sadfsdaf323fs3');
    $url="http://test.erpcoo.cn/index.php/Api/Weixin/access_token?reqtime=$reqtime&sign=$mdsign";
    $result=file_get_contents($url);
    $data=json_decode($result,true);
    if($data['status']==1){
        return $data['token']['access_token'];
    }
}

/**
 * 获取用户信息
 * autor:vjin
 */
function getLzzUserInfo($access_token="",$openid="") {
    $urls="https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
    $ress=file_get_contents($urls);
    $data=json_decode($ress);

    $nickname=str_replace(array("'","\\"),array(''),$data->nickname);
    $nickname = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $nickname);

    $city=str_replace(array("'","\\"),array(''),$data->city);
    $city = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '',$city );

    $province=str_replace(array("'","\\"),array(''),$data->province);
    $province = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '',$province );

    $uinfo=D("wechat_group_list")->where("openid='$openid'")->find();
    if(empty($uinfo)){
        $save['openid']=$openid;
        $save['nickname']=$nickname;
        $save['sex']=$data->sex;
        $save['province']=$province;
        $save['city']=$city;
        $save['headimgurl']=$data->headimgurl;
        $save['addtime']=date("Y-m-d H:i:s");
        D("wechat_group_list")->add($save);
    } 
}


/**
 * 获取微信openid----美善品
 * [getMsOpenids description]
 * @return [type] [description]
 */
function getMsOpenid() {
    $backurl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
    $url="http://test.erpcoo.cn/index.php/Api/Weixin/auth?scope=snsapi_base&redirect_uri=$backurl";
    redirect($url);
}

/**
 * 获取用户信息
 * [getVichyOpenid description]
 * @return [type] [description]
 */
function getVichyOpenid() {
    $backurl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
    $url="http://wechat.vichy.com.cn/authorize.aspx?type=cust&usource=h5&callbackurl=$backurl";
    redirect($url);
}

/**
 * 获取jsapi
 * [getJsapiVichy description]
 * @return [type] [description]
 */
function getJsapiVichy(){
    $backurl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
    $url="http://wechat.vichy.com.cn/api/appconfig.ashx?url=$backurl";
    $result=file_get_contents($url);
    $signPackage=json_decode($result,true);
    return $signPackage;
}