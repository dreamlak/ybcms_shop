<?php
/**
 * 分销管理
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-05-02
 */

namespace app\admin\controller;
use think\Validate;
use \think\Db;
class Distribut extends AdminBase{
	//分销商列表
	public function index(){
		$map=[];
		$map['is_distribut']=1;
		if(input('status')!='') $map['status']=input('status');
		if(input('username')!='') $map['username']=input('username');
		if(input('email')!='') $map['email']=input('email');
		if(input('tel')!='') $map['tel']=input('tel');

    	$totalCount=Db::name('member')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('member')->where($map)->order('userid DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		foreach($lists as $k=>$v){
			$lists[$k]['first_total']=Db::name('member')->where('first_leader',$v['userid'])->count();
			$lists[$k]['second_total']=Db::name('member')->where('second_leader',$v['userid'])->count();
			$lists[$k]['third_total']=Db::name('member')->where('third_leader',$v['userid'])->count();
		}
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		$levelData=Db::name('member_level')->field('id,name')->select();
		foreach($levelData as $k=>$v){
			$level[$v['id']]=$v['name'];
		}
		$this->assign('level',$level);
		return $this->show();
	}
	//分销商详细
	public function info(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//数据验证
        	$validate = new Validate([
            	'username|用户名' => 'require',
            	'tel|手机' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}

			return model('Member')->addUserData($data,'edit');
		}else{
			$info=Db::name('member')->where('userid',input('userid'))->find();
			$info['level']=Db::name('member_level')->where('id',$info['levelid'])->value('name');
			$info['first_total']=Db::name('member')->where('first_leader',$v['userid'])->count();
			$info['second_total']=Db::name('member')->where('second_leader',$v['userid'])->count();
			$info['third_total']=Db::name('member')->where('third_leader',$v['userid'])->count();
			
			$this->assign('info',$info);

    		return $this->show();
		}
	}
	//分销关系
	public function filiation(){
		
	}
}
?>