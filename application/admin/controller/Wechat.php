<?php
/**
 * 微信管理
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
class Wechat extends AdminBase{
	//微信配置
	public function setting(){
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			foreach($data as $k=>$v){
				$rs=Db::name('cofing')->where('name',$k)->setField('value',$v);
			}
			
			$this->updateConfig();
			return ['status'=>1,'msg'=>'更新成功！'];
		}else{
			$cdata=Db::name('cofing')->select();
			$config=[];
			foreach($cdata as $v){
				$config[$v['name']]=$v['value'];
			}
			
			$httphost='http://'.$_SERVER['HTTP_HOST'];
			$config['wx_url']=$httphost.url('mobile/Wechat/index');
			$config['wx_token']=md5($config['wx_url']);
			
			
			$this->assign('config',$config);
			
			return $this->show();
		}
	}
	
	//微信粉丝列表
	public function wechatuser(){
		$map=[];
		if(input('subscribe')!='') $map['subscribe']=input('subscribe');
		if(input('openid')!='') $map['openid']=input('openid');
		if(input('nickname')!='') $map['nickname']=input('nickname');
		if(input('groupid')!='') $map['groupid']=input('groupid');

    	$totalCount=Db::name('wx_user')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('wx_user')->where($map)->order('id')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		foreach($lists as $k=>$v){
			$lists[$k]['nickname']=deal_emoji($v['nickname']);
		}
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());

		return $this->show();
	}
	
	//查看粉丝
	public function showuser(){
		$id=input('id');
		$info=Db::name('wx_user')->where('id',$id)->find();
		$info['nickname']=deal_emoji($info['nickname']);
		$this->assign('info',$info);
		return $this->show();
	}
	
	public function delwxuser(){
		if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//删除前获取该删除的会员名
					$names.=Db::name('wx_user')->where('id',$id)->value('nickname').'，';
					//删除
					$rs=Db::name('wx_user')->where('id',$id)->delete();
					if($rs==0){
						return $rd;
					}else{
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除会员:'.$names);
				}
			}
			return $rd;
		}else{//单条删除
			//删除前获取该删除的会员名
			$names=Db::name('wx_user')->where('id',input('id'))->value('nickname');
			//删除
			$rs=Db::name('wx_user')->where('id',input('id'))->delete();
			if($rs==0){
				$this->error('删除失败');
			}else{
				addAdminLog('成功删除会员:'.$names);
				$this->success('删除成功');
			}
		}
	}
	
	//同步粉丝 deal_emoji($aaa,0)
	public function updatewxuser(){
		$openidArr=model('Wechat')->getWxUserOpenId();
		if($openidArr['status']==0){
			return $openidArr;
		}
		
		$netUser=$openidArr['data'];
		
		$localUser=[];
		$userOpenid=Db::name('wx_user')->field('openid')->select();
		foreach($userOpenid as $k=>$v){
			$localUser[]=$v['openid'];
		}
		$newUser=array_diff($netUser,$localUser);//数组的差集
		
		if(empty($newUser)) return ['status'=>0,'msg'=>'已经没有最新关注的粉丝了！'];
		$wxuser=model('Wechat')->getWxUserList($newUser);
		
		$rs=Db::name('wx_user')->insertAll($wxuser);
		if($rs>0){
			return ['status'=>1,'msg'=>'微信粉丝同步成功！'];
		}else{
			return ['status'=>0,'msg'=>'微信粉丝同步失败！'];
		}
	}

	//微信粉丝分组
	public function wechatUserGroup(){
		$tags=model('Wechat')->getUserTag();
		$this->assign('tags',$result['tags']);
	}
}
?>