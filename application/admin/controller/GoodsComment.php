<?php
/**
 * 评论管理控制器
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
class GoodsComment extends AdminBase{
	//评论
	public function index(){
		$map=[];
		if(input('nickname')!='')$map['username']=input('nickname');
		if(input('content')!='')$map['content']=['like','%'.input('content').'%'];
		$totalCount=Db::name('goods_comment')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('goods_comment')->where($map)->order('add_time DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		if(!empty($lists)){
            $goods_id_arr = get_arr_column($lists, 'goods_id');
            $goods_list = Db::name('goods')->where("goods_id", "in" , implode(',', $goods_id_arr))->column("goods_id,goods_name");
        }
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		$this->assign('goods_list',$goods_list);
		return $this->fetch();
	}
	//查看详情
	public function detail(){
		$id = input('id');
        $res = Db::name('goods_comment')->where('comment_id',$id)->find();
        if(!$res){
            return $this->error('不存在该评论');
        }
		$res['img']=unserialize($res['img']);
		
        if(request()->isPost() || request()->isAjax()){
            $add['parent_id'] = $id;
            $add['content'] = input('content');
            $add['goods_id'] = $res['goods_id'];
            $add['add_time'] = time();
            $add['username'] = get_adminName();
            $add['is_show'] = 1;
            $row =  Db::name('goods_comment')->insert($add);
            if($row!==false){
				addAdminLog('成功回复商品评论');
				$rd= ['status'=>1,'msg'=>'商品评论回复成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'商品评论回复失败'];
			}
			return $rd;
        }
        $reply = Db::name('goods_comment')->where('parent_id',$id)->order('add_time')->select(); // 评论回复列表
        
        $this->assign('info',$res);
        $this->assign('reply',$reply);
        return $this->fetch();
	}
	//删除评论
	public function del(){
        $ids=input('ids/a');
        $id = implode(',', $ids);
        $row = Db::name('goods_comment')->where('comment_id','in', $id)->whereOr('parent_id','in',$id)->delete();
        if($row!==false){
			addAdminLog('成功删除商品评论');
			$rd= ['status'=>1,'msg'=>'商品评论删除成功'];
		}else{
			$rd= ['status'=>0,'msg'=>'商品评论删除失败'];
		}
		return $rd;
    }
	
	//****************************************************************************************************
	//商品咨询列表
	public function ask_list(){
    	$map=[];
		if(input('username')!='')$map['username']=input('username');
		if(input('content')!='')$map['content']=['like','%'.input('content').'%'];
		$totalCount=Db::name('goods_consult')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('goods_consult')->where($map)->order('add_time DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$goods_list=[];
		if(!empty($lists)){
            $goods_id_arr = get_arr_column($lists, 'goods_id');
            $goods_list = Db::name('goods')->where("goods_id", "in" , implode(',', $goods_id_arr))->column("goods_id,goods_name");
        }
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		$this->assign('goods_list',$goods_list);
		$consult_type = array(0=>'默认咨询',1=>'商品咨询',2=>'支付咨询',3=>'配送',4=>'售后');
		$this->assign('consult_type',$consult_type);
		
		return $this->fetch();
    }
	//咨询详细
	public function consult_info(){
    	$id = input('id');
    	$res = Db::name('goods_consult')->where('id',$id)->find();
    	if(!$res){
    		exit($this->error('不存在该咨询'));
    	}
    	if(request()->isPost() || request()->isAjax()){
    		$add['parent_id'] = $id;
    		$add['content'] = input('content');
    		$add['goods_id'] = $res['goods_id'];
            $add['consult_type'] = $res['consult_type'];
			$add['username'] = get_adminName();
    		$add['add_time'] = time();    		
    		$add['is_show'] = 1;   	
    		$row =  Db::name('goods_consult')->insert($add);
    		if($row!==false){
				addAdminLog('成功回复商品咨询');
				$rd= ['status'=>1,'msg'=>'商品咨询回复成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'商品咨询回复失败'];
			}
			return $rd;
  	
    	}
    	$reply = Db::name('goods_consult')->where('parent_id',$id)->select(); // 咨询回复列表   	 
    	$this->assign('info',$res);
    	$this->assign('reply',$reply);
		
    	return $this->fetch();
    }
	//处理
	public function ask_handle(){
        $type = input('type');
        $selected_id = input('ids/a');
        $selected_id = implode(',', $selected_id);
		$row = Db::name('goods_consult')->where('id','IN', $selected_id)->whereOr('parent_id', 'IN', $selected_id)->delete();
        if($row!==false){
			addAdminLog('成功删除咨询删除咨询');
			$rd= ['status'=>1,'msg'=>'操作完成'];
		}else{
			$rd= ['status'=>0,'msg'=>'操作失败'];
		}
		return $rd;
    }
}
?>