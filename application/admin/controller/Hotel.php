<?php
/**
 * 酒店管理
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
class Hotel extends AdminBase{
	//酒店列表
	public function index(){
		$map=[];
		if(input('status')!='') $map['status']=input('status');
		if(input('name')!='') $map['name']=['like','%'.input('name').'%'];

    	$totalCount=Db::name('hotel')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('hotel')->where($map)->order('sort ASC,hotel_id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		
		$lists = $data->all();
		foreach($lists as $k=>$v){
			$lists[$k]['level']=str_repeat('★',$v['star_level']);
		}
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
	}
	//添加编辑酒店
	public function addedithotel(){
		$hotel_id=input('hotel_id',0);
    	if(request()->isPost() || request()->isAjax()){
    		$data=input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|酒店名称' => 'require',
            	'address|详细地址' => 'require',
            	'tel|联系电话' => 'require',
            	'thumb|缩略图' => 'require',
            	'content|酒店详情描述' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			$data['images']=serialize($data['images']);
			
			$data['attribute_id']=implode(',',$data['attr']);
			unset($data['attr']);
			//编辑
    		if($hotel_id>0){
				$rs=Db::name('hotel')->where('hotel_id',$hotel_id)->update($data);
				if($rs!==false){
					addAdminLog('成功编辑酒店:'.$data['name']);
					$rd= ['status'=>1,'msg'=>'酒店编辑成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'酒店编辑失败'];
				}
    		}else{//添加
    			$data['addtime']=time();
    			$myid=Db::name('hotel')->insertGetId($data);
				if($myid!==false){
					addAdminLog('成功添加酒店:'.$data['name']);
					$rd= ['status'=>1,'msg'=>'酒店添加成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'酒店添加失败'];
				}
    		}
			return $rd;
    	}else{
    		//分类信息
			$info = Db::name('hotel')->where('hotel_id',$hotel_id)->find();
			$info['images']=unserialize($info['images']);
			$this->assign('info',$info);
			//属性
			$attrArr=Db::name('hotel_attribute')->where('pid',0)->order('sort')->select();
			foreach($attrArr as $k=>$v){
				$attrArr[$k]['child']=Db::name('hotel_attribute')->where('pid',$v['attr_id'])->order('sort')->select();
			}
			$this->assign('attrArr',$attrArr);
			
			return $this->fetch();
    	}
	}
	//删除酒店
	public function delhotel(){
		$hotel_id = input('hotel_id');
		if(empty($hotel_id)){
			//批量删除
			$ids=input('ids/a');
	        $id = implode(',', $ids);
	        $row = Db::name('hotel')->where('hotel_id','in', $id)->delete();
	        if($row!==false){
	        	DB::name('hotel_room')->where('hotel_id','in', $id)->delete();//删除该酒店下的房间
				DB::name('hotel_price')->where('hotel_id','in', $id)->delete();//删除房间下价格
				addAdminLog('成功批量删除酒店');
				$rd= ['status'=>1,'msg'=>'酒店批量删除成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'酒店批量删除失败'];
			}
			return $rd;
		}else{
	        //单删除
	        $catname=DB::name('hotel')->where('hotel_id',$hotel_id)->value('name');
	        $rs=DB::name('hotel')->where('hotel_id',$hotel_id)->delete();
	        if($rs!==false){
	        	DB::name('hotel_room')->where('hotel_id',$hotel_id)->delete();//删除该酒店下的房间
				DB::name('hotel_price')->where('hotel_id',$hotel_id)->delete();//删除房间下价格
	        	addAdminLog('成功删除酒店:'.$catname);
	        	return ['status'=>1,'msg'=>'删除成功！'];
	        }else{
	        	addAdminLog('视图删除酒店失败:'.$catname);
	        	return ['status'=>1,'msg'=>'删除失败！'];
	        }
		}
	}
	
	//=====酒店属性=============================================================
	
	//酒店属性
	public function hotelattr(){
		$map=[];
		if(input('status')!='') $map['status']=input('status');
		if(input('name')!='') $map['name']=['like','%'.input('name').'%'];
		if(input('typeid')!=''){
			$map['typeid']=input('typeid');
		}else{
			$map['typeid']=0;
		}
		if(input('pid')!=''){
			$map['pid']=input('pid');
		}else{
			$map['pid']=0;
		}
    	$totalCount=Db::name('hotel_attribute')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('hotel_attribute')->where($map)->order('sort ASC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		if(input('pid')>0){
			$pname=Db::name('hotel_attribute')->where('attr_id',input('pid'))->value('name');
			$this->assign('pname','('.$pname.') ');
		}
		return $this->fetch();
	}
	//添加编辑酒店属性
	public function addedithotelattr(){
		$attr_id=input('attr_id',0);
    	if(request()->isPost() || request()->isAjax()){
    		$data=input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|属性名称' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}

			//编辑
    		if($attr_id>0){
				$rs=Db::name('hotel_attribute')->where('attr_id',$attr_id)->update($data);
				if($rs!==false){
					addAdminLog('成功编辑酒店属性:'.$data['name']);
					$rd= ['status'=>1,'msg'=>'酒店属性编辑成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'酒店属性编辑失败'];
				}
    		}else{//添加
    			$myid=Db::name('hotel_attribute')->insertGetId($data);
				if($myid!==false){
					addAdminLog('成功添加酒店属性:'.$data['name']);
					$rd= ['status'=>1,'msg'=>'酒店属性添加成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'酒店属性添加失败'];
				}
    		}
			return $rd;
    	}else{
    		//信息
			$info = Db::name('hotel_attribute')->where('attr_id',$attr_id)->find();
			$this->assign('info',$info);
			
			$attrArr=Db::name('hotel_attribute')->where(['pid'=>0,'typeid'=>input('typeid')])->order('sort')->select();
			$this->assign('attrArr',$attrArr);
			
			return $this->fetch();
    	}
	}
	
	//删除酒店属性
	public function delhotelattr(){
		$attr_id = input('attr_id');
		if(empty($attr_id)){
			//批量删除
			$ids=input('ids/a');
	        $id = implode(',', $ids);
	        $row = Db::name('hotel_attribute')->where('attr_id','in', $id)->delete();
	        if($row!==false){
				addAdminLog('成功批量删除酒店属性');
				$rd= ['status'=>1,'msg'=>'酒店属性批量删除成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'酒店属性批量删除失败'];
			}
			return $rd;
		}else{
	        //单删除
			$catname=DB::name('hotel_attribute')->where('attr_id',$attr_id)->value('name');
			$rs=DB::name('hotel_attribute')->where('attr_id',$attr_id)->delete();
	        if($rs!==false){
	        	addAdminLog('成功删除酒店属性:'.$catname);
	        	return ['status'=>1,'msg'=>'删除成功！'];
	        }else{
	        	addAdminLog('视图删除酒店属性失败:'.$catname);
	        	return ['status'=>1,'msg'=>'删除失败！'];
	        }
		}
	}
	
	//=====酒店房间=============================================================
	
	//酒店房间
	public function hotelroom(){
		$map=[];
		$map['hotel_id']=input('hotel_id');
		if(input('status')!='') $map['status']=input('status');
		if(input('name')!='') $map['name']=['like','%'.input('name').'%'];

    	$totalCount=Db::name('hotel_room')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('hotel_room')->where($map)->order('sort ASC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		foreach($lists as $k=>$v){
			$lists['$k']['hotelname']=Db::name('hotel')->where('hotel_id',$v['hotel_id'])->value('name');
		}
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
	}
	//添加编辑酒店房间
	public function addedithotelroom(){
		$room_id=input('room_id',0);
    	if(request()->isPost() || request()->isAjax()){
    		$data=input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|房间名称' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			//编辑
    		if($room_id>0){
				$rs=Db::name('hotel_room')->where('room_id',$room_id)->update($data);
				if($rs!==false){
					addAdminLog('成功编辑酒店房间:'.$data['name']);
					$rd= ['status'=>1,'msg'=>'酒店房间编辑成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'酒店房间编辑失败'];
				}
    		}else{//添加
    			$myid=Db::name('hotel_room')->insertGetId($data);
				if($myid!==false){
					addAdminLog('成功添加酒店房间:'.$data['name']);
					$rd= ['status'=>1,'msg'=>'酒店房间添加成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'酒店房间添加失败'];
				}
    		}
			return $rd;
    	}else{
    		//信息
			$info = Db::name('hotel_room')->where('room_id',$room_id)->find();
			$this->assign('info',$info);

			return $this->fetch();
    	}
	}
	
	//删除酒店房间
	public function delhotelroom(){
		$room_id = input('room_id');
		if(empty($room_id)){
			//批量删除
			$ids=input('ids/a');
	        $id = implode(',', $ids);
	        $row = Db::name('hotel_room')->where('room_id','in', $id)->delete();
	        if($row!==false){
	        	DB::name('hotel_price')->where('room_id','in', $id)->delete();//同时删除房间价格
				addAdminLog('成功批量删除酒店房间');
				$rd= ['status'=>1,'msg'=>'酒店房间批量删除成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'酒店房间批量删除失败'];
			}
			return $rd;
		}else{
	        //单删除
			$catname=DB::name('hotel_room')->where('room_id',$room_id)->value('name');
			$rs=DB::name('hotel_room')->where('room_id',$room_id)->delete();
	        if($rs!==false){
	        	DB::name('hotel_price')->where('room_id',$room_id)->delete();
	        	addAdminLog('成功删除酒店房间:'.$catname);
	        	return ['status'=>1,'msg'=>'删除成功！'];
	        }else{
	        	addAdminLog('视图删除酒店房间失败:'.$catname);
	        	return ['status'=>1,'msg'=>'删除失败！'];
	        }
		}
	}
	
	//=====酒店房间价格=============================================================
	
	//酒店房间价格
	public function hotelprice(){
		$map=[];
		$map['room_id']=input('room_id');
		if(input('status')!='') $map['status']=input('status');
		if(input('name')!='') $map['name']=['like','%'.input('name').'%'];
		if(input('hotel_id')!='') $map['hotel_id']=input('hotel_id');

    	$totalCount=Db::name('hotel_price')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('hotel_price')->where($map)->order('sort ASC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		foreach($lists as $k=>$v){
			$lists[$k]['hotelname']=Db::name('hotel')->where('hotel_id',$v['hotel_id'])->value('name');
			$lists[$k]['roomname']=Db::name('hotel_room')->where('room_id',$v['room_id'])->value('name');
		}
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
	}
	//添加编辑酒店房间价格
	public function addedithotelprice(){
		$price_id=input('price_id',0);
    	if(request()->isPost() || request()->isAjax()){
    		$data=input('post.');
			//数据验证
        	$validate = new Validate([
            	'name|名称' => 'require',
            	'price|价格' => 'require',
        	]);
        	if(!$validate->check($data)){
            	$msg=$validate->getError();
            	return ['status'=>0,'msg'=>$msg];
        	}
			//编辑
    		if($price_id>0){
				$rs=Db::name('hotel_price')->where('price_id',$price_id)->update($data);
				if($rs!==false){
					addAdminLog('成功编辑酒店房间价格:'.$data['name']);
					$rd= ['status'=>1,'msg'=>'酒店房间价格编辑成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'酒店房间价格编辑失败'];
				}
    		}else{//添加
    			$myid=Db::name('hotel_price')->insertGetId($data);
				if($myid!==false){
					addAdminLog('成功添加酒店房间价格:'.$data['name']);
					$rd= ['status'=>1,'msg'=>'酒店房间价格添加成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'酒店房间价格添加失败'];
				}
    		}
			return $rd;
    	}else{
    		//信息
			$info = Db::name('hotel_price')->where('price_id',$price_id)->find();
			$this->assign('info',$info);

			return $this->fetch();
    	}
	}
	
	//删除酒店房间价格
	public function delhotelprice(){
		$price_id = input('price_id');
		if(empty($price_id)){
			//批量删除
			$ids=input('ids/a');
	        $id = implode(',', $ids);
	        $row = Db::name('hotel_price')->where('price_id','in', $id)->delete();
	        if($row!==false){
				addAdminLog('成功批量删除酒店房间价格');
				$rd= ['status'=>1,'msg'=>'酒店房间价格批量删除成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'酒店房间价格批量删除失败'];
			}
			return $rd;
		}else{
	        //单删除
			$catname=DB::name('hotel_price')->where('price_id',$price_id)->value('name');
			$rs=DB::name('hotel_price')->where('price_id',$price_id)->delete();
	        if($rs!==false){
	        	addAdminLog('成功删除酒店房间价格:'.$catname);
	        	return ['status'=>1,'msg'=>'删除成功！'];
	        }else{
	        	addAdminLog('视图删除酒店房间价格失败:'.$catname);
	        	return ['status'=>1,'msg'=>'删除失败！'];
	        }
		}
	}
	
	//=====酒店订单=============================================================
	
	//酒店订单
	public function hotelorder(){
		$map=[];
		if(input('order_status')!='') $map['order_status']=input('order_status');
		if(input('username')!='') $map['username']=['like','%'.input('username').'%'];
		if(input('hotel_id')!='') $map['hotel_id']=input('hotel_id');
		if(input('tel')!='') $map['tel']=input('tel');

    	$totalCount=Db::name('hotel_order')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('hotel_order')->where($map)->order('id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
	}
	
	//酒店订单详情
	public function hotelorderinfo(){
		$id=input('id',0);
    	if(request()->isPost() || request()->isAjax()){
    		$data=input('post.');
			$rs=Db::name('hotel_order')->where('id',$id)->update($data);
			if($rs!==false){
				addAdminLog('成功处理酒店订单:'.$data['order_sn']);
				$rd= ['status'=>1,'msg'=>'酒店订单处理成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'酒店订单处理失败'];
			}
			return $rd;
    	}else{
    		//信息
			$info = Db::name('hotel_order')->where('id',$id)->find();
			$this->assign('info',$info);

			return $this->fetch();
    	}
	}
	
	//=====酒店评论=============================================================
	
	//酒店评论
	public function comment(){
		$map=[];
		if(input('username')!='')$map['username']=input('username');
		if(input('content')!='')$map['content']=['like','%'.input('content').'%'];
		
		$totalCount=Db::name('hotel_comment')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('hotel_comment')->where($map)->order('addtime DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
	}
	//查看酒店评论详情
	public function commentinfo(){
		$id = input('id');
        $res = Db::name('hotel_comment')->where('id',$id)->find();
        if(!$res){
            return $this->error('不存在该评论');
        }
		$res['img']=unserialize($res['img']);
		
        if(request()->isPost() || request()->isAjax()){
            $add['parent_id'] = $id;
            $add['content'] = input('content');
            $add['hotel_id'] = $res['hotel_id'];
            $add['addtime'] = time();
            $add['username'] = get_adminName();
            $add['is_show'] = 1;
            $row =  Db::name('hotel_comment')->insert($add);
            if($row!==false){
				addAdminLog('成功回复酒店评论');
				$rd= ['status'=>1,'msg'=>'酒店评论回复成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'酒店评论回复失败'];
			}
			return $rd;
        }
        $reply = Db::name('hotel_comment')->where('parent_id',$id)->order('addtime')->select(); // 评论回复列表
        
        $this->assign('info',$res);
        $this->assign('reply',$reply);
        return $this->fetch();
	}
	//删除酒店评论
	public function commentdel(){
        $ids=input('ids/a');
        $id = implode(',', $ids);
        $row = Db::name('hotel_comment')->where('id','in', $id)->whereOr('parent_id','in',$id)->delete();
        if($row!==false){
			addAdminLog('成功删除酒店评论');
			$rd= ['status'=>1,'msg'=>'酒店评论删除成功'];
		}else{
			$rd= ['status'=>0,'msg'=>'酒店评论删除失败'];
		}
		return $rd;
    }
}
?>