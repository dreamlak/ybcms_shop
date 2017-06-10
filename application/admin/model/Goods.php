<?php
/**
 * 商品模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\admin\model;
use think\Model;
use think\Db;
class Goods extends Model {
	/**
     * 后置操作方法
     * 自定义的一个函数 用于数据保存后做的相应处理操作, 使用时手动调用
     * @param int $goods_id 商品id
     */
    public function afterSave($goods_id){            
        //商品货号
        $goods_sn = "YB".str_pad($goods_id,9,"0",STR_PAD_LEFT);   
        $this->where("goods_id = $goods_id and goods_sn = ''")->update(array("goods_sn"=>$goods_sn)); // 根据条件更新记录
                 
        // 商品图片相册  图册
        $goods_images = input('goods_images/a');
        if(count($goods_images) > 1){                          
            //array_pop($goods_images); //删除最后一个             
            $goodsImagesArr = Db::name('goods_images')->where('goods_id',$goods_id)->column('img_id,image_url'); // 查出所有已经存在的图片
             
            //删除图片
            foreach($goodsImagesArr as $key => $val){
                if(!in_array($val, $goods_images))
                    Db::name('goods_images')->where('img_id',$key)->delete(); // 
            }
            //添加图片
            foreach($goods_images as $key => $val){
                if($val == null)  continue;                                  
                if(!in_array($val, $goodsImagesArr)){                 
                    $data = array(
                        'goods_id' => $goods_id,
                        'image_url' => $val,
                    );
                    Db::name('goods_images')->insert($data); // 实例化User对象                     
                }
            }
        }
        //查看主图是否已经存在相册中
        $original_img = input('original_img');
        $c = Db::name('goods_images')->where(['goods_id'=>$goods_id,'image_url'=>$original_img])->count(); 
        if($c == 0 && $original_img){
            Db::name('goods_images')->insert(array('goods_id'=>$goods_id,'image_url'=>$original_img)); 
        }
        //delFile("./static/uploads/goods/thumb/$goods_id");//删除缩略图
         
        // 商品规格价钱处理        
        Db::name("spec_goods_price")->where('goods_id',$goods_id)->delete(); // 删除原有的价格规格对象
        //dump(input('item/a'));die;
        if(is_array(input('item/a'))){
            $spec = Db::name('spec')->column('id,name'); // 规格表
            $specItem = Db::name('spec_item')->column('id,item');//规格项
                          
            foreach(input('item/a') as $k => $v){
               	//批量添加数据
               	$v['price'] = trim($v['price']);
               	$store_count = $v['store_count'] = trim($v['store_count']); // 记录商品总库存
               	$v['sku'] = trim($v['sku']);
               	$dataList[] = ['goods_id'=>$goods_id,'key'=>$k,'key_name'=>$v['key_name'],'price'=>$v['price'],'store_count'=>$v['store_count'],'sku'=>$v['sku']];
                // 修改商品后购物车的商品价格也修改一下
                Db::name('cart')->where(['goods_id'=>$goods_id,'spec_key'=>$k])->update(array(
                    'market_price'=>$v['price'], //市场价
                    'goods_price'=>$v['price'], // 本店价
                    'member_goods_price'=>$v['price'], // 会员折扣价                        
                ));
            }            
            Db::name("spec_goods_price")->insertAll($dataList);             
        }
        // 商品规格图片处理
        if(input('item_img/a')){    
            Db::name('spec_image')->where('goods_id',$goods_id)->delete(); // 把原来是删除再重新插入
            foreach (input('item_img/a') as $key => $val){                 
                Db::name('spec_image')->insert(array('goods_id'=>$goods_id ,'spec_image_id'=>$key,'src'=>$val));
            }                                                    
        }
        refresh_stock($goods_id); //刷新商品库存
    }
	//删除相册
	public function del_goods_images($goods_id){
		$goodsImagesArr=Db::name("goods_images")->where('goods_id ='.$goods_id)->column('img_id,image_url');
		//删除图片
        foreach($goodsImagesArr as $key => $val){
            if(!empty($val)){
                Db::name('goods_images')->where('img_id',$key)->delete();
				//删除文件
		        $filename= str_replace('../','',$val);
		        $filename= trim($filename,'.');
		        $filename= trim($filename,'/');
				if(!empty($filename) && file_exists($filename)){
		            unlink($filename);
					Db::name('goods_images')->where('image_url',$val)->delete();//再次按图片地址删除
					Db::name('attachment')->where('url',$val)->delete();
		        }
			}
        }
	}
	
	//删除规格图片
	public function del_spec_image($goods_id){
		$specImagesArr=Db::name("spec_image")->where('goods_id',$goods_id)->column('spec_image_id,src');
		Db::name("spec_image")->where('goods_id',$goods_id)->delete();//删除规格图
		//删除图片
        foreach($specImagesArr as $key => $val){
            if(!empty($val)){
				//删除文件
		        $filename= str_replace('../','',$val);
		        $filename= trim($filename,'.');
		        $filename= trim($filename,'/');
				if(!empty($filename) && file_exists($filename)){
		            unlink($filename);
					//删除附件记录
					Db::name('attachment')->where('url',$val)->delete();
		        }
			}
        }
	}
}
