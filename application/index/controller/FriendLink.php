<?php
/*
 * @name  友情链接
 * @time on 2016/09/20
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\index\controller;
use think\Controller;
use think\Validate;
use think\Db;
class FriendLink extends CommonBase{
	//友情链接列表
    public function lists(){
    	$map=['status'=>1];
		if(input('typeid')!='')$map['typeid']=input('typeid');
		$pagecount=config('paginate.list_rows');
		$totalCount=Db::name('friend_link')->where($map)->count();
		$data=Db::name('friend_link')->where($map)->order('linkid DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		//类别
		$link_type=Db::name('friend_link_type')->order('sort ASC')->select();
		$this->assign('link_type',$link_type);
		
		return $this->fetch();
    }
	
	//友情链接申请
	public function linkapp(){
		if(request()->isPost() || request()->isAjax()){
    		$data=input('post.');
			//数据验证
        	$validate = new Validate([
        		'typeid|类别' => 'require',
            	'name|姓名' => 'require',
            	'url|网站地址' => 'require',
            	'logo|logo' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}

			$data['ip']=request()->ip();
			$data['addtime']=time();
			$data['status']=0;
			$linkid=Db::name('friend_link')->insertGetId($data);
			if($linkid>0){
				return ['status'=>1,'msg'=>'提交成功！','url'=>$url,];
			}else{
				return ['status'=>0,'msg'=>'提交失败！','url'=>''];
			}
    	}else{
    		return $this->fetch();
    	}
	}
}
