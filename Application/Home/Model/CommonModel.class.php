<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Home\Model;
use Think\Model;

/**
 * 图片模型
 * 负责图片的上传
 */

class CommonModel extends Model{
    protected $tableName='wechat_group_list';
    /**
     * 生成二维码
     * [qrcode description]
     * @param  integer $type [description]
     * @return [type]        [description]
     */
    public function qrcode($uid=0,$content,$type=0,$size=10){
        Vendor('phpqrcode.phpqrcode');
        //生成二维码图片
        $object = new \QRcode();
        $errors=array();
        $errors['status']=0;
        $contentSize=strlen($content);
        if($contentSize>150){
            $errors['msg']='字数过长，不能多于150个字符！';
            return $errors;
        }
        $tpgs="png";//图片格式
        $qrcode_bas_path='Uploads/qrcode/'.date("Ymd")."/";
        if(!is_dir($qrcode_bas_path)){
            mkdir($qrcode_bas_path, 0777, true);
        }
        if($uid>0){
            $uniqid_rand=uniqid()."_".$uid;
        }else{
            $uniqid_rand=date("Ymdhis").uniqid(). rand(1,1000);
        }
        $qrcode_path=ROOT."/".$qrcode_bas_path.$uniqid_rand. "_1.".$tpgs;//原始图片路径
        $qrcode_path_new=$qrcode_bas_path.$uniqid_rand."_2.".$tpgs;//二维码图片路径
        $errors['status']=1;
        $errorCorrectionLevel = "Q";//容错级别 L(7%) M(15%) Q(25%) H(30%)
        $matrixPointSize = $size;//生成图片大小 1-20之间
        $matrixMarginSize = 2;//边距大小  0-20
        //生成二维码图片
        $object::png($content,$qrcode_path_new, $errorCorrectionLevel, $matrixPointSize, $matrixMarginSize);
        $QR = $qrcode_path_new;//已经生成的原始二维码图
        $logo = $qrcode_path;//准备好的logo图片
        $errors['status']=1;
        if ($type==0){//要logo
            $file_tmp_name=ROOT.'/Public/Admin/images/qrcode.png';
            if (!copy($file_tmp_name, $qrcode_path)) {
                $errors['msg']='复制图片错误';
                return $errors;
            }
            $QR = imagecreatefromstring(file_get_contents($QR));
            $logo = imagecreatefromstring(file_get_contents($logo));
            $QR_width = imagesx($QR);//二维码图片宽度
            $QR_height = imagesy($QR);//二维码图片高度
            $logo_width = imagesx($logo);//logo图片宽度
            $logo_height = imagesy($logo);//logo图片高度
            $logo_qr_width = $QR_width / 5;
            $scale = $logo_width/$logo_qr_width;
            $logo_qr_height = $logo_height/$scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            //重新组合图片并调整大小
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
            $logo_qr_height, $logo_width, $logo_height);
            //输出图片
            //header("Content-type: image/png");
            imagepng($QR,$qrcode_path);
            imagedestroy($QR);
            unlink(ROOT."/".$qrcode_path_new);
            $qrcode_path=str_replace(ROOT,"",$qrcode_path);
        }else{
            $qrcode_path=$qrcode_path_new;
        }

