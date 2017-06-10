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
	
	/**
	 * 列出地区所有父级(顺序输出)
	 * @param Array $data      //数据库里获取的结果集
	 * @param Int $pcode     //上级pcode
	 */
	public function areaParents($code=0){
		if(!isset($code)) return false;
		$arr=[];
		
		$arr[]=$code;
		$pcode=Db::name('areas')->where(['code'=>$code])->value('pcode');
		if($pcode>0){
			$arr=array_merge($this->areaParents($pcode),$arr);
		}
		return $arr;
	}
	//获地区JSON
	public function getRegionJson(){
		/*
		$data = file_get_contents('http://qhd.68dsw.com/site/region-list.html?region_code=52,26,01');
		$arr = json_decode($data,1);//转换为PHP数组
		dump($arr);die;
		*/
		$level_names=['0'=>'国家','1'=>'省','2'=>'市','3'=>'区/县','4'=>'镇'];
		$data=['code'=>-1,'data'=>[],'message'=>'','level_names'=>$level_names,'region_names'=>[]];
		
		$code=input('region_code')!=''?input('region_code'):0;
		if(input('parent_code')!=''){
			$code=input('parent_code');
		}
		//顶级和所有上级code组
		$code_arr=$this->areaParents($code);
		if($code_arr[0]!=0){
			@array_unshift($code_arr,'0');
		}
		
		//列出地区
		$region_data=$region_names=[];
		//只显示三级
		$code_arr_count=count($code_arr);
		if($code_arr_count>3){
			$code_arr_count=$code_arr_count-1;
		}
		for($i=0;$i<$code_arr_count;$i++){
			$region = Db::name('areas')->where(['status'=>1,'pcode'=>$code_arr[$i]])->order('sort')->select();
			if(count($region)>0){
				$data['code']=0;
				foreach($region as $k=>$v){
					$region_data[$i][$k]['region_id']=$v['id'];
					$region_data[$i][$k]['region_name']=$v['name'];
					$region_data[$i][$k]['region_code']=$v['code'];
					$region_data[$i][$k]['parent_code']=$v['pcode'];
					$region_data[$i][$k]['sort']=$v['sort'];
					$region_data[$i][$k]['level']=$v['level'];
					
					if($v['code']==$code_arr[$i+1]){
						$region_names[$v['code']]=$v['name'];
					}
				}
			}
		}
		$data['message']='';
		$data['data']=$region_data;
		$data['region_names']=$region_names;
		
		$data_json = json_encode($data,JSON_UNESCAPED_UNICODE);
		
		//dump($code_arr);die;
		return $data;
		
	}

	
}
?>