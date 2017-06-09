<?php
/*
 * @name  在线报名
 * @time on 2016/09/20
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\index\controller;
use think\Controller;
use think\Validate;
use think\Db;
class Signup extends CommonBase{
	public function index(){
		$map=[];
		if(input('sid')!=''){
			$map['id']=input('sid');
		}else{
			$map['id']=1;
		}
		$signup=Db::name('signup')->where($map)->find();
		$this->assign('info',$signup);
		
		$this->setModName('在线报名');
		return $this->fetch();
	}
	//报名查询
	public function search(){
		if(request()->isPost()){
			if(input('name')==''){
				$this->error("姓名不能为空！");
			}
			if(input('idcard')==''){
				$this->error("身份证不能为空！");
			}
			
			$map=[];
			$map['name']=input('name');
			$map['idcard']=input('idcard');
			
			$info=Db::name('signup_data')->where($map)->find();
			if(!$info){
				$this->error("真抱歉！没有你要查找的内容！");
			}
			
			$this->assign('info',$info);
			$this->setModName('报名查询结果');
			return $this->fetch('result');
		}else{
			$this->setModName('报名查询');
			return $this->fetch();
		}
	}

	//报名提交
	public function Signuppost(){
		if(request()->isPost() || request()->isAjax()){
    		$data=input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|姓名' => 'require',
            	'tel|联系电话' => 'require',
            	'email|邮箱地址' => 'require',
            	'address|家庭住址' => 'require',
            	'idcard|身份证号' => 'require',
            	'fromschool|毕业学校' => 'require',
            	'majorsid|所报专业' => 'require',
            	'content|简介' => 'require',
        	]);
			if(!captcha_check(input('vcord'))){
		    	return $this->error("验证码错误");
	   		}
			unset($data['vcord']);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			$data['addtime']=time();
			$data['ip']=request()->ip();
			$data['status']=0;
			$signid=Db::name('signup_data')->insertGetId($data);
			if($signid>0){
				return ['status'=>1,'msg'=>'报名成功！'];
			}else{
				return ['status'=>0,'msg'=>'报名失败！'];
			}
    	}else{
			$map=[];
			if(input('sid')!=''){
				$map['id']=input('sid');
			}else{
				$map['id']=1;
			}
    		$signup=Db::name('signup')->where($map)->find();
			//专业
			$signup['majors']=Db::name('edu_majors')->where('id','in',$signup['major'])->field('id,name')->select();
			$this->assign('signup',$signup);
			
			$this->setModName('在线报名');
    		return $this->fetch();
    	}
	}
}
