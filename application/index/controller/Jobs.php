<?php
/*
 * @name  招聘
 * @time on 2016/09/20
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\index\controller;
use think\Controller;
use think\Validate;
use think\Db;
class Jobs extends CommonBase{
	//职位列表
    public function lists(){
    	$map=['status'=>1];

		$pagecount=config('paginate.list_rows');
		$totalCount=Db::name('jobs')->where($map)->count();
		$data=Db::name('jobs')->where($map)->order('sort DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		$jobtype=['全职','兼职','临时'];
		$this->assign("jobtype", $jobtype);
		
		$this->setModName('人才招聘');
		return $this->fetch();
    }
	
	//职位内容
	public function shows(){
		$id=input('id');
		$info=Db::name('jobs')->where('id',$id)->find();
		$deals=str_replace(array(' ','，','|','/'),',',$info['deals']);
		$this->assign('deals',explode(',',$deals));
		$this->assign('info',$info);
		
		$jobtype=['全职','兼职','临时'];
		$this->assign("jobtype", $jobtype);
		
		Db::name('jobs')->where('id',$id)->setInc('hot',1);
		$this->setModName('职位详情');
		return $this->fetch();
	}
	
	//应聘职位
	public function jobspost(){
		if(request()->isPost() || request()->isAjax()){
    		$data=input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|姓名' => 'require',
            	'tel|联系电话' => 'require',
            	'email|邮箱地址' => 'require',
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
			
			$mailid=Db::name('jobs_apply')->insertGetId($data);
			if($mailid>0){
				Db::name('jobs')->where('id',input('jobid'))->setInc('applynum',1);
				return ['status'=>1,'msg'=>'提交成功！'];
			}else{
				return ['status'=>0,'msg'=>'提交失败！'];
			}
    	}else{
    		$id=input('id');
			$info=Db::name('jobs')->where('id',$id)->field('id,name')->find();
			$this->assign('info',$info);
		
    		$this->setModName('应聘职位');
    		return $this->fetch();
    	}
	}
}
?>