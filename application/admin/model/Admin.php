<?php
/**
 * 管理员模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class Admin extends Base{
	// status属性读取器
    protected function getStatusAttr($value){
        $status = [-1 => '删除', 0 => '<font color="#999">禁用</font>', 1 => '正常', 2 => '待审核'];
        return $status[$value];
    }
	// Addtime读取器
	protected function getAddtimeAttr($value){
		return date('Y-m-d H:i:s',$value);
    }
	
    //管理员列表
    public function getLists(){
		$map=[];
		if(input('status')!='')$map=['status'=>input('status')];
		if(input('roleid')!='')$map=['roleid'=>input('roleid')];
		if(input('adminname')!='')$map=['adminname'=>['like','%'.input('adminname').'%']];
		if(input('tel')!='')$map=['tel'=>input('tel')];
		if(input('realname')!='')$map=['realname'=>input('realname')];
		$totalCount=$this->where($map)->count();
		$pagecount=config('paginate.list_admin');//每页显示数
		$lists = $this->where($map)->order('adminid asc')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		return $lists;
	}
	
    //登录较验
    public function logincheck($adminname,$password){
    	$info=$this->where(array('adminname'=>$adminname,'status'=>1))->find();
    	if(count($info)<=0 || !$info){
			return $this->returnInfo('该用户名不存在，或已禁用！',0);
        }
        
        $password=md5($password.$info['encrypt']);//加密后的密码
    	$codes=base64_encode($adminname.$password);
    	$token=hash('md5',$codes);//登录验证token
    	//重新对比
    	$map=[
    		'adminname'=>$adminname,
    		'password'=>$password,
    		'status'=>1,
    		'token'=>$token
    	];
    	$au = $this->where($map)->find();
    	if(count($au)>0){
    		$request = request();
    		$this->where('adminid',$au['adminid'])->setField(array('lastloginip'=>$request->ip(),'lastlogintime'=>time()));
    		//登录成功后写入Cookie或Session
    		$data=[
    			'adminid'=>$au['adminid'],
    			'roleid'=>$au['roleid'],
    			'adminname'=>$au['adminname'],
    			'lastloginip'=>$au['lastloginip'],
    			'lastlogintime'=>$au['lastlogintime']
    		];
    		session('adminuser', $data);
    		//cookie('adminuser', $data);
			return $this->returnInfo('登录成功！');
    	}else{
    		return $this->returnInfo('登录失败:账号或密码错误！',0);
    	}
    }

	//添加管理员
	public function addAdmin($data,$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		
		$rd=['status'=>0,'msg'=>'操作失败'];
		if($opttype=='add'){
			$rs=$this->insert($data);
			if($rs>0){
				addAdminLog('成功添加管理员:'.$data['adminname']);
				$rd=['status'=>1,'msg'=>'管理员添加成功'];
			}else{
				$rd=['status'=>0,'msg'=>'管理员添加失败'];
			}
		}else{
			$map=['adminid'=>input('post.adminid')];
			$rs=$this->where($map)->update($data);
			if($rs>0){
				addAdminLog('成功编辑管理员:'.$data['adminname']);
				$rd=['status'=>1,'msg'=>'管理员编辑成功'];
			}else{
				$rd=['status'=>0,'msg'=>'管理员编辑失败'];
			}
		}
		return $rd;
	}
	
	//修改管理员密码
	public function editPwd($adminid,$pwd='888888'){
		if($adminid=='') return ['status'=>0,'msg'=>'参数丢失，重置密码失败！'];
		$adminname=$this->where('adminid',$adminid)->value('adminname');//管理员名
		$data=[];
		$encrypt=GetRandStr(6);
		$password=md5($pwd.$encrypt);//加密后的新密码
		$codes=base64_encode($adminname.$password);
		$token=hash('md5',$codes);//登录验证token
		$data['encrypt']=$encrypt;
		$data['password']=$password;
		$data['token']=$token;
		$rs=$this->where('adminid',$adminid)->update($data);
		if($rs>0){
			addAdminLog('成功重置管理员'.$adminname.'的密码为：'.$pwd);
			return ['status'=>1];
		}else{
			addAdminLog('重置管理员'.$adminname.'的密码失败');
			return ['status'=>0];
		}
	}
}
?>