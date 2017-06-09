<?php
/**
 * 系统设置
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\controller;
use think\Validate;
use \think\Db;
class System extends AdminBase{
	//系统配置
	public function config(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			$data['config']['province']=input('province');
			$data['config']['city']=input('city');
			$data['config']['district']=input('district');
			$data['config']['twon']=input('twon');
			foreach($data['config'] as $k=>$v){
				$rs=Db::name('cofing')->where('name',$k)->setField('value',$v);
			}
			//更新配置文件
			$this->updateConfig();
			return ['status'=>1,'msg'=>'更新成功！'];
		}else{
			$cdata=Db::name('cofing')->select();
			$config=[];
			foreach($cdata as $v){
				$config[$v['name']]=$v['value'];
			}
			
			$this->assign('config',$config);
			return $this->show();
		}
	}
	
	//发送测试邮件
	public function test_email(){
		$email=input('email');
		if($email=='') return ['status'=>0,'msg'=>'测试邮件地址不能为空！'];
		$rs = send_email($email,'邮件测试','您的邮件配置成功了！');
		if($rs['status']==1){
			return ['status'=>1,'msg'=>'邮件发送成功！'];
		}else{
			return ['status'=>0,'msg'=>'邮件发送失败！'.$rs['msg']];
		}
	}
}
?>