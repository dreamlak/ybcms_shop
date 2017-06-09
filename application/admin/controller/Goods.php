<?php
/**
 * 商品管理
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
use app\admin\logic\GoodsLogic;
use think\AjaxPage;
use think\Page;
class Goods extends AdminBase{
	//商品分类列表
    public function categoryList(){
    	if(request()->isPost() || request()->isAjax()){
    		$GoodsLogic = new GoodsLogic();               
        	$cat_list = $GoodsLogic->goods_cat_list();
        	$this->assign('cat_list',$cat_list);
        	return $this->fetch('_categorylist');
    	}else{
    		return $this->fetch();
    	}
		
    }
	//添加修改商品分类
    public function addEditCategory(){
    	$id=input('id',0);
    	$GoodsLogic = new GoodsLogic();   
    	if(request()->isPost() || request()->isAjax()){
    		$data=input('post.');
			$data['parent_id']=0;
			if($data['parent_id_1']>0) $data['parent_id']=$data['parent_id_1'];//顶级ID
			if($data['parent_id_2']>0) $data['parent_id']=$data['parent_id_2'];//二级ID
			unset($data['parent_id_1']);
			unset($data['parent_id_2']);
			if($data['parent_id']==0){
				$data['level']=1;
				$parent_id_path='0';
			}else{
				$level=Db::name('goods_category')->where('id',$data['parent_id'])->value('level');//上级层次
				$data['level']=$level+1;//本级层次
				$parent_id_path=Db::name('goods_category')->where('id',$data['parent_id'])->value('parent_id_path');//上级家族图谱
			}
			//编辑
    		if($id>0){
    			$data['parent_id_path']=$parent_id_path.'_'.$id;
				$rs=Db::name('goods_category')->where('id',$id)->update($data);
				if($rs!==false){
					addAdminLog('成功编辑商品分类:'.$data['name']);
					$rd= ['status'=>1,'msg'=>'商品分类编辑成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'商品分类编辑失败'];
				}
    		}else{//添加
    			$myid=Db::name('goods_category')->insertGetId($data);
				if($myid!==false){
					Db::name('goods_category')->where('id',$myid)->setField('parent_id_path',$parent_id_path.'_'.$myid);
					addAdminLog('成功添加商品分类:'.$data['name']);
					$rd= ['status'=>1,'msg'=>'商品分类添加成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'商品分类添加失败'];
				}
    		}
			return $rd;
    	}else{
    		//分类信息
			$info = Db::name('goods_category')->where('id',$id)->find();
			$this->assign('info',$info);
			//顶级分类列表
			$cat_list = Db::name('goods_category')->where('parent_id',0)->select();
			$this->assign('cat_list',$cat_list);
			//获取分类默认选中的下拉框
			$level_cat = $GoodsLogic->find_parent_cat($info['id']);
			$this->assign('level_cat',$level_cat);
			return $this->fetch();
    	}
    }
	//删除分类
    public function delGoodsCategory(){
        $id = input('id');
        //判断子分类
        $count = Db::name('goods_category')->where('parent_id',$id)->count();
        $count > 0 && $this->error('该分类下还有分类不得删除!');
		
        //判断是否存在商品
        $goods_count = Db::name('goods')->where('cat_id',$id)->count();
        $goods_count > 0 && $this->error('该分类下有商品不得删除!');
		
        // 删除分类
        $rs=DB::name('goods_category')->where('id',$id)->delete();
		$catname=DB::name('goods_category')->where('id',$id)->value('name');
        if($rs!==false){
        	addAdminLog('成功删除商品分类:'.$catname);
        	return ['status'=>1,'msg'=>'删除成功！'];
        }else{
        	addAdminLog('视图删除商品分类失败:'.$catname);
        	return ['status'=>1,'msg'=>'删除失败！'];
        }
    }
	
	/**********************************************************************************************************************
     * 商品列表
	 **********************************************************************************************************************
     */
    public function goodsList(){
    	if(request()->isPost() || request()->isAjax()){
    		$map=[];
			if(input('key_word')!='') $map['goods_name|goods_remark|goods_content']=['like','%'.input('key_word').'%'];
			if(input('intro')) $map[input('intro')]=1;//新品/推荐
			if(input('brand_id')!='') $map['brand_id']=input('brand_id');//品牌
			if(input('is_on_sale')!='') $map['is_on_sale']=input('is_on_sale');//上架/下架
			
			if(input('cat_id') > 0){
	            $grandson_ids = getCatGrandson(input('cat_id'));
				$cids=implode(',',$grandson_ids);
	            $map['cat_id']=['in',$cids];
	        }
			
	    	$totalCount=Db::name('goods')->where($map)->count();//总数
			$pagecount=config('paginate.list_admin');//每页显示多少条
			$Page  = new AjaxPage($totalCount,$pagecount);
			$show = $Page->show();
			$orderby1=input('orderby1','goods_id');
			$orderby2=input('orderby2','desc');
	        $order_str = "`{$orderby1}` {$orderby2}";
			
			$goodsList = Db::name('goods')->where($map)->order($order_str)->limit($Page->firstRow.','.$Page->listRows)->select();
			
			$catList = Db::name('goods_category')->select();
	        $catList = convert_arr_key($catList, 'id');
	        $this->assign('catList',$catList);
	        $this->assign('goodsList',$goodsList);
	        $this->assign('page',$show);// 赋值分页输出
	        
	        return $this->fetch('_goodsList');
    	}else{
    		$GoodsLogic = new GoodsLogic();        
	        $brandList = $GoodsLogic->getSortBrands();//品牌(搜索)
	        $categoryList = $GoodsLogic->getSortCategory();//分类(搜索)
	        $this->assign('categoryList',$categoryList);
	        $this->assign('brandList',$brandList);
			$this->assign('totalCount',Db::name('goods')->count());
    		return $this->fetch();
    	}
    }
	//添加编辑商品
	public function addEditGoods(){
		$GoodsLogic = new GoodsLogic();
        $Goods = model('Goods');
        $type = input('goods_id')>0 ?2:1;//标识自动验证时的 场景 1 表示插入 2 表示更新
        //ajax提交验证
        if(request()->isPost() || request()->isAjax()){
            // 数据验证            
            $validate = \think\Loader::validate('Goods');
            if(!$validate->batch()->check(input('post.'))){                          
                $error = $validate->getError();
                $error_msg = array_values($error);
                $return_arr = ['status' => -1,'msg' => $error_msg[0],'data' => $error];
                return $return_arr;
            }else{
                $Goods->data(input('post.'),true); // 收集数据
                $Goods->on_time = time(); // 上架时间
                input('cat_id_2') && ($Goods->cat_id = input('cat_id_2'));
                input('cat_id_3') && ($Goods->cat_id = input('cat_id_3'));

                input('extend_cat_id_2') && ($Goods->extend_cat_id = input('extend_cat_id_2'));
                input('extend_cat_id_3') && ($Goods->extend_cat_id = input('extend_cat_id_3'));
				
                $Goods->shipping_area_ids = implode(',',input('shipping_area_ids/a',[]));
                $Goods->shipping_area_ids = $Goods->shipping_area_ids ? $Goods->shipping_area_ids : '';
                $Goods->spec_type = $Goods->goods_type;
                
                if($type == 2){//编辑
                    $goods_id = input('goods_id');
                    $Goods->allowField(true)->isUpdate(true)->save(); // 写入数据到数据库
                    // 修改商品后购物车的商品价格也修改一下
                    Db::name('cart')->where(['goods_id'=>$goods_id,'spec_key'=>''])->update([
                            'market_price'=>input('market_price'), //市场价
                            'goods_price'=>input('shop_price'), // 本店价
                            'member_goods_price'=>input('shop_price'), // 会员折扣价                        
                            ]);
                }else{//添加
                    $Goods->allowField(true)->save();//写入数据到数据库 (过滤post数组中的非数据表字段数据)
                    $goods_id = $insert_id = $Goods->getLastInsID();
                }
                $Goods->afterSave($goods_id);
                $GoodsLogic->saveGoodsAttr($goods_id,input('goods_type')); // 处理商品 属性

                $return_arr = ['status'=>1,'msg'=>'操作成功'];
                return $return_arr;
            }
        }
		//商品详情
        $goodsInfo = Db::name('goods')->where('goods_id=' . input('id', 0))->find();
		$this->assign('goodsInfo', $goodsInfo);  
		//获取分类默认选中的下拉框
        $level_cat = $GoodsLogic->find_parent_cat($goodsInfo['cat_id']);
		$this->assign('level_cat', $level_cat);
		//获取分类默认选中的下拉框
        $level_cat2 = $GoodsLogic->find_parent_cat($goodsInfo['extend_cat_id']);
        $this->assign('level_cat2', $level_cat2);
		//已经改成联动菜单
        $cat_list = Db::name('goods_category')->where("parent_id = 0")->select();
        $this->assign('cat_list', $cat_list);
		//品牌
        $brandList = $GoodsLogic->getSortBrands();
		$this->assign('brandList', $brandList);
		//模型
        $goodsType = Db::name("goods_type")->select();
		$this->assign('goodsType', $goodsType);
		//供应商
        $suppliersList = Db::name("suppliers")->where('status',1)->select();
		$this->assign('suppliersList',$suppliersList);
		//插件物流
        $plugin_shipping = Db::name('plugin')->where(array('type'=>array('eq','shipping')))->select();
		$this->assign('plugin_shipping',$plugin_shipping);
		//配送区域
        $shipping_area = model('ShippingArea')->getShippingArea();
		$this->assign('shipping_area',$shipping_area);
		//配送物流
        $goods_shipping_area_ids = explode(',',$goodsInfo['shipping_area_ids']);
        $this->assign('goods_shipping_area_ids',$goods_shipping_area_ids);
        //商品相册
        $goodsImages = Db::name("goods_images")->where('goods_id',input('id'))->select();
        $this->assign('goodsImages', $goodsImages);
        return $this->fetch();
	}
	/**
     * 删除商品
     */
    public function delGoods(){
        $goods_id = input('id');
        $error = '';
        
        // 判断此商品是否有订单
        $c1 = Db::name('order_goods')->where("goods_id = $goods_id")->count('1');
        $c1 && $error .= '此商品有订单,不得删除! <br/>';
        
        
         // 商品团购
        $c1 = Db::name('group_buy')->where("goods_id = $goods_id")->count('1');
        $c1 && $error .= '此商品有团购,不得删除! <br/>';   
        
         // 商品退货记录
        $c1 = Db::name('return_goods')->where("goods_id = $goods_id")->count('1');
        $c1 && $error .= '此商品有退货记录,不得删除! <br/>';
        
        if($error){
            $return_arr = array('status'=>-1,'msg'=>$error,'data'=>'',);      
            return $return_arr;
        }
        
        // 删除此商品        
        Db::name("Goods")->where('goods_id',$goods_id)->delete();  //商品表
        Db::name("cart")->where('goods_id',$goods_id)->delete();  // 购物车
        Db::name("goods_comment")->where('goods_id',$goods_id)->delete();  //商品评论
        Db::name("goods_consult")->where('goods_id',$goods_id)->delete();  //商品咨询
        model('Goods')->del_goods_images($goods_id);//商品相册
        Db::name("spec_goods_price")->where('goods_id',$goods_id)->delete();  //商品规格
        model('Goods')->del_spec_image($goods_id);//商品规格图片
        Db::name("goods_attr")->where('goods_id',$goods_id)->delete();  //商品属性     
        Db::name("goods_collect")->where('goods_id',$goods_id)->delete();  //商品收藏          
        
        $return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'');
		$goodsame=Db::name("Goods")->where('goods_id',$goods_id)->value('goods_name');
		
		addAdminLog('成功删除商品:'.$goodsame);
        return $return_arr;
    }
	/**
     * 删除商品相册图
     */
    public function del_goods_images(){
        $filename = input('filename','');
        $filename= str_replace('../','',$filename);
        $filename= trim($filename,'.');
        $filename= trim($filename,'/');
		if(!file_exists($filename))return ['status'=>0,'msg'=>'该图片不存在！'];
		
		if(!empty($filename) && file_exists($filename)){
            $size = getimagesize($filename);
            $filetype = explode('/',$size['mime']);
            if($filetype[0]!='image'){
                return ['status'=>0,'msg'=>'只能删除图片！'];
            }
            unlink($filename);
			$r=Db::name('goods_images')->where('image_url',input('filename'))->delete();
			if($r!==false){
				Db::name('attachment')->where('url',input('filename'))->delete();
				return ['status'=>1,'msg'=>'图片删除成功！'];
			}else{
				return ['status'=>0,'msg'=>'图片删除失败！'];
			}
        }
    }
	/**
     * 动态获取商品规格选择框 根据不同的数据返回不同的选择框
     */
    public function ajaxGetSpecSelect(){
        $goods_id = input('goods_id')?input('goods_id'):0;
		$spec_type=input('spec_type')?input('spec_type'):0;
        $specList = Db::name('spec')->where("type_id",$spec_type)->order('`order` desc')->select();
        foreach($specList as $k => $v){
            $specList[$k]['spec_item'] = Db::name('spec_item')->where('spec_id',$v['id'])->order('id')->column('id,item'); // 获取规格项                
		}
        $items_id = Db::name('spec_goods_price')->where('goods_id',$goods_id)->value("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");
        //dump($items_id);die;
        $items_ids = explode('_', $items_id);       
        // 获取商品规格图片
        $specImageList=[];
        if($goods_id){
           $specImageList = Db::name('spec_image')->where('goods_id',$goods_id)->column('spec_image_id,src');                 
        }        
        $this->assign('specImageList',$specImageList);
        
        $this->assign('items_ids',$items_ids);
        $this->assign('specList',$specList);
        return $this->fetch('ajax_spec_select');        
    }
	/**
     * 动态获取商品规格输入框 根据不同的数据返回不同的输入框
     */    
    public function ajaxGetSpecInput(){     
         $GoodsLogic = new GoodsLogic();
         $goods_id = input('goods_id') ? input('goods_id') : 0;
         $str = $GoodsLogic->getSpecInput($goods_id ,input('spec_arr/a',[[]]));
         exit($str);
    }
	/**
     * 动态获取商品属性输入框 根据不同的数据返回不同的输入框类型
     */
    public function ajaxGetAttrInput(){
        $GoodsLogic = new GoodsLogic();
        $str = $GoodsLogic->getAttrInput($_REQUEST['goods_id'],$_REQUEST['type_id']);
        exit($str);
    }
	/********************************************************************************************************************
     * 商品模型
     */
    public function goodsTypeList(){
		$totalCount=Db::name('goods_type')->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('goods_type')->order('id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		return $this->fetch();
    }
    //添加编辑商品模型
    public function addEditGoodsType(){
        $id = $this->request->param('id', 0);
        if(request()->isPost() || request()->isAjax()){
            $data = $this->request->post();
            if($id){
                DB::name('goods_type')->update($data);
				addAdminLog('成功添加商品模型:'.$data['name']);
            }else{
                DB::name('goods_type')->insert($data);
				addAdminLog('成功编商品模型:'.$data['name']);
			}
            return ['status'=>1,'msg'=>'操作成功！'];
        }
        $goodsType = DB::name('goods_type')->find($id);
        $this->assign('info', $goodsType);
        return $this->fetch();
    }
	//删除商品模型
    public function delGoodsType(){
        //判断 商品规格
        $id = $this->request->param('id');
        $count = Db::name("spec")->where('type_id',$id)->count("1");
        $count > 0 && $this->error('该类型下有商品规格不得删除!');
        //判断 商品属性
        $count = Db::name("goods_attribute")->where('type_id',$id)->count("1");
        $count > 0 && $this->error('该类型下有商品属性不得删除!');
        //删除分类
        $names=Db::name("goods_type")->where('id',$id)->value('goods_name');
        DB::name('goods_type')->where('id',$id)->delete();
		
		addAdminLog('成功删除商品模型:'.$names);
		return ['status'=>1,'msg'=>'操作成功！'];
    }
	
	/**************************************************************************************************************************
     * 商品规格列表    
     */
    public function specList(){
    	$map=[];
		if(input('type_id')!='')$map['type_id']=input('type_id');
    	$totalCount=Db::name('spec')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('spec')->where($map)->order('type_id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		
		$GoodsLogic = new GoodsLogic();        
        foreach($lists as $k => $v){// 获取规格项
            $arr = $GoodsLogic->getSpecItem($v['id']);
            $lists[$k]['spec_item'] = implode(',',$arr);
        }
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		//模型
		$goodsTypeList = Db::name("goods_type")->select();
        $this->assign('goodsTypeList',$goodsTypeList);
		//规格分类
        $goodsTypeArr = convert_arr_key($goodsTypeList, 'id');
		$this->assign('goodsTypeArr',$goodsTypeArr);
		
		$this->assign('typeName',Db::name('goods_type')->where('id',input('type_id'))->value('name'));
		return $this->fetch();
    }
	//添加修改编辑品规格
    public  function addEditSpec(){
    	$id = input('id',0);
        if(request()->isPost() || request()->isAjax()){
        	$validate = \think\Loader::validate('Spec');
            $post_data = input('post.');
			if($id>0){
                //更新数据
                $check = $validate->scene('edit')->batch()->check($post_data);
            }else{
                //插入数据
                $check = $validate->batch()->check($post_data);
            }
			if(!$check){
                $error = $validate->getError();
                $error_msg = array_values($error);
                $return_arr = ['status'=>-1,'msg'=>$error_msg[0],'data'=>$error];
                return $return_arr;
            }
			unset($post_data['items']);
			if($id){
				$rs=Db::name('spec')->where('id',$id)->update($post_data);
				if($rs!==false){
					model('Spec')->afterSave($id);//规格项处理
					addAdminLog('成功更新商品规格:'.$post_data['name']);
					return ['status'=>1,'msg'=>'操作成功！'];
				}else{
					return ['status'=>0,'msg'=>'操作失败！'];
				}
            }else{
				$insert_id=Db::name('spec')->insertGetId($post_data);
                if($insert_id>0){
					model('Spec')->afterSave($insert_id);//规格项处理
					addAdminLog('成功添加商品规格:'.$post_data['name']);
					return ['status'=>0,'msg'=>'操作成功！'];
				}else{
					return ['status'=>0,'msg'=>'操作失败！'];
				}
			}
        }
		//规格信息
        $info = Db::name('spec')->where('id',$id)->find();
        $GoodsLogic = new GoodsLogic();  
		$items = $GoodsLogic->getSpecItem($id);
		$info['items'] = implode(PHP_EOL,$items);//换行分割
		$this->assign('info',$info);
		
		$type_id=isset($info['type_id'])?$info['type_id']:input('type_id');
		$this->assign('type_id',$type_id);
		//商品模型
		$goodsTypeList = Db::name("goods_type")->select();
		$this->assign('goodsTypeList',$goodsTypeList);
		
		return $this->fetch();
    }
	//删除商品规格
    public function delGoodsSpec(){
        $id = input('id');
        // 判断 商品规格项
        $count = Db::name("spec_item")->where('spec_id',$id)->count();
        $count > 0 && $this->error('清空规格项后才可以删除!');
        // 删除分类
        Db::name('spec')->where('id',$id)->delete();
		$names=Db::name("spec")->where('id',$id)->value('name');
        addAdminLog('成功删除商品规格:'.$names);
		return ['status'=>1,'msg'=>'操作成功！'];
    }
	
	/****************************************************************************************************************
     * 商品属性列表
     */
    public function goodsAttributeList(){
    	$map=[];
    	if(input('type_id')!='')$map['type_id']=input('type_id');
    	$totalCount=Db::name('goods_attribute')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('goods_attribute')->where($map)->order('`order` DESC,attr_id DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		//模型
        $goodsTypeList = Db::name("goods_type")->select();
        $this->assign('goodsTypeList',$goodsTypeList);
		//规格分类
        $goodsTypeArr = convert_arr_key($goodsTypeList, 'id');
		$this->assign('goodsTypeArr',$goodsTypeArr);
		
		$attr_input_type = [0=>'手工录入',1=>'从列表中选择',2=>'多行文本框'];
		$this->assign('attr_input_type',$attr_input_type);
		
		$this->assign('typeName',Db::name('goods_type')->where('id',input('type_id'))->value('name'));
        return $this->fetch();
    }
	 /**
     * 添加修改编辑商品属性
     */
    public function addEditGoodsAttribute(){
    	$attr_id = input('attr_id/d',0);
		
		//添加编辑
        if(request()->isAjax()){
        	$post_data = input('post.');
			$attr_values = str_replace('_', '', input('attr_values')); // 替换特殊字符
	        $attr_values = str_replace('@', '', $attr_values); // 替换特殊字符            
	    	$attr_values = trim($attr_values);
			$post_data['attr_values'] = $attr_values;
        	// 数据验证            
            $validate = \think\Loader::validate('GoodsAttribute');
            if(!$validate->batch()->check($post_data)){
            	$error = $validate->getError();
                $error_msg = array_values($error);
                $return_arr = ['status'=>-1,'msg'=>$error_msg[0],'data'=>$error];
				return $return_arr;
            }else{
            	if($attr_id>0){
            		$rs=Db::name('goods_attribute')->where('attr_id',$attr_id)->update($post_data);
					if($rs!==false){
						addAdminLog('成功更新商品属性:'.$post_data['attr_name']);
						return ['status'=>1,'msg'=>'更新成功！'];
					}else{
						return ['status'=>0,'msg'=>'更新失败！'];
					}
            	}else{
            		$insert_id=Db::name('goods_attribute')->insertGetId($post_data);
					if($insert_id>0){
						addAdminLog('成功添加商品属性:'.$post_data['attr_name']);
						return ['status'=>1,'msg'=>'添加成功！'];
					}else{
						return ['status'=>0,'msg'=>'添加失败！'];
					}
            	}
            }
        }
    	$goodsTypeList = Db::name("goods_type")->select();
       	$goodsAttribute = Db::name("goods_attribute")->where('attr_id',$attr_id)->find();
		
       	$this->assign('goodsTypeList',$goodsTypeList);   
       	$this->assign('info',$goodsAttribute);
		
		$type_id=isset($goodsAttribute['type_id'])?$goodsAttribute['type_id']:input('type_id');
		$this->assign('type_id',$type_id);
		
		return $this->fetch();
    }
	/**
     * 删除商品属性
     */
    public function delGoodsAttribute(){
        $id = input('id');
        // 判断 有无商品使用该属性
        $count = Db::name("goods_attr")->where('attr_id',$id)->count();
        $count > 0 && $this->error('有商品使用该属性,不得删除!');
		
        //删除 属性
        Db::name('goods_attribute')->where('attr_id',$id)->delete();
		
        $names=Db::name("goods_attribute")->where('attr_id',$id)->value('attr_name');
        addAdminLog('成功删除商品规格:'.$names);
		
		return ['status'=>1,'msg'=>'操作成功！'];
    }
	
	/********************************************************************************************************
     * 品牌列表
     */
    public function brandList(){
    	$map=[];
    	if(input('keyword')!='')$map['name']=['like','%'.input('keyword').'%'];
    	
    	$totalCount=Db::name('brand')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('brand')->where($map)->order('sort')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
        $cat_list = Db::name("goods_category")->column('id,name');
        $this->assign('cat_list',$cat_list);
		//dump($cat_list);die;
        return $this->fetch();
    }
	/**
     * 添加修改编辑 商品品牌
     */
    public  function addEditBrand(){        
        $id = input('id');            
        if(request()->isPost() || request()->isAjax()){
            $data = input('post.');
            if($id){
                $rs=Db::name("brand")->where('id',$id)->update($data);
          		if($rs!==false){
					addAdminLog('成功更新商品品牌:'.$data['name']);
					return ['status'=>1,'msg'=>'更新成功！'];
				}else{
					return ['status'=>0,'msg'=>'更新失败！'];
				}
			}else{
                $rs=Db::name("brand")->insert($data);
				if($rs!==false){
					addAdminLog('成功添加商品品牌:'.$data['name']);
					return ['status'=>1,'msg'=>'添加成功！'];
				}else{
					return ['status'=>0,'msg'=>'添加失败！'];
				}
			}
        }           
       $cat_list = Db::name('goods_category')->where('parent_id',0)->select(); // 已经改成联动菜单
       $this->assign('cat_list',$cat_list);
	   
       $brand = Db::name("brand")->where('id',$id)->find();             
       $this->assign('brand',$brand);
	   
       return $this->fetch();
	}
	/**
     * 删除品牌
     */
    public function delBrand(){        
        // 判断此品牌是否有商品在使用
        $goods_count = Db::name('goods')->where('brand_id',input('id'))->count();
        if($goods_count){
            $return_arr = ['status'=>0,'msg'=>'此品牌有商品在用不得删除!','data'=>''];
            return $return_arr;
        }
        
        Db::name("brand")->where('id',input('id'))->delete(); 
        $return_arr = ['status'=>1,'msg'=>'操作成功','data'=>''];
		
        return $return_arr;
    }
	
	
}
?>