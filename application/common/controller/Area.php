<?php
/*
 * @name  地区API
 * @time on 2016/12/11
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\common\controller;
use think\Controller;
use \think\Db;
class Area extends Controller{
	//Ajax获取地区数据
	public function ajaxArea(){
		$pcode=input('pcode');
		$map=[];
		$map['status']=1;
		$map['pcode']=$pcode;
		$lists=db('areas')->where($map)->order('sort')->select();
		if(count($lists)>0){
			$data['list']=$lists;
			$data['status']=1;
		}else{
			$data['list']='';
			$data['status']=0;
		}
		return $data;
	}
	
	//Ajax获取地区下拉筐 ID
    public function getAreas(){
    	$pid=input('pid');
		$selected=input('selected');
    	$data = Db::name('areas')->where(['status'=>1,'pid'=>$pid])->field('id,pid,name,sort,status')->order('sort')->select();
		
    	$html = '';
    	if($data){
    		foreach($data as $v){
    			if($v['id'] == $selected){
            		$html .= "<option value='{$v['id']}' selected>{$v['name']}</option>";
            	}
    			$html .= "<option value='{$v['id']}'>{$v['name']}</option>";
    		}
    	}
    	if(empty($html)){
    		echo '0';
    	}else{
    		echo $html;
    	}
    }
	
	//Ajax获取地区下拉筐 code
    public function getAreasCode(){
    	$pcode=input('pcode');
		$selected=input('selected');
    	$data = Db::name('areas')->where(['status'=>1,'pcode'=>$pcode])->order('sort')->select();
    	$html = '';
    	if($data){
    		foreach($data as $v){
    			if($v['code'] == $selected){
            		$html .= "<option value='{$v['code']}' selected>{$v['name']}</option>";
            	}
    			$html .= "<option value='{$v['code']}'>{$v['name']}</option>";
    		}
    	}
    	if(empty($html)){
    		echo '0';
    	}else{
    		echo $html;
    	}
    }
}
?>