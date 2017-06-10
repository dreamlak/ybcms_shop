<?php
/**
 * 文章评论管理
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
class ArticleComment extends AdminBase{
	public function index(){
		$map=[];
		if(input('artid')!='')$map['artid']=input('artid');
		if(input('key')!='')$map['content']=['like','%'.input('key').'%'];
		if(input('ip')!='')$map['ip']=input('ip');
		if(input('username')!='')$map['username']=input('username');
		
		$pagecount=config('paginate.list_admin');
		$totalCount=Db::name('article_comment')->where($map)->count();
		$data=Db::name('article_comment')->where($map)->order('id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		foreach($lists as $k=>$v){
			$lists[$k]['artname']=Db::name('article')->where('artid',$v['artid'])->value('title');
		}
		//dump($lists);die;
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->show();
	}
	//删除文章评论
	public function del(){
		$ids=input('ids/a');
		$rd=['status'=>0,'msg'=>'删除失败!'];
    	if(is_array($ids)){
			foreach($ids as $id){
				$rs=Db::name('article_comment')->where('id',$id)->delete();
				if($rs==0){
					return $rd;
				}else{
					$rd=['status'=>1,'msg'=>'删除成功!'];
				}
	        }
		}
		addAdminLog('成功删除文章评论');
		return $rd;
	}
}
?>