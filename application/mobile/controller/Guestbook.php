<?php
/*
 * @name  留言板
 * @time on 2016/09/20
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\mobile\controller;
use think\Controller;
use think\Validate;
use think\Db;
class Guestbook extends MobileBase{
	//留言板列表
    public function lists(){
    	$map=[];
		$pagecount=config('paginate.list_rows');
		$totalCount=Db::name('guestbook')->where($map)->count();
		$data=Db::name('guestbook')->where($map)->order('id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
    }
	//留言板内容
	public function shows(){
		$id=input('id');
		$info=Db::name('guestbook')->where('id',$id)->find();
		$this->assign('info',$info);
		
		return $this->fetch();
	}
	//留言板提交
	public function guestbookpost(){
		if(request()->isPost() || request()->isAjax()){
    		$data=input('post.');
			//数据验证
        	$validate = new Validate([
        		'title|标题' => 'require',
            	'name|姓名' => 'require',
            	'tel|联系电话' => 'require',
            	'email|邮箱地址' => 'require',
            	'content|内容' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}

			$data['ip']=request()->ip();
			$data['addtime']=time();
			$data['status']=0;
			$mailid=Db::name('guestbook')->insertGetId($data);
			if($mailid>0){
				return ['status'=>1,'msg'=>'提交成功！感谢您的建议，我们会尽快落实的'];
			}else{
				return ['status'=>0,'msg'=>'提交失败！','url'=>''];
			}
    	}else{
    		$this->setModName('留言反馈');
    		return $this->fetch();
    	}
	}
}
