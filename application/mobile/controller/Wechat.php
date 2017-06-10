<?php
/**
 * 微信
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\mobile\controller;
use app\mobile\logic\WechatLogic;
use think\Controller;
use think\Request;
use think\Db;
class Wechat extends MobileBase{
	protected $wechat;
	protected $openid;
	public function _initialize(){
        $this->wechat = &load_wechat('Receive');
    }
    public function index() {
        /* 验证接口 */
        if($this->wechat->valid() === FALSE){
			exit(1);
        }
		
        /* 发送者的openid */
        $this->openid = $this->wechat->getRev()->getRevFrom();
		
		//消息发送类型
		$rx_type=$this->wechat->getRev()->getRevType();
        /*分别执行对应类型的操作 */
        switch ($rx_type) {
            case "text":
                //文本类型处理
                return $this->_auto_reply();
				//return $this->_default_reply();
            case "event":
				//事件类型处理
                return $this->_event();
            case "image":
				//图片类型处理
                return $this->_image();
            case "location":
				//发送位置类的处理
                return $this->_location();
            default:
                return $this->_default();
        }
    }
	public function test(){
		$msg=input('m');
		$Keymsg=model('Wechat')->getKeymsg($msg);
		dump($Keymsg);
	}
	protected function _auto_reply($msg = ''){
		if($msg=='')$msg = $this->wechat->getRevContent();
		//关键词
		$Keymsg=model('Wechat')->getKeymsg($msg);
		if(!empty($Keymsg)){
			if(is_array($Keymsg)){
				return $this->wechat->news($Keymsg)->reply();
			}else{
				return $this->wechat->text($Keymsg)->reply();
			}
		}else{
			return $this->_default_reply();
		}
		
		//自动回复
		$Automsg=model('Wechat')->getAutoreply();
		if(!empty($Automsg)){
			if(is_array($Automsg)){
				return $this->wechat->news($Automsg)->reply();
			}else{
				return $this->wechat->text($Automsg)->reply();
			}
		}else{
			return $this->_default_reply();
		}
	}
	
	/**
     * 智能默认回复
     * @param type $msg
     */
    protected function _default_reply($msg = '') {
        $keys = $this->wechat->getRevContent();
		# 识别身份证
        if (empty($msg) && preg_match('/^\d{17}\d|X$/i', $keys)) {
            $url = "http://apis.juhe.cn/idcard/index?cardno={$keys}&dtype=json&key=81352b16963e290f98c016cdcd2508b5";
            $result = json_decode(httpRequest($url,'GET'), TRUE);
            if (!empty($result['result']['area'])) {
                $msg = "证号：{$keys}\n";
                $msg .= "性别：{$result['result']['sex']}\n";
                $msg .= "生日：{$result['result']['birthday']}\n";
                $msg .= "区域：{$result['result']['area']}";
            }
        }
        # 识别手机号
        if (empty($msg) && preg_match('/^1\d{10}$/i', $keys)) {
            $url = "http://apis.juhe.cn/mobile/get?phone={$keys}&dtype=json&key=836454688c9a444fc4813b943fb8e4cd";
            $result = json_decode(httpRequest($url,'GET'), TRUE);
            if (!empty($result['result']['province'])) {
                $msg = "手机号：{$keys}\n";
                $msg .= "地　区：{$result['result']['province']}{$result['result']['city']}\n";
                $msg .= "运营商：{$result['result']['company']}\n";
                $msg .= "卡类型：{$result['result']['card']}";
            }
        }
        # 机器人
        if (empty($msg)) {
            $url = "http://op.juhe.cn/robot/index?info={$keys}&dtype=json&loc=&lon=&lat=&userid={$this->openid}&key=cfe2928ef010668f2e07ef95feeedc9d";
            $result = json_decode(httpRequest($url,'GET'), TRUE);
            if (!empty($result['result']['text'])) {
                $msg = $result['result']['text'];
            }
        }
        exit(empty($msg) ? 'success' : $this->wechat->text($msg)->reply());
    }
	
	protected function _image(){
		// $wechat 中有获取图片的方法
	    return $this->wechat->text('您发送了一张图片过来，我已经收下，谢谢！')->reply();
	}
	protected function _event() {
		$event = $this->wechat->getRevEvent();
	    switch (strtolower($event['event'])) {
	        // 粉丝关注事件
	        case 'subscribe':
				$beaddedMsg=model('Wechat')->getBeadded();
				if(is_array($beaddedMsg)){
					return $this->wechat->news($beaddedMsg)->reply();
				}else{
					return $this->wechat->text($beaddedMsg)->reply();
				}
	        // 粉丝取消关注
	        case 'unsubscribe':
				return $this->wechat->text('真抱歉，没能留注您！！')->reply();
	        // 点击微信菜单的链接
	        case 'click': 
	            return $this->wechat->text('你点了菜单链接！')->reply();
	        // 微信扫码推事件
	        case 'scancode_push':
	        case 'scancode_waitmsg':
	          	$scanInfo = $this->wechat->getRev()->getRevScanInfo();
	           	return $this->wechat->text("你扫码的内容是:{$scanInfo['ScanResult']}")->reply();
	        // 扫码关注公众号事件（一般用来做分销）
	        case 'scan':
	           	$beaddedMsg=model('Wechat')->getBeadded();
				if(is_array($beaddedMsg)){
					return $this->wechat->news($beaddedMsg)->reply();
				}else{
					return $this->wechat->text($beaddedMsg)->reply();
				}
	    }
	}
	 /**
     * 位置类事情回复
     */
    protected function _location() {
        $vo = $this->wechat->getRevData();
        $url = "http://apis.map.qq.com/ws/geocoder/v1/?location={$vo['Location_X']},{$vo['Location_Y']}&key=ZBHBZ-CHQ2G-RDXQF-I5TUX-SAK53-A5BZT";
        $data = json_decode(file_get_contents($url), true);
        if (!empty($data) && intval($data['status']) === 0) {
            $msg = $data['result']['formatted_addresses']['recommend'];
        } else {
            $msg = "{$vo['Location_X']},{$vo['Location_Y']}";
        }
        $this->wechat->text($msg)->reply();
    }
	/**
     * 默认事件处理
     */
    protected function _default() {
        return $this->wechat->transfer_customer_service()->reply();
        exit('success');
    }
	
	/**
     * 记录接口日志
     */
    protected function _logs() {
        $data = $this->wechat->getRev()->getRevData();
        if (empty($data)) {
            return;
        }
        if (isset($data['Event']) && in_array($data['Event'], array('scancode_push', 'scancode_waitmsg', 'scan'))) {
            $scanInfo = $this->wechat->getRev()->getRevScanInfo();
            $data = array_merge($data, $scanInfo);
        }
        if (isset($data['Event']) && in_array($data['Event'], array('location_select'))) {
            $locationInfo = $this->wechat->getRev()->getRevSendGeoInfo();
            $data = array_merge($data, $locationInfo);
        }

		$filename='msg'.date('Ymdhis').'.txt';
		\Wechat\Loader::register("CachePut",function($data,$filename){
		    return \think\Log::record($data,'notice');
		});
    }
	
	//==================================================================
	//图文消息内容展示页
	public function artshow(){
		$artid=input('artid');
		$info=Db::name('wx_mediaart')->where('artid',$artid)->find();
		$this->assign("info", $info);
		
		$this->setModName('图文消息');
		return $this->fetch();
	}
}
