<?php
/**
 * 促销管理
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\controller;
use app\admin\logic\GoodsLogic;
use think\Validate;
use \think\Db;
class Promotion extends AdminBase{
	//团购管理
	public function group_buy(){
		$map=[];
		$start_time = strtotime(input('start_time'));
        $end_time = strtotime(input('end_time'));
		if(input('start_time')!='')$map['start_time']=['>=',$start_time];
		if(input('end_time')!='')$map['end_time']=['<=',$end_time];
		input('title')!=''?$map['title']=['like','%'.input('title').'%']:'';

		$totalCount=Db::name('group_buy')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('group_buy')->where($map)->order('sort ASC,id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
	}
	//团购添加编辑
	public function group_buy_addedit(){
		$id=input('id',0);
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
			//$data['groupbuy_intro'] = htmlspecialchars(stripslashes($this->request->param('groupbuy_intro')));
        	$data['start_time'] = strtotime($data['start_time']);
        	$data['end_time'] = strtotime($data['end_time']);
			if($id){
				$r = Db::name('group_buy')->where('id',$data['id'])->update($data);
            	Db::name('goods')->where(['prom_type'=>2,'prom_id'=>$data['id']])->update(['prom_id'=>0,'prom_type'=>0]);
            	Db::name('goods')->where("goods_id",$data['goods_id'])->update(['prom_id'=>$data['id'],'prom_type'=>2]);
				if($r!==false){
					addAdminLog('成功编辑团购:'.$data['title']);
					return ['status'=>1,'msg'=>'编辑成功！','url'=>url('group_buy')];
				}else{
					return ['status'=>0,'msg'=>'编辑失败！','url'=>''];
				}
			}else{
				$r = Db::name('group_buy')->insertGetId($data);
            	Db::name('goods')->where("goods_id",$data['goods_id'])->update(['prom_id'=>$r,'prom_type'=>2]);//prom_type 0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠,4预售
				if($r!==false){
					addAdminLog('成功添加团购:'.$data['title']);
					return ['status'=>1,'msg'=>'添加成功！','url'=>url('group_buy')];
				}else{
					return ['status'=>0,'msg'=>'添加失败！','url'=>''];
				}
			}
		}
		$info = Db::name('group_buy')->where('id',$id)->find();
		$this->assign('info',$info);
		return $this->fetch();
	}
	//团购删除
	public function group_buy_del(){
		$id=input('id');
    	$orderLogic = new OrderLogic();
		if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $v){
					//删除前获取该删除的名
					$names.=Db::name('group_buy')->where('id',$v)->value('title').'，';
					$r = Db::name('group_buy')->where('id',$v)->delete();//删除
            		Db::name('goods')->where("prom_type=2 and prom_id=".$v)->update(['prom_id'=>0,'prom_type'=>0]);
					if($r){
						addAdminLog('成功删除团购:'.$names);
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}else{
						return $rd;
					}
		        }
			}
			return $rd;
		}else{
			$names.=Db::name('group_buy')->where('id',$v)->value('title');
	    	$r = Db::name('group_buy')->where('id',$id)->delete();//删除
    		Db::name('goods')->where("prom_type=2 and prom_id=".$id)->update(['prom_id'=>0,'prom_type'=>0]);
	        if($r){
	        	addAdminLog('成功删除团购:'.$names);
	            return ['status'=>1,'msg'=>'成功删除团购!'];
	        }else{
	        	addAdminLog('试图删除团购失败:'.$order_sn);
	        	return ['status'=>1,'msg'=>'删除团购失败!'];
	        }
		}
	}
	//团购排序
	public function group_buy_sort(){
		$sort=$_POST['sort'];
		foreach($sort as $k=>$v){
			Db::name('group_buy')->where('id',$k)->setField('sort',$v);
		}
		return ['status'=>1,'msg'=>'排序成功！'];
	}
	//选择商品
	public function search_goods(){
        $GoodsLogic = new GoodsLogic;
        $brandList = $GoodsLogic->getSortBrands();
        $this->assign('brandList', $brandList);
        $categoryList = $GoodsLogic->getSortCategory();
        $this->assign('categoryList', $categoryList);

        $goods_id = input('goods_id');
		$map=['is_on_sale'=>1,'prom_type'=>0,'store_count'=>['>',0]];//上架、没参其他活动、有库存的
		if (!empty($goods_id))$map['goods_id']=['not in',$goods_id];
		if(input('intro'))$map[input('intro')]=1;
		if (input('cat_id')) {
			$grandson_ids = getCatGrandson(input('cat_id'));
			$map['cat_id']=['in',implode(',',$grandson_ids)];
		}
		if (input('brand_id'))$map[input('brand_id')]=input('brand_id');
		if (!empty($_REQUEST['keywords'])){
			$map['goods_name|keywords']=['like','%'.input('keywords').'%'];
		}
		$totalCount=Db::name('goods')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('goods')->where($map)->order('goods_id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
        $tpl = input('tpl', 'search_goods');
        return $this->fetch($tpl);
    }

	/*******************************************************************************************************************************/

	//限时抢购
    public function flash_sale(){
    	$map=[];
		$start_time = strtotime(input('start_time'));
        $end_time = strtotime(input('end_time'));
		if(input('start_time')!='')$map['start_time']=['>=',$start_time];
		if(input('end_time')!='')$map['end_time']=['<=',$end_time];
		input('title')!=''?$map['title']=['like','%'.input('title').'%']:'';

		$totalCount=Db::name('flash_sale')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('flash_sale')->where($map)->order('sort ASC,id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
    }
	//添加编辑限时抢购
    public function flash_sale_addedit(){
    	$id=input('id',0);
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
        	$data['start_time'] = strtotime($data['start_time']);
        	$data['end_time'] = strtotime($data['end_time']);
			if($id){
				$r = Db::name('flash_sale')->where('id',$data['id'])->update($data);
            	Db::name('goods')->where(['prom_type'=>1,'prom_id'=>$data['id']])->update(['prom_id'=>0,'prom_type'=>0]);
            	Db::name('goods')->where("goods_id",$data['goods_id'])->update(['prom_id'=>$data['id'],'prom_type'=>1]);
				if($r!==false){
					addAdminLog('成功编辑抢购:'.$data['title']);
					return ['status'=>1,'msg'=>'编辑成功！','url'=>url('flash_sale')];
				}else{
					return ['status'=>0,'msg'=>'编辑失败！','url'=>''];
				}
			}else{
				$r = Db::name('flash_sale')->insertGetId($data);
            	Db::name('goods')->where("goods_id",$data['goods_id'])->update(['prom_id'=>$r,'prom_type'=>1]);//prom_type 0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠,4预售
				if($r!==false){
					addAdminLog('成功添加抢购:'.$data['title']);
					return ['status'=>1,'msg'=>'添加成功！','url'=>url('flash_sale')];
				}else{
					return ['status'=>0,'msg'=>'添加失败！','url'=>''];
				}
			}
		}
		$info = Db::name('flash_sale')->where('id',$id)->find();
		$this->assign('info',$info);
		return $this->fetch();
    }
	//删除限时抢购
    public function flash_sale_del(){
    	$id=input('id');
		if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $v){
					//删除前获取该删除的名
					$names.=Db::name('flash_sale')->where('id',$v)->value('title').'，';
					$r = Db::name('flash_sale')->where('id',$v)->delete();//删除
            		Db::name('goods')->where("prom_type=1 and prom_id=".$v)->update(['prom_id'=>0,'prom_type'=>0]);
					if($r){
						addAdminLog('成功删除抢购:'.$names);
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}else{
						return $rd;
					}
		        }
			}
			return $rd;
		}else{
	    	$r = Db::name('flash_sale')->where('id',$id)->delete();//删除
    		Db::name('goods')->where("prom_type=1 and prom_id=".$id)->update(['prom_id'=>0,'prom_type'=>0]);
	        if($r){
	        	addAdminLog('成功删除抢购:'.$order_sn);
	            return ['status'=>1,'msg'=>'成功删除抢购!'];
	        }else{
	        	addAdminLog('试图删除抢购失败:'.$order_sn);
	        	return ['status'=>1,'msg'=>'删除抢购失败!'];
	        }
		}
    }
	//排序限时抢购
    public function flash_sale_sort(){
    	$sort=$_POST['sort'];
		foreach($sort as $k=>$v){
			Db::name('flash_sale')->where('id',$k)->setField('sort',$v);
		}
		return ['status'=>1,'msg'=>'排序成功！'];
    }
	
	/*******************************************************************************************************************************/
	
	/**
     * 商品活动促销
     */
    public function prom_goods(){
    	$parse_type = ['0'=>'直接打折','1'=>'减价优惠','2'=>'固定金额出售','3'=>'买就赠优惠券'];
		$this->assign("parse_type", $parse_type);
		
		$level = Db::name('member_level')->column('id,name');
        
		$map=[];
		$start_time = strtotime(input('start_time'));
        $end_time = strtotime(input('end_time'));
		if(input('start_time')!='')$map['start_time']=['>=',$start_time];
		if(input('end_time')!='')$map['end_time']=['<=',$end_time];
		if(input('name')!='')$map['name']=['like','%'.input('name').'%'];
		if(input('type')!='')$map['type']=input('type');

		$totalCount=Db::name('prom_goods')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('prom_goods')->where($map)->order('id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		if($lists){
            foreach($lists as $k=>$v) {
                if(!empty($v['group']) && !empty($level)) {
                    $lists[$k]['group'] = explode(',', $v['group']);
                    foreach($lists[$k]['group'] as $s) {
                        $lists[$k]['group_name'] .= $level[$s] . ',';
                    }
                }
            }
        }
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
    }
	/**
     * 查看活动商品
     */
    public function prom_goods_shosgoods(){
    	$map=['prom_id'=>input('id'),'prom_type'=>3];
		$totalCount=Db::name('goods')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('goods')->where($map)->order('goods_id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
    }
	/**
     * 添加编辑活动促销
     */
    public function prom_goods_addedit(){
    	$id=input('id',0);
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
        	$data['start_time'] = strtotime($data['start_time']);
        	$data['end_time'] = strtotime($data['end_time']);
			if(is_array($data['group']))$data['group'] = implode(',', $data['group']);
			$goods_id_date=$data['goods_id'];
			unset($data['goods_id']);
			$rd=[];
			if($id){
				$r = Db::name('prom_goods')->where('id',$data['id'])->update($data);
				if($r!==false){
					$last_id = $id;
					addAdminLog('成功编辑优惠活动:'.$data['name']);
					$rd = ['status'=>1,'msg'=>'编辑成功！','url'=>url('flash_sale')];
				}else{
					$rd = ['status'=>0,'msg'=>'编辑失败！','url'=>''];
				}
			}else{
				$last_id = Db::name('prom_goods')->insertGetId($data);
            	if($r!==false){
					addAdminLog('成功添加优惠活动:'.$data['name']);
					$rd = ['status'=>1,'msg'=>'添加成功！','url'=>url('flash_sale')];
				}else{
					$rd = ['status'=>0,'msg'=>'添加失败！','url'=>''];
				}
			}
			
			if(is_array($goods_id_date) && !empty($goods_id_date)){
	            $goods_id = implode(',', $goods_id_date);
	            if ($id>0) {
	                Db::name("goods")->where("prom_id=$id and prom_type=3")->update(['prom_id'=>0,'prom_type'=>0]);
	            }
	            Db::name("goods")->where("goods_id in($goods_id)")->update(['prom_id'=>$last_id,'prom_type'=>3]);
	        }
			return $rd;
		}
		$level = Db::name('member_level')->select();
		$this->assign('level',$level);
		
		$info = Db::name('prom_goods')->where('id',$id)->find();
		$this->assign('info',$info);

		$prom_goods = Db::name('goods')->where("prom_id=$id and prom_type=3")->select();
        $this->assign('prom_goods', $prom_goods);
		
		$coupon = Db::name('coupon')->where('type',0)->select();
        $this->assign('coupon', $coupon);
		return $this->fetch();
    }
	/**
     * 删除活动促销
     */
    public function prom_goods_del(){
    	$id=input('id');
		if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $v){
					$order_goods = Db::name('order_goods')->where("prom_type = 3 and prom_id = $v")->find();
			        if (!empty($order_goods)) {
			            $this->error("该活动有订单参与不能删除!");
			        }
					//删除前获取该删除的名
					$names.=Db::name('prom_goods')->where('id',$v)->value('name').'，';
			        Db::name("goods")->where("prom_id=$v and prom_type=3")->update(array('prom_id' => 0, 'prom_type' => 0));
			        $r=Db::name('prom_goods')->where("id",$v)->delete();
					if($r){
						addAdminLog('成功删除优惠活动:'.$names);
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}else{
						return $rd;
					}
		        }
			}
			return $rd;
		}else{
			$order_goods = Db::name('order_goods')->where("prom_type = 3 and prom_id = $id")->find();
	        if (!empty($order_goods)) {
	            $this->error("该活动有订单参与不能删除!");
	        }
			$names.=Db::name('prom_goods')->where('id',$id)->value('name');
	    	Db::name("goods")->where("prom_id=$id and prom_type=3")->update(array('prom_id' => 0, 'prom_type' => 0));
	        $r=Db::name('prom_goods')->where("id",$id)->delete();
	        if($r){
	        	addAdminLog('成功删除优惠活动:'.$order_sn);
	            return ['status'=>1,'msg'=>'成功删除抢购!'];
	        }else{
	        	return ['status'=>1,'msg'=>'删除抢购失败!'];
	        }
		}
    }

	/*******************************************************************************************************************************/
	
	/**
     * 订单活动
     */
    public function prom_order(){
    	$parse_type = ['0'=>'满额打折','1' =>'满额优惠金额','2' =>'满额送积分','3' =>'满额送优惠券'];
		$this->assign("parse_type", $parse_type);
		
		$level = Db::name('member_level')->column('id,name');
        
		$map=[];
		$start_time = strtotime(input('start_time'));
        $end_time = strtotime(input('end_time'));
		if(input('start_time')!='')$map['start_time']=['>=',$start_time];
		if(input('end_time')!='')$map['end_time']=['<=',$end_time];
		if(input('name')!='')$map['name']=['like','%'.input('name').'%'];
		if(input('type')!='')$map['type']=input('type');

		$totalCount=Db::name('prom_order')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('prom_order')->where($map)->order('id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		if($lists){
            foreach($lists as $k=>$v) {
                if(!empty($v['group']) && !empty($level)) {
                    $lists[$k]['group'] = explode(',', $v['group']);
                    foreach($lists[$k]['group'] as $s) {
                        $lists[$k]['group_name'] .= $level[$s] . ',';
                    }
                }
            }
        }
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
    }
	/**
     * 订单活动添加编辑
     */
    public function prom_order_addedit(){
    	$id=input('id',0);
		if(request()->isPost() || request()->isAjax()){
			$data = input('post.');
        	$data['start_time'] = strtotime($data['start_time']);
        	$data['end_time'] = strtotime($data['end_time']);
			if(is_array($data['group']))$data['group'] = implode(',',$data['group']);
			$rd=[];
			if($id){
				$r = Db::name('prom_order')->where('id',$data['id'])->update($data);
				if($r!==false){
					addAdminLog('成功编辑订单促销:'.$data['name']);
					$rd = ['status'=>1,'msg'=>'编辑成功！','url'=>url('prom_order')];
				}else{
					$rd = ['status'=>0,'msg'=>'编辑失败！','url'=>''];
				}
			}else{
				$last_id = Db::name('prom_order')->insertGetId($data);
            	if($r!==false){
					addAdminLog('成功添加订单促销:'.$data['name']);
					$rd = ['status'=>1,'msg'=>'添加成功！','url'=>url('prom_order')];
				}else{
					$rd = ['status'=>0,'msg'=>'添加失败！','url'=>''];
				}
			}
			return $rd;
		}
		$level = Db::name('member_level')->select();
		$this->assign('level',$level);
		
		$info = Db::name('prom_order')->where('id',$id)->find();
		$this->assign('info',$info);
		
		$coupon = Db::name('coupon')->where('type',0)->select();
        $this->assign('coupon', $coupon);
		
		return $this->fetch();
    }
	/**
     * 订单活动删除
     */
    public function prom_order_del(){
    	$id=input('id');
		if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $v){
					$order = Db::name('order')->where("order_prom_id = $v")->find();
			        if (!empty($order)) {
			            $this->error("该活动有订单参与不能删除!");
			        }
					//删除前获取该删除的名
					$names.=Db::name('prom_order')->where('id',$v)->value('name').'，';
			        $r=Db::name('prom_order')->where("id",$v)->delete();
					if($r){
						addAdminLog('成功删除订单促销:'.$names);
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}else{
						return $rd;
					}
		        }
			}
			return $rd;
		}else{
			$order = Db::name('order')->where("order_prom_id = $v")->find();
	        if (!empty($order)) {
	            $this->error("该活动有订单参与不能删除!");
	        }
			$names.=Db::name('prom_order')->where('id',$id)->value('name');
	        $r=Db::name('prom_order')->where("id",$id)->delete();
	        if($r){
	        	addAdminLog('成功删除订单促销:'.$order_sn);
	            return ['status'=>1,'msg'=>'成功删除抢购!'];
	        }else{
	        	return ['status'=>1,'msg'=>'删除抢购失败!'];
	        }
		}
    }

	//预售管理
	public function pre_sell_list(){
		
	}
}
?>