        //记录阿里云OSS操作记录
        //aliyunoss($qrcode_path);
        $errors['image']=$qrcode_path;
        return $errors;
    }

    /**
     * 生产带背景的二维码
     * [bg_qrcode description]
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function bg_qrcode($id=0){
        Vendor('phpqrcode.phpqrcode');       
        $data = IMG_URL.'/index.php?s=/Home/Index/result/id/'.$id;            // 纠错级别：L、M、Q、H            
        $level = 'M';            // 点的大小：1到10,用于手机端4就可以了            
        $size = 10;            // 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false            //
        $path = 'Uploads/qrcode/'.date("Ymd")."/";            // 生成的文件名            //
        if(!is_dir($path)){
            mkdir($path, 0777, true);
        }
        $filename = $path.date("Ymdhis").uniqid(). rand(1,1000).'.png';          //文件名也可以考虑用生成一个日期变量  
        $object = new \QRcode();
        $object->png($data, $filename, $level, $size, 2,false,"003061");
        M()->table(C("DB_PREFIX")."wechat_group_list")->where("id=$id")->setField("qrcode","/".$filename);
        return IMG_URL."/".$filename;
    }

    /**
     * 新增到大幕表
     * [pushBarrage description]
     * @param  [type]  $openid   [description]
     * @param  integer $tattoo   [description]
     * @param  integer $isupdata [description]
     * @return [type]            [description]
     */
    public function pushBarrage($openid,$tattoo=0){
        $db=M();
        $save['openid']=$openid;
        $save['message']=$tattoo;
        $save['update_time']=time();
        $info=$db->table(C("DB_PREFIX")."barrage")->where("openid='$openid' and type_id=2")->find();
        if(empty($info)){
            $save['type_id']=2;
            $save['addtime']=time();
            $db->table(C("DB_PREFIX")."barrage")->add($save);
        }else{
            $db->table(C("DB_PREFIX")."barrage")->where("openid='$openid' and type_id=2")->save($save);
        }
    }

    /**
     * 判断是否存在敏感词
     * [getSensitive description]
     * @return [type] [description]
     */
    public function getSensitive($content){
        $num=0;
        $words=M()->table(C("DB_PREFIX")."sensitive")->select();
        $flag_arr=array('？','！','￥','*','（','）','：','‘','’','“','”','《','》','，','…','。','、','nbsp','】','【','～');
        $content=preg_replace('/\s/','',preg_replace("/[[:punct:]]/",'',strip_tags(html_entity_decode(str_replace($flag_arr,'',$content),ENT_QUOTES,'UTF-8'))));
        foreach ($words as $v)
        {   
            $content=strtolower($content);
            if (substr_count ($content, $v['word']) > 0) {
                $num ++;
            }
        }
        return $num; 
    }

    /**
     * 更新信息到数据库
     * [pushWechatInfo description]
     * @param  [type] $openid [description]
     * @param  [type] $data   [description]
     * @return [type]         [description]
     */
    public function pushWechatInfo($openid,$data){
        $uinfo=D("wechat_group_list")->where("openid='$openid'")->find();
        if(empty($uinfo)){
            $data['nickname']=$this->unicode_decode($data['nickname']);
            $nickname=str_replace(array("'","\\"),array(''),$data['nickname']);
            $nickname = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $nickname);
            $save['openid']=$openid;
            $save['nickname']=$nickname;
            $save['role']=$data['role'];
            $save['province']="";
            $save['city']="";
            $save['headimgurl']=$data['headimgurl'];
            $save['addtime']=date("Y-m-d H:i:s");
            D("wechat_group_list")->add($save);
        } 
    }

    /**
     * unicode转换成中文
     * [unicode_decode description]
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    function unicode_decode($name)  
    {  
        $name=str_replace("%","\\",$name);
        // 转换编码，将Unicode编码转换成可以浏览的utf-8编码  
        $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';  
        preg_match_all($pattern, $name, $matches);  
        if (!empty($matches))  
        {  
            $name = '';  
            for ($j = 0; $j < count($matches[0]); $j++)  
            {  
                $str = $matches[0][$j];  
                if (strpos($str, '\\u') === 0)  
                {  
                    $code = base_convert(substr($str, 2, 2), 16, 10);  
                    $code2 = base_convert(substr($str, 4), 16, 10);  
                    $c = chr($code).chr($code2);  
                    $c = iconv('UCS-2', 'UTF-8', $c);  
                    $name .= $c;  
                }  
                else  
                {  
                    $name .= $str;  
                }  
            }  
        }  
        return $name;  
    }
}
