<?php
 /**
 * 规格模型
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
class Spec extends Model {
   /**
     * 后置操作方法
     * 自定义的一个函数 用于数据保存后做的相应处理操作, 使用时手动调用
     * @param int $id 规格id
     */
    public function afterSave($id){
        $model = Db::name("spec_item"); // 实例化User对象
        $post_items = explode(PHP_EOL, input('items'));
        foreach($post_items as $key => $val){//去除空格
            $val = str_replace('_', '', $val); //替换特殊字符
            $val = str_replace('@', '', $val); //替换特殊字符
            
            $val = trim($val);
            if(empty($val)){ 
                unset($post_items[$key]);
            }else{
                $post_items[$key] = $val;
			}
        }
        $db_items = $model->where('spec_id',$id)->column('id,item');
        //两边 比较两次
        /*提交过来的 跟数据库中比较 不存在 插入*/
        $dataList=[];
        foreach($post_items as $key => $val){
            if(!in_array($val, $db_items))            
                $dataList[] = ['spec_id'=>$id,'item'=>$val];            
        }
        //批量添加数据
        $dataList && $model->insertAll($dataList);
        
        /*数据库中的 跟提交过来的比较 不存在删除*/
        foreach($db_items as $key => $val){
            if(!in_array($val, $post_items)){       
                Db::name("spec_goods_price")->where("`key` REGEXP '^{$key}_' OR `key` REGEXP '_{$key}_' OR `key` REGEXP '_{$key}$' or `key` = '{$key}'")->delete(); //删除规格项价格表
                Db::name("spec_item")->where('id',$key)->delete(); // 删除规格项
            }
        }        
    }    
}
