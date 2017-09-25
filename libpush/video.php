<?php
/**
 * 修改渠道数据
 * @var string
 */
date_default_timezone_set('Asia/shanghai');
define('ROOT',dirname(__FILE__));
//error_reporting(0);
$localhost="127.0.0.1";
$mysql_user="user226";
$mysql_password="123456";

$link = mysql_connect($localhost, $mysql_user, $mysql_password);
if (!$link) {
    die(mysql_error());
}
mysql_select_db('vichy', $link) or die (mysql_error());
mysql_query('set names utf8');

require_once ROOT.'/vendor/autoload.php';

use WindowsAzure\Common\ServicesBuilder;
use MicrosoftAzure\Storage\Common\ServiceException;

$connectionString="DefaultEndpointsProtocol=https;AccountName=vichy89vocie;AccountKey=db2aqCMPP0MMnV/KVtZplGUu+vOv94eeI5D2b6cASW6JF+aJ4u7JjwNL+13k/u5+k2ZITKTc9kKmoBMNAIQ1oQ==;EndpointSuffix=core.chinacloudapi.cn";
// Create blob REST proxy.
$blobRestProxy = ServicesBuilder::getInstance()->createBlobService($connectionString);


$content = fopen("/www/web/h5/vichy/Uploads/video/20170918/2017091801101859bf553a5a7ef394.amr", "r");
$blob_name = "20170918/2017091801101859bf553a5a7ef394.amr";

try    {
    //Upload blob
    $blobRestProxy->createBlockBlob("vichy89vocie", $blob_name, $content);
    print_r($blobRestProxy);exit;
}
catch(ServiceException $e){
    // Handle exception based on error codes and messages.
    // Error codes and messages are here:
    // http://msdn.microsoft.com/library/azure/dd179439.aspx
    $code = $e->getCode();
    $error_message = $e->getMessage();
    echo $code.": ".$error_message."<br />";
}
exit;


while (1) {
	//获取
	$sql = "select con_value from tp_config where con_name='RUM_TIME'";
	$obj = mysql_query($sql,$link);
	$iginfo=mysql_fetch_assoc($obj);
	if(empty($iginfo)){
		$runtime=time();
	}else{
		$runtime=$iginfo['con_value'];
	}
	$utime=time();
	$mdsign=md5('sdfdsas##*sEae'.$runtime);
	$url="http://h5.gemekk.com/vichy/index.php?s=/Home/Vichy/getvoicelist/sign/".$mdsign."/time/".$runtime;
	$result=file_get_contents($url);
	$data=json_decode($result,true);

	//更新最后的请求日期
	$uSql="update tp_config set con_value='".$utime."' where con_name='RUM_TIME'";
	mysql_query($uSql,$link);

	if($data['errcode']==1){
	    $list=$data['data'];
	    $iSql="";
	    foreach ($list as $k => $v) {
	        $openid = $this->wechat;
	        $access_token=getAccessToken();
	        $url="http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".$access_token."&media_id=".$v['media_id'];
	        header("Content-Type: application/force-download");
	        header('Content-Disposition: attachment; filename='.time());
	        $video = file_get_contents($url); 
	        $path = 'Uploads/video/'.date("Ymd")."/";            // 生成的文件名            //
	        if(!is_dir($path)){
	            mkdir($path, 0777);
	        }
	        $route=$path.date("Ymdhis").uniqid();
	        $video_url=$route."_$wechat_id.amr";
	        $amrUrl=ROOT."/".$video_url;
	        //写入文件夹中
	        $fp=fopen($amrUrl, "w");
	        fwrite($fp, $video);
	        fclose($fp);
	        //将amr格式转换成MP3
	        $mp3Url=$route."_$wechat_id.mp3";
	        $mp3_url=ROOT."/".$mp3Url;
	        //运行脚本生成MP3
	        $command = "/usr/local/bin/ffmpeg -i $amrUrl $mp3_url";  
	        exec($command);
	        $errcode=0;
	        $errormsg="";
	        $isrun=0;
	        if(is_file($mp3_url)){//判断mp3是否生成成功
	        	$sign=md5('sdfdsas##*sEae'.$v['media_id']);
				$url="http://h5.gemekk.com/vichy/index.php?s=/Home/Vichy/updatevoice/id/".$v['id']."/sign/".$sign."/p3/".$mp3_url."/mediaid/".$v['media_id'];
				$results=file_get_contents($url);
				$emsg=json_decode($results,true);
	        	$errcode=$emsg['errcode'];
	        	$errormsg=$emsg['errormsg'];
	        	$isrun=1;
	        }
	        $create_time=date("Y-m-d H:i:s",$v['create_time']);
	        $iSql.=",('".$vo['id']."','".$video_url."','".$mp3Url."','".$v['media_id']."','".$v['localid']."','".$create_time."','".date("Y-m-d H:i:s")."','".$errcode."','".$errormsg."','".$isrun."')";
	    }
	    if(!empty($iSql)){
	    	$nSql="INSERT INTO `tp_voice_list`(`voiceid`, `video_url`, `mp3_url`, `media_id`, `localid`, `create_time`, `update_time`, `errcode`, `errormsg`, `isrun`) VALUES ";
            $isql=$nSql.substr($iSql,1);
            mysql_query($isql,$link);
	    }
	}
	exit;
}

function getAccessToken(){

}

