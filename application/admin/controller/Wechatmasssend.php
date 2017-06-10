<?php
/**
 * 微信群发管理
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
class Wechatmasssend extends AdminBase{
	protected $groupArr=['0'=>'全部用户','1'=>'星标用户'];
	//新建群发消息
	public function index(){
		//dump(model('Wechatmasssend')->upArts(9,4));die;
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			if($data['types']=='text'){
				$vdata=['words|文字内容' => 'require'];
			}else{
				$vdata=['artid|图文内容' => 'require'];
			}
			//数据验证
        	$validate = new Validate($vdata);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			$data['addtime']=time();
			$data['status']=0;
			return model('Wechatmasssend')->addData($data);
		}else{
			$this->assign('groupArr',model('Wechat')->getUserTag());
			
			//今天发送数
			$todaycount=Db::name('wx_masssend')->whereTime('addtime', 'today')->count();
			$this->assign('todaycount',$todaycount);
			
			return $this->show();
		}
	}
	//已群发内容
	public function historys(){
		$map=[];
		$totalCount=Db::name('wx_masssend')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('wx_masssend')->where($map)->order('id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		foreach($lists as $k=>$v){
			if($v['types']=='text'){
				$lists[$k]['thumb']='';
				$lists[$k]['title']=$v['words'];
			}else{
				//主图文
				$artindex=Db::name('wx_mediaart')->where('artid',$v['artid'])->field('artid,artpid,title,thumb')->find();
				$lists[$k]['thumb']=$artindex['thumb'];
				$artArr='1、'.$artindex['title'];
				//子图文
				$artgrop=Db::name('wx_mediaart')->where('artpid',$artindex['artid'])->field('artid,artpid,title')->select();
				if(count($artgrop)>0){
					foreach($artgrop as $ak=>$av){
						$n=$ak+2;
						$artArr.='<br>'.$n.'、'.$av['title'];
					}
				}
				
				$lists[$k]['title']=$artArr;
			}
		}
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		$this->assign('groupArr',$this->groupArr);
		return $this->show();
	}

	//删除
	public function del(){
		$ids=input('ids/a');
		$rd=['status'=>0,'msg'=>'删除失败!'];
    	if(is_array($ids)){
			foreach($ids as $id){
				$rs=Db::name('wx_masssend')->where('id',$id)->delete();
				if($rs==0){
					return $rd;
				}else{
					$rd=['status'=>1,'msg'=>'删除成功!'];
				}
	        }
		}
		if($rd['status']==1){
			addAdminLog('成功删除群发记录:');
		}
		return $rd;
	}
	
	//重新群发
	public function ressend(){
		$msgid=input('id');
		$info=Db::name('wx_masssend')->where('id',$msgid)->find();
		
		if($info['types']=='mpnews'){
			$rs=model('Wechatmasssend')->upArts($msgid,$info['artid']);
			if($rs['status']==0) return $rs;
		}
		return model('Wechatmasssend')->artMasssend($msgid);
	}
}
?>