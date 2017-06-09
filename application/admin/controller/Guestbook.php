<?php
/**
 * 留言管理
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
class Guestbook extends AdminBase{
	//留言列表
	public function index(){
		$map=[];
		if(input('status')!='') $map['status']=input('status');
		if(input('title')!='') $map['title']=['like','%'.input('title').'%'];
		if(input('name')!='') $map['name']=input('name');
		
		$totalCount=Db::name('guestbook')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('guestbook')->where($map)->order('id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());

		return $this->show();
	}
	//留言处理
	public function disposal(){
		$id=!empty(input('id'))?input('id'):0;
		if(request()->isPost() || request()->isAjax()){
			$data=input('post.');
			$data['adminid']=is_login();
			$data['answertime']=time();
			
			unset($data['id']);
			$rs=Db::name('guestbook')->where('id',$id)->update($data);
			if($rs!==false){
				addAdminLog('成功处理留言:'.$data['title']);
				$rd= ['status'=>1,'msg'=>'留言处理成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'留言处理失败'];
			}
			return $rd;
		}else{
			$info=Db::name('guestbook')->where('id',$id)->find();
			$this->assign('info',$info);
			
			return $this->show();
		}
	}
	
	//删除留言
    public function del(){
        if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//删除前获取该删除的留言名
					$names.=Db::name('guestbook')->where('id',$id)->value('title').'，';
					//删除
					$rs=Db::name('guestbook')->where('id',$id)->delete();
					if($rs==0){
						return $rd;
					}else{
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除留言:'.$names);
				}
			}
			return $rd;
		}else{//单条删除
			//删除前获取该删除的留言名
			$names=Db::name('guestbook')->where('id',input('id'))->value('title');
			//删除
			$rs=Db::name('guestbook')->where('id',input('id'))->delete();
			if($rs==0){
				$this->error('删除失败');
			}else{
				addAdminLog('成功删除留言:'.$names);
				$this->success('删除成功');
			}
		}
    }
	
	
}
?>