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
class AdminLog extends AdminBase{
	//日志管理
	public function index(){
		$map=[];
		if(input('adminname')!='')$map['adminname']=input('adminname');
		if(input('remark')!='')$map['remark']=['like','%'.input('remark').'%'];
		if(input('logip')!='')$map['logip']=input('logip');
		$starttime=!empty(input('starttime'))?strtotime(input('starttime')):'';
		$endtime=!empty(input('endtime'))?strtotime(input('endtime')):'';
		if($starttime!='')$map['addtime']=['>',$starttime];
		if($endtime!='')$map['addtime']=['<',$endtime];
		
		$totalCount=Db::name('admin_log')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('admin_log')->where($map)->order('addtime DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		foreach($lists as $k=>$v){
			$lists[$k]['fname']='a';
		}
		//dump($lists);die;
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		/*
		$arttotal=$lists->total();//数据总数
		$currentPage=$lists->currentPage();//当前页
		$lastPage=$lists->lastPage();//最后一页
		$listRows=$lists->listRows();//每页的数量
		$hasPages=$lists->hasPages();//是否有下一页
		$getCurrentPath=$lists->getCurrentPath();//自动获取当前的path
		*/
		//dump($lists->getCurrentPage());die;
		return $this->show();
	}
	
	//删除日志
	public function del(){
		$tody=strtotime(date('Y-m-d',time()));
		$mday=$tody-86400*30;
		
		$rd=['status'=>0,'msg'=>'删除失败!可能没有您需要清除的数据了！'];
    	$rs=Db::name('admin_log')->where('addtime','<=',$mday)->delete();
    	if($rs>0){
			addAdminLog('成功删除行为日志');
			return ['status'=>1,'msg'=>'删除成功!'];
		}else{
			return $rd;
		}
	}
}
?>