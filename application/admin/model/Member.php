<?php
/**
 * 会员模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class Member extends Base{
	//添加会员
	public function addUserData($data,$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		
		$rd=['status'=>0,'msg'=>'操作失败'];
		if($opttype=='add'){
			$rs=$this->insert($data);
			if($rs>0){
				addAdminLog('成功添加会员:'.$data['username']);
				$rd=['status'=>1,'msg'=>'会员添加成功'];
			}else{
				$rd=['status'=>0,'msg'=>'会员添加失败'];
			}
		}else{
			unset($data['userid']);
			$rs=$this->where('userid',input('userid'))->update($data);
			if($rs>0){
				addAdminLog('成功编辑会员:'.$data['username']);
				$rd=['status'=>1,'msg'=>'会员编辑成功'];
			}else{
				$rd=['status'=>0,'msg'=>'会员编辑失败,或不作任何修改'];
			}
		}
		return $rd;
	}
	
	//修改会员密码
	public function editPwd($userid,$pwd='888888'){
		if($userid=='') return ['status'=>0,'msg'=>'参数丢失，重置密码失败！'];
		$username=$this->where('userid',$userid)->value('username');//管理员名
		$data=[];
		$encrypt=GetRandStr(6);
		$password=md5($pwd.$encrypt);//加密后的新密码
		$codes=base64_encode($username.$password);
		$token=hash('md5',$codes);//登录验证token
		$data['encrypt']=$encrypt;
		$data['password']=$password;
		$data['token']=$token;
		$rs=$this->where('userid',$userid)->update($data);
		if($rs>0){
			addAdminLog('成功重置管理员'.$username.'的密码为：'.$pwd);
			return ['status'=>1];
		}else{
			addAdminLog('重置管理员'.$username.'的密码失败');
			return ['status'=>0];
		}
	}
	
	/**
	 * 记录帐户变动
	 * @param   int     $userid     用户id
	 * @param   float   $mymoney    可用余额变动
	 * @param   int     $mypoints   消费积分变动
	 * @param   string  $content    变动说明
	 * @param   float   $getmoney 	分佣金额
	 * @return  bool
	 */
	function accountLog($userid,$mymoney=0,$mypoints=0,$content='',$getmoney=0){
	    /* 插入帐户变动记录 */
	    $data = [
	        'userid'    => $userid,
	        'mymoney'   => $mymoney,
	        'mypoints'  => $mypoints,
	        'addtime'	=> time(),
	        'content'   => $content,
	    ];
		//更新用户信息
	    $rs=DB::name('member')->where('userid',$userid)->setInc('mymoney',$mymoney);
		$rs=DB::name('member')->where('userid',$userid)->setInc('mypoints',$mypoints);
		$rs=DB::name('member')->where('userid',$userid)->setInc('getmoney',$getmoney);
		//添加资金变动日志
		$rs=DB::name('member_account_log')->insert($data);
		//添加操作日志
		addAdminLog('成功变动会员ID为'.$userid.'的资金：(余额:'.$mymoney.'，积分:'.$mypoints.')');
		if($rs){
			return true;
		}else{
			return false;
		}
	}
	
	//添加等级
	public function addLevelData($data,$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		
		$rd=['status'=>0,'msg'=>'操作失败'];
		if($opttype=='add'){
			$rs=Db::name('member_level')->insert($data);
			if($rs>0){
				addAdminLog('成功添加会员等级:'.$data['name']);
				$rd=['status'=>1,'msg'=>'会员等级添加成功'];
			}else{
				$rd=['status'=>0,'msg'=>'会员等级添加失败'];
			}
		}else{
			unset($data['id']);
			$rs=Db::name('member_level')->where('id',input('id'))->update($data);
			if($rs>0){
				addAdminLog('成功等级编辑会员:'.$data['name']);
				$rd=['status'=>1,'msg'=>'会员等级编辑成功'];
			}else{
				$rd=['status'=>0,'msg'=>'会员等级编辑失败,或不作任何修改'];
			}
		}
		return $rd;
	}
}
?>