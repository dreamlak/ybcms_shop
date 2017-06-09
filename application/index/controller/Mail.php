<?php
/*
 * @name  领导信箱
 * @time on 2016/09/20
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\index\controller;
use think\Controller;
use think\Validate;
use think\Db;
class Mail extends CommonBase{
	//信箱列表
    public function lists(){
    	$map=['status'=>2,'isopen'=>1];
		if(input('typeid')!='')$map['typeid']=input('typeid');
		
		$pagecount=config('paginate.list_rows');
		$totalCount=Db::name('mail')->where($map)->count();
		$data=Db::name('mail')->where($map)->order('id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		$this->setModName('校长信箱');
		return $this->fetch();
    }
	//信箱内容
	public function shows(){
		$id=input('id');
		$info=Db::name('mail')->where('id',$id)->find();
		$this->assign('info',$info);
		
		$this->setModName('校长信箱');
		return $this->fetch();
	}
	//信箱查找
	public function search(){
		if(input('number')==''){
			$this->error("邮箱编号不能为空！");
		}
		if(input('showpwd')==''){
			$this->error("邮箱查看密码不能为空！");
		}
		
		$map=['status'=>1];
		$map['number']=input('number');
		$map['showpwd']=input('showpwd');
		
		$info=Db::name('mail')->where($map)->find();
		if(!$info){
			$this->error("没有你要查找的内容！");
		}
		$this->assign('info',$info);
		
		$this->setModName('校长信箱');
		return $this->fetch();
	}
	//发布信箱
	public function mailpost(){
		if(request()->isPost() || request()->isAjax()){
    		$data=input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|姓名' => 'require',
            	'tel|联系电话' => 'require',
            	'email|邮箱地址' => 'require',
            	'mailtype|邮件类型' => 'require',
            	'title|标题' => 'require',
            	'content|内容' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			$ordernumber=date('YmdHis').GetRandStr(3);
			$data['number']=$ordernumber;
			$data['addtime']=time();
			$data['ip']=request()->ip();
			$data['status']=0;
			$mailid=Db::name('mail')->insertGetId($data);
			if($mailid>0){
				return ['status'=>1,'msg'=>'发送提交成功！'];
			}else{
				return ['status'=>0,'msg'=>'发送提交失败！'];
			}
    	}else{
    		$this->setModName('校长信箱');
    		return $this->fetch();
    	}
	}
}
