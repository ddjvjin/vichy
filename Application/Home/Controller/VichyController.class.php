<?php
namespace Home\Controller;
use Think\Controller;
class VichyController extends Controller {
    /**
     * 4个页面
     * [index description]
     * @return [type] [description]
     */
    public function index(){
        $type=$_GET['type'];
        if($type==1){
            $page="voiceo";
        }elseif($type==2){
            $page="voicet";
        }elseif($type==3){
            $page="voices";
        }else{
            $page="voicef";
        }
        $shareurl =IMG_URL.'/index.php?s=/Home/Index/index';
        $this->assign('shareurl',$shareurl);
        $shareList['title']="什么？马伊琍的纹身说话？";
        $shareList['message']="能录音的纹身想要吗？点进来";
        $shareList['image']="https://mmbiz.qpic.cn/mmbiz_jpg/LQqk9YGMbYLOP5zVqiaSgyuYVDPnbzOaZrHsn7awIg2Wniclia7l8iafAtnCOxDmJ57o0cUypic0rFol2yOrs4kH6DQ/0?wx_fmt=jpeg";
        $this->assign("shareList",$shareList);
        //分享
        $signPackage=getSignPackage();
        $this->assign('signPackage',$signPackage);
        $this->display($page);
    }

    /**
     * 大幕
     * [drop description]
     * @return [type] [description]
     */
    public function drop(){
        $data=M()->table(C("DB_PREFIX")."barrage b"
                )->join(C("DB_PREFIX")."wechat_group_list w on b.openid=w.openid"
                )->field("b.message,b.type_id,w.nickname,w.headimgurl"
                )->where("b.status=0")->order("b.update_time DESC")->limit(0,30)->select();
        $list=array();
        foreach ($data as $k => $v) {
            $message=$v['message'];
            $qr=false;
            if($v['type_id']==2){
                $qr=true;
                $message=IMG_URL."/Public/0919/images/qr".$v['message'].".png";
            }
            $list[$k]['img']=$v['headimgurl'];
            $list[$k]['qr']=$qr;
            $list[$k]['user']=$v['nickname'];
            $list[$k]['info']=$message;
        }
        if(IS_AJAX){
            $return['status']=0;
            if(empty($list)){
                $this->ajaxReturn($return);
            }
            $return['status']=1;
            $return['list']=$list;
            $this->ajaxReturn($return);
       }else{
            $list=json_encode($list);
            $this->assign("list",$list);
            $this->display();
       }
    }

    /**
     * 获取最新的语音列表
     * [getvoicelist description]
     * @return [type] [description]
     */
    public function getvoicelist(){
        $db=M();
        $time=I('time');
        $sign=I("sign");
        $return['errcode']=0;
        $return['errormsg']="没有数据";
        $mdsign=md5('sdfdsas##*sEae'.$time);
        if($sign !=$mdsign){
            $return['errormsg']="签名验证错误";
            $this->ajaxReturn($return);
        }
        $data=$db->table(C("DB_PREFIX")."voice")->where("update_time>=$time and isrun=0")->select();
        if(!empty($data)){
            $return['errcode']=1;
            $return['errormsg']="获取数据成功";
            $return['data']=$data;

        }
        $this->ajaxReturn($return);
    }

    /**
     * 更新mp3地址
     * [updatevoice description]
     * @return [type] [description]
     */
    public function updatevoice(){
        $db=M();
        $sign=I("sign");
        $voiceid=I('id');
        $media_id=I('mediaid');
        $mp3Url=I('p3');//mp3地址
        $return['errcode']=0;
        $return['errormsg']="没有数据";
        $mdsign=md5('sdfdsas##*sEae'.$media_id);
        if($sign !=$mdsign){
            $return['errormsg']="签名验证错误";
            $this->ajaxReturn($return);
        }
        $save['mp3_url']=$mp3Url;
        $save['isrun']=1;
        //查询录音记录是否存在
        $info=$db->table(C("DB_PREFIX")."voice")->where("id=$voiceid")->find();
        if(empty($info)){
            $return['errcode']=404;
            $return['errormsg']="数据不存在";
            $this->ajaxReturn($return);
        }elseif($info['media_id'] !=$media_id){//录音已变更
            $return['errcode']=201;
            $return['errormsg']="录音已变更";
            $this->ajaxReturn($return);
        }elseif($info['isrun'] !=0){
            $return['errcode']=203;//已生成了mp3
            $return['errormsg']="录音已变更";
            $this->ajaxReturn($return);
        }
        $res=$db->table(C("DB_PREFIX")."voice"
                )->where("id=$voiceid and isrun=0 and media_id='$media_id'")->save($save);
        if($res>0){
            $return['errcode']=1;
            $return['errormsg']="更新成功";
        }
        $this->ajaxReturn($return);
    }
}