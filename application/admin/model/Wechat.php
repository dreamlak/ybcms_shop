<?php
/**
 * 微信模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class Wechat extends Base{
	/*微信粉丝OpenId列表
	["total"] => int(5) //关注该公众账号的总用户数
	["count"] => int(5) //拉取的OPENID个数，最大值为10000
	["data"] => array(1) { //列表数据，OPENID的列表
		["openid"] => array(5) {
			[0] => string(28) "oCfpQuB-rE9xUa_Ngg6DGMbrufT0"
		   	[1] => string(28) "oCfpQuKOCle_5RUeWtvam1z11aos"
		  	[2] => string(28) "oCfpQuPQS6qLI2s8id3WwVF89Rm4"
		 	[3] => string(28) "oCfpQuHAb7BWloy2uPI7ZOvsJj4k"
		  	[4] => string(28) "oCfpQuETW4_OlFCrRjcDGoXi9o7I"
		}
	}
	["next_openid"] => string(28) "oCfpQuETW4_OlFCrRjcDGoXi9o7I" //拉取列表的最后一个用户的OPENID
	*/
	public function getWxUserOpenId($openid=''){
		$openidArr=$data=[];
		//实例微信粉丝接口
		$wxuser = & load_wechat('User');
		//读取微信粉丝列表
		$result = $wxuser->getUserList($openid);
		if($result!==FALSE){
			$openidArr=array_merge($result['data']['openid'],$openidArr);
			if($result['total']>10000 && $result['count']==10000){
				$this->getWxUserOpenId($result['next_openid']);
			}
			$data['status']=1;
			$data['msg']='获取成功';
			$data['total']=$result['total'];
			$data['data']=$openidArr;
		}else{
			$data = ['status'=>0,'msg'=>$wxuser->errCode.'-'.$wxuser->errMsg];
		}
		return $data;
	}
	
	//微信粉丝w信息列表
	//$data openid数组
	public function getWxUserList($data){
		if(count($data)==0) return ['status'=>0,'msg'=>'获取微信用户信息失败！'];
		$wxusInfo=[];
		// 实例微信粉丝接口
		$wxuser = & load_wechat('User');
		//读取微信粉丝列表
		foreach($data as $k=>$v){
			$result = $wxuser->getUserInfo($v);
			if($result!==false){
				$result['nickname']=deal_emoji($result['nickname'],0);
				$wxusInfo[]=$result;
			}
		}
		return $wxusInfo;
	}
	
	public function getUserTag(){
		if(empty(S('wx_tag_list'))){
			//实例微信粉丝接口
			$user = & load_wechat('User');
			//获取粉丝标签列表
			$result = $user->getTags();
			//处理结果
			if($result===FALSE){
				$this->error($user->errMsg);
			}
			S('wx_tag_list',$result['tags']);
			return $result['tags'];
		}else{
			return S('wx_tag_list');
		}
	}
}
?>