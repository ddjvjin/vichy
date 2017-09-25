<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {

    public function _initialize()
    { 
        //薇姿
        $useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
        if(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false ){
            header("Content-type: text/html; charset=utf-8");
            echo " Sorry！非微信浏览器不能访问";exit;
        }
        $openid=cookie("openid_Vichy170918_2091");
        //$openid="o0AlIuIp2fWYB81DJL6aSmULFUWQ";
        if(empty($openid)){
            $openid=$_GET['openid'];
            if(!empty($openid)){
                $wechat['nickname']=$_GET['name'];
                $wechat['headimgurl']=$_GET['img'];
                $wechat['role']=$_GET['role'];
                D("Common")->pushWechatInfo($openid,$wechat);
                cookie('openid_Vichy170918_2091',$openid,array('expire'=>3600*24)); 
            }else{
                getVichyOpenid();
                exit;
            }
        }
        $this->wechat=$openid;
        $shareurl =IMG_URL.'/index.php?s=/Home/Index/index';
        $this->assign('shareurl',$shareurl);
        $shareList['title']="什么？马伊琍的纹身说话？";
        $shareList['message']="能录音的纹身想要吗？点进来";
        $shareList['image']="https://mmbiz.qpic.cn/mmbiz_jpg/LQqk9YGMbYLOP5zVqiaSgyuYVDPnbzOaZrHsn7awIg2Wniclia7l8iafAtnCOxDmJ57o0cUypic0rFol2yOrs4kH6DQ/0?wx_fmt=jpeg";
        $this->assign("shareList",$shareList);
        //分享
        $signPackage=getSignPackage();//getJsapiVichy
        $this->assign('signPackage',$signPackage);
    }

    /**
     * 生成二维码
     * [index description]
     * @return [type] [description]
     */
    public function index(){
        $openid=$this->wechat;
        $userinfo=M()->table(C("DB_PREFIX")."wechat_group_list")->where("openid='$openid'")->find();
        if(empty($userinfo['qrcode'])){
            $userinfo['qrcode']=D("Common")->bg_qrcode($userinfo['id']);
        }else{
            $userinfo['qrcode']=IMG_URL.$userinfo['qrcode'];
        }
        $info=M()->table(C("DB_PREFIX")."voice")->where("wechat_id=$userinfo[id]")->find();
        if(!empty($info)){
            $userinfo['sounds']=1;
            //redirect(U('Index/result',array("id"=>$userinfo['id'])));
            //exit;
        }
        $this->assign("info",$userinfo);
        $this->display();
    }

    /**
     * 更新纹身
     * [pushinfo description]
     * @return [type] [description]
     */
    public function pushinfo(){
        $return['status']=0;
        $return['msg']="网络异常";
        $openid=$this->wechat;
        $tattoo=$_POST['tattoo'];
        $data = $_POST['base64']; 
        $temp['tattoo']=$tattoo;
        /*preg_match("/data:image\/(.*);base64,/",$data,$res); 
        $ext = $res[1]; 
        if(!in_array($ext,array("jpg","jpeg","png","gif"))){ 
            $return['msg']="格式错误";
            $this->ajaxReturn($return);
        } 
        $path = 'Uploads/tattoo/'.date("Ymd")."/";            // 生成的文件名            //
        if(!is_dir($path)){
            mkdir($path, 0777);
        }
        
        $suffix=substr($openid, 7,5);
        $file='./'.$path.date("Ymdhis").uniqid().'_'.$suffix.'.'.$ext; 
        $data = preg_replace("/data:image\/(.*);base64,/","",$data); 
        if (file_put_contents($file,base64_decode($data))===false) { 
            $return['msg']="图片保存错误";
            $this->ajaxReturn($return);
        }else{ 
            $imgPath=ltrim($file,".");
            $return['img']=$imgPath;
            $temp['tattooimg']=$imgPath;
        } */
        $res=D("Voice")->where("openid='$openid'")->save($temp);
        if($res !==false){
            $return['status']=1; 
            //分享的图片
            $arr=array(
                "https://mmbiz.qpic.cn/mmbiz_png/LQqk9YGMbYKJgaYmLdjTrsqrKohHgaiaelHBH0mfJ8qodaGguVPT2J5S0cnsLNnBY2yx1HVO31jVxpkhOANMs4g/0?wx_fmt=png",
                "https://mmbiz.qpic.cn/mmbiz_png/LQqk9YGMbYKJgaYmLdjTrsqrKohHgaiaeqQKfyib2mDX07sILojQCZYicnh5MdOIoIXm1Jhzp6LVp3uvJZJsBYS5w/0?wx_fmt=png",
                "https://mmbiz.qpic.cn/mmbiz_png/LQqk9YGMbYKJgaYmLdjTrsqrKohHgaiaefBX3icJyaFxIUcmVpENkBJh0v0dqFSMRADOicDY6vjcYOb0f7nZ6NibGA/0?wx_fmt=png",
                "https://mmbiz.qpic.cn/mmbiz_png/LQqk9YGMbYKJgaYmLdjTrsqrKohHgaiaeQS5ZLu7qfnLIy9pqfGuvFEtSMdJHDG9FLGMIpY1bSP8XYHLNlN0Avg/0?wx_fmt=png",
            );
            D("Common")->pushBarrage($openid,$tattoo);
            $return['img']=$arr[$tattoo];
        }
        $this->ajaxReturn($return);
    }

    /**
     * 结果页面
     * [result description]
     * @return [type] [description]
     */
    public function result(){
        $id=$_GET['id'];
        $openid=$this->wechat;
        $info=M()->table(C("DB_PREFIX")."voice v"
            )->join(C("DB_PREFIX")."wechat_group_list w on v.wechat_id=w.id"
            )->field("v.*,w.nickname,w.headimgurl,w.qrcode"
            )->where("v.wechat_id=$id")->find();
        if($openid==$info['openid']){
            $info['same']=1;
        }
        //分享的图片
        $arr=array(
            "https://mmbiz.qpic.cn/mmbiz_png/LQqk9YGMbYKJgaYmLdjTrsqrKohHgaiaelHBH0mfJ8qodaGguVPT2J5S0cnsLNnBY2yx1HVO31jVxpkhOANMs4g/0?wx_fmt=png",
            "https://mmbiz.qpic.cn/mmbiz_png/LQqk9YGMbYKJgaYmLdjTrsqrKohHgaiaeqQKfyib2mDX07sILojQCZYicnh5MdOIoIXm1Jhzp6LVp3uvJZJsBYS5w/0?wx_fmt=png",
            "https://mmbiz.qpic.cn/mmbiz_png/LQqk9YGMbYKJgaYmLdjTrsqrKohHgaiaefBX3icJyaFxIUcmVpENkBJh0v0dqFSMRADOicDY6vjcYOb0f7nZ6NibGA/0?wx_fmt=png",
            "https://mmbiz.qpic.cn/mmbiz_png/LQqk9YGMbYKJgaYmLdjTrsqrKohHgaiaeQS5ZLu7qfnLIy9pqfGuvFEtSMdJHDG9FLGMIpY1bSP8XYHLNlN0Avg/0?wx_fmt=png",
        );
        $info['qrcode']=IMG_URL.$info['qrcode'];
        $shareList['title']="什么？马伊琍的纹身说话？";
        $shareList['message']="能录音的纹身想要吗？点进来";
        $shareList['image']=$arr[$info['tattoo']];
        $this->assign("shareList",$shareList);
        $this->assign("info",$info);
        $this->display();
    }

    /**
     * 录音下载音频
     * [uplaodvoice description]
     * @return [type] [description]
     */
    public function uplaodvoice(){
        $return['status']=0;
        $return['msg']="操作异常";
        $wechat_id=$_POST['wechat_id'];
        $media_id=$_POST['media_id'];
        $sounds=$_POST['sounds'];
        $localId=$_POST['localId'];
        if(!empty($media_id)){
            $openid = $this->wechat;
            /*$access_token=getAccessToken();
            $url="http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".$access_token."&media_id=".$media_id;
            header("Content-Type: application/force-download");
            $time=time();
            header('Content-Disposition: attachment; filename='.$time);
            $video = file_get_contents($url); 
            $path = 'Uploads/video/'.date("Ymd")."/";            // 生成的文件名            //
            if(!is_dir($path)){
                mkdir($path, 0777);
                //chmod($path, 0777);
            }
            $route=$path.date("Ymdhis").uniqid(). rand(1,1000);
            $video_url=$route."_$wechat_id.amr";
            $amr_url=ROOT."/".$video_url;
            //写入文件夹中
            $fp=fopen($amr_url, "w");
            fwrite($fp, $video);
            fclose($fp);
            //将amr格式转换成MP3
            $mp3Url=$route."_$wechat_id.mp3";
            $mp3_url=ROOT."/".$mp3Url;

            $command = "/usr/local/bin/ffmpeg -i $amr_url $mp3_url";  
            //echo $command;exit;
            exec($command,$error);*/
            //入库
            $temp=array();
            $temp['wechat_id']=$wechat_id;
            $temp['openid']=$openid;
            $temp['video_url']="";//video_url
            //$temp['mp3_url']=$mp3Url;
            $temp['sounds']=$sounds;
            $temp['media_id']=$media_id;
            $temp['localid']=$localId;
            $temp['tattoo']=0;
            $temp['update_time']=time();
            $info=D("Voice")->where("wechat_id=$wechat_id")->find();
            if(empty($info)){
                $temp['create_time']=time();
                $res=D("Voice")->add($temp);
                if($res){
                    D("Common")->pushBarrage($openid);
                    $info['id']=$res;
                    $return['status']=1; 
                }
            }else{
                $res=D("Voice")->where("wechat_id=$wechat_id")->save($temp);
                if($res !==false){
                    D("Common")->pushBarrage($openid);
                    $return['status']=1; 
                }
            }
            $return['url']=IMG_URL."/index.php?s=/Home/Index/result/id/".$wechat_id;
        }
        $this->ajaxReturn($return);
    }

    /**
     * 弹幕信息
     * [msginfo description]
     * @return [type] [description]
     */
    public function msginfo(){
        if(IS_POST){
            $msg="提交失败";
            $return['status']=0;
            $return['msg']="网络异常";
            $openid = $this->wechat;
            $data=D("Barrage")->create();
            $message=!empty($data['message'])?trim($data['message']):"";
            if(empty($message)){
                $msg="请提交内容";
                $this->ajaxReturn($return);
            }
            //判断是否存在敏感词
            $words=D("Common")->getSensitive($message);
            if($words>0){
                $return['msg']="内容中存在敏感词";
                $this->ajaxReturn($return);
            }
            $data=array();
            $data['openid']=$openid;
            $data['addtime']=time();
            $data['update_time']=time();
            $data['message']=$message;
            $res=D("Barrage")->add($data);
            if($res){
                $return['status']=1;
                $return['msg']="提交成功";
            }
            $this->ajaxReturn($return);
        }else{
            $this->assign("msg",$msg);
            $this->display(); 
        }
    }
}