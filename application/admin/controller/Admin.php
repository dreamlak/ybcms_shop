<?php
/**
 * 行为日志
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
class Admin extends AdminBase{
	//管理员列表
	public function index(){
		$lists=model('Admin')->getLists();
		$this->assign('lists',$lists);
		return $this->show();
	}
	
	//添加管理员
	public function add(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'roleid|角色' => 'require',
            	'adminname|管理员名' => 'require|min:6',
            	'password|密码' => 'require|min:6',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			//检查是否重名
			$adname=Db::name('admin')->where('adminname',$data['adminname'])->count();
			if($adname>0){
				return ['status'=>0,'msg'=>'该管理员名已存在！'];
			}
			//重新设置
			$encrypt=GetRandStr(6);
			$password=md5($data['password'].$encrypt);//加密后的密码
    		$codes=base64_encode($data['adminname'].$password);
    		$token=hash('md5',$codes);//登录验证token
    		$data['encrypt']=$encrypt;//密码加密KEY
    		$data['password']=$password;
			$data['token']=$token;
			$data['addtime']=time();
			return model('Admin')->addAdmin($data);
		}else{
			$admin_role=Db::name('admin_role')->where('status',1)->select();
			$this->assign('admin_role',$admin_role);
    		return $this->show();
		}
	}
	//编辑管理员
	public function edit(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'roleid|角色' => 'require',
            	'adminname|管理员' => 'require|min:6',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			unset($data['adminid']);
			return model('Admin')->addAdmin($data,'edit');
		}else{
			$adminid=input('adminid');
			$info=Db::name('admin')->where('adminid',$adminid)->find();
			$this->assign('info',$info);
			
			$admin_role=Db::name('admin_role')->where('status',1)->select();
			$this->assign('admin_role',$admin_role);
    		return $this->show();
		}
	}
	//删除管理员
	public function del(){
		$adminid=input('adminid');
		$rd=['status'=>0,'msg'=>'删除失败!'];
		$rs=Db::name('admin')->where('adminid',$adminid)->delete();
    	if($rs>0){
			addAdminLog('删除管理员:'.get_adminName($adminid));
			$rd=['status'=>1,'msg'=>'删除成功!'];
		}else{
			return $rd;
		}
	}
	
	//设置状态
	public function setStatus(){
		$status=input('status');
		$ids=$_POST['ids'];
		foreach($ids as $id){
			Db::name('admin')->where('adminid',$id)->setField('status',$status);
		}
		return ['status'=>1,'msg'=>'设置成功！'];
	}
	
	//重置密码
	public function ajaxResetPwd(){
		$adminid=input('adminid');
		$rs=model('Admin')->editPwd($adminid,'888888');
		if($rs){
			return ['status'=>1,'msg'=>'密码重置成功！'];
		}else{
			return ['status'=>0,'msg'=>'密码重置失败！'];
		}
	}
	
	//管理员个人资料
	public function myinfo(){
		$adminid=input('adminid')?input('adminid'):is_login();
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'adminname|管理员' => 'require|min:6',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			
			$rs=Db::name('admin')->where('adminid',$adminid)->update($data);
			if($rs>0){
				addAdminLog($data['adminname'].':成功编辑个人资料');
				$rd= ['status'=>1,'msg'=>'编辑个人资料成功！'];
			}else{
				$rd= ['status'=>0,'msg'=>'编辑个人资料失败！'];
			}
		}else{
			$info=Db::name('admin')->where('adminid',$adminid)->find();
			//dump($info);die;
			$this->assign('info',$info);
    		return $this->show();
		}
	}
	//管理员自己修改密码
	public function editmypwd(){
		$adminid=input('adminid')?input('adminid'):is_login();
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'password|密码' => 'require|min:6',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			$rs=model('Admin')->editPwd($adminid,input('post.password'));
			if($rs){
				return ['status'=>1,'msg'=>'密码重置成功！'];
			}else{
				return ['status'=>0,'msg'=>'密码重置失败！'];
			}
		}else{
			return $this->show();
		}
	}
}
?>