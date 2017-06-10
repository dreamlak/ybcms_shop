<?php
/**
 * 管理角色
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
class AdminRole extends AdminBase{
	//角色管理
	public function index(){
		$lists=Db::name('admin_role')->select();
		$this->assign('lists',$lists);
		return $this->show();
	}
	//添加角色
	public function add(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|名称' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}

			return model('AdminRole')->addData($data);
		}else{
    		return $this->show();
		}
	}
	//编辑角色
	public function edit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|名称' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			unset($data['roleid']);
			return model('AdminRole')->addData($data,'edit');
		}else{
			$roleid=input('roleid');
			$info=Db::name('admin_role')->where('roleid',$roleid)->find();
			$this->assign('info',$info);
    		return $this->show();
		}
	}
	//删除角色
	public function del(){
		$roleid=input('roleid');
		$rd=['status'=>0,'msg'=>'删除失败!'];
		$rs=Db::name('admin_role')->where('roleid',$roleid)->delete();
    	if($rs>0){
			addAdminLog('删除管理角色:'.get_rolename($roleid));
			return ['status'=>1,'msg'=>'删除成功!'];
		}else{
			return $rd;
		}
	}
	//设置状态
	public function setStatus(){
		$status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('admin_role')->where('roleid',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
	}
	
	//权限设置
	public function setRole(){
		$roleid=input('roleid');
		if(request()->isPost() || request()->isAjax()){
			$node = input('post.node/a');
			$data = implode(',',$node);
			//dump($roleid);die;
			$rs=Db::name('admin_role')->where('roleid',$roleid)->update(['data'=>$data]);
			if($rs!==false){
				addAdminLog('成功设置管理角色'.get_rolename($roleid).'的权限');
				return ['status'=>1,'msg'=>'设置成功!','data'=>$data];
			}else{
				return ['status'=>0,'msg'=>'设置失败!','data'=>$data];
			}
		}else{
			$roleinfo=Db::name('admin_role')->where('roleid',$roleid)->field('name,data')->find();
			$rolename=$roleinfo['name'];
			$roleData=$roleinfo['data'];
			$role_arr = explode(',', $roleData);
			
			$getTree=model('AdminRole')->getTree($role_arr);
			$this->assign('getTree',$getTree);
			
			$this->assign('rolename',$rolename);
			$this->assign('roleid',$roleid);
			return $this->show();
		}
	}
}
?